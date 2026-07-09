@extends('layouts.app')

@section('titulo', 'Analisis de Vendedores')
@section('titulo_header', 'Analisis de Vendedores')
@section('subtitulo_header', 'Dashboard gerencial por periodo, rendimiento y metas')

@php
    $periodosJson = json_encode($periodos, JSON_UNESCAPED_UNICODE);
    $rankingVendedoresJson = json_encode($rankingVendedores->take(8)->values(), JSON_UNESCAPED_UNICODE);
    $topProductosJson = json_encode($topProductos->take(7)->values(), JSON_UNESCAPED_UNICODE);
@endphp

@section('styles')
@verbatim
<style>
    :root {
        --av-navy: #0b2239;
        --av-blue: #1d4f7a;
        --av-teal: #0f766e;
        --av-gold: #c78c1f;
        --av-ink: #13202f;
        --av-muted: #617083;
        --av-soft: #eef4fb;
    }

    .av-hero {
        border-radius: 24px;
        background: linear-gradient(125deg, #08182a 0%, #143754 42%, #1d4f7a 100%);
        color: #fff;
        box-shadow: 0 20px 44px rgba(7, 28, 48, .24);
        position: relative;
        overflow: hidden;
    }

    .av-hero::before,
    .av-hero::after {
        content: '';
        position: absolute;
        border-radius: 999px;
        pointer-events: none;
        background: rgba(255, 255, 255, .08);
    }

    .av-hero::before {
        width: 320px;
        height: 320px;
        right: -120px;
        top: -140px;
    }

    .av-hero::after {
        width: 180px;
        height: 180px;
        left: -70px;
        bottom: -90px;
        background: rgba(199, 140, 31, .18);
    }

    .av-chip {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .4rem .8rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, .1);
        border: 1px solid rgba(255, 255, 255, .22);
        font-size: .76rem;
        font-weight: 800;
        letter-spacing: .4px;
    }

    .av-panel {
        background: #fff;
        border-radius: 18px;
        border: 1px solid #dbe5ef;
        box-shadow: 0 10px 22px rgba(7, 28, 48, .08);
    }

    .av-kpi {
        height: 100%;
        border-radius: 18px;
        background: #fff;
        border: 1px solid #dbe5ef;
        box-shadow: 0 10px 24px rgba(7, 28, 48, .08);
        padding: 15px 16px;
    }

    .av-kpi .kpi-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: .35rem;
    }

    .av-kpi .kpi-label {
        color: var(--av-muted);
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .9px;
        font-weight: 800;
    }

    .av-kpi .kpi-value {
        color: var(--av-ink);
        font-size: 1.45rem;
        line-height: 1.1;
        font-weight: 900;
    }

    .av-kpi .kpi-sub {
        color: var(--av-muted);
        font-size: .78rem;
        margin-top: .25rem;
    }

    .av-icon {
        width: 36px;
        height: 36px;
        border-radius: 11px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
    }

    .av-card {
        border-radius: 18px;
        border: 1px solid #dbe5ef;
        overflow: hidden;
        box-shadow: 0 10px 24px rgba(7, 28, 48, .08);
    }

    .av-card .card-header {
        background: linear-gradient(180deg, #f8fbff 0%, #eef4fb 100%);
        border-bottom: 1px solid #dbe5ef;
        font-weight: 800;
        color: #15324c;
    }

    .av-table thead th {
        background: #f7fbff;
        border-top: 0;
        border-bottom: 1px solid #e5edf5;
        text-transform: uppercase;
        letter-spacing: .7px;
        font-size: .72rem;
        color: #294562;
        white-space: nowrap;
    }

    .av-table tbody td {
        font-size: .86rem;
        vertical-align: middle;
        color: #253647;
        border-top: 1px solid #edf3f8;
    }

    .av-badge {
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 800;
        padding: .25rem .65rem;
        display: inline-flex;
        align-items: center;
        gap: .3rem;
    }

    .av-badge.ok {
        color: #166534;
        background: #dcfce7;
        border: 1px solid #86efac;
    }

    .av-badge.warn {
        color: #92400e;
        background: #fff7d6;
        border: 1px solid #f6d97d;
    }

    .av-badge.off {
        color: #b42318;
        background: #fee4e2;
        border: 1px solid #fda29b;
    }

    .av-progress {
        height: 10px;
        border-radius: 99px;
        overflow: hidden;
        background: #e6eef6;
    }

    .av-progress > span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #1f8a70, #1d4f7a);
    }

    .av-muted {
        color: var(--av-muted);
        font-size: .78rem;
    }

    @media (max-width: 768px) {
        .av-kpi .kpi-value {
            font-size: 1.18rem;
        }
    }
