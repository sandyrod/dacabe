<?php

namespace App\Http\Middleware;

use App\Models\Vendedor;
use Closure;
use Illuminate\Support\Facades\Auth;

class EnsureSellerIsActive
{
    /**
     * Bloquea el acceso funcional cuando el vendedor esta suspendido.
     */
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        if ($request->routeIs('logout')) {
            return $next($request);
        }

        $vendedor = Vendedor::select('id', 'estatus')
            ->whereRaw('LOWER(TRIM(COALESCE(email, ""))) = ?', [strtolower(trim((string) $user->email))])
            ->first();

        if (!$vendedor) {
            return $next($request);
        }

        $estatus = strtoupper(trim((string) optional($vendedor)->estatus));
        if ($estatus !== 'SUSPENDIDO') {
            return $next($request);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Tu usuario se encuentra suspendido. Contacta al administrador.'
            ], 403);
        }

        return response()->view('auth.suspended', [
            'user' => $user,
            'vendedor' => $vendedor,
        ], 403);
    }
}
