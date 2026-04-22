<?php

namespace App\Models;

use App\Models\Company;
use Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $connection = 'company';
    protected $table = 'dacabe.pagos';
    protected $fillable = [
        'fecha',
        'monto',
        'descripcion',
        'seller_id',
        'user_id',
        'comprobante',
        'estatus',
        'tpago_id',
        'banco_codigo',
        'referencia',
        'rate',
        'monto_bs',
        'pago_destino_id',
        'moneda_pago',
        'pago_grupo_id'
    ];
     
    public function getData($id = null)
    {
        if ($id) {
            return $this->where('id', $id)->first();
        }
        return $this->get();
    }

    public function pago_pedidos()
    {
        return $this->hasMany(PagoPedido::class)->with('pedido');
    }
    
    public function tipo_pago()
    {
        return $this->belongsTo(OrderTpago::class, 'tpago_id', 'CPAGO');
    }

    public function banco()
    {
        return $this->belongsTo(OrderBanco::class, 'banco_codigo', 'CODIGO');
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

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

}
