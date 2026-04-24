<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SetDatabaseConnection
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $company = $user->company; // Asumiendo que tienes una relación definida

            if ($company) {
                // Configurar la conexión a la base de datos de la empresa
                if (app()->environment('local') && env('DB_COMPANY_DATABASE')) {
                    $dbName = env('DB_COMPANY_DATABASE');
                } else {
                    $dbName = $company->db_name;
                }

                config(['database.connections.company.database' => $dbName]);

                // Purga la conexión anterior
                DB::purge('company');
                DB::reconnect('company');
            }
        }

        return $next($request);
    }
}
?>