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

        $mainDb = DB::connection('mysql')->getDatabaseName();
        $recentOrders = DB::connection('company')
            ->table('pedidos as p')
            ->leftJoin(DB::raw("{$mainDb}.users as u"), 'u.id', '=', 'p.user_id')
            ->select('p.id', 'p.created_at', 'p.descripcion', 'p.rif', 'p.observations', 'u.name as seller_name', 'u.last_name as seller_last_name')
            ->orderByDesc('p.created_at')
            ->limit(8)
            ->get();

        $recentPayments = Pago::with(['user', 'tipo_pago', 'pago_destino', 'pago_pedidos.pedido'])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        $recentRetentions = DB::connection('company')
            ->table('pedidos as p')
            ->leftJoin(DB::raw("{$mainDb}.users as u"), 'u.id', '=', 'p.user_id')
            ->whereNotNull('p.comprobante_retencion')
            ->whereNotNull('p.comprobante_retencion_at')
            ->select('p.id', 'p.comprobante_retencion_at', 'p.descripcion', 'p.rif', 'u.name as seller_name', 'u.last_name as seller_last_name')
            ->orderByDesc('p.comprobante_retencion_at')
            ->limit(8)
            ->get();

        $recentActivity = collect();

        foreach ($recentOrders as $order) {
            $recentActivity->push((object) [
                'type' => 'order',
                'occurred_at' => \Carbon\Carbon::parse($order->created_at),
                'seller' => trim(($order->seller_name ?? '') . ' ' . ($order->seller_last_name ?? '')) ?: 'Un vendedor',
                'client' => $order->descripcion ?: $order->rif ?: 'cliente sin identificar',
                'observations' => $order->observations,
                'order_id' => $order->id,
            ]);
        }

        foreach ($recentPayments as $payment) {
            $orders = $payment->pago_pedidos->pluck('pedido')->filter();
            $user = $payment->user;
            $recentActivity->push((object) [
                'type' => 'payment',
                'occurred_at' => \Carbon\Carbon::parse($payment->created_at),
                'seller' => $user ? trim($user->name . ' ' . ($user->last_name ?? '')) : 'Un vendedor',
                'client' => optional($orders->first())->descripcion ?: 'cliente sin identificar',
                'order_ids' => $orders->pluck('id')->unique()->values(),
                'payment' => $payment,
            ]);
        }

        foreach ($recentRetentions as $retention) {
            $recentActivity->push((object) [
                'type' => 'retention',
                'occurred_at' => \Carbon\Carbon::parse($retention->comprobante_retencion_at),
                'seller' => trim(($retention->seller_name ?? '') . ' ' . ($retention->seller_last_name ?? '')) ?: 'Un vendedor',
                'client' => $retention->descripcion ?: $retention->rif ?: 'cliente sin identificar',
                'order_id' => $retention->id,
            ]);
        }

        $recentActivity = $recentActivity->sortByDesc('occurred_at')->take(12)->values();

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
            'retencionesPendientesBs',
            'recentActivity'
        ));
    }
}
