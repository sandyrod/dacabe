@extends('layouts.app')

@section('titulo', 'Dashboard de Inventario')
@section('titulo_header', 'Dashboard Gerencial de Inventario')
@section('subtitulo_header', 'Análisis visual y alertas de stock')

@section('styles')
<link rel="stylesheet" href="{{ asset('theme/plugins/chart.js/Chart.min.css') }}">
<style>
.kpi-dash {
    border-radius:.75rem; padding:1.2rem 1.4rem; color:#fff;
    display:flex; align-items:center; gap:1rem;
    box-shadow:0 4px 20px rgba(0,0,0,.12);
}
.kpi-dash i { font-size:2.2rem; opacity:.7; }
.kpi-dash .val { font-size:1.7rem; font-weight:800; line-height:1; }
.kpi-dash .lbl { font-size:.73rem; opacity:.85; text-transform:uppercase; letter-spacing:.6px; margin-top:2px; }
.chart-card {
    border:0; border-radius:.75rem;
    box-shadow:0 2px 12px rgba(0,0,0,.08);
}
.chart-card .card-header {
    border-radius:.75rem .75rem 0 0;
    font-weight:700; font-size:.9rem;
    padding:.75rem 1rem;
}
.chart-wrapper { position:relative; }
/* Tabla críticos */
.tbl-crit { font-size:.78rem; }
.tbl-crit thead th { background:#c53030; color:#fff; font-weight:600; padding:.4rem .6rem; white-space:nowrap; }
.tbl-crit tbody td { padding:.35rem .6rem; border-bottom:1px solid #f0f0f0; vertical-align:middle; }
.tbl-crit tbody tr:hover { background:#fff5f5; }
.bar-critico { background:#fee2e2; }
.prod-pct { height:6px; border-radius:3px; background:#e53e3e; }
.badge-stock-ok   { background:#d4edda; color:#155724; padding:2px 8px; border-radius:10px; font-size:.7rem; }
.badge-stock-crit { background:#f8d7da; color:#721c24; padding:2px 8px; border-radius:10px; font-size:.7rem; }
</style>
@endsection

@section('content')
<div class="container-fluid">

    {{-- ── KPIs ── --}}
    <div class="row mb-4">
        <div class="col-6 col-md-3 mb-3">
            <div class="kpi-dash" style="background:linear-gradient(135deg,#1e3a5f,#2d5282)">
                <i class="fas fa-cubes"></i>
                <div>
                    <div class="val">{{ number_format($resumen['total_productos']) }}</div>
                    <div class="lbl">Productos activos</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="kpi-dash" style="background:linear-gradient(135deg,#155724,#28a745)">
                <i class="fas fa-boxes"></i>
                <div>
                    <div class="val">{{ number_format($resumen['con_stock']) }}</div>
                    <div class="lbl">Con stock disponible</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="kpi-dash" style="background:linear-gradient(135deg,#2d6a4f,#40916c)">
                <i class="fas fa-dollar-sign"></i>
                <div>
                    <div class="val">${{ number_format($resumen['valor_total_usd'], 0) }}</div>
                    <div class="lbl">Valor total USD</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="kpi-dash" style="background:linear-gradient(135deg,#c53030,#e53e3e)">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <div class="val">{{ number_format($resumen['total_criticos']) }}</div>
                    <div class="lbl">Stock crítico</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Fila 1: Distribución por grupo + Top valor USD ── --}}
    <div class="row mb-4">

        {{-- Doughnut por grupo --}}
        <div class="col-md-5 mb-3">
            <div class="card chart-card h-100">
                <div class="card-header" style="background:linear-gradient(90deg,#1e3a5f,#2d5282);color:#fff">
                    <i class="fas fa-chart-pie mr-2"></i>Distribución por Grupo (Unidades)
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div class="chart-wrapper" style="width:100%;max-width:380px;margin:0 auto">
                        <canvas id="chartGrupo" height="260"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bar: top 15 valor USD --}}
        <div class="col-md-7 mb-3">
            <div class="card chart-card h-100">
                <div class="card-header" style="background:linear-gradient(90deg,#2d6a4f,#40916c);color:#fff">
                    <i class="fas fa-trophy mr-2"></i>Top 15 Productos por Valor en Inventario (USD)
                </div>
                <div class="card-body">
                    <div class="chart-wrapper">
                        <canvas id="chartTopValor" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Fila 2: Stock por depósito + Críticos ── --}}
    <div class="row mb-4">

        {{-- Bar: stock por depósito --}}
        <div class="col-md-5 mb-3">
            <div class="card chart-card h-100">
                <div class="card-header" style="background:linear-gradient(90deg,#553c9a,#7b5ea7);color:#fff">
                    <i class="fas fa-warehouse mr-2"></i>Stock Total por Depósito
                </div>
                <div class="card-body">
                    <div class="chart-wrapper">
                        <canvas id="chartDeposito" height="220"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla: Alertas de stock crítico --}}
        <div class="col-md-7 mb-3">
            <div class="card chart-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center"
                     style="background:linear-gradient(90deg,#c53030,#e53e3e);color:#fff">
                    <span><i class="fas fa-bell mr-2"></i>Alertas de Stock Crítico</span>
                    <span class="badge badge-light text-danger">{{ $criticos->count() }}</span>
                </div>
                <div class="card-body p-0" style="overflow-y:auto;max-height:350px">
                    @if($criticos->isEmpty())
                        <div class="text-center text-success py-4">
                            <i class="fas fa-check-circle fa-2x mb-2 d-block"></i>
                            Sin alertas — todos los stocks están sobre el mínimo.
                        </div>
                    @else
                    <table class="table tbl-crit mb-0">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Descripción</th>
                                <th class="text-center">Stock</th>
                                <th class="text-center">Mín.</th>
                                <th class="text-center">Déficit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($criticos as $c)
                            @php
                                $deficit = max(0, $c->SMIN - $c->total_stock);
                                $pct     = $c->SMIN > 0 ? min(100, $c->total_stock / $c->SMIN * 100) : 0;
                            @endphp
                            <tr class="{{ $c->total_stock <= 0 ? 'bar-critico' : '' }}">
                                <td><code class="small font-weight-bold text-danger">{{ $c->CODIGO }}</code></td>
                                <td class="small">{{ Str::limit($c->DESCR, 35) }}</td>
                                <td class="text-center">
                                    <span class="{{ $c->total_stock <= 0 ? 'badge-stock-crit' : 'badge-stock-ok' }}">
                                        {{ number_format($c->total_stock, 0) }}
                                    </span>
                                </td>
                                <td class="text-center text-muted small">{{ number_format($c->SMIN, 0) }}</td>
                                <td class="text-center">
                                    <div style="width:60px;height:6px;background:#f0f0f0;border-radius:3px;margin:0 auto 2px">
                                        <div class="prod-pct" style="width:{{ $pct }}%"></div>
                                    </div>
                                    <span class="small text-danger font-weight-bold">-{{ number_format($deficit, 0) }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script src="{{ asset('theme/plugins/chart.js/Chart.bundle.min.js') }}"></script>
<script>
// ── Paleta ──────────────────────────────────────────────────────────────────
const PALETA = [
    '#1e3a5f','#2980b9','#40916c','#c9a84c','#553c9a',
    '#e53e3e','#2d6a4f','#7b5ea7','#b7950b','#1b4f72',
    '#117a65','#784212','#6c3483','#1a5276','#4d0000',
];

function rgba(hex, a) {
    const r = parseInt(hex.slice(1,3),16);
    const g = parseInt(hex.slice(3,5),16);
    const b = parseInt(hex.slice(5,7),16);
    return `rgba(${r},${g},${b},${a})`;
}

// ── 1. Doughnut: Distribución por grupo ─────────────────────────────────────
(function () {
    const data  = @json($stockPorGrupo);
    const ctx   = document.getElementById('chartGrupo').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.map(d => d.DGRUPO || d.CGRUPO || 'Sin grupo'),
            datasets: [{
                data:            data.map(d => parseFloat(d.total)),
                backgroundColor: data.map((_, i) => PALETA[i % PALETA.length]),
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'right', labels: { font: { size: 11 }, padding: 12 } },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            const total = ctx.dataset.data.reduce((a,b)=>a+b, 0);
                            const pct   = ((ctx.parsed / total) * 100).toFixed(1);
                            return ` ${ctx.label}: ${ctx.parsed.toLocaleString()} u (${pct}%)`;
                        }
                    }
                }
            }
        }
    });
})();

