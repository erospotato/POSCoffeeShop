<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCashierShiftOpen
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Admin and Manager can view POS or assist without strict shift restriction
        if ($user && ($user->isAdmin() || $user->isManager())) {
            return $next($request);
        }

        if ($user && $user->isCashier()) {
            $shift = $user->activeShift();

            if (!$shift) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You must open a cashier shift before processing transactions.',
                        'requires_shift' => true,
                    ], 422);
                }

                return redirect()->route('shifts.index')->with('warning', 'Please open a shift before accessing POS checkout.');
            }
        }

        return $next($request);
    }
}
