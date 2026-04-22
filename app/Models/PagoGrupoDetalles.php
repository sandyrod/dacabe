<?php

namespace App\Models;

use App\Models\Company;
use Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Model;
use App\Models\Pedido;

class PagoGrupoDetalles extends Model
{
    protected $connection = 'company';
    protected $table = 'pago_grupo_detalles';
    protected $fillable = ['pago_grupo_id', 'pedido_id', 'base', 'iva', 'retencion', 'descuento', 'total'];
     
    public function getData($id = null)
    {
        if ($id) {
            return $this->where('id', $id)->first();
        }
        return $this->get();
    }

    public function pago_grupo()
    {
        return $this->belongsTo(PagoGrupo::class);
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
