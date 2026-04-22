<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class PagoDestino extends Model
{
    protected $connection = 'company';
    protected $table = 'pago_destinos';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function getData($pago_destino_id = null)
    {
        if ($pago_destino_id) {
            return $this->where('id', $pago_destino_id)->first();
        }

        return $this->orderBy('created_at', 'desc')->get();
    }
    
    public function getReportConfig()
    {
        return [
            'title' => 'Listado de Destinos de Pago', 
            'company' => Auth::user()->company
        ];
    }
}
