<?php

namespace App\Models;

use App\Models\Company;
use Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Model;

class PagoGrupo extends Model
{
    protected $connection = 'company';
    protected $table = 'pago_grupos';
    protected $fillable = [
        'moneda_pago',
        'fecha_pago',
        'user_id',
        'seller_id'
    ];
     
    public function getData($id = null)
    {
        if ($id) {
            return $this->where('id', $id)->first();
        }
        return $this->get();
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class)->with('pago_pedidos', 'tipo_pago', 'banco');
    }

    public function archivos()
    {
        return $this->hasMany(PagoArchivo::class);
    }
    
    public function tipo_pago()
    {
        return $this->belongsTo(OrderTpago::class, 'tpago_id', 'CPAGO');
    }

    public function banco()
    {
        return $this->belongsTo(Bank::class, 'banco_codigo', 'codigo');
    }
    
    public function pago_destino()
    {
        return $this->belongsTo(PagoDestino::class, 'pago_destino_id', 'id');
    }

    public function getReportConfig()
    {
        return [
            'title' => 'Listado de Pagos', 
            'company' => Auth::user()->company
        ];
    }

}
