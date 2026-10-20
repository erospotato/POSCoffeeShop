<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::withCount('orders');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderByDesc('total_spent')->paginate(15);
        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30', 'unique:customers,phone'],
            'email' => ['nullable', 'email', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $customer = Customer::create($data);

        AuditService::log('customer_registered', $customer);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'customer' => $customer]);
        }

        return redirect()->route('customers.index')->with('success', "Customer '{$customer->name}' registered.");
    }

    public function show(Customer $customer)
    {
        $customer->load([
            'orders' => fn($q) => $q->latest(),
            'orders.items',
            'loyaltyTransactions' => fn($q) => $q->with('order')->latest()
        ]);
        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30', Rule::unique('customers', 'phone')->ignore($customer->id)],
            'email' => ['nullable', 'email', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', false);
        $customer->update($data);

        AuditService::log('customer_updated', $customer);

        return redirect()->route('customers.index')->with('success', "Customer '{$customer->name}' updated.");
    }

    public function destroy(Customer $customer)
    {
        $name = $customer->name;
        $customer->delete();
        AuditService::log('customer_deleted', null, ['name' => $name]);

        return redirect()->route('customers.index')->with('success', "Customer '{$name}' deleted.");
    }
}
