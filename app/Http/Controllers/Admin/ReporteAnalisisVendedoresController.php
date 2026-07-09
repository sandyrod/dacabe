<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ReporteAnalisisVendedoresController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        [$vendedores, $filtros] = $this->resolverContexto($request);
        $data = $this->buildData($filtros);

        return view('admin.reportes.analisis_vendedores', [
            'vendedores' => $vendedores,
            'filtros' => $filtros,
            'resumen' => $data['resumen'],
            'periodos' => $data['periodos'],
            'rankingVendedores' => $data['rankingVendedores'],
            'topProductos' => $data['topProductos'],
            'pedidoDetalle' => $data['pedidoDetalle'],
            'metas' => $data['metas'],
            'metaTableDisponible' => $data['metaTableDisponible'],
        ]);
    }

    public function guardarMetaPeriodo(Request $request)
    {
        $usuario = Auth::user();
        $puedeEditar = $usuario
            && method_exists($usuario, 'hasRole')
            && $usuario->hasRole(['admin', 'admin_pedidos', 'gerente']);

        if (!$puedeEditar) {
            abort(403, 'Solo los administradores pueden modificar metas.');
        }

        if (!Schema::connection('company')->hasTable('metas_vendedores_periodo')) {
            return back()->with('error', 'No existe la tabla de metas manuales. Ejecuta el SQL de creacion y vuelve a intentar.');
        }

        $rules = [
            'vendedor_id' => ['required', 'integer', 'min:1', 'exists:company.vendedores,id'],
            'periodo_tipo' => ['required', Rule::in(['mes', 'trimestre', 'semestre', 'anual'])],
            'periodo_key' => ['required', 'string', 'max:10'],
            'meta_ventas_usd' => ['nullable', 'numeric', 'min:0'],
            'meta_pedidos_aprobados' => ['nullable', 'integer', 'min:0'],
            'meta_pedidos_pagados' => ['nullable', 'integer', 'min:0'],
            'meta_logro_pedidos_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];

        $validated = $request->validate($rules);

        if (!$this->isPeriodoKeyValido($validated['periodo_key'], $validated['periodo_tipo'])) {
            return back()->with('error', 'El formato del periodo no es valido para el tipo seleccionado.');
        }

        $todosNulos = is_null($validated['meta_ventas_usd'])
            && is_null($validated['meta_pedidos_aprobados'])
            && is_null($validated['meta_pedidos_pagados'])
            && is_null($validated['meta_logro_pedidos_pct']);

        if ($todosNulos) {
            return back()->with('error', 'Debes indicar al menos una meta para guardar.');
        }

        $periodoKey = strtoupper(trim($validated['periodo_key']));
        $filtro = [
            'vendedor_id' => (int) $validated['vendedor_id'],
            'periodo_tipo' => $validated['periodo_tipo'],
            'periodo_key' => $periodoKey,
        ];

        $payload = [
            'meta_ventas_usd' => is_null($validated['meta_ventas_usd']) ? null : round((float) $validated['meta_ventas_usd'], 2),
            'meta_pedidos_aprobados' => is_null($validated['meta_pedidos_aprobados']) ? null : (int) $validated['meta_pedidos_aprobados'],
            'meta_pedidos_pagados' => is_null($validated['meta_pedidos_pagados']) ? null : (int) $validated['meta_pedidos_pagados'],
            'meta_logro_pedidos_pct' => is_null($validated['meta_logro_pedidos_pct']) ? null : round((float) $validated['meta_logro_pedidos_pct'], 2),
            'actualizado_por' => (int) $usuario->id,
            'updated_at' => now(),
        ];

        $query = DB::connection('company')->table('metas_vendedores_periodo')->where($filtro);
        if ($query->exists()) {
            $query->update($payload);
        } else {
            DB::connection('company')->table('metas_vendedores_periodo')->insert(array_merge($filtro, $payload, [
                'created_at' => now(),
            ]));
        }

        return back()->with('success', 'Meta manual guardada correctamente.');
    }

    private function resolverContexto(Request $request): array
    {
        $vendedores = $this->getVendedores();

        $filtros = [
            'vendedor_id' => (int) $request->get('vendedor_id', 0),
            'periodo' => in_array($request->get('periodo', 'mes'), ['mes', 'trimestre', 'semestre', 'anual'], true)
                ? $request->get('periodo', 'mes')
                : 'mes',
            'fecha_desde' => $request->filled('fecha_desde')
                ? Carbon::parse($request->fecha_desde)->format('Y-m-d')
                : now()->subMonths(11)->startOfMonth()->format('Y-m-d'),
            'fecha_hasta' => $request->filled('fecha_hasta')
                ? Carbon::parse($request->fecha_hasta)->format('Y-m-d')
                : now()->format('Y-m-d'),
            'estatus' => strtoupper(trim((string) $request->get('estatus', 'TODOS'))),
            'producto' => trim((string) $request->get('producto', '')),
            'meta_crecimiento' => max(0, (float) $request->get('meta_crecimiento', 10)),
            'top_limit' => min(25, max(5, (int) $request->get('top_limit', 10))),
            'meta_periodo_objetivo' => trim((string) $request->get('meta_periodo_objetivo', '')),
        ];

        if ($filtros['fecha_desde'] > $filtros['fecha_hasta']) {
            $tmp = $filtros['fecha_desde'];
            $filtros['fecha_desde'] = $filtros['fecha_hasta'];
            $filtros['fecha_hasta'] = $tmp;
        }

        $filtros['meta_periodo_objetivo'] = $this->normalizePeriodoObjetivo(
            $filtros['meta_periodo_objetivo'],
            $filtros['periodo'],
            $filtros['fecha_hasta']
        );

        return [$vendedores, $filtros];
    }

    private function getVendedores()
    {
        $companyDb = DB::connection('company')->getDatabaseName();
        $mysqlDb = config('database.connections.mysql.database');

        return DB::connection('company')
            ->table($companyDb . '.vendedores as v')
            ->leftJoin($mysqlDb . '.users as u', function ($join) {
                $join->whereRaw('LOWER(TRIM(COALESCE(CONVERT(v.email USING utf8mb4), ""))) COLLATE utf8mb4_unicode_ci = LOWER(TRIM(COALESCE(CONVERT(u.email USING utf8mb4), ""))) COLLATE utf8mb4_unicode_ci');
            })
            ->selectRaw('v.id')
            ->selectRaw('v.codigo')
            ->selectRaw('v.email')
            ->selectRaw('u.id as user_id')
            ->selectRaw('COALESCE(NULLIF(TRIM(CONCAT(COALESCE(u.name, ""), " ", COALESCE(u.last_name, ""))), ""), v.codigo, v.email) as nombre')
            ->orderBy('v.codigo')
            ->get();
    }

    private function buildData(array $filtros): array
    {
        $periodoExpr = $this->periodExpression($filtros['periodo']);
        $mysqlDb = config('database.connections.mysql.database');

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

        $pedidos = DB::connection('company')
            ->table('pedidos as p')
            ->leftJoinSub($totalesPorPedido, 'tp', function ($join) {
                $join->on('tp.pedido_id', '=', 'p.id');
            })
            ->leftJoinSub($pagosPorPedido, 'pp', function ($join) {
                $join->on('pp.pedido_id', '=', 'p.id');
            })
            ->leftJoin($mysqlDb . '.users as pu', 'pu.id', '=', 'p.user_id')
            ->leftJoin('vendedores as v', function ($join) {
                $join->whereRaw('LOWER(TRIM(COALESCE(v.codigo, ""))) = LOWER(TRIM(COALESCE(p.seller_code, "")))')
                    ->orWhereRaw('LOWER(TRIM(COALESCE(v.email, ""))) = LOWER(TRIM(COALESCE(pu.email, "")))');
            })
            ->leftJoin($mysqlDb . '.users as u', function ($join) {
                $join->whereRaw('LOWER(TRIM(COALESCE(CONVERT(v.email USING utf8mb4), ""))) COLLATE utf8mb4_unicode_ci = LOWER(TRIM(COALESCE(CONVERT(u.email USING utf8mb4), ""))) COLLATE utf8mb4_unicode_ci');
            })
            ->whereDate(DB::raw('COALESCE(p.fecha, p.created_at)'), '>=', $filtros['fecha_desde'])
            ->whereDate(DB::raw('COALESCE(p.fecha, p.created_at)'), '<=', $filtros['fecha_hasta']);

        if ($filtros['vendedor_id'] > 0) {
            $pedidos->where('v.id', $filtros['vendedor_id']);
        }

        if ($filtros['estatus'] !== 'TODOS') {
            $pedidos->whereRaw('UPPER(COALESCE(p.estatus, "")) = ?', [$filtros['estatus']]);
        } else {
            $pedidos->whereRaw('UPPER(COALESCE(p.estatus, "")) NOT IN ("CARGANDO", "ANULADO", "CANCELADO")');
        }

        if ($filtros['producto'] !== '') {
            $pedidos->whereExists(function ($query) use ($filtros) {
                $query->select(DB::raw(1))
                    ->from('pedido_detalle as pdx')
                    ->whereColumn('pdx.pedido_id', 'p.id')
                    ->where(function ($sub) use ($filtros) {
                        $sub->where('pdx.codigo_inven', 'like', '%' . $filtros['producto'] . '%')
                            ->orWhere('pdx.inven_descr', 'like', '%' . $filtros['producto'] . '%');
                    });
            });
        }

        $pedidos = $pedidos
            ->selectRaw('p.id as pedido_id')
            ->selectRaw('p.fecha')
            ->selectRaw('p.estatus')
            ->selectRaw($periodoExpr . ' as periodo_key')
            ->selectRaw('COALESCE(v.id, 0) as vendedor_id')
            ->selectRaw('COALESCE(NULLIF(TRIM(CONCAT(COALESCE(pu.name, ""), " ", COALESCE(pu.last_name, ""))), ""), NULLIF(TRIM(CONCAT(COALESCE(u.name, ""), " ", COALESCE(u.last_name, ""))), ""), v.codigo, v.email, "Sin vendedor") as vendedor_nombre')
            ->selectRaw('COALESCE(tp.unidades, 0) as unidades')
            ->selectRaw('COALESCE(tp.ventas_usd, 0) as ventas_usd')
            ->selectRaw('COALESCE(pp.pagos_aprobados_usd, 0) as pagos_aprobados_usd')
            ->selectRaw('CASE WHEN UPPER(COALESCE(p.estatus, "")) = "APROBADO" THEN 1 ELSE 0 END as es_aprobado')
            ->selectRaw('CASE WHEN COALESCE(pp.tiene_pago_aprobado, 0) > 0 THEN 1 ELSE 0 END as es_pagado')
            ->orderBy('periodo_key')
            ->orderBy('vendedor_nombre')
            ->orderBy('p.fecha');

        $pedidoDetalle = $pedidos->get();

        $periodos = $pedidoDetalle
            ->groupBy('periodo_key')
            ->map(function ($items, $periodoKey) use ($filtros) {
                $ventasUsd = (float) $items->sum('ventas_usd');
                $pedidosAprobados = (int) $items->sum('es_aprobado');
                $pedidosPagados = (int) $items->sum('es_pagado');
                $pagosUsd = (float) $items->sum('pagos_aprobados_usd');
                $coberturaPedidos = $this->calcularCoberturaPedidos($pedidosAprobados, $pedidosPagados);

                return (object) [
                    'periodo_key' => $periodoKey,
                    'etiqueta' => $this->formatPeriodLabel($periodoKey, $filtros['periodo']),
                    'pedidos_total' => (int) $items->count(),
                    'pedidos_aprobados' => $pedidosAprobados,
                    'pedidos_pagados' => $pedidosPagados,
                    'ventas_usd' => $ventasUsd,
                    'pagos_aprobados_usd' => $pagosUsd,
                    'logro_pedidos_pct' => $coberturaPedidos,
                    'logro_monto_pct' => $ventasUsd > 0 ? round(($pagosUsd / $ventasUsd) * 100, 2) : 0,
                    'unidades' => (float) $items->sum('unidades'),
                ];
            })
            ->sortBy('periodo_key')
            ->values();

        $periodosOrdenados = $periodos->values();
        foreach ($periodosOrdenados as $index => $periodo) {
            $anterior = $periodosOrdenados->get($index - 1);
            $periodo->variacion_ventas_pct = $anterior && (float) $anterior->ventas_usd > 0
                ? round((($periodo->ventas_usd - $anterior->ventas_usd) / $anterior->ventas_usd) * 100, 2)
                : null;
            $periodo->variacion_pedidos_pct = $anterior && (int) $anterior->pedidos_aprobados > 0
                ? round((($periodo->pedidos_aprobados - $anterior->pedidos_aprobados) / $anterior->pedidos_aprobados) * 100, 2)
                : null;
        }

        $rankingVendedores = $pedidoDetalle
            ->groupBy('vendedor_id')
            ->map(function ($items) {
                $ventasUsd = (float) $items->sum('ventas_usd');
                $pedidosAprobados = (int) $items->sum('es_aprobado');
                $pedidosPagados = (int) $items->sum('es_pagado');
                $pagosUsd = (float) $items->sum('pagos_aprobados_usd');
                $coberturaPedidos = $this->calcularCoberturaPedidos($pedidosAprobados, $pedidosPagados);

                return (object) [
                    'vendedor_id' => (int) optional($items->first())->vendedor_id,
                    'vendedor_nombre' => optional($items->first())->vendedor_nombre ?: 'Sin vendedor',
                    'pedidos_total' => (int) $items->count(),
                    'pedidos_aprobados' => $pedidosAprobados,
                    'pedidos_pagados' => $pedidosPagados,
                    'ventas_usd' => $ventasUsd,
                    'pagos_aprobados_usd' => $pagosUsd,
                    'unidades' => (float) $items->sum('unidades'),
                    'logro_pedidos_pct' => $coberturaPedidos,
                    'logro_monto_pct' => $ventasUsd > 0 ? round(($pagosUsd / $ventasUsd) * 100, 2) : 0,
                ];
            })
            ->sortByDesc('ventas_usd')
            ->values();

        $topProductosQuery = DB::connection('company')
            ->table('pedido_detalle as pd')
            ->join('pedidos as p', 'p.id', '=', 'pd.pedido_id')
            ->leftJoin($mysqlDb . '.users as pu', 'pu.id', '=', 'p.user_id')
            ->leftJoin('vendedores as v', function ($join) {
                $join->whereRaw('LOWER(TRIM(COALESCE(v.codigo, ""))) = LOWER(TRIM(COALESCE(p.seller_code, "")))')
                    ->orWhereRaw('LOWER(TRIM(COALESCE(v.email, ""))) = LOWER(TRIM(COALESCE(pu.email, "")))');
            })
            ->whereDate(DB::raw('COALESCE(p.fecha, p.created_at)'), '>=', $filtros['fecha_desde'])
            ->whereDate(DB::raw('COALESCE(p.fecha, p.created_at)'), '<=', $filtros['fecha_hasta']);

        if ($filtros['vendedor_id'] > 0) {
            $topProductosQuery->where('v.id', $filtros['vendedor_id']);
        }

        if ($filtros['estatus'] !== 'TODOS') {
            $topProductosQuery->whereRaw('UPPER(COALESCE(p.estatus, "")) = ?', [$filtros['estatus']]);
        } else {
            $topProductosQuery->whereRaw('UPPER(COALESCE(p.estatus, "")) NOT IN ("CARGANDO", "ANULADO", "CANCELADO")');
        }

        if ($filtros['producto'] !== '') {
            $topProductosQuery->where(function ($query) use ($filtros) {
                $query->where('pd.codigo_inven', 'like', '%' . $filtros['producto'] . '%')
                    ->orWhere('pd.inven_descr', 'like', '%' . $filtros['producto'] . '%');
            });
        }

        $topProductos = $topProductosQuery
            ->selectRaw('pd.codigo_inven')
            ->selectRaw('MAX(pd.inven_descr) as descripcion')
            ->selectRaw('COALESCE(SUM(pd.cantidad), 0) as unidades')
            ->selectRaw('COALESCE(SUM(pd.cantidad * pd.precio_dolar), 0) as ventas_usd')
            ->selectRaw('COUNT(DISTINCT p.id) as pedidos')
            ->selectRaw('MIN(COALESCE(p.fecha, p.created_at)) as primera_venta')
            ->selectRaw('MAX(COALESCE(p.fecha, p.created_at)) as ultima_venta')
            ->whereRaw('TRIM(COALESCE(pd.codigo_inven, "")) <> ""')
            ->groupBy('pd.codigo_inven')
            ->orderByDesc('ventas_usd')
            ->limit($filtros['top_limit'])
            ->get();

        $resumenGlobal = [
            'pedidos_total' => (int) $pedidoDetalle->count(),
            'pedidos_aprobados' => (int) $pedidoDetalle->sum('es_aprobado'),
            'pedidos_pagados' => (int) $pedidoDetalle->sum('es_pagado'),
            'ventas_usd' => (float) $pedidoDetalle->sum('ventas_usd'),
            'pagos_aprobados_usd' => (float) $pedidoDetalle->sum('pagos_aprobados_usd'),
            'unidades' => (float) $pedidoDetalle->sum('unidades'),
        ];

        $periodoActual = $periodos->last();
        $promedioVentas = (float) ($periodos->avg('ventas_usd') ?? 0);
        $promedioPedidos = (float) ($periodos->avg('pedidos_aprobados') ?? 0);
        $promedioPagados = (float) ($periodos->avg('pedidos_pagados') ?? 0);
        $promedioCobertura = (float) ($periodos->avg('logro_pedidos_pct') ?? 0);
        $metaFactor = 1 + ($filtros['meta_crecimiento'] / 100);

        $metas = [
            'ventas_usd' => round(max((float) optional($periodoActual)->ventas_usd, $promedioVentas) * $metaFactor, 2),
            'pedidos_aprobados' => (int) ceil(max((float) optional($periodoActual)->pedidos_aprobados, $promedioPedidos) * $metaFactor),
            'pedidos_pagados' => (int) ceil(max((float) optional($periodoActual)->pedidos_pagados, $promedioPagados) * $metaFactor),
            'logro_pedidos_pct' => min(100, round(max((float) optional($periodoActual)->logro_pedidos_pct, $promedioCobertura) + max(2, $filtros['meta_crecimiento'] / 2), 2)),
        ];

        $metaTableDisponible = Schema::connection('company')->hasTable('metas_vendedores_periodo');
        $metasManualMap = collect();
        if ($metaTableDisponible) {
            $metasManualMap = DB::connection('company')
                ->table('metas_vendedores_periodo')
                ->where('periodo_tipo', $filtros['periodo'])
                ->where('periodo_key', strtoupper($filtros['meta_periodo_objetivo']))
                ->get()
                ->keyBy('vendedor_id');
        }

        $rankingVendedores = $rankingVendedores
            ->map(function ($vendedor) use ($metasManualMap, $promedioVentas, $metaFactor, $promedioCobertura) {
                $metaSugeridaVentas = round(max((float) $vendedor->ventas_usd, (float) $promedioVentas) * $metaFactor, 2);
                $metaSugeridaPedidosAprobados = (int) ceil(max((float) $vendedor->pedidos_aprobados, 1) * $metaFactor);
                $metaSugeridaPedidosPagados = (int) ceil(max((float) $vendedor->pedidos_pagados, 1) * $metaFactor);
                $metaSugeridaLogro = min(100, round(max((float) $vendedor->logro_pedidos_pct, $promedioCobertura) + 2, 2));

                $manual = $metasManualMap->get((int) $vendedor->vendedor_id);

                $vendedor->meta_ventas_usd = !is_null(optional($manual)->meta_ventas_usd)
                    ? (float) $manual->meta_ventas_usd
                    : $metaSugeridaVentas;
                $vendedor->meta_pedidos_aprobados = !is_null(optional($manual)->meta_pedidos_aprobados)
                    ? (int) $manual->meta_pedidos_aprobados
                    : $metaSugeridaPedidosAprobados;
                $vendedor->meta_pedidos_pagados = !is_null(optional($manual)->meta_pedidos_pagados)
                    ? (int) $manual->meta_pedidos_pagados
                    : $metaSugeridaPedidosPagados;
                $vendedor->meta_logro_pedidos_pct = !is_null(optional($manual)->meta_logro_pedidos_pct)
                    ? (float) $manual->meta_logro_pedidos_pct
                    : $metaSugeridaLogro;
                $vendedor->meta_manual = !is_null($manual);
                $vendedor->brecha_meta_usd = round($vendedor->meta_ventas_usd - (float) $vendedor->ventas_usd, 2);

                return $vendedor;
            })
            ->values();

        return [
            'resumen' => [
                'pedidos_total' => $resumenGlobal['pedidos_total'],
                'pedidos_aprobados' => $resumenGlobal['pedidos_aprobados'],
                'pedidos_pagados' => $resumenGlobal['pedidos_pagados'],
                'ventas_usd' => $resumenGlobal['ventas_usd'],
                'pagos_aprobados_usd' => $resumenGlobal['pagos_aprobados_usd'],
                'unidades' => $resumenGlobal['unidades'],
                'logro_pedidos_pct' => $this->calcularCoberturaPedidos(
                    $resumenGlobal['pedidos_aprobados'],
                    $resumenGlobal['pedidos_pagados']
                ),
                'logro_monto_pct' => $resumenGlobal['ventas_usd'] > 0
                    ? round(($resumenGlobal['pagos_aprobados_usd'] / $resumenGlobal['ventas_usd']) * 100, 2)
                    : 0,
                'cobertura_pedidos_base' => $resumenGlobal['pedidos_aprobados'] + $resumenGlobal['pedidos_pagados'],
                'ticket_promedio' => $resumenGlobal['pedidos_aprobados'] > 0
                    ? round($resumenGlobal['ventas_usd'] / $resumenGlobal['pedidos_aprobados'], 2)
                    : 0,
                'promedio_periodo_ventas' => $promedioVentas,
                'promedio_periodo_pedidos' => $promedioPedidos,
                'promedio_periodo_cobertura' => $promedioCobertura,
            ],
            'periodos' => $periodos,
            'rankingVendedores' => $rankingVendedores,
            'topProductos' => $topProductos,
            'pedidoDetalle' => $pedidoDetalle,
            'metas' => $metas,
            'metaTableDisponible' => $metaTableDisponible,
        ];
    }

    private function normalizePeriodoObjetivo(string $periodoObjetivo, string $periodoTipo, string $fechaHasta): string
    {
        $periodoObjetivo = strtoupper(trim($periodoObjetivo));
        if ($periodoObjetivo !== '' && $this->isPeriodoKeyValido($periodoObjetivo, $periodoTipo)) {
            return $periodoObjetivo;
        }

        return $this->nextPeriodKeyFromDate($periodoTipo, $fechaHasta);
    }

    private function nextPeriodKeyFromDate(string $periodoTipo, string $fechaHasta): string
    {
        $fecha = Carbon::parse($fechaHasta)->startOfDay();

        if ($periodoTipo === 'trimestre') {
            $year = (int) $fecha->format('Y');
            $quarter = (int) ceil(((int) $fecha->format('n')) / 3);
            $quarter++;
            if ($quarter > 4) {
                $quarter = 1;
                $year++;
            }

            return $year . '-T' . $quarter;
        }

        if ($periodoTipo === 'semestre') {
            $year = (int) $fecha->format('Y');
            $semestre = ((int) $fecha->format('n')) <= 6 ? 1 : 2;
            $semestre++;
            if ($semestre > 2) {
                $semestre = 1;
                $year++;
            }

            return $year . '-S' . $semestre;
        }

        if ($periodoTipo === 'anual') {
            return (string) (((int) $fecha->format('Y')) + 1);
        }

        return $fecha->copy()->startOfMonth()->addMonth()->format('Y-m');
    }

    private function isPeriodoKeyValido(string $periodoKey, string $periodoTipo): bool
    {
        if ($periodoTipo === 'trimestre') {
            return preg_match('/^\d{4}-T[1-4]$/', $periodoKey) === 1;
        }

        if ($periodoTipo === 'semestre') {
            return preg_match('/^\d{4}-S[1-2]$/', $periodoKey) === 1;
        }

        if ($periodoTipo === 'anual') {
            return preg_match('/^\d{4}$/', $periodoKey) === 1;
        }

        return preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $periodoKey) === 1;
    }

    private function calcularCoberturaPedidos(int $pedidosAprobados, int $pedidosPagados): float
    {
        $base = $pedidosAprobados + $pedidosPagados;

        if ($base <= 0) {
            return 0;
        }

        return round(($pedidosPagados / $base) * 100, 2);
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