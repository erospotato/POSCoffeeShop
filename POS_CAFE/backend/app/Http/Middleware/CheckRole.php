<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->is_active) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Your staff account has been deactivated. Please contact the administrator.',
            ]);
        }

        // Administrators bypass standard role limits
        if ($user->isAdmin()) {
            return $next($request);
        }

        if (!$user->hasRole($roles)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action for your role.'
                ], 403);
            }

            abort(403, 'Unauthorized access: your role (' . ($user->role?->name ?? 'None') . ') cannot access this resource.');
        }

        return $next($request);
    }
}
