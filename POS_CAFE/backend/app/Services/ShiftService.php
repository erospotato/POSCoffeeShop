<?php

namespace App\Services;

use App\Models\CashierShift;
use App\Models\CashMovement;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class ShiftService
{
    /**
     * Open a new shift for the cashier.
     */
    public function openShift(User $user, float $openingCash, ?string $cashierStation = 'Cashier 1', ?string $shiftPeriod = 'Morning Shift'): CashierShift
    {
        return DB::transaction(function () use ($user, $openingCash, $cashierStation, $shiftPeriod) {
            $existing = CashierShift::where('user_id', $user->id)
                ->where('status', 'open')
                ->lockForUpdate()
                ->first();

            if ($existing) {
                throw new Exception("You already have an open shift (Shift #{$existing->id}) started at {$existing->opened_at->format('M d, H:i')}. Please close it before opening a new one.");
            }

            $shift = CashierShift::create([
                'user_id' => $user->id,
                'cashier_station' => $cashierStation ?: 'Cashier 1',
                'shift_period' => $shiftPeriod ?: 'Morning Shift',
                'opened_at' => now(),
                'opening_cash' => round($openingCash, 2),
                'expected_cash' => round($openingCash, 2),
                'status' => 'open',
            ]);

            AuditService::log('shift_opened', $shift, null, [
                'user' => $user->name,
                'station' => $shift->cashier_station,
                'period' => $shift->shift_period,
                'opening_cash' => $openingCash,
            ]);

            return $shift;
        });
    }

    /**
     * Add cash in or cash out movement.
     */
    public function recordCashMovement(CashierShift $shift, string $type, float $amount, string $reason, User $user): CashMovement
    {
        return DB::transaction(function () use ($shift, $type, $amount, $reason, $user) {
            $shift = CashierShift::where('id', $shift->id)->lockForUpdate()->firstOrFail();

            if ($shift->isClosed()) {
                throw new Exception("Cannot record cash movement on a closed shift.");
            }

            if ($amount <= 0) {
                throw new Exception("Movement amount must be greater than zero.");
            }

            $movement = CashMovement::create([
                'cashier_shift_id' => $shift->id,
                'user_id' => $user->id,
                'type' => $type,
                'amount' => round($amount, 2),
                'reason' => $reason,
            ]);

            $this->recalculateExpectedCash($shift);

            AuditService::log('cash_movement_recorded', $movement, null, [
                'shift_id' => $shift->id,
                'type' => $type,
                'amount' => $amount,
                'reason' => $reason,
            ]);

            return $movement;
        });
    }

    /**
     * Calculate and save expected cash balance for shift.
     */
    public function recalculateExpectedCash(CashierShift $shift): float
    {
        $opening = (float) $shift->opening_cash;

        // Cash payments made during this shift
        $cashPayments = (float) Payment::whereHas('order', function ($q) use ($shift) {
            $q->where('cashier_shift_id', $shift->id);
        })->where('payment_method', 'cash')
          ->where('status', 'successful')
          ->sum('amount');

        // Cash-ins
        $cashIn = (float) $shift->cashMovements()->where('type', 'cash_in')->sum('amount');

        // Cash-outs
        $cashOut = (float) $shift->cashMovements()->where('type', 'cash_out')->sum('amount');

        // Cash refunds
        $cashRefunds = (float) Refund::whereHas('order', function ($q) use ($shift) {
            $q->where('cashier_shift_id', $shift->id);
        })->whereHas('payment', function ($q) {
            $q->where('payment_method', 'cash');
        })->sum('amount');

        $expected = round($opening + $cashPayments + $cashIn - $cashOut - $cashRefunds, 2);

        $shift->expected_cash = $expected;
        $shift->save();

        return $expected;
    }

    /**
     * Close shift with actual counted cash.
     */
    public function closeShift(CashierShift $shift, float $actualCash, ?string $closingNotes = null): CashierShift
    {
        return DB::transaction(function () use ($shift, $actualCash, $closingNotes) {
            $shift = CashierShift::where('id', $shift->id)->lockForUpdate()->firstOrFail();

            if ($shift->isClosed()) {
                throw new Exception("Shift #{$shift->id} has already been closed.");
            }

            $expected = $this->recalculateExpectedCash($shift);
            $actual = round($actualCash, 2);
            $discrepancy = round($actual - $expected, 2);

            $shift->closed_at = now();
            $shift->actual_cash = $actual;
            $shift->discrepancy = $discrepancy;
            $shift->status = 'closed';
            $shift->closing_notes = $closingNotes;
            $shift->save();

            AuditService::log('shift_closed', $shift, null, [
                'shift_id' => $shift->id,
                'expected' => $expected,
                'actual' => $actual,
                'discrepancy' => $discrepancy,
            ]);

            return $shift;
        });
    }

    /**
     * Manager reviews shift and discrepancy.
     */
    public function reviewShift(CashierShift $shift, User $manager, ?string $reviewNotes = null): CashierShift
    {
        $shift->manager_reviewed_by = $manager->id;
        $shift->reviewed_at = now();
        $shift->review_notes = $reviewNotes;
        $shift->save();

        AuditService::log('shift_reviewed', $shift, null, [
            'reviewed_by' => $manager->name,
            'discrepancy' => $shift->discrepancy,
        ]);

        return $shift;
    }
}
