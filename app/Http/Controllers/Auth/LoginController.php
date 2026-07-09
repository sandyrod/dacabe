<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Vendedor;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\Tasa;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function authenticated(Request $request, $user)
    {
        $vendedor = Vendedor::select('id', 'estatus')
            ->whereRaw('LOWER(TRIM(COALESCE(email, ""))) = ?', [strtolower(trim((string) $user->email))])
            ->first();

        $estatusVendedor = strtoupper(trim((string) optional($vendedor)->estatus));
        if ($estatusVendedor === 'SUSPENDIDO') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                $this->username() => 'Tu usuario de vendedor se encuentra suspendido. Contacta al administrador.',
            ]);
        }

        $todayTasa = Tasa::whereDate('fecha', now())->exists();
        if (!$todayTasa) {
            session(['prompt_bcv_rate' => true]);
        }
    }
}
