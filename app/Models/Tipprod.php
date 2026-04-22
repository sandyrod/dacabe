<?php

namespace App\Models;

use App\Models\Company;
use Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Model;

class Tipprod extends Model
{
    protected $connection = 'company';
    protected $table = 'TIPPROD';
    public $timestamps = false;
    
     
    public function createNew($request)
    {
        /*
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

        return $secondaryConnection->table('TIPPROD')->insert([
            'CTIPPROD' => $request->CTIPPROD,
            'DTIPPROD' => $request->DTIPPROD
        ]);
        */
        return $this->insert([
            'CTIPPROD' => $request->CTIPPROD,
            'DTIPPROD' => $request->DTIPPROD
        ]);
    }

    public function deleteRecord($code)
    {
        /*
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

        return $secondaryConnection->table('TIPPROD')->where('CTIPPROD', $code)->delete();
        */
        return $this->where('CTIPPROD', $code)->delete();
    }

    public function getData($code = null)
    {
        /*
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
            return $secondaryConnection->table('TIPPROD')->where('CTIPPROD', $code)->first();
        }
        return $secondaryConnection->table('TIPPROD')->get();
        */

        return $this->get();
    }

    public function updateItem($code, $request)
    {
        /*
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

        return $secondaryConnection->table('TIPPROD')->where('CTIPPROD', $code)->update([
            'CTIPPROD' => $request->CTIPPROD,
            'DTIPPROD' => $request->DTIPPROD
        ]);
        */

        return $this->where('CTIPPROD', $code)->update([
            'CTIPPROD' => $request->CTIPPROD,
            'DTIPPROD' => $request->DTIPPROD
        ]);
    }

    public function getReportConfig()
    {
        return [
            'title' => 'Listado de Tipo de Producto', 
            'company' => Auth::user()->company
        ];
    }

}