</style>
@endverbatim
@endsection

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(!$metaTableDisponible)
        <div class="alert alert-warning" role="alert">
            Las metas manuales por vendedor aun no estan activas porque falta crear la tabla <strong>metas_vendedores_periodo</strong> en la base de datos de company.
        </div>
    @endif

    <div class="av-hero p-4 mb-4">
        <div class="row align-items-center">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <div class="av-chip mb-2">
                    <i class="fas fa-chart-line"></i>
                    Reporte Gerencial
                </div>
                <h4 class="mb-1 font-weight-bold">Analisis de Vendedores</h4>
                <div class="mb-2" style="font-size:.95rem;opacity:.95;">
                    Rendimiento por periodo, comparacion entre pedidos aprobados y pagados, analisis de productos y metas sugeridas.
                </div>
                <div style="font-size:.84rem;opacity:.86;">
                    Filtros de periodo, rango de fechas, vendedor, estatus y producto para una lectura gerencial inmediata.
                </div>
            </div>

            <div class="col-lg-4">
                <form method="GET" action="{{ route('admin.reportes.vendedores.analisis') }}" class="av-panel p-3">
                    <div class="form-row">
                        <div class="form-group col-12">
                            <label class="mb-1 font-weight-bold text-dark" style="font-size:.82rem;">Vendedor</label>
                            <select class="form-control form-control-sm" name="vendedor_id">
                                <option value="0" {{ (int) $filtros['vendedor_id'] === 0 ? 'selected' : '' }}>Todos los vendedores</option>
                                @foreach($vendedores as $vendedor)
                                    <option value="{{ $vendedor->id }}" {{ (int) $filtros['vendedor_id'] === (int) $vendedor->id ? 'selected' : '' }}>
                                        {{ $vendedor->codigo }} - {{ $vendedor->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-6">
                            <label class="mb-1 font-weight-bold text-dark" style="font-size:.82rem;">Periodo</label>
                            <select class="form-control form-control-sm" name="periodo">
                                <option value="mes" {{ $filtros['periodo'] === 'mes' ? 'selected' : '' }}>Mensual</option>
                                <option value="trimestre" {{ $filtros['periodo'] === 'trimestre' ? 'selected' : '' }}>Trimestral</option>
                                <option value="semestre" {{ $filtros['periodo'] === 'semestre' ? 'selected' : '' }}>Semestral</option>
                                <option value="anual" {{ $filtros['periodo'] === 'anual' ? 'selected' : '' }}>Anual</option>
                            </select>
                        </div>

                        <div class="form-group col-6">
                            <label class="mb-1 font-weight-bold text-dark" style="font-size:.82rem;">Estatus</label>
                            <select class="form-control form-control-sm" name="estatus">
                                @php
                                    $estatusActual = strtoupper($filtros['estatus']);
                                @endphp
                                <option value="TODOS" {{ $estatusActual === 'TODOS' ? 'selected' : '' }}>Todos</option>
                                <option value="APROBADO" {{ $estatusActual === 'APROBADO' ? 'selected' : '' }}>Aprobado</option>
                                <option value="PENDIENTE" {{ $estatusActual === 'PENDIENTE' ? 'selected' : '' }}>Pendiente</option>
                                <option value="EN REVISION" {{ $estatusActual === 'EN REVISION' ? 'selected' : '' }}>En revision</option>
                                <option value="PAGADO" {{ $estatusActual === 'PAGADO' ? 'selected' : '' }}>Pagado</option>
                                <option value="CANCELADO" {{ $estatusActual === 'CANCELADO' ? 'selected' : '' }}>Cancelado</option>
                            </select>
                        </div>

                        <div class="form-group col-6">
                            <label class="mb-1 font-weight-bold text-dark" style="font-size:.82rem;">Desde</label>
                            <input type="date" class="form-control form-control-sm" name="fecha_desde" value="{{ $filtros['fecha_desde'] }}">
                        </div>

                        <div class="form-group col-6">
                            <label class="mb-1 font-weight-bold text-dark" style="font-size:.82rem;">Hasta</label>
                            <input type="date" class="form-control form-control-sm" name="fecha_hasta" value="{{ $filtros['fecha_hasta'] }}">
                        </div>

                        <div class="form-group col-8">
                            <label class="mb-1 font-weight-bold text-dark" style="font-size:.82rem;">Producto</label>
                            <input type="text" class="form-control form-control-sm" name="producto" value="{{ $filtros['producto'] }}" placeholder="Codigo o descripcion">
                        </div>

                        <div class="form-group col-4">
                            <label class="mb-1 font-weight-bold text-dark" style="font-size:.82rem;">Meta %</label>
                            <input type="number" step="0.5" min="0" class="form-control form-control-sm" name="meta_crecimiento" value="{{ $filtros['meta_crecimiento'] }}">
                        </div>

                        <div class="form-group col-12">
                            <label class="mb-1 font-weight-bold text-dark" style="font-size:.82rem;">Periodo objetivo de metas manuales</label>
                            <input type="text" class="form-control form-control-sm" name="meta_periodo_objetivo" value="{{ $filtros['meta_periodo_objetivo'] }}" placeholder="{{ $filtros['periodo'] === 'mes' ? 'YYYY-MM' : ($filtros['periodo'] === 'trimestre' ? 'YYYY-T1..T4' : ($filtros['periodo'] === 'semestre' ? 'YYYY-S1 o S2' : 'YYYY')) }}">
                        </div>

                        <div class="form-group col-12">
                            <label class="mb-1 font-weight-bold text-dark" style="font-size:.82rem;">Top productos</label>
                            <input type="number" min="5" max="25" class="form-control form-control-sm" name="top_limit" value="{{ $filtros['top_limit'] }}">
                        </div>
                    </div>

                    <div class="d-flex flex-wrap" style="gap:.4rem;">
                        <button class="btn btn-sm btn-primary" type="submit"><i class="fas fa-filter mr-1"></i>Filtrar</button>
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.reportes.vendedores.analisis') }}"><i class="fas fa-eraser mr-1"></i>Limpiar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-6 col-lg-3 mb-3">
            <div class="av-kpi">
                <div class="kpi-head">
                    <div class="kpi-label">Pedidos Aprobados</div>
                    <span class="av-icon" style="background:linear-gradient(135deg,#1d4f7a,#0b2239)"><i class="fas fa-check-circle"></i></span>
                </div>
                <div class="kpi-value">{{ number_format($resumen['pedidos_aprobados']) }}</div>
                <div class="kpi-sub">Total pedidos filtrados: {{ number_format($resumen['pedidos_total']) }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <div class="av-kpi">
                <div class="kpi-head">
                    <div class="kpi-label">Pedidos Pagados</div>
                    <span class="av-icon" style="background:linear-gradient(135deg,#0f766e,#1d4f7a)"><i class="fas fa-hand-holding-usd"></i></span>
                </div>
                <div class="kpi-value">{{ number_format($resumen['pedidos_pagados']) }}</div>
                <div class="kpi-sub">Cobertura sobre aprobados + pagados: {{ number_format($resumen['logro_pedidos_pct'], 2, ',', '.') }}%</div>
            </div>
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <div class="av-kpi">
                <div class="kpi-head">
                    <div class="kpi-label">Ventas / Cobro</div>
                    <span class="av-icon" style="background:linear-gradient(135deg,#c78c1f,#8f5f0d)"><i class="fas fa-coins"></i></span>
                </div>
                <div class="kpi-value">${{ number_format($resumen['ventas_usd'], 2, ',', '.') }}</div>
                <div class="kpi-sub">Cobrado: ${{ number_format($resumen['pagos_aprobados_usd'], 2, ',', '.') }} | Cobertura: {{ number_format($resumen['logro_monto_pct'], 2, ',', '.') }}%</div>
            </div>
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <div class="av-kpi">
                <div class="kpi-head">
                    <div class="kpi-label">Meta Siguiente Periodo</div>
                    <span class="av-icon" style="background:linear-gradient(135deg,#7c3aed,#1d4f7a)"><i class="fas fa-bullseye"></i></span>
                </div>
                <div class="kpi-value">${{ number_format($metas['ventas_usd'], 2, ',', '.') }}</div>
                <div class="kpi-sub">Meta pedidos: {{ number_format($metas['pedidos_aprobados']) }} | Crecimiento base: {{ number_format($filtros['meta_crecimiento'], 1, ',', '.') }}%</div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-8 mb-3">
            <div class="card av-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-chart-area mr-2"></i>Pedidos Aprobados vs Pagados por Periodo</span>
                    <span class="av-muted">Agrupacion: {{ ucfirst($filtros['periodo']) }}</span>
                </div>
                <div class="card-body">
                    <canvas id="chartPeriodos" height="120"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-3">
            <div class="card av-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-trophy mr-2"></i>Top Vendedores</span>
                    <span class="av-muted">Por ventas USD</span>
                </div>
                <div class="card-body">
                    <canvas id="chartVendedores" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-4 mb-3">
            <div class="card av-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-box-open mr-2"></i>Top Productos</span>
                    <span class="av-muted">{{ count($topProductos) }} registros</span>
                </div>
                <div class="card-body">
                    <canvas id="chartProductos" height="220"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-8 mb-3">
            <div class="card av-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-chart-pie mr-2"></i>Lectura Ejecutiva por Periodo</span>
                    <span class="av-muted">Tendencia y variacion</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm av-table mb-0">
                            <thead>
                                <tr>
                                    <th>Periodo</th>
                                    <th class="text-right">Pedidos</th>
                                    <th class="text-right">Aprobados</th>
                                    <th class="text-right">Pagados</th>
                                    <th class="text-right">Ventas USD</th>
                                    <th class="text-right">Cobertura</th>
                                    <th class="text-right">Var. Ventas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($periodos as $periodo)
                                    <tr>
                                        <td class="font-weight-bold">{{ $periodo->etiqueta }}</td>
                                        <td class="text-right">{{ number_format($periodo->pedidos_total) }}</td>
                                        <td class="text-right">{{ number_format($periodo->pedidos_aprobados) }}</td>
                                        <td class="text-right">{{ number_format($periodo->pedidos_pagados) }}</td>
                                        <td class="text-right">${{ number_format($periodo->ventas_usd, 2, ',', '.') }}</td>
                                        <td class="text-right">{{ number_format($periodo->logro_pedidos_pct, 2, ',', '.') }}%</td>
                                        <td class="text-right">
                                            @if(!is_null($periodo->variacion_ventas_pct))
                                                <span class="av-badge {{ $periodo->variacion_ventas_pct >= 0 ? 'ok' : 'off' }}">
                                                    <i class="fas fa-arrow-{{ $periodo->variacion_ventas_pct >= 0 ? 'up' : 'down' }}"></i>
                                                    {{ number_format($periodo->variacion_ventas_pct, 2, ',', '.') }}%
                                                </span>
                                            @else
                                                <span class="av-muted">N/D</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">No hay datos para los filtros seleccionados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-8 mb-3">
            <div class="card av-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-user-tie mr-2"></i>Ranking de Vendedores</span>
                    <span class="av-muted">Metas para {{ $filtros['meta_periodo_objetivo'] }} ({{ $metaTableDisponible ? 'edicion manual activa' : 'solo sugeridas' }})</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 460px; overflow:auto;">
                        <table class="table table-sm av-table mb-0">
                            <thead>
                                <tr>
                                    <th>Vendedor</th>
                                    <th class="text-right">Pedidos</th>
                                    <th class="text-right">Aprobados</th>
                                    <th class="text-right">Pagados</th>
                                    <th class="text-right">Ventas USD</th>
                                    <th class="text-right">Cobertura</th>
                                    <th class="text-right">Meta Ventas</th>
                                    <th>Accion</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rankingVendedores as $vendedor)
                                    <tr>
                                        <td>
                                            <div class="font-weight-bold">{{ $vendedor->vendedor_nombre }}</div>
                                            <div class="av-muted">{{ $vendedor->vendedor_id > 0 ? 'ID ' . $vendedor->vendedor_id : 'Sin identificacion' }}</div>
                                        </td>
                                        <td class="text-right">{{ number_format($vendedor->pedidos_total) }}</td>
                                        <td class="text-right">{{ number_format($vendedor->pedidos_aprobados) }}</td>
                                        <td class="text-right">{{ number_format($vendedor->pedidos_pagados) }}</td>
                                        <td class="text-right">${{ number_format($vendedor->ventas_usd, 2, ',', '.') }}</td>
                                        <td class="text-right">
                                            <span class="av-badge {{ $vendedor->logro_pedidos_pct >= 80 ? 'ok' : ($vendedor->logro_pedidos_pct >= 50 ? 'warn' : 'off') }}">
                                                {{ number_format($vendedor->logro_pedidos_pct, 2, ',', '.') }}%
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            <div>${{ number_format($vendedor->meta_ventas_usd, 2, ',', '.') }}</div>
                                            <div class="av-muted">Brecha: ${{ number_format($vendedor->brecha_meta_usd, 2, ',', '.') }}</div>
                                            <div class="av-muted">{{ $vendedor->meta_manual ? 'Manual' : 'Sugerida' }}</div>
                                        </td>
                                        <td>
                                            @if($vendedor->vendedor_id > 0)
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-primary btn-editar-meta"
                                                    data-toggle="modal"
                                                    data-target="#modalEditarMeta"
                                                    data-vendedor-id="{{ $vendedor->vendedor_id }}"
                                                    data-vendedor-nombre="{{ $vendedor->vendedor_nombre }}"
                                                    data-meta-ventas-usd="{{ number_format((float) $vendedor->meta_ventas_usd, 2, '.', '') }}"
                                                    data-meta-pedidos-aprobados="{{ (int) $vendedor->meta_pedidos_aprobados }}"
                                                    data-meta-pedidos-pagados="{{ (int) $vendedor->meta_pedidos_pagados }}"
                                                    data-meta-logro-pedidos-pct="{{ number_format((float) $vendedor->meta_logro_pedidos_pct, 2, '.', '') }}"
                                                    {{ !$metaTableDisponible ? 'disabled' : '' }}>
                                                    <i class="fas fa-pen mr-1"></i>Editar
                                                </button>
                                            @else
                                                <span class="av-muted">No editable</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">Sin vendedores con datos para mostrar.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-3">
            <div class="card av-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-boxes-stacked mr-2"></i>Analisis de Productos</span>
                    <span class="av-muted">Fechas y volumen</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 460px; overflow:auto;">
                        <table class="table table-sm av-table mb-0">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-right">Unid.</th>
                                    <th class="text-right">USD</th>
                                    <th>Fechas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topProductos as $producto)
                                    <tr>
                                        <td>
                                            <div class="font-weight-bold">{{ $producto->codigo_inven }}</div>
                                            <div class="av-muted">{{ \Illuminate\Support\Str::limit((string) $producto->descripcion, 34) }}</div>
                                        </td>
                                        <td class="text-right">{{ number_format($producto->unidades, 0, ',', '.') }}</td>
                                        <td class="text-right">${{ number_format($producto->ventas_usd, 2, ',', '.') }}</td>
                                        <td>
                                            <div>{{ $producto->primera_venta ? \Carbon\Carbon::parse($producto->primera_venta)->format('d/m/Y') : '-' }}</div>
                                            <div class="av-muted">{{ $producto->ultima_venta ? \Carbon\Carbon::parse($producto->ultima_venta)->format('d/m/Y') : '-' }}</div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">Sin productos para el filtro actual.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 mb-3">
            <div class="card av-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-bullseye mr-2"></i>Metas Sugeridas para el Siguiente Periodo</span>
                    <span class="av-muted">Las metas manuales por vendedor se gestionan en la tabla de ranking</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="av-muted mb-1">Ventas USD objetivo</div>
                            <div class="av-progress mb-2"><span data-width="{{ max(0, min(100, $resumen['logro_monto_pct'])) }}"></span></div>
                            <div class="font-weight-bold">${{ number_format($metas['ventas_usd'], 2, ',', '.') }}</div>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="av-muted mb-1">Pedidos aprobados objetivo</div>
                            <div class="av-progress mb-2"><span data-width="{{ max(0, min(100, $metas['logro_pedidos_pct'])) }}"></span></div>
                            <div class="font-weight-bold">{{ number_format($metas['pedidos_aprobados']) }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="av-muted mb-1">Pedidos pagados objetivo</div>
                            <div class="av-progress mb-2"><span data-width="{{ max(0, min(100, $resumen['logro_pedidos_pct'])) }}"></span></div>
                            <div class="font-weight-bold">{{ number_format($metas['pedidos_pagados']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditarMeta" tabindex="-1" role="dialog" aria-labelledby="modalEditarMetaLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.reportes.vendedores.analisis.meta.guardar') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditarMetaLabel">Editar Metas por Vendedor</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="vendedor_id" id="meta_modal_vendedor_id">
                        <input type="hidden" name="periodo_tipo" value="{{ $filtros['periodo'] }}">
                        <input type="hidden" name="periodo_key" value="{{ $filtros['meta_periodo_objetivo'] }}">

                        <div class="alert alert-info mb-3" role="alert">
                            <div><strong>Vendedor:</strong> <span id="meta_modal_vendedor_nombre">-</span></div>
                            <div><strong>Periodo objetivo:</strong> {{ $filtros['meta_periodo_objetivo'] }} ({{ ucfirst($filtros['periodo']) }})</div>
                        </div>

                        <div class="border rounded p-3 mb-3" style="background:#f7fbff;">
                            <div class="font-weight-bold mb-2">Leyenda de campos</div>
                            <div class="av-muted">Meta Ventas USD: monto de ventas objetivo del periodo.</div>
                            <div class="av-muted">Meta Pedidos Aprobados: cantidad objetivo de pedidos aprobados.</div>
                            <div class="av-muted">Meta Pedidos Pagados: cantidad objetivo de pedidos con pago aprobado.</div>
                            <div class="av-muted">Meta Cobertura (%): porcentaje objetivo de cobertura de pedidos pagados.</div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="meta_modal_ventas_usd">Meta Ventas USD</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="meta_modal_ventas_usd" name="meta_ventas_usd" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="meta_modal_pedidos_aprobados">Meta Pedidos Aprobados</label>
                                <input type="number" min="0" class="form-control" id="meta_modal_pedidos_aprobados" name="meta_pedidos_aprobados" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="meta_modal_pedidos_pagados">Meta Pedidos Pagados</label>
                                <input type="number" min="0" class="form-control" id="meta_modal_pedidos_pagados" name="meta_pedidos_pagados" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="meta_modal_logro_pedidos_pct">Meta Cobertura (%)</label>
                                <input type="number" step="0.01" min="0" max="100" class="form-control" id="meta_modal_logro_pedidos_pct" name="meta_logro_pedidos_pct" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" {{ !$metaTableDisponible ? 'disabled' : '' }}>Guardar Meta</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div id="av-report-data"
    class="d-none"
    data-periodos='{{ $periodosJson }}'
    data-vendedores='{{ $rankingVendedoresJson }}'
    data-productos='{{ $topProductosJson }}'></div>
@endsection

@section('scripts')
<script src="{{ asset('theme/plugins/chart.js/Chart.bundle.min.js') }}"></script>
<script>
    const avPalette = ['#0b2239', '#1d4f7a', '#0f766e', '#c78c1f', '#7c3aed', '#dc2626', '#0ea5e9', '#334155'];
    const reportDataNode = document.getElementById('av-report-data');

    (function renderPeriodos() {
        const data = reportDataNode ? JSON.parse(reportDataNode.dataset.periodos || '[]') : [];
        const periodosCtx = document.getElementById('chartPeriodos');
        if (!periodosCtx) return;

        new Chart(periodosCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: data.map(item => item.etiqueta),
                datasets: [{
                    label: 'Aprobados',
                    data: data.map(item => Number(item.pedidos_aprobados || 0)),
                    borderColor: '#1d4f7a',
                    backgroundColor: 'rgba(29, 79, 122, .12)',
                    pointBackgroundColor: '#1d4f7a',
                    borderWidth: 3,
                    tension: .35,
                    fill: true,
                }, {
                    label: 'Pagados',
                    data: data.map(item => Number(item.pedidos_pagados || 0)),
                    borderColor: '#0f766e',
                    backgroundColor: 'rgba(15, 118, 110, .10)',
                    pointBackgroundColor: '#0f766e',
                    borderWidth: 3,
                    tension: .35,
                    fill: false,
                }]
            },
            options: {
                responsive: true,
                tooltips: {
                    callbacks: {
                        label: function (context) {
                            return ' ' + context.dataset.label + ': ' + Number(context.parsed.y || 0).toLocaleString('es-VE');
                        }
                    }
                },
                legend: { display: true, position: 'bottom' },
                scales: {
                    yAxes: [{ ticks: { beginAtZero: true, precision: 0 } }]
                }
            }
        });
    })();

    (function renderVendedores() {
        const data = reportDataNode ? JSON.parse(reportDataNode.dataset.vendedores || '[]') : [];
        const vendedoresCtx = document.getElementById('chartVendedores');
        if (!vendedoresCtx) return;

        new Chart(vendedoresCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: data.map(item => item.vendedor_nombre),
                datasets: [{
                    label: 'Ventas USD',
                    data: data.map(item => Number(item.ventas_usd || 0)),
                    backgroundColor: data.map((_, idx) => avPalette[idx % avPalette.length]),
                    borderRadius: 8,
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                legend: { display: false },
                scales: {
                    yAxes: [{ ticks: { beginAtZero: true } }]
                }
            }
        });
    })();

    (function renderProductos() {
        const data = reportDataNode ? JSON.parse(reportDataNode.dataset.productos || '[]') : [];
        const productosCtx = document.getElementById('chartProductos');
        if (!productosCtx) return;

        new Chart(productosCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: data.map(item => item.codigo_inven),
                datasets: [{
                    data: data.map(item => Number(item.ventas_usd || 0)),
                    backgroundColor: data.map((_, idx) => avPalette[idx % avPalette.length]),
                    borderColor: '#ffffff',
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                legend: { position: 'bottom' }
            }
        });
    })();

    (function applyProgressWidths() {
        document.querySelectorAll('.av-progress > span[data-width]').forEach(function (bar) {
            bar.style.width = bar.dataset.width + '%';
        });
    })();

    (function setupMetaModal() {
        var modal = document.getElementById('modalEditarMeta');
        if (!modal || typeof window.jQuery === 'undefined') return;

        window.jQuery(modal).on('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;

            var vendedorId = button.getAttribute('data-vendedor-id') || '';
            var vendedorNombre = button.getAttribute('data-vendedor-nombre') || '-';
            var metaVentas = button.getAttribute('data-meta-ventas-usd') || '';
            var metaAprobados = button.getAttribute('data-meta-pedidos-aprobados') || '';
            var metaPagados = button.getAttribute('data-meta-pedidos-pagados') || '';
            var metaCobertura = button.getAttribute('data-meta-logro-pedidos-pct') || '';

            document.getElementById('meta_modal_vendedor_id').value = vendedorId;
            document.getElementById('meta_modal_vendedor_nombre').textContent = vendedorNombre;
            document.getElementById('meta_modal_ventas_usd').value = metaVentas;
            document.getElementById('meta_modal_pedidos_aprobados').value = metaAprobados;
            document.getElementById('meta_modal_pedidos_pagados').value = metaPagados;
            document.getElementById('meta_modal_logro_pedidos_pct').value = metaCobertura;
        });
    })();
</script>
@endsection