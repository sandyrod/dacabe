<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class EnsureActiveRoleSet
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            if (!session()->has('active_role_id')) {
                $user = Auth::user();
                $firstRole = $user->roles()->first();
                if ($firstRole) {
                    session(['active_role_id' => $firstRole->id]);
                    session(['active_role_name' => $firstRole->display_name]);
                }
            }
        }

        return $next($request);
    }
}
