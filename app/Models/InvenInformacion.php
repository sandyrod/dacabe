<?php

namespace App\Models;

use Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Model;

class InvenInformacion extends Model
{
    protected $connection = 'company';
    protected $table = 'inven_informacion';

    protected $fillable = ['codigo', 'detalle', 'observaciones', 'stock_minimo', 'descuento', 'comision'];

    public function saveDetails($codigo, $data)
    {
        $model = $this->where('codigo', $codigo)->first();
        if (!$model){
            $model= new InvenInformacion();
        }
        $model->codigo = $codigo;
        $model->detalle = $data->detalle ?? ' ';
        $model->observaciones = $data->observaciones;
        $model->stock_minimo = $data->stock_minimo;
        $model->descuento = $data->descuento;
        $model->comision = $data->comision;
        $model->save();
    }

    public function getData($id = null)
    {
        if ($id) {
            return $this->where('id', $id)->first();
        }
        return $this->get();
    }

    public function getReportConfig()
    {
        return [
            'title' => 'Listado de Información', 
            'company' => Auth::user()->company
        ];
    }

}
