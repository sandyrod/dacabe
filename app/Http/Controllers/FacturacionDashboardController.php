<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Pedido;
use App\Models\Pago;

class FacturacionDashboardController extends Controller
{
    public function index()
    {
        // Pedidos pendientes de gestión
        $pedidosPendientesCount = Pedido::where('estatus', 'PENDIENTE')->count();

        // Pagos en revisión pendientes de aprobación
        $pagosPendientesCount = Pago::where('estatus', 'EN REVISION')->count();

        // Pedidos sin cliente asignado
        $pedidosSinCruceCount = DB::connection('company')->table('pedidos')
            ->where(function ($q) {
                $q->whereNull('codcli')
                  ->orWhere('codcli', '')
                  ->orWhere('codcli', '0');
            })
            ->count();

        // Retenciones IVA pendientes
        $retencionesPendientesCount = Pedido::where('porc_retencion', '>', 0)
            ->where('saldo_iva_bs', '>', 0)
            ->where('estatus', '!=', 'CANCELADO')
            ->count();

        $retencionesPendientesBs = Pedido::where('porc_retencion', '>', 0)
            ->where('saldo_iva_bs', '>', 0)
            ->where('estatus', '!=', 'CANCELADO')
            ->sum('saldo_iva_bs');

        return view('dashboards.facturacion', compact(
            'pedidosPendientesCount',
            'pagosPendientesCount',
            'pedidosSinCruceCount',
            'retencionesPendientesCount',
            'retencionesPendientesBs'
        ));
    }
}
