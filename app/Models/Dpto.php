<?php

namespace App\Models;

use App\Models\Company;
use Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Model;

class Dpto extends Model
{
    protected $table = 'DPTO';
    
     
    public function createNew($request)
    {
        $company = Company::find(Auth::user()->company->id);

         Config::set('database.connections.secondary', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $company->db_name,
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]); 

        DB::purge('secondary');
        $secondaryConnection = DB::connection('secondary');

        return $secondaryConnection->table('DPTO')->insert([
            'CDPTO' => $request->CDPTO,
            'DDPTO' => $request->DDPTO
        ]);
    }

    public function deleteRecord($code)
    {
        $company = Company::find(Auth::user()->company->id);

         Config::set('database.connections.secondary', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $company->db_name,
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]); 

        DB::purge('secondary');
        $secondaryConnection = DB::connection('secondary');

        return $secondaryConnection->table('DPTO')->where('CDPTO', $code)->delete();
    }

    public function getData($code = null)
    {
        $company = Company::find(Auth::user()->company->id);

         Config::set('database.connections.secondary', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $company->db_name,
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]); 

        DB::purge('secondary');
        $secondaryConnection = DB::connection('secondary');

        if ($code) {
            return $secondaryConnection->table('DPTO')->where('CDPTO', $code)->first();
        }
        return $secondaryConnection->table('DPTO')->get();
    }

    public function updateItem($code, $request)
    {
        $company = Company::find(Auth::user()->company->id);

         Config::set('database.connections.secondary', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $company->db_name,
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]); 

        DB::purge('secondary');
        $secondaryConnection = DB::connection('secondary');

        return $secondaryConnection->table('DPTO')->where('CDPTO', $code)->update([
            'CDPTO' => $request->CDPTO,
            'DDPTO' => $request->DDPTO
        ]);
    }

    public function getReportConfig()
    {
        return [
            'title' => 'Listado de Departamentos', 
            'company' => Auth::user()->company
        ];
    }

}