// ── 2. Bar: Top 15 por valor USD ────────────────────────────────────────────
(function () {
    const data = @json($topValorUSD);
    const ctx  = document.getElementById('chartTopValor').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(d => d.CODIGO),
            datasets: [{
                label: 'Valor USD',
                data:  data.map(d => parseFloat(d.valor_usd).toFixed(2)),
                backgroundColor: data.map((_, i) => rgba(PALETA[i % PALETA.length], .8)),
                borderColor:     data.map((_, i) => PALETA[i % PALETA.length]),
                borderWidth: 1,
                borderRadius: 4,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ` $${parseFloat(ctx.parsed.x).toLocaleString('es-VE', {minimumFractionDigits:2})}`,
                        afterLabel: ctx => {
                            const d = data[ctx.dataIndex];
                            return ` ${parseFloat(d.total_unidades).toLocaleString()} unidades`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(0,0,0,.05)' },
                    ticks: { callback: v => '$' + Number(v).toLocaleString() }
                },
                y: { ticks: { font: { size: 11 }, color: '#1e3a5f' } }
            }
        }
    });
})();

// ── 3. Bar: Stock por depósito ───────────────────────────────────────────────
(function () {
    const data = @json($stockPorDeposito);
    const ctx  = document.getElementById('chartDeposito').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(d => d.DDEPOS || d.CDEPOS),
            datasets: [{
                label: 'Unidades en stock',
                data:  data.map(d => parseFloat(d.total)),
                backgroundColor: data.map((_, i) => rgba(PALETA[(i+4) % PALETA.length], .85)),
                borderColor:     data.map((_, i) => PALETA[(i+4) % PALETA.length]),
                borderWidth: 1,
                borderRadius: 5,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => ` ${ctx.parsed.y.toLocaleString()} unidades` } }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,.05)' },
                    ticks: { callback: v => v.toLocaleString() }
                },
                x: { ticks: { font: { size: 10 } } }
            }
        }
    });
})();
</script>
@endsection
