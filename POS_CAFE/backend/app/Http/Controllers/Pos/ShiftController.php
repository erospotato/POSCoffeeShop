<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pos\CashMovementRequest;
use App\Http\Requests\Pos\CloseShiftRequest;
use App\Http\Requests\Pos\OpenShiftRequest;
use App\Models\CashierShift;
use App\Services\ShiftService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ShiftController extends Controller
{
    public function __construct(protected ShiftService $shiftService) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $query = CashierShift::with(['user', 'manager'])->latest();

        if ($user->isCashier()) {
            $query->where('user_id', $user->id);
        }

        $shifts = $query->paginate(15);
        $activeShift = $user->activeShift();

        return view('shifts.index', compact('shifts', 'activeShift'));
    }

    public function show(CashierShift $shift, Request $request)
    {
        if (!Gate::allows('view', $shift)) {
            abort(403);
        }

        $shift->load(['user', 'manager', 'cashMovements.user', 'orders.payments']);
        $this->shiftService->recalculateExpectedCash($shift);

        return view('shifts.show', compact('shift'));
    }

    public function open(OpenShiftRequest $request)
    {
        try {
            $shift = $this->shiftService->openShift(
                $request->user(),
                (float) $request->input('opening_cash'),
                $request->input('cashier_station', 'Cashier 1'),
                $request->input('shift_period', 'Morning Shift')
            );

            return redirect()->route('pos.index')->with('success', "Shift #{$shift->id} ({$shift->cashier_station} - {$shift->shift_period}) opened with $" . number_format($shift->opening_cash, 2) . " opening cash.");
        } catch (Exception $e) {
            return back()->withErrors(['shift_error' => $e->getMessage()]);
        }
    }

    public function close(CloseShiftRequest $request, CashierShift $shift)
    {
        if (!Gate::allows('close', $shift)) {
            abort(403);
        }

        try {
            $closedShift = $this->shiftService->closeShift(
                $shift,
                (float) $request->input('actual_cash'),
                $request->input('closing_notes')
            );

            $message = "Shift #{$closedShift->id} closed. Discrepancy: $" . number_format($closedShift->discrepancy, 2);
            return redirect()->route('shifts.show', $closedShift->id)->with('success', $message);
        } catch (Exception $e) {
            return back()->withErrors(['shift_error' => $e->getMessage()]);
        }
    }

    public function cashMovement(CashMovementRequest $request, CashierShift $shift)
    {
        if (!Gate::allows('addCashMovement', $shift)) {
            abort(403);
        }

        try {
            $this->shiftService->recordCashMovement(
                $shift,
                $request->input('type'),
                (float) $request->input('amount'),
                $request->input('reason'),
                $request->user()
            );

            return back()->with('success', 'Cash movement recorded successfully.');
        } catch (Exception $e) {
            return back()->withErrors(['movement_error' => $e->getMessage()]);
        }
    }

    public function review(Request $request, CashierShift $shift)
    {
        if (!Gate::allows('review', $shift)) {
            abort(403);
        }

        $request->validate(['review_notes' => ['nullable', 'string', 'max:500']]);

        $this->shiftService->reviewShift($shift, $request->user(), $request->input('review_notes'));

        return back()->with('success', 'Shift review saved.');
    }
}
