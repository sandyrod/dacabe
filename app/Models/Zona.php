<?php

namespace App\Models;

use App\Models\Company;
use Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Model;

class Zona extends Model
{
    protected $connection = 'company';
    protected $table = 'zonas';
    public $timestamps = false;
    
     
    public function createNew($request)
    {
        return $this->insert([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion
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
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion
        ]);
    }

    public function getReportConfig()
    {
        return [
            'title' => 'Listado de Zonas', 
            'company' => Auth::user()->company
        ];
    }

}
