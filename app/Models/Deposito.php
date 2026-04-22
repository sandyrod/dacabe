<?php

namespace App\Models;

use App\Models\Company;
use Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Model;

class Deposito extends Model
{
    protected $connection = 'company';
    protected $table = 'DEPOSITO';
    public $timestamps = false;

    public function vendedores()
    {
        return $this->hasMany(VendedorDeposito::class, 'CDEPOS');
    }
    
     
    public function createNew($request)
    {
        return $this->insert([
            'CDEPOS' => $request->CDEPOS,
            'DDEPOS' => $request->DDEPOS
        ]);
    }

    public function deleteRecord($id)
    {
        return $this->where('id', $id)->delete();
    }

    public function getData($id = null)
    {
        if ($id) {
            return $this->where('id', $id)->first();
        }
        return $this->get();
    }

    public function updateItem($id, $request)
    {
        return $this->where('id', $id)->update([
            'CDEPOS' => $request->CDEPOS,
            'DDEPOS' => $request->DDEPOS
        ]);
    }

    public function getReportConfig()
    {
        return [
            'title' => 'Listado de Depósitos', 
            'company' => Auth::user()->company
        ];
    }

}
