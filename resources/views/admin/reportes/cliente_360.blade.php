@extends('layouts.app')

@section('titulo', 'Vision 360 Cliente')
@section('titulo_header', 'Vision 360 Cliente')
@section('subtitulo_header', 'Analitica integral de compras, cobranza y cartera por cliente')

@section('styles')
<style>
    :root {
        --c360-navy: #0f243a;
        --c360-cyan: #1f6e8c;
        --c360-teal: #0f766e;
        --c360-gold: #c58a2a;
        --c360-muted: #64748b;
    }

    .c360-hero {
        border-radius: 22px;
        background: linear-gradient(130deg, #0d2235 0%, #1f6e8c 55%, #279ca7 100%);
        color: #fff;
        box-shadow: 0 20px 45px rgba(10, 29, 48, .25);
        position: relative;
        overflow: hidden;
    }

    .c360-hero::before,
    .c360-hero::after {
        content: "";
        position: absolute;
        border-radius: 50%;
        background: rgba(255,255,255,.08);
    }

    .c360-hero::before { width: 240px; height: 240px; right: -90px; top: -90px; }
    .c360-hero::after { width: 160px; height: 160px; left: -60px; bottom: -70px; }

    .c360-chip {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        border: 1px solid rgba(255,255,255,.25);
        background: rgba(255,255,255,.12);
        border-radius: 999px;
        padding: .35rem .75rem;
        font-size: .74rem;
        font-weight: 700;
    }

    .c360-kpi {
        border-radius: 16px;
        border: 1px solid #dce7f2;
        background: #fff;
        box-shadow: 0 8px 20px rgba(13, 36, 58, .08);
        padding: 14px;
        height: 100%;
    }

    .c360-kpi .label {
        font-size: .71rem;
        text-transform: uppercase;
        letter-spacing: .8px;
        color: var(--c360-muted);
        font-weight: 800;
    }

    .c360-kpi .value {
        font-size: 1.35rem;
        font-weight: 900;
        color: #1e293b;
        line-height: 1.15;
        margin-top: .15rem;
    }

    .c360-kpi .sub {
        font-size: .78rem;
        color: var(--c360-muted);
        margin-top: .2rem;
    }

    .c360-card {
        border-radius: 16px;
        border: 1px solid #dfe8f2;
        box-shadow: 0 8px 20px rgba(13, 36, 58, .08);
        overflow: hidden;
        background: #fff;
    }

    .c360-card .card-header {
        background: linear-gradient(180deg, #f8fbff 0%, #eef4fb 100%);
        border-bottom: 1px solid #dfe8f2;
        font-weight: 800;
        color: #15324c;
    }

    .c360-table thead th {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .7px;
        background: #f7fbff;
        color: #27405a;
        border-top: 0;
        border-bottom: 1px solid #e3ebf4;
        white-space: nowrap;
    }

    .c360-table tbody td {
        font-size: .86rem;
        color: #26384a;
        vertical-align: middle;
        border-top: 1px solid #edf3f8;
    }

    .c360-status {
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 800;
        padding: .25rem .65rem;
        display: inline-flex;
        align-items: center;
        gap: .3rem;
    }

    .c360-status.ok { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
    .c360-status.warn { background: #fff4cc; color: #9a6700; border: 1px solid #f7cc66; }
    .c360-status.off { background: #fee4e2; color: #b42318; border: 1px solid #fda29b; }

    .c360-mini { font-size: .75rem; color: var(--c360-muted); }

    .c360-progress {
        height: 8px;
        border-radius: 99px;
        background: #e7eff7;
        overflow: hidden;
    }

    .c360-progress > span {
        display: block;
        height: 100%;
        background: linear-gradient(90deg, #1f6e8c, #0f766e);
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="c360-hero p-4 mb-4">
        <div class="row align-items-center">
            <div class="col-lg-7 mb-3 mb-lg-0" style="z-index:2; position:relative;">
                <div class="c360-chip mb-2"><i class="fas fa-user-circle"></i> Reporte Gerencial</div>
                <h4 class="mb-1 font-weight-bold">Visi&oacute;n 360&ordm; Cliente</h4>
                @if($clienteSeleccionado)
                    <div class="mb-1" style="font-size:.95rem;opacity:.95;">
                        {{ $clienteSeleccionado->nombre }}
                        <span class="mx-2">|</span>
                        RIF {{ $clienteSeleccionado->rif ?: 'N/D' }}
                        <span class="mx-2">|</span>
                        Codcli {{ $clienteSeleccionado->codcli ?: 'N/D' }}
                    </div>
                    <div style="font-size:.82rem;opacity:.9;">
                        <i class="fas fa-phone mr-1"></i>{{ $clienteSeleccionado->telefono ?: 'Sin telefono' }}
                        <span class="mx-2">•</span>
                        <i class="fas fa-envelope mr-1"></i>{{ $clienteSeleccionado->email ?: 'Sin email' }}
                    </div>
                @endif
            </div>
            <div class="col-lg-5" style="z-index:2; position:relative;">
                <form method="GET" action="{{ route('admin.reportes.cliente.360') }}" class="bg-white rounded p-3" style="box-shadow:0 8px 20px rgba(7,35,58,.2);">
                    <label class="mb-1 font-weight-bold text-dark" style="font-size:.82rem;">Cliente</label>
                    <select class="form-control form-control-sm mb-2" name="cliente_id">
                        @foreach($clientes as $c)
                            <option value="{{ $c->id_selector }}" {{ optional($clienteSeleccionado)->id_selector === $c->id_selector ? 'selected' : '' }}>
                                {{ $c->nombre }} - {{ $c->rif ?: 'N/D' }}
                            </option>
                        @endforeach
                    </select>
                    <div class="row">
                        <div class="col-6">
                            <label class="mb-1 font-weight-bold text-dark" style="font-size:.74rem;">Desde</label>
                            <input type="date" class="form-control form-control-sm" name="fecha_desde" value="{{ $filtros['fecha_desde'] }}">
                        </div>
                        <div class="col-6">
                            <label class="mb-1 font-weight-bold text-dark" style="font-size:.74rem;">Hasta</label>
                            <input type="date" class="form-control form-control-sm" name="fecha_hasta" value="{{ $filtros['fecha_hasta'] }}">
                        </div>
                    </div>
                    <div class="d-flex flex-wrap mt-3" style="gap:.4rem;">
                        <button class="btn btn-sm btn-primary" type="submit"><i class="fas fa-filter"></i> Filtrar</button>
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.reportes.cliente.360', ['cliente_id' => optional($clienteSeleccionado)->id_selector]) }}"><i class="fas fa-eraser"></i> Limpiar</a>
                        <a class="btn btn-sm btn-outline-success" href="{{ route('admin.reportes.cliente.360.export.excel', ['cliente_id' => optional($clienteSeleccionado)->id_selector, 'fecha_desde' => $filtros['fecha_desde'], 'fecha_hasta' => $filtros['fecha_hasta']]) }}"><i class="fas fa-file-excel"></i> Excel</a>
                        <a class="btn btn-sm btn-outline-danger" target="_blank" href="{{ route('admin.reportes.cliente.360.export.pdf', ['cliente_id' => optional($clienteSeleccionado)->id_selector, 'fecha_desde' => $filtros['fecha_desde'], 'fecha_hasta' => $filtros['fecha_hasta']]) }}"><i class="fas fa-file-pdf"></i> PDF</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-6 col-lg-3 mb-3"><div class="c360-kpi"><div class="label">Ventas USD</div><div class="value">${{ number_format($resumen['ventas_usd'], 2, ',', '.') }}</div><div class="sub">Ticket: ${{ number_format($resumen['ticket_promedio'], 2, ',', '.') }}</div></div></div>
        <div class="col-6 col-lg-3 mb-3"><div class="c360-kpi"><div class="label">Pedidos</div><div class="value">{{ number_format($resumen['pedidos_total']) }}</div><div class="sub">Aprobados: {{ number_format($resumen['pedidos_aprobados']) }}</div></div></div>
        <div class="col-6 col-lg-3 mb-3"><div class="c360-kpi"><div class="label">Cobranza</div><div class="value">${{ number_format($resumen['pagos_aprobados'], 2, ',', '.') }}</div><div class="sub">En revision: ${{ number_format($resumen['pagos_revision'], 2, ',', '.') }}</div></div></div>
        <div class="col-6 col-lg-3 mb-3"><div class="c360-kpi"><div class="label">Cartera</div><div class="value">${{ number_format($resumen['saldo_cartera_usd'], 2, ',', '.') }}</div><div class="sub">Bs: {{ number_format($resumen['saldo_cartera_bs'], 2, ',', '.') }}</div></div></div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-8 mb-3">
            <div class="card c360-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-chart-line mr-2"></i>Ventas Mensuales (Ultimos 6 meses)</span>
                    <span class="c360-mini">Unidades: {{ number_format($resumen['unidades']) }}</span>
                </div>
                <div class="card-body"><canvas id="chartVentasMensuales" height="120"></canvas></div>
            </div>
        </div>
        <div class="col-lg-4 mb-3">
            <div class="card c360-card h-100">
                <div class="card-header"><i class="fas fa-tasks mr-2"></i>Distribucion de Estatus</div>
                <div class="card-body d-flex align-items-center justify-content-center"><canvas id="chartEstatusPedidos" height="210"></canvas></div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-6 mb-3">
            <div class="card c360-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center"><span><i class="fas fa-box-open mr-2"></i>Top Productos</span><span class="c360-mini">Por unidades</span></div>
                <div class="card-body">
                    <canvas id="chartTopProductos" height="180"></canvas>
                    <div class="table-responsive mt-3">
                        <table class="table table-sm c360-table mb-0">
                            <thead><tr><th>Producto</th><th class="text-right">Unid.</th><th class="text-right">Ventas USD</th></tr></thead>
                            <tbody>
                                @forelse($topProductos as $item)
                                    <tr>
                                        <td><div class="font-weight-bold">{{ $item->codigo_inven }}</div><div class="c360-mini">{{ \Illuminate\Support\Str::limit($item->descripcion, 46) }}</div></td>
                                        <td class="text-right">{{ number_format($item->unidades, 0, ',', '.') }}</td>
                                        <td class="text-right">${{ number_format($item->total_usd, 2, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted py-3">Sin datos disponibles</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-3">
            <div class="card c360-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center"><span><i class="fas fa-file-alt mr-2"></i>Ultimos Pedidos</span><span class="c360-mini">Ultimos 20</span></div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height:430px;overflow:auto;">
                        <table class="table table-sm c360-table mb-0">
                            <thead><tr><th>#</th><th>Fecha</th><th>Cliente</th><th class="text-right">USD</th><th>Estatus</th></tr></thead>
                            <tbody>
                                @forelse($ultimosPedidos as $pedido)
                                    @php
                                        $estatusUp = strtoupper((string) $pedido->estatus);
                                        $estatusClass = 'warn';
                                        if ($estatusUp === 'APROBADO') {
                                            $estatusClass = 'ok';
                                        } elseif (in_array($estatusUp, ['CANCELADO', 'ANULADO'])) {
                                            $estatusClass = 'off';
                                        }
                                    @endphp
                                    <tr>
                                        <td class="font-weight-bold">{{ $pedido->id }}</td>
                                        <td>{{ $pedido->fecha ? \Carbon\Carbon::parse($pedido->fecha)->format('d/m/Y') : '-' }}</td>
                                        <td><div>{{ \Illuminate\Support\Str::limit($pedido->descripcion, 28) }}</div><div class="c360-mini">Codcli: {{ $pedido->codcli ?: '-' }}</div></td>
                                        <td class="text-right">${{ number_format($pedido->total_usd, 2, ',', '.') }}</td>
                                        <td><span class="c360-status {{ $estatusClass }}">{{ $pedido->estatus ?: 'SIN ESTATUS' }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-3">Sin pedidos para mostrar</td></tr>
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
            <div class="card c360-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-receipt mr-2"></i>Estado de Cuenta y Saldos (CxC)</span>
                    <span class="c360-mini">Cobranza/Venta: {{ number_format($resumen['cobranza_vs_venta'], 2, ',', '.') }}%</span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 mb-2 mb-md-0">
                            <div class="c360-mini mb-1">Porcentaje de cobranza sobre venta</div>
                            <div class="c360-progress"><span style="width: {{ max(0, min(100, $resumen['cobranza_vs_venta'])) }}%;"></span></div>
                        </div>
                        <div class="col-md-8">
                            <div class="d-flex flex-wrap" style="gap:.5rem;">
                                <span class="c360-status ok"><i class="fas fa-layer-group"></i> Base: ${{ number_format($resumen['saldo_base_usd'], 2, ',', '.') }}</span>
                                <span class="c360-status warn"><i class="fas fa-receipt"></i> IVA Bs: {{ number_format($resumen['saldo_iva_bs'], 2, ',', '.') }}</span>
                                <span class="c360-status ok"><i class="fas fa-sliders-h"></i> Ajustes: ${{ number_format($resumen['saldo_ajustes_usd'], 2, ',', '.') }}</span>
                                <span class="c360-status off"><i class="fas fa-exclamation-triangle"></i> Vencidos: {{ number_format($resumen['pedidos_vencidos']) }}</span>
                                <span class="c360-status warn"><i class="fas fa-hourglass-half"></i> Vencen en 7 días: {{ number_format($resumen['pedidos_por_vencer']) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm c360-table mb-0">
                            <thead><tr><th>Pedido</th><th>Fecha</th><th>Cliente</th><th>Vencimiento</th><th class="text-right">Saldo USD</th><th class="text-right">Saldo Bs</th></tr></thead>
                            <tbody>
                                @forelse($carteraDetalle as $item)
                                    <tr>
                                        <td class="font-weight-bold">#{{ $item->id }}</td>
                                        <td>{{ $item->fecha ? \Carbon\Carbon::parse($item->fecha)->format('d/m/Y') : '-' }}</td>
                                        <td><div>{{ \Illuminate\Support\Str::limit((string) $item->descripcion, 40) }}</div><div class="c360-mini">Codcli: {{ $item->codcli ?: '-' }}</div></td>
                                        <td>
                                            @if(!empty($item->fecha_vencimiento))
                                                {{ \Carbon\Carbon::parse($item->fecha_vencimiento)->format('d/m/Y') }}
                                                @if(!is_null($item->dias_restantes))
                                                    <div class="c360-mini">
                                                        @if((int)$item->dias_restantes < 0)
                                                            Vencido {{ abs((int)$item->dias_restantes) }} d
                                                        @elseif((int)$item->dias_restantes === 0)
                                                            Vence hoy
                                                        @else
                                                            {{ (int)$item->dias_restantes }} d restantes
                                                        @endif
                                                    </div>
                                                @endif
                                            @else
                                                <span class="c360-mini">Sin crédito</span>
                                            @endif
                                        </td>
                                        <td class="text-right">${{ number_format($item->saldo_total_usd, 2, ',', '.') }}</td>
                                        <td class="text-right">{{ number_format($item->saldo_total_bs, 2, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-3">Sin saldos en cuentas por cobrar para el rango seleccionado</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('theme/plugins/chart.js/Chart.bundle.min.js') }}"></script>
<script>
    const paletaC360 = ['#0f243a', '#1f6e8c', '#0f766e', '#c58a2a', '#7c3aed', '#c2410c', '#0f766e', '#334155'];

    (function renderVentasMensuales() {
        const data = @json($ventasMensuales);
        const ctx = document.getElementById('chartVentasMensuales');
        if (!ctx) return;

        new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: data.map(item => item.etiqueta),
                datasets: [{
                    label: 'Ventas USD',
                    data: data.map(item => Number(item.ventas_usd || 0)),
                    borderColor: '#1f6e8c',
                    backgroundColor: 'rgba(31, 110, 140, .12)',
                    pointBackgroundColor: '#0f766e',
                    pointRadius: 4,
                    borderWidth: 3,
                    tension: .35,
                    fill: true,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return ' $' + Number(context.parsed.y || 0).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value) {
                                return '$' + Number(value).toLocaleString('es-VE');
                            }
                        }
                    }
                }
            }
        });
    })();

    (function renderEstatusPedidos() {
        const data = @json($estadoPedidos);
        const ctx = document.getElementById('chartEstatusPedidos');
        if (!ctx) return;

        new Chart(ctx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: data.map(item => item.estatus),
                datasets: [{
                    data: data.map(item => Number(item.cantidad || 0)),
                    backgroundColor: data.map((_, idx) => paletaC360[idx % paletaC360.length]),
                    borderColor: '#ffffff',
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    })();

    (function renderTopProductos() {
        const data = @json($topProductos);
        const ctx = document.getElementById('chartTopProductos');
        if (!ctx) return;

        new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: data.map(item => item.codigo_inven),
                datasets: [{
                    label: 'Unidades',
                    data: data.map(item => Number(item.unidades || 0)),
                    backgroundColor: data.map((_, idx) => paletaC360[idx % paletaC360.length]),
                    borderRadius: 6,
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false }
                }
            }
        });
    })();
</script>
@endsection
