<?php

namespace App\Models;

use App\Models\{Company, ArtDepos, Pedido, Vendedor};
use App\User;
use Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Model;

class PedidoFactura extends Model
{
    protected $connection = 'company';
    protected $table = 'pedidos_facturas';
    
    
    public function getReportConfig()
    {
        return [
            'title' => 'Listado de Pedidos', 
            'company' => Auth::user()->company
        ];
    }

}
