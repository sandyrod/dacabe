<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Pedido;
use App\Models\Vendedor;
use App\Models\Pago;
use App\Models\OrderInven;

class ManagerDashboardController extends Controller
{
    public function refreshSignals()
    {
        return response()->json([
            'signals' => $this->getDashboardSignals(),
            'activity_trend' => $this->getActivityTrend(),
            'updated_at' => now()->format('H:i:s'),
        ]);
    }

    public function index()
    {
        $signals = $this->getDashboardSignals();
        $pendingOrdersCount = $signals['orders']['pending'];
        $approvedOrdersCount = $signals['orders']['approved'];
        $createdOrdersCount = $signals['orders']['created_today'];
        $totalOrdersCount = $signals['orders']['total'];
        $pendingPaymentsCount = $signals['payments']['pending'];
        $approvedPaymentsCount = $signals['payments']['approved'];
        $totalPaymentsCount = $signals['payments']['total'];
        $pendingCommissions = $signals['commissions']['pending'];
        $paidCommissions = $signals['commissions']['paid'];
        $totalCommissions = $signals['commissions']['total'];

        $sellersCount = Vendedor::count();
        $productsCount = OrderInven::count();

        $pedidosSinCruceCount = $signals['unmatched_orders'];
        $retencionesPendientesCount = $signals['retentions']['count'];
        $retencionesPendientesBs = $signals['retentions']['amount'];
        $activityTrend = $this->getActivityTrend();

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
            'recentActivity',
            'activityTrend'
        ));
    }

    private function getDashboardSignals(): array
    {
        $pendingOrdersCount = Pedido::where('estatus', 'PENDIENTE')->count();
        $approvedOrdersCount = Pedido::where('estatus', 'APROBADO')->count();
        $createdOrdersCount = Pedido::whereDate('created_at', today())->count();
        $pendingPaymentsCount = Pago::where('estatus', 'EN REVISION')->count();
        $approvedPaymentsCount = Pago::where('estatus', 'APROBADO')->count();
        $pendingCommissions = \App\Models\ComisionVendedor::where('estatus_comision', 'pendiente')->sum('monto_comision');
        $paidCommissions = \App\Models\ComisionVendedor::where('estatus_comision', 'pagada')->sum('monto_comision');

        $pendingRetentions = Pedido::where('porc_retencion', '>', 0)
            ->where('saldo_iva_bs', '>', 0)
            ->where('estatus', '!=', 'CANCELADO');

        return [
            'unmatched_orders' => DB::connection('company')->table('pedidos')
                ->where(function ($query) {
                    $query->whereNull('codcli')
                        ->orWhere('codcli', '')
                        ->orWhere('codcli', '0');
                })
                ->count(),
            'orders' => [
                'pending' => $pendingOrdersCount,
                'approved' => $approvedOrdersCount,
                'created_today' => $createdOrdersCount,
                'total' => $pendingOrdersCount + $approvedOrdersCount + $createdOrdersCount,
            ],
            'payments' => [
                'pending' => $pendingPaymentsCount,
                'approved' => $approvedPaymentsCount,
                'total' => $pendingPaymentsCount + $approvedPaymentsCount,
            ],
            'commissions' => [
                'pending' => (float) $pendingCommissions,
                'paid' => (float) $paidCommissions,
                'total' => (float) ($pendingCommissions + $paidCommissions),
            ],
            'retentions' => [
                'count' => (clone $pendingRetentions)->count(),
                'amount' => (float) $pendingRetentions->sum('saldo_iva_bs'),
            ],
        ];
    }

    private function getActivityTrend()
    {
        $start = today()->subDays(6);
        $end = now();
        $groupByDate = DB::raw('DATE(created_at)');

        $orders = DB::connection('company')->table('pedidos')
            ->selectRaw('DATE(created_at) as activity_date, COUNT(*) as total')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy($groupByDate)
            ->pluck('total', 'activity_date');

        $payments = Pago::query()
            ->selectRaw('DATE(created_at) as activity_date, COUNT(*) as total')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy($groupByDate)
            ->pluck('total', 'activity_date');

        $retentions = DB::connection('company')->table('pedidos')
            ->selectRaw('DATE(comprobante_retencion_at) as activity_date, COUNT(*) as total')
            ->whereNotNull('comprobante_retencion')
            ->whereNotNull('comprobante_retencion_at')
            ->whereBetween('comprobante_retencion_at', [$start, $end])
            ->groupBy(DB::raw('DATE(comprobante_retencion_at)'))
            ->pluck('total', 'activity_date');

        return collect(range(0, 6))->map(function ($offset) use ($start, $orders, $payments, $retentions) {
            $date = $start->copy()->addDays($offset);
            $key = $date->format('Y-m-d');

            return [
                'key' => $key,
                'label' => $date->format('d/m'),
                'orders' => (int) $orders->get($key, 0),
                'payments' => (int) $payments->get($key, 0),
                'retentions' => (int) $retentions->get($key, 0),
            ];
        });
    }
}
