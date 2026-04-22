<?php

namespace App\Models;

use App\Models\Company;
use Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Model;

class ProductoFuturo extends Model
{
    protected $connection = 'company';
    protected $table = 'promociones';
    public $timestamps = false;

    public function createNew($request)
    {
        return $this->insert([
            'codigo' => $request->codigo,
            'promocion' => null,
            'nuevo' => null,
            'descripcion' => $request->descripcion,
            'pronto' => 1
        ]);
    }

    public function deleteRecord($code)
    {
        return $this->where('codigo', $code)->delete();
    }

    public function getData($code = null)
    {
        if ($code) {
            return $this->where('codigo', $code)->first();
        }
        return $this->where('pronto', 1)->orderBy('descripcion')->get();
    }

    public function getDataLimit($limit=5)
    {
        return $this->where('pronto', 1)->orderBy('descripcion')->get()->take($limit);
    }

    public function getProduct($code)
    {
        return $this->where('codigo', $code)->first();        
    }

    public function updateItem($code, $photo, $request)
    {
        return $this->where('codigo', $code)->update([
            'codigo' => $request->codigo,
            'promocion' => null,
            'nuevo' => null,
            'descripcion' => $request->descripcion,
            'pronto' => 1,
            'foto' => $photo
        ]);
    }

    public function getReportConfig()
    {
        return [
            'title' => 'Listado de Productos por Llegar', 
            'company' => Auth::user()->company
        ];
    }

}
