<?php

namespace App\Models;

use App\Models\Company;
use Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Model;
use App\Models\Pedido;

class PagoPedido extends Model
{
    protected $connection = 'company';
    protected $table = 'pagos_pedidos';
    protected $fillable = ['pago_id', 'monto', 'pedido_id', 'iva', 'retencion', 'descuento', 'ajustes_monto'];
     
    public function getData($id = null)
    {
        if ($id) {
            return $this->where('id', $id)->first();
        }
        return $this->get();
    }

    public function pago()
    {
        return $this->belongsTo(Pago::class);
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function getReportConfig()
    {
        return [
            'title' => 'Listado de Pagos', 
            'company' => Auth::user()->company
        ];
    }

}
