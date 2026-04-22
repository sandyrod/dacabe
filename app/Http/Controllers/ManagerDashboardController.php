<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Pedido;
use App\Models\Vendedor;
use App\Models\Pago;
use App\Models\OrderInven;

class ManagerDashboardController extends Controller
{
    public function index()
    {
        // Totales y estadísticas para el dashboard
        $pendingOrdersCount = Pedido::where('estatus', 'PENDIENTE')->count();
        $approvedOrdersCount = Pedido::where('estatus', 'APROBADO')->count();
        
        $createdOrdersCount = Pedido::whereDate('created_at', today())->count(); // Pedidos de hoy
        $totalOrdersCount = $pendingOrdersCount + $approvedOrdersCount + $createdOrdersCount;
        
        // Pagos
        $pendingPaymentsCount = Pago::where('estatus', 'EN REVISION')->count();
        $approvedPaymentsCount = Pago::where('estatus', 'APROBADO')->count();
        $totalPaymentsCount = $pendingPaymentsCount + $approvedPaymentsCount;

        // Comisiones
        $pendingCommissions = \App\Models\ComisionVendedor::where('estatus_comision', 'pendiente')->sum('monto_comision');
        $paidCommissions = \App\Models\ComisionVendedor::where('estatus_comision', 'pagada')->sum('monto_comision');
        $totalCommissions = $pendingCommissions + $paidCommissions;

        $sellersCount = Vendedor::count();
        $productsCount = OrderInven::count();

        // Pedidos sin cliente asignado (codcli nulo o vacío)
        $pedidosSinCruceCount = DB::connection('company')->table('pedidos')
            ->where(function ($q) {
                $q->whereNull('codcli')
                  ->orWhere('codcli', '')
                  ->orWhere('codcli', '0');
            })
            ->count();

        // Retenciones de IVA pendientes
        $retencionesPendientesCount = Pedido::where('porc_retencion', '>', 0)
            ->where('saldo_iva_bs', '>', 0)
            ->where('estatus', '!=', 'CANCELADO')
            ->count();

        $retencionesPendientesBs = Pedido::where('porc_retencion', '>', 0)
            ->where('saldo_iva_bs', '>', 0)
            ->where('estatus', '!=', 'CANCELADO')
            ->sum('saldo_iva_bs');

        return view('dashboards.manager', compact(
            'pendingOrdersCount',
            'approvedOrdersCount',
            'createdOrdersCount',
            'totalOrdersCount',
            'pendingPaymentsCount',
            'approvedPaymentsCount',
            'totalPaymentsCount',
            'pendingCommissions',
            'paidCommissions',
            'totalCommissions',
            'sellersCount',
            'productsCount',
            'pedidosSinCruceCount',
            'retencionesPendientesCount',
            'retencionesPendientesBs'
        ));
    }
}
