<?php

namespace App\Models;

use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Model;

class Descuento extends Model
{
    protected $connection = 'company';
    protected $table = 'descuentos';

    protected $fillable = [
        'nombre',
        'porcentaje',
        'dias_despacho',
        'promocion'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function getData($descuento_id = null)
    {
        if ($descuento_id) {
            return $this->where('id', $descuento_id)->first();
        }

        return $this->orderBy('created_at', 'desc')->get();
    }
    
    public function getReportConfig()
    {
        return [
            'title' => 'Listado de Descuentos', 
            'company' => Auth::user()->company
        ];
    }

}
