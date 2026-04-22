<?php

namespace App\Models;

use App\Models\Company;
use Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Model;

class OrderGrupo extends Model
{
    protected $connection = 'company';
    protected $table = 'GRUPO';
    public $timestamps = false;

    
     
    public function createNew($request)
    {
        return $this->insert([
            'CGRUPO' => $request->CGRUPO,
            'DGRUPO' => $request->DGRUPO
        ]);
    }

    public function deleteRecord($code)
    {
        return $this->where('CGRUPO', $code)->delete();
    }

    public function getData($code = null)
    {
        if ($code) {
            return $this->where('CGRUPO', $code)->orderBy('DGRUPO')->first();
        }
        return $this->orderBy('DGRUPO')->get();
    }

    public function updateItem($code, $request)
    {
        return $this->where('CGRUPO', $code)->update([
            'CGRUPO' => $request->CGRUPO,
            'DGRUPO' => $request->DGRUPO
        ]);
    }

    public function getReportConfig()
    {
        return [
            'title' => 'Listado de Grupos', 
            'company' => Auth::user()->company
        ];
    }

}
