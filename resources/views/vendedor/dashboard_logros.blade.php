@extends('layouts.app')

@section('titulo', 'Mi Dashboard de Logros')
@section('titulo_header', 'Mi Dashboard de Logros')
@section('subtitulo_header', 'Metas, ventas, pagos y comisiones en una sola vista')

@php
    $periodosJson = json_encode($periodos->values(), JSON_UNESCAPED_UNICODE);

    $anillos = [
        'clientes' => [
            'pct' => $resumen['clientes_activados_pct'],
        ],
        'meta' => [
            'pct' => $metaVentasRing['disponible'] ? $metaVentasRing['pct'] : null,
        ],
        'pedidos' => [
            'pct' => $resumen['pedidos_aprobacion_pct'],
        ],
    ];
    $anillosJson = json_encode($anillos, JSON_UNESCAPED_UNICODE);
@endphp

@section('styles')
<style>
    :root {
        --vd-navy: #0b2239;
        --vd-blue: #1d4f7a;
        --vd-teal: #0f766e;
        --vd-gold: #c78c1f;
        --vd-ink: #13202f;
        --vd-muted: #617083;
        --vd-soft: #eef4fb;
    }

    .historial-wrapper .vd-hero,
    .vd-hero {
        border-radius: 20px;
        background: linear-gradient(135deg, var(--vd-navy) 0%, var(--vd-blue) 100%);
        color: #fff;
        box-shadow: 0 16px 34px rgba(7, 28, 48, .22);
        position: relative;
        overflow: hidden;
    }

    .historial-wrapper .vd-hero::after,
    .vd-hero::after {
        content: '';
        position: absolute;
        width: 260px;
        height: 260px;
        right: -120px;
        top: -120px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .08);
        pointer-events: none;
    }

    .vd-chip {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .35rem .75rem;
        border-radius: 999px;
        font-size: .76rem;
        font-weight: 800;
        letter-spacing: .35px;
        background: rgba(255, 255, 255, .12);
        border: 1px solid rgba(255, 255, 255, .22);
    }

    .vd-panel,
    .vd-kpi,
    .vd-card {
        border-radius: 18px;
        background: #fff;
        border: 1px solid #dbe5ef;
        box-shadow: 0 10px 24px rgba(7, 28, 48, .08);
    }

    .vd-kpi {
        padding: 15px 16px;
        height: 100%;
    }

    .vd-kpi .label {
        color: var(--vd-muted);
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .9px;
        font-weight: 800;
    }

    .vd-kpi .value {
        color: var(--vd-ink);
        font-size: 1.45rem;
        line-height: 1.1;
        font-weight: 900;
        margin-top: .25rem;
    }

    .vd-kpi .sub {
        color: var(--vd-muted);
        font-size: .78rem;
        margin-top: .25rem;
    }

    .vd-card .card-header {
        background: linear-gradient(180deg, #f8fbff 0%, #eef4fb 100%);
        border-bottom: 1px solid #dbe5ef;
        color: #15324c;
        font-weight: 800;
    }

    .vd-table thead th {
        background: #f7fbff;
        border-top: 0;
        border-bottom: 1px solid #e5edf5;
        text-transform: uppercase;
        letter-spacing: .7px;
        font-size: .72rem;
        color: #294562;
        white-space: nowrap;
    }

    .vd-table tbody td {
        font-size: .86rem;
        color: #253647;
        border-top: 1px solid #edf3f8;
        vertical-align: middle;
    }

    .vd-badge {
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 800;
        padding: .25rem .65rem;
        display: inline-flex;
        align-items: center;
        gap: .3rem;
    }

    .vd-badge.ok {
        color: #166534;
        background: #dcfce7;
        border: 1px solid #86efac;
    }

    .vd-badge.warn {
        color: #92400e;
        background: #fff7d6;
        border: 1px solid #f6d97d;
    }

    .vd-badge.off {
        color: #b42318;
        background: #fee4e2;
        border: 1px solid #fda29b;
    }

    .vd-progress {
        height: 10px;
        border-radius: 99px;
        overflow: hidden;
        background: #e6eef6;
    }

    .vd-progress > span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #1f8a70, #1d4f7a);
    }

    .vd-ring-card .card-body {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .vd-ring-subtitle {
        font-size: .78rem;
        color: var(--vd-muted);
        text-align: center;
        margin-bottom: .75rem;
    }

    .vd-ring-wrap {
        position: relative;
        width: 168px;
        height: 168px;
        margin: 0 auto;
    }

    .vd-ring-value {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 1.55rem;
        font-weight: 900;
        color: var(--vd-ink);
        line-height: 1;
        white-space: nowrap;
    }

    .vd-ring-legend {
        display: flex;
        justify-content: center;
        gap: 1.25rem;
        flex-wrap: wrap;
        font-size: .82rem;
        color: var(--vd-muted);
        margin-top: 1rem;
    }

    .vd-ring-legend .dot {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 999px;
        margin-right: .35rem;
    }

    .vd-ring-legend strong {
        color: var(--vd-ink);
        margin-left: .25rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-3 historial-wrapper">
    <div class="vd-hero p-4 mb-4">
        <div class="row align-items-center">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <div class="vd-chip mb-2">
                    <i class="fas fa-medal"></i>
                    Desempeno Individual
                </div>
                <h4 class="mb-1 font-weight-bold">Mi Panel de Logros</h4>
                <div style="font-size:.95rem;opacity:.95;">Visualiza tu avance frente a metas, comportamiento de ventas, estado de pagos y comisiones.</div>
                <div style="font-size:.82rem;opacity:.84;" class="mt-2">
                    @if($vendedor)
                        Codigo: {{ $vendedor->codigo ?? 'N/D' }} | Estatus: {{ strtoupper(trim((string) ($vendedor->estatus ?? 'ACTIVO'))) ?: 'ACTIVO' }}
                    @else
                        Tu usuario no esta vinculado a un registro de vendedor. Contacta al administrador.
                    @endif
                </div>
            </div>
            <div class="col-lg-4">
                <form method="GET" action="{{ route('vendedor.dashboard.logros') }}" class="vd-panel p-3">
                    <div class="form-row">
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
                            <label class="mb-1 font-weight-bold text-dark" style="font-size:.82rem;">Periodo Meta</label>
                            <input type="text" class="form-control form-control-sm" name="periodo_objetivo" value="{{ $filtros['periodo_objetivo'] }}">
                        </div>
                        <div class="form-group col-6">
                            <label class="mb-1 font-weight-bold text-dark" style="font-size:.82rem;">Desde</label>
                            <input type="date" class="form-control form-control-sm" name="fecha_desde" value="{{ $filtros['fecha_desde'] }}">
                        </div>
                        <div class="form-group col-6">
                            <label class="mb-1 font-weight-bold text-dark" style="font-size:.82rem;">Hasta</label>
                            <input type="date" class="form-control form-control-sm" name="fecha_hasta" value="{{ $filtros['fecha_hasta'] }}">
                        </div>
                    </div>
                    <button class="btn btn-sm btn-primary" type="submit"><i class="fas fa-filter mr-1"></i>Actualizar</button>
                </form>
            </div>
        </div>
    </div>

    <div class="row mb-4 kpi-grid">
        <div class="col-6 col-lg-3 mb-3">
            <div class="vd-kpi">
                <div class="label">Ventas USD</div>
                <div class="value">${{ number_format($resumen['ventas_usd'], 2, ',', '.') }}</div>
                <div class="sub">Cobrado: ${{ number_format($resumen['pagos_aprobados_usd'], 2, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <div class="vd-kpi">
                <div class="label">Pedidos Aprobados</div>
                <div class="value">{{ number_format($resumen['pedidos_aprobados']) }}</div>
                <div class="sub">Pagados: {{ number_format($resumen['pedidos_pagados']) }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <div class="vd-kpi">
                <div class="label">Comision Acumulada</div>
                <div class="value">${{ number_format((float) $comisionesResumen->comision_total_usd, 2, ',', '.') }}</div>
                <div class="sub">Pendiente: ${{ number_format((float) $comisionesResumen->comision_pendiente_usd, 2, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <div class="vd-kpi">
                <div class="label">Cobertura de Pedidos</div>
                <div class="value">{{ number_format($resumen['cobertura_pedidos_pct'], 2, ',', '.') }}%</div>
                <div class="sub">Cobertura monto: {{ number_format($resumen['cobertura_monto_pct'], 2, ',', '.') }}%</div>
            </div>
        </div>
    </div>

    @php
        $ringColor = function ($pct) {
            if (is_null($pct)) {
                return '#94a3b8';
            }
            if ($pct >= 100) {
                return '#16a34a';
            }
            if ($pct >= 70) {
                return '#d97706';
            }
            return '#dc2626';
        };
        $ringTrack = '#e6eef6';
    @endphp

    <div class="row mb-4">
        <div class="col-12 mb-2">
            <h5 class="font-weight-bold mb-1" style="color:#15324c;">Indicadores de Cumplimiento</h5>
            <div style="font-size:.85rem;color:#60758a;">Un vistazo rapido a la activacion de clientes, el avance de la meta y la calidad de tus pedidos.</div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card vd-card h-100 vd-ring-card">
                <div class="card-header"><i class="fas fa-user-check mr-2"></i>Activacion de Clientes</div>
                <div class="card-body">
                    <div class="vd-ring-subtitle">Clientes con pedidos vs. cartera asignada en el periodo</div>
                    <div class="vd-ring-wrap">
                        <canvas id="ringClientes" width="168" height="168"></canvas>
                        <div class="vd-ring-value" id="ringClientesValue" style="color: {{ $ringColor($resumen['clientes_activados_pct']) }};">
                            {{ is_null($resumen['clientes_activados_pct']) ? 'N/D' : number_format($resumen['clientes_activados_pct'], 2, ',', '.') . '%' }}
                        </div>
                    </div>
                    <div class="vd-ring-legend">
                        <div><span class="dot" style="background: {{ $ringColor($resumen['clientes_activados_pct']) }};"></span>Activos<strong>{{ number_format($resumen['clientes_activos']) }}</strong></div>
                        <div><span class="dot" style="background: {{ $ringTrack }};"></span>Inactivos<strong>{{ number_format($resumen['clientes_inactivos']) }}</strong></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card vd-card h-100 vd-ring-card">
                <div class="card-header"><i class="fas fa-bullseye mr-2"></i>Cumplimiento de Meta</div>
                <div class="card-body">
                    <div class="vd-ring-subtitle">Ventas alcanzadas vs. meta propuesta para {{ $filtros['periodo_objetivo'] }}</div>
                    <div class="vd-ring-wrap">
                        <canvas id="ringMeta" width="168" height="168"></canvas>
                        <div class="vd-ring-value" id="ringMetaValue" style="color: {{ $ringColor($metaVentasRing['disponible'] ? $metaVentasRing['pct'] : null) }};">
                            {{ $metaVentasRing['disponible'] ? number_format($metaVentasRing['pct'], 2, ',', '.') . '%' : 'Sin meta' }}
                        </div>
                    </div>
                    <div class="vd-ring-legend">
                        @if($metaVentasRing['disponible'])
                            <div><span class="dot" style="background: {{ $ringColor($metaVentasRing['pct']) }};"></span>Alcanzado<strong>${{ number_format($metaVentasRing['ventas_alcanzadas'], 2, ',', '.') }}</strong></div>
                            <div><span class="dot" style="background: {{ $ringTrack }};"></span>Meta<strong>${{ number_format($metaVentasRing['meta_ventas'], 2, ',', '.') }}</strong></div>
                        @else
                            <div>No hay meta de ventas configurada para este periodo.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card vd-card h-100 vd-ring-card">
                <div class="card-header"><i class="fas fa-check-double mr-2"></i>Aprobacion de Pedidos</div>
                <div class="card-body">
                    <div class="vd-ring-subtitle">Pedidos aprobados vs. rechazados en el periodo</div>
                    <div class="vd-ring-wrap">
                        <canvas id="ringPedidos" width="168" height="168"></canvas>
                        <div class="vd-ring-value" id="ringPedidosValue" style="color: {{ $ringColor($resumen['pedidos_aprobacion_pct']) }};">
                            {{ is_null($resumen['pedidos_aprobacion_pct']) ? 'N/D' : number_format($resumen['pedidos_aprobacion_pct'], 2, ',', '.') . '%' }}
                        </div>
                    </div>
                    <div class="vd-ring-legend">
                        <div><span class="dot" style="background: {{ $ringColor($resumen['pedidos_aprobacion_pct']) }};"></span>Aprobados<strong>{{ number_format($resumen['pedidos_aprobados']) }}</strong></div>
                        <div><span class="dot" style="background: {{ $ringTrack }};"></span>Rechazados<strong>{{ number_format($resumen['pedidos_rechazados']) }}</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-8 mb-3">
            <div class="card vd-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-chart-line mr-2"></i>Evolucion de Ventas y Pagos</span>
                    <span style="font-size:.78rem;color:#60758a;">Periodo: {{ ucfirst($filtros['periodo']) }}</span>
                </div>
                <div class="card-body">
                    <canvas id="chartLogros" height="120"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-3">
            <div class="card vd-card h-100">
                <div class="card-header"><i class="fas fa-bullseye mr-2"></i>Meta Vigente</div>
                <div class="card-body">
                    @if(!$metaTableDisponible)
                        <div class="alert alert-warning mb-0">No hay tabla de metas manuales configurada.</div>
                    @elseif(!$metaActual)
                        <div class="alert alert-info mb-0">No tienes metas cargadas para {{ $filtros['periodo_objetivo'] }}.</div>
                    @else
                        <div class="mb-2"><strong>Periodo:</strong> {{ $filtros['periodo_objetivo'] }}</div>
                        <div class="mb-2">Meta ventas: <strong>${{ number_format((float) $metaActual->meta_ventas_usd, 2, ',', '.') }}</strong></div>
                        <div class="mb-2">Meta aprobados: <strong>{{ number_format((int) $metaActual->meta_pedidos_aprobados) }}</strong></div>
                        <div class="mb-2">Meta pagados: <strong>{{ number_format((int) $metaActual->meta_pedidos_pagados) }}</strong></div>
                        <div class="mb-2">Meta cobertura: <strong>{{ number_format((float) $metaActual->meta_logro_pedidos_pct, 2, ',', '.') }}%</strong></div>
                        <hr>
                        <div class="mb-2">Logro ventas: <span class="vd-badge {{ ($metaActual->logro_ventas_pct ?? 0) >= 100 ? 'ok' : (($metaActual->logro_ventas_pct ?? 0) >= 70 ? 'warn' : 'off') }}">{{ is_null($metaActual->logro_ventas_pct) ? 'N/D' : number_format($metaActual->logro_ventas_pct, 2, ',', '.') . '%' }}</span></div>
                        <div class="mb-2">Logro aprobados: <span class="vd-badge {{ ($metaActual->logro_aprobados_pct ?? 0) >= 100 ? 'ok' : (($metaActual->logro_aprobados_pct ?? 0) >= 70 ? 'warn' : 'off') }}">{{ is_null($metaActual->logro_aprobados_pct) ? 'N/D' : number_format($metaActual->logro_aprobados_pct, 2, ',', '.') . '%' }}</span></div>
                        <div>Logro pagados: <span class="vd-badge {{ ($metaActual->logro_pagados_pct ?? 0) >= 100 ? 'ok' : (($metaActual->logro_pagados_pct ?? 0) >= 70 ? 'warn' : 'off') }}">{{ is_null($metaActual->logro_pagados_pct) ? 'N/D' : number_format($metaActual->logro_pagados_pct, 2, ',', '.') . '%' }}</span></div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card vd-card table-card">
                <div class="card-header"><i class="fas fa-table mr-2"></i>Detalle por Periodo</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm vd-table mb-0">
                            <thead>
                                <tr>
                                    <th>Periodo</th>
                                    <th class="text-right">Ventas USD</th>
                                    <th class="text-right">Pagos USD</th>
                                    <th class="text-right">Aprobados</th>
                                    <th class="text-right">Pagados</th>
                                    <th class="text-right">Cobertura</th>
                                    <th class="text-right">Meta Ventas</th>
                                    <th class="text-right">Logro vs Meta</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($periodos as $periodo)
                                    <tr>
                                        <td class="font-weight-bold">{{ $periodo->etiqueta }}</td>
                                        <td class="text-right">${{ number_format($periodo->ventas_usd, 2, ',', '.') }}</td>
                                        <td class="text-right">${{ number_format($periodo->pagos_aprobados_usd, 2, ',', '.') }}</td>
                                        <td class="text-right">{{ number_format($periodo->pedidos_aprobados) }}</td>
                                        <td class="text-right">{{ number_format($periodo->pedidos_pagados) }}</td>
                                        <td class="text-right">{{ number_format($periodo->cobertura_pedidos_pct, 2, ',', '.') }}%</td>
                                        <td class="text-right">
                                            @if(!empty($periodo->meta_ventas_usd))
                                                ${{ number_format($periodo->meta_ventas_usd, 2, ',', '.') }}
                                            @else
                                                <span class="text-muted">N/D</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            @if(!is_null($periodo->logro_vs_meta_ventas_pct))
                                                <div class="vd-progress mb-1"><span data-width="{{ max(0, min(100, $periodo->logro_vs_meta_ventas_pct)) }}"></span></div>
                                                <span class="vd-badge {{ $periodo->logro_vs_meta_ventas_pct >= 100 ? 'ok' : ($periodo->logro_vs_meta_ventas_pct >= 70 ? 'warn' : 'off') }}">{{ number_format($periodo->logro_vs_meta_ventas_pct, 2, ',', '.') }}%</span>
                                            @else
                                                <span class="text-muted">Sin meta</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">Sin informacion para el rango seleccionado.</td>
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
        <div class="col-lg-6 mb-3">
            <div class="vd-kpi">
                <div class="label">Pagos del Periodo</div>
                <div class="value">{{ number_format((int) $pagosResumen->pagos_total) }}</div>
                <div class="sub">Aprobados: {{ number_format((int) $pagosResumen->pagos_aprobados) }} | En revision: {{ number_format((int) $pagosResumen->pagos_revision) }}</div>
                <div class="sub">Monto aprobado: ${{ number_format((float) $pagosResumen->monto_aprobado_usd, 2, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-lg-6 mb-3">
            <div class="vd-kpi">
                <div class="label">Comisiones del Periodo</div>
                <div class="value">${{ number_format((float) $comisionesResumen->comision_total_usd, 2, ',', '.') }}</div>
                <div class="sub">Pagadas: ${{ number_format((float) $comisionesResumen->comision_pagada_usd, 2, ',', '.') }} | Pendientes: ${{ number_format((float) $comisionesResumen->comision_pendiente_usd, 2, ',', '.') }}</div>
                <div class="sub">Registros: {{ number_format((int) $comisionesResumen->registros) }}</div>
            </div>
        </div>
    </div>
</div>

<div id="vd-data" class="d-none" data-periodos='{{ $periodosJson }}' data-anillos='{{ $anillosJson }}'></div>
@endsection

@section('scripts')
<script src="{{ asset('theme/plugins/chart.js/Chart.bundle.min.js') }}"></script>
<script>
    (function renderChart() {
        var node = document.getElementById('vd-data');
        var chart = document.getElementById('chartLogros');
        if (!node || !chart) return;

        var periodos = JSON.parse(node.dataset.periodos || '[]');
        new Chart(chart.getContext('2d'), {
            type: 'bar',
            data: {
                labels: periodos.map(function (p) { return p.etiqueta; }),
                datasets: [{
                    label: 'Ventas USD',
                    backgroundColor: '#1d4f7a',
                    borderRadius: 8,
                    data: periodos.map(function (p) { return Number(p.ventas_usd || 0); })
                }, {
                    label: 'Pagos USD',
                    backgroundColor: '#0d9488',
                    borderRadius: 8,
                    data: periodos.map(function (p) { return Number(p.pagos_aprobados_usd || 0); })
                }]
            },
            options: {
                responsive: true,
                legend: { position: 'bottom' },
                scales: {
                    yAxes: [{ ticks: { beginAtZero: true } }]
                }
            }
        });
    })();

    (function applyProgressWidths() {
        document.querySelectorAll('.vd-progress > span[data-width]').forEach(function (bar) {
            bar.style.width = bar.dataset.width + '%';
        });
    })();

    (function renderRings() {
        var node = document.getElementById('vd-data');
        if (!node) return;

        var anillos = JSON.parse(node.dataset.anillos || '{}');
        var trackColor = '#e6eef6';

        function ringColor(pct) {
            if (pct === null || typeof pct === 'undefined') return '#94a3b8';
            if (pct >= 100) return '#16a34a';
            if (pct >= 70) return '#d97706';
            return '#dc2626';
        }

        function drawRing(canvasId, pct) {
            var canvas = document.getElementById(canvasId);
            if (!canvas || typeof Chart === 'undefined') return;

            var hasValue = (pct !== null && typeof pct !== 'undefined');
            var capped = hasValue ? Math.max(0, Math.min(100, pct)) : 0;
            var color = ringColor(hasValue ? pct : null);

            new Chart(canvas.getContext('2d'), {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: hasValue ? [capped, 100 - capped] : [0, 100],
                        backgroundColor: [color, trackColor],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutoutPercentage: 78,
                    rotation: -0.5 * Math.PI,
                    circumference: Math.PI * 2,
                    tooltips: { enabled: false },
                    legend: { display: false },
                    animation: { animateRotate: true }
                }
            });
        }

        drawRing('ringClientes', anillos.clientes ? anillos.clientes.pct : null);
        drawRing('ringMeta', anillos.meta ? anillos.meta.pct : null);
        drawRing('ringPedidos', anillos.pedidos ? anillos.pedidos.pct : null);
    })();
</script>
@endsection
