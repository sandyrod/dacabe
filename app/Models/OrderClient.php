<?php

namespace App\Models;

use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Model;

class OrderClient extends Model
{
    protected $connection = 'company';
    protected $table = 'CLIENTE';
    public $timestamps = false;
    
     
    public function createNew($request)
    {
        /*
        return $this->insert([
            'seller_code' => $request->seller_code,
            'rif' => $request->rif,
            'name' => $request->name,
            'last_name' => $request->last_name,
            'address' => $request->address,
            'phone' => $request->phone,
            'cellphone' => $request->cellphone,
            'status' => 1
        ]);
        */
    }

    public function deleteRecord($id)
    {
        //return $this->where('id', $id)->delete();
    }

    public function getData($id = null, $emailVendedor = null)
    {
        $query = $this->select('NOMBRE', 'TELEFONO', 'EMAIL', 'RIF', 'CLIAGERET', 'CODCLI');

        if ($id) {
            return $query->where('id', $id)->first();
        }

        if ($emailVendedor) {
            // Filtrar clientes asociados al vendedor
            $rifsAsociados = \App\Models\ClienteVendedor::whereRaw('LOWER(email_vendedor) = ?', [strtolower($emailVendedor)])->pluck('rif');
            $query->whereIn('RIF', $rifsAsociados);
        }

        return $query->orderBy('NOMBRE')->get();
    }

    public function getEmailData()
    {
        return $this->select('NOMBRE', 'EMAIL', 'RIF')->where('EMAIL', '!=', '')->orderBy('NOMBRE')->get();
    }

    public function updateItem($id, $request)
    {
        /*
        return $this->where('id', $id)->update([
            'seller_code' => $request->seller_code,
            'rif' => $request->rif,
            'name' => $request->name,
            'last_name' => $request->last_name,
            'address' => $request->address,
            'phone' => $request->phone,
            'cellphone' => $request->cellphone
        ]);
        */
    }

    public function getReportConfig()
    {
        return [
            'title' => 'Listado de Clientes', 
            'company' => Auth::user()->company
        ];
    }

}
