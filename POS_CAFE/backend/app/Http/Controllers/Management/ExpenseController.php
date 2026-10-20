<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Requests\Management\ExpenseRequest;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\AuditService;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with(['category', 'user', 'approver'])->latest('expense_date');

        if ($request->filled('category_id')) {
            $query->where('expense_category_id', $request->input('category_id'));
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('expense_date', [$request->input('start_date'), $request->input('end_date')]);
        }

        $expenses = $query->paginate(15);
        $categories = ExpenseCategory::all();
        $totalExpenseAmount = (float) $query->sum('amount');

        return view('expenses.index', compact('expenses', 'categories', 'totalExpenseAmount'));
    }

    public function create()
    {
        $categories = ExpenseCategory::all();
        return view('expenses.create', compact('categories'));
    }

    public function store(ExpenseRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $data['is_approved'] = $request->user()->isAdmin() || $request->user()->isManager();
        $data['approved_by'] = $data['is_approved'] ? $request->user()->id : null;

        $expense = Expense::create($data);
        AuditService::log('expense_recorded', $expense, null, ['amount' => $expense->amount, 'description' => $expense->description]);

        return redirect()->route('expenses.index')->with('success', 'Expense recorded successfully.');
    }

    public function approve(Expense $expense, Request $request)
    {
        $expense->is_approved = true;
        $expense->approved_by = $request->user()->id;
        $expense->save();

        AuditService::log('expense_approved', $expense);

        return back()->with('success', 'Expense approved.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        AuditService::log('expense_deleted');

        return redirect()->route('expenses.index')->with('success', 'Expense deleted.');
    }
}
