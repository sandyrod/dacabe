<?php

namespace App\Http\Controllers\Vendedor;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardLogrosController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $filtros = $this->resolverFiltros($request);
        $vendedor = $this->resolverVendedor($user->email);

        $data = $this->buildData((int) $user->id, (string) $user->email, $vendedor, $filtros);

        return view('vendedor.dashboard_logros', [
            'filtros' => $filtros,
            'vendedor' => $vendedor,
            'resumen' => $data['resumen'],
            'periodos' => $data['periodos'],
            'pagosResumen' => $data['pagosResumen'],
            'comisionesResumen' => $data['comisionesResumen'],
            'metaActual' => $data['metaActual'],
            'metaTableDisponible' => $data['metaTableDisponible'],
            'metaVentasRing' => $data['metaVentasRing'],
        ]);
    }

    private function resolverFiltros(Request $request): array
    {
        $periodo = in_array($request->get('periodo', 'mes'), ['mes', 'trimestre', 'semestre', 'anual'], true)
            ? $request->get('periodo', 'mes')
            : 'mes';

        $fechaDesde = $request->filled('fecha_desde')
            ? Carbon::parse($request->fecha_desde)->format('Y-m-d')
            : now()->subMonths(11)->startOfMonth()->format('Y-m-d');

        $fechaHasta = $request->filled('fecha_hasta')
            ? Carbon::parse($request->fecha_hasta)->format('Y-m-d')
            : now()->format('Y-m-d');

        if ($fechaDesde > $fechaHasta) {
            $tmp = $fechaDesde;
            $fechaDesde = $fechaHasta;
            $fechaHasta = $tmp;
        }

        $periodoObjetivo = strtoupper(trim((string) $request->get('periodo_objetivo', '')));
        if ($periodoObjetivo === '') {
            $periodoObjetivo = $this->periodKeyFromDate($periodo, $fechaHasta);
        }

        return [
            'periodo' => $periodo,
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta,
            'periodo_objetivo' => $periodoObjetivo,
        ];
    }

    private function resolverVendedor(string $email)
    {
        return DB::connection('company')
            ->table('vendedores')
            ->select('id', 'codigo', 'email', 'estatus')
            ->whereRaw('LOWER(TRIM(COALESCE(email, ""))) = ?', [strtolower(trim($email))])
            ->first();
    }

    private function buildData(int $userId, string $email, $vendedor, array $filtros): array
    {
        $periodoExpr = $this->periodExpression($filtros['periodo']);
        $sellerCode = strtoupper(trim((string) optional($vendedor)->codigo));

        $totalesPorPedido = DB::connection('company')
            ->table('pedido_detalle as pd')
            ->selectRaw('pd.pedido_id')
            ->selectRaw('COALESCE(SUM(pd.cantidad), 0) as unidades')
            ->selectRaw('COALESCE(SUM(pd.cantidad * pd.precio_dolar), 0) as ventas_usd')
            ->groupBy('pd.pedido_id');

        $pagosPorPedido = DB::connection('company')
            ->table('pagos_pedidos as pp')
            ->join('pagos as pg', 'pg.id', '=', 'pp.pago_id')
            ->selectRaw('pp.pedido_id')
            ->selectRaw('COALESCE(SUM(CASE WHEN UPPER(COALESCE(pg.estatus, "")) = "APROBADO" THEN pp.monto ELSE 0 END), 0) as pagos_aprobados_usd')
            ->selectRaw('MAX(CASE WHEN UPPER(COALESCE(pg.estatus, "")) = "APROBADO" THEN 1 ELSE 0 END) as tiene_pago_aprobado')
            ->groupBy('pp.pedido_id');

        $pedidoDetalle = DB::connection('company')
            ->table('pedidos as p')
            ->leftJoinSub($totalesPorPedido, 'tp', function ($join) {
                $join->on('tp.pedido_id', '=', 'p.id');
            })
            ->leftJoinSub($pagosPorPedido, 'pp', function ($join) {
                $join->on('pp.pedido_id', '=', 'p.id');
            })
            ->where(function ($query) use ($userId, $sellerCode) {
                $query->where('p.user_id', $userId);

                if ($sellerCode !== '') {
                    $query->orWhereRaw('UPPER(TRIM(COALESCE(p.seller_code, ""))) = ?', [$sellerCode]);
                }
            })
            ->whereDate(DB::raw('COALESCE(p.fecha, p.created_at)'), '>=', $filtros['fecha_desde'])
            ->whereDate(DB::raw('COALESCE(p.fecha, p.created_at)'), '<=', $filtros['fecha_hasta'])
            ->whereRaw('UPPER(COALESCE(p.estatus, "")) NOT IN ("CARGANDO", "ANULADO", "CANCELADO")')
            ->selectRaw('p.id as pedido_id')
            ->selectRaw($periodoExpr . ' as periodo_key')
            ->selectRaw('COALESCE(tp.unidades, 0) as unidades')
            ->selectRaw('COALESCE(tp.ventas_usd, 0) as ventas_usd')
            ->selectRaw('COALESCE(pp.pagos_aprobados_usd, 0) as pagos_aprobados_usd')
            ->selectRaw('CASE WHEN UPPER(COALESCE(p.estatus, "")) = "APROBADO" THEN 1 ELSE 0 END as es_aprobado')
            ->selectRaw('CASE WHEN UPPER(COALESCE(p.estatus, "")) = "RECHAZADO" THEN 1 ELSE 0 END as es_rechazado')
            ->selectRaw('CASE WHEN COALESCE(pp.tiene_pago_aprobado, 0) > 0 THEN 1 ELSE 0 END as es_pagado')
            ->orderBy('periodo_key')
            ->get();

        $periodos = $pedidoDetalle
            ->groupBy('periodo_key')
            ->map(function ($items, $periodoKey) use ($filtros) {
                $ventas = (float) $items->sum('ventas_usd');
                $aprobados = (int) $items->sum('es_aprobado');
                $pagados = (int) $items->sum('es_pagado');

                return (object) [
                    'periodo_key' => $periodoKey,
                    'etiqueta' => $this->formatPeriodLabel($periodoKey, $filtros['periodo']),
                    'pedidos_total' => (int) $items->count(),
                    'pedidos_aprobados' => $aprobados,
                    'pedidos_pagados' => $pagados,
                    'ventas_usd' => $ventas,
                    'pagos_aprobados_usd' => (float) $items->sum('pagos_aprobados_usd'),
                    'unidades' => (float) $items->sum('unidades'),
                    'cobertura_pedidos_pct' => $this->calcularCobertura($aprobados, $pagados),
                    'logro_monto_pct' => $ventas > 0 ? round((((float) $items->sum('pagos_aprobados_usd')) / $ventas) * 100, 2) : 0,
                ];
            })
            ->sortBy('periodo_key')
            ->values();

        $resumen = [
            'pedidos_total' => (int) $pedidoDetalle->count(),
            'pedidos_aprobados' => (int) $pedidoDetalle->sum('es_aprobado'),
            'pedidos_rechazados' => (int) $pedidoDetalle->sum('es_rechazado'),
            'pedidos_pagados' => (int) $pedidoDetalle->sum('es_pagado'),
            'ventas_usd' => (float) $pedidoDetalle->sum('ventas_usd'),
            'pagos_aprobados_usd' => (float) $pedidoDetalle->sum('pagos_aprobados_usd'),
            'unidades' => (float) $pedidoDetalle->sum('unidades'),
        ];

        $resumen['cobertura_pedidos_pct'] = $this->calcularCobertura($resumen['pedidos_aprobados'], $resumen['pedidos_pagados']);
        $resumen['cobertura_monto_pct'] = $resumen['ventas_usd'] > 0
            ? round(($resumen['pagos_aprobados_usd'] / $resumen['ventas_usd']) * 100, 2)
            : 0;

        $pedidosDecididos = $resumen['pedidos_aprobados'] + $resumen['pedidos_rechazados'];
        $resumen['pedidos_aprobacion_pct'] = $pedidosDecididos > 0
            ? round(($resumen['pedidos_aprobados'] / $pedidosDecididos) * 100, 2)
            : null;

        $resumen['clientes_asignados'] = DB::connection('company')
            ->table('cliente_vendedor')
            ->whereRaw('LOWER(TRIM(email_vendedor)) = ?', [strtolower(trim($email))])
            ->distinct()
            ->count('rif');

        $resumen['clientes_activos'] = DB::connection('company')
            ->table('pedidos as p')
            ->where(function ($query) use ($userId, $sellerCode) {
                $query->where('p.user_id', $userId);

                if ($sellerCode !== '') {
                    $query->orWhereRaw('UPPER(TRIM(COALESCE(p.seller_code, ""))) = ?', [$sellerCode]);
                }
            })
            ->whereDate(DB::raw('COALESCE(p.fecha, p.created_at)'), '>=', $filtros['fecha_desde'])
            ->whereDate(DB::raw('COALESCE(p.fecha, p.created_at)'), '<=', $filtros['fecha_hasta'])
            ->whereRaw('UPPER(COALESCE(p.estatus, "")) NOT IN ("CARGANDO", "ANULADO", "CANCELADO")')
            ->whereRaw('TRIM(COALESCE(p.rif, "")) <> ""')
            ->distinct()
            ->count('p.rif');

        $resumen['clientes_inactivos'] = max(0, $resumen['clientes_asignados'] - $resumen['clientes_activos']);
        $resumen['clientes_activados_pct'] = $resumen['clientes_asignados'] > 0
            ? round(($resumen['clientes_activos'] / $resumen['clientes_asignados']) * 100, 2)
            : null;

        $pagosResumen = DB::connection('company')
            ->table('pagos')
            ->where(function ($query) use ($userId, $vendedor) {
                $query->where('user_id', $userId);

                if ($vendedor) {
                    $query->orWhere('seller_id', (int) $vendedor->id);
                }
            })
            ->whereDate(DB::raw('COALESCE(fecha, created_at)'), '>=', $filtros['fecha_desde'])
            ->whereDate(DB::raw('COALESCE(fecha, created_at)'), '<=', $filtros['fecha_hasta'])
            ->selectRaw('COUNT(*) as pagos_total')
            ->selectRaw('SUM(CASE WHEN UPPER(COALESCE(estatus, "")) = "APROBADO" THEN 1 ELSE 0 END) as pagos_aprobados')
            ->selectRaw('SUM(CASE WHEN UPPER(COALESCE(estatus, "")) IN ("EN REVISION", "REVISION") THEN 1 ELSE 0 END) as pagos_revision')
            ->selectRaw('COALESCE(SUM(CASE WHEN UPPER(COALESCE(estatus, "")) = "APROBADO" THEN monto ELSE 0 END), 0) as monto_aprobado_usd')
            ->first();

        $comisionesResumen = DB::connection('company')
            ->table('comision_vendedores')
            ->whereRaw('LOWER(TRIM(COALESCE(correo_vendedor, ""))) = ?', [strtolower(trim($email))])
            ->whereDate(DB::raw('COALESCE(created_at, NOW())'), '>=', $filtros['fecha_desde'])
            ->whereDate(DB::raw('COALESCE(created_at, NOW())'), '<=', $filtros['fecha_hasta'])
            ->selectRaw('COUNT(*) as registros')
            ->selectRaw('COALESCE(SUM(monto_comision), 0) as comision_total_usd')
            ->selectRaw('COALESCE(SUM(CASE WHEN LOWER(COALESCE(estatus_comision, "")) = "pagada" THEN monto_comision ELSE 0 END), 0) as comision_pagada_usd')
            ->selectRaw('COALESCE(SUM(CASE WHEN LOWER(COALESCE(estatus_comision, "")) IN ("pendiente", "en revision") THEN monto_comision ELSE 0 END), 0) as comision_pendiente_usd')
            ->first();

        $metaTableDisponible = Schema::connection('company')->hasTable('metas_vendedores_periodo');
        $metaActual = null;

        if ($metaTableDisponible && $vendedor) {
            $metaActual = DB::connection('company')
                ->table('metas_vendedores_periodo')
                ->where('vendedor_id', $vendedor->id)
                ->where('periodo_tipo', $filtros['periodo'])
                ->where('periodo_key', $filtros['periodo_objetivo'])
                ->first();

            $metasPorPeriodo = DB::connection('company')
                ->table('metas_vendedores_periodo')
                ->where('vendedor_id', $vendedor->id)
                ->where('periodo_tipo', $filtros['periodo'])
                ->whereIn('periodo_key', $periodos->pluck('periodo_key')->values())
                ->get()
                ->keyBy('periodo_key');

            $periodos = $periodos->map(function ($periodo) use ($metasPorPeriodo) {
                $meta = $metasPorPeriodo->get($periodo->periodo_key);
                $periodo->meta_ventas_usd = (float) optional($meta)->meta_ventas_usd;
                $periodo->meta_pedidos_aprobados = (int) optional($meta)->meta_pedidos_aprobados;
                $periodo->meta_pedidos_pagados = (int) optional($meta)->meta_pedidos_pagados;
                $periodo->meta_cobertura_pct = (float) optional($meta)->meta_logro_pedidos_pct;
                $periodo->logro_vs_meta_ventas_pct = $periodo->meta_ventas_usd > 0
                    ? round(($periodo->ventas_usd / $periodo->meta_ventas_usd) * 100, 2)
                    : null;

                return $periodo;
            })->values();
        }

        $periodoObjetivoData = $periodos->firstWhere('periodo_key', $filtros['periodo_objetivo']);
        if (!$periodoObjetivoData) {
            $periodoObjetivoData = $periodos->last();
        }

        if ($metaActual && $periodoObjetivoData) {
            $metaActual->logro_ventas_pct = (float) $metaActual->meta_ventas_usd > 0
                ? round((((float) $periodoObjetivoData->ventas_usd) / ((float) $metaActual->meta_ventas_usd)) * 100, 2)
                : null;
            $metaActual->logro_aprobados_pct = (int) $metaActual->meta_pedidos_aprobados > 0
                ? round((((int) $periodoObjetivoData->pedidos_aprobados) / ((int) $metaActual->meta_pedidos_aprobados)) * 100, 2)
                : null;
            $metaActual->logro_pagados_pct = (int) $metaActual->meta_pedidos_pagados > 0
                ? round((((int) $periodoObjetivoData->pedidos_pagados) / ((int) $metaActual->meta_pedidos_pagados)) * 100, 2)
                : null;
            $metaActual->logro_cobertura_pct = (float) $metaActual->meta_logro_pedidos_pct > 0
                ? round((((float) $periodoObjetivoData->cobertura_pedidos_pct) / ((float) $metaActual->meta_logro_pedidos_pct)) * 100, 2)
                : null;
        }

        $metaVentasRing = [
            'disponible' => (bool) ($metaActual && (float) $metaActual->meta_ventas_usd > 0),
            'pct' => $metaActual->logro_ventas_pct ?? null,
            'ventas_alcanzadas' => (float) optional($periodoObjetivoData)->ventas_usd,
            'meta_ventas' => (float) optional($metaActual)->meta_ventas_usd,
        ];

        return [
            'resumen' => $resumen,
            'periodos' => $periodos,
            'pagosResumen' => $pagosResumen,
            'comisionesResumen' => $comisionesResumen,
            'metaActual' => $metaActual,
            'metaTableDisponible' => $metaTableDisponible,
            'metaVentasRing' => $metaVentasRing,
        ];
    }

    private function calcularCobertura(int $aprobados, int $pagados): float
    {
        $base = $aprobados + $pagados;
        if ($base <= 0) {
            return 0;
        }

        return round(($pagados / $base) * 100, 2);
    }

    private function periodExpression(string $periodo): string
    {
        $fecha = 'COALESCE(p.fecha, p.created_at)';

        if ($periodo === 'trimestre') {
            return 'CONCAT(YEAR(' . $fecha . '), "-T", QUARTER(' . $fecha . '))';
        }

        if ($periodo === 'semestre') {
            return 'CONCAT(YEAR(' . $fecha . '), "-S", IF(QUARTER(' . $fecha . ') <= 2, 1, 2))';
        }

        if ($periodo === 'anual') {
            return 'DATE_FORMAT(' . $fecha . ', "%Y")';
        }

        return 'DATE_FORMAT(' . $fecha . ', "%Y-%m")';
    }

    private function periodKeyFromDate(string $periodo, string $fecha): string
    {
        $date = Carbon::parse($fecha);

        if ($periodo === 'trimestre') {
            return $date->format('Y') . '-T' . (int) ceil($date->month / 3);
        }

        if ($periodo === 'semestre') {
            return $date->format('Y') . '-S' . ($date->month <= 6 ? 1 : 2);
        }

        if ($periodo === 'anual') {
            return $date->format('Y');
        }

        return $date->format('Y-m');
    }

    private function formatPeriodLabel(string $periodoKey, string $periodo): string
    {
        if ($periodo === 'trimestre' && preg_match('/^(\d{4})-T([1-4])$/', $periodoKey, $match)) {
            return 'T' . $match[2] . ' ' . $match[1];
        }

        if ($periodo === 'semestre' && preg_match('/^(\d{4})-S([12])$/', $periodoKey, $match)) {
            return 'S' . $match[2] . ' ' . $match[1];
        }

        if ($periodo === 'anual') {
            return $periodoKey;
        }

        try {
            return ucfirst(Carbon::createFromFormat('Y-m', $periodoKey)->locale('es')->translatedFormat('M Y'));
        } catch (\Throwable $e) {
            return $periodoKey;
        }
    }
}
