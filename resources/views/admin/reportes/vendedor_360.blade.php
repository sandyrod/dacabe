@extends('layouts.app')

@section('titulo', 'Vision 360 Vendedor')
@section('titulo_header', 'Vision 360 Vendedor')
@section('subtitulo_header', 'Pantalla gerencial integral por vendedor')

@section('styles')
<style>
    :root {
        --v360-navy: #0d2b45;
        --v360-blue: #1f5f8b;
        --v360-green: #1f8a70;
        --v360-amber: #d4a017;
        --v360-soft: #eef4f9;
        --v360-ink: #1a2632;
        --v360-muted: #6a7785;
    }

    .v360-hero {
        border-radius: 22px;
        background: linear-gradient(125deg, #0c243a 0%, #114166 45%, #1f5f8b 100%);
        color: #fff;
        box-shadow: 0 18px 45px rgba(12, 36, 58, .25);
        overflow: hidden;
        position: relative;
    }

    .v360-hero::before,
    .v360-hero::after {
        content: "";
        position: absolute;
        border-radius: 999px;
        background: rgba(255, 255, 255, .08);
        pointer-events: none;
    }

    .v360-hero::before {
        width: 280px;
        height: 280px;
        right: -100px;
        top: -120px;
    }

    .v360-hero::after {
        width: 170px;
        height: 170px;
        left: -60px;
        bottom: -70px;
        background: rgba(212, 160, 23, .18);
    }

    .v360-chip {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, .25);
        background: rgba(255, 255, 255, .1);
        padding: .4rem .75rem;
        font-size: .75rem;
        font-weight: 700;
    }

    .v360-kpi {
        border-radius: 18px;
        background: #fff;
        border: 1px solid #dfe9f3;
        box-shadow: 0 10px 24px rgba(11, 39, 63, .08);
        height: 100%;
        padding: 14px 15px;
    }

    .v360-kpi .kpi-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: .4rem;
    }

    .v360-kpi .kpi-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
    }

    .v360-kpi .kpi-label {
        font-size: .7rem;
        text-transform: uppercase;
        letter-spacing: .8px;
        color: var(--v360-muted);
        font-weight: 800;
    }

    .v360-kpi .kpi-value {
        font-size: 1.35rem;
        font-weight: 900;
        color: var(--v360-ink);
        line-height: 1.1;
    }

    .v360-kpi .kpi-sub {
        font-size: .78rem;
        color: var(--v360-muted);
        margin-top: .2rem;
    }

    .v360-card {
        border-radius: 18px;
        border: 1px solid #dfe8f2;
        box-shadow: 0 10px 24px rgba(11, 39, 63, .08);
        overflow: hidden;
        background: #fff;
    }

    .v360-card .card-header {
        background: linear-gradient(180deg, #f8fbff 0%, #eef4fb 100%);
        border-bottom: 1px solid #dfe8f2;
        font-weight: 800;
        color: #15324c;
    }

    .v360-table thead th {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .7px;
        color: #294562;
        border-top: 0;
        border-bottom: 1px solid #e5edf5;
        background: #f7fbff;
        white-space: nowrap;
    }

    .v360-table tbody td {
        font-size: .86rem;
        vertical-align: middle;
        border-top: 1px solid #edf3f8;
        color: #26384a;
    }

    .v360-progress {
        height: 8px;
        border-radius: 99px;
        background: #e6eef6;
        overflow: hidden;
    }

    .v360-progress > span {
        height: 100%;
        display: block;
        border-radius: inherit;
        background: linear-gradient(90deg, #1f8a70, #1f5f8b);
    }

    .v360-status {
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 800;
        padding: .25rem .65rem;
        display: inline-flex;
        align-items: center;
        gap: .3rem;
    }

    .v360-status.ok {
        color: #166534;
        background: #dcfce7;
        border: 1px solid #86efac;
    }

    .v360-status.warn {
        color: #9a6700;
        background: #fff4cc;
        border: 1px solid #f7cc66;
    }

    .v360-status.off {
        color: #b42318;
        background: #fee4e2;
        border: 1px solid #fda29b;
    }

    .v360-mini {
        font-size: .75rem;
        color: var(--v360-muted);
    }

    @media (max-width: 768px) {
        .v360-hero h4 {
            font-size: 1.1rem;
        }

        .v360-kpi .kpi-value {
            font-size: 1.15rem;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="v360-hero p-4 mb-4">
        <div class="row align-items-center">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <div class="v360-chip mb-2">
                    <i class="fas fa-chart-pie"></i>
                    Reporte Gerencial
                </div>
                <h4 class="mb-1 font-weight-bold">Visi&oacute;n 360&ordm; Vendedor</h4>
                @if($vendedorSeleccionado)
                    <div class="mb-2" style="font-size:.95rem;opacity:.95;">
                        <i class="fas fa-user-tie mr-1"></i>
                        {{ $vendedorSeleccionado->nombre }}
                        <span class="mx-2">|</span>
                        Cod. {{ $vendedorSeleccionado->codigo }}
                        <span class="mx-2">|</span>
                        {{ $vendedorSeleccionado->email }}
                    </div>
                @else
                    <div class="mb-2" style="font-size:.95rem;opacity:.95;">
                        No hay vendedor seleccionado
                    </div>
                @endif
                <div style="font-size:.84rem;opacity:.85;">
                    Estadisticas integrales de pedidos, facturacion, producto lider, cobranza y estado de cuenta.
                </div>
            </div>

            <div class="col-lg-4">
                <form method="GET" action="{{ route('admin.reportes.vendedor.360') }}" class="bg-white rounded p-3" style="box-shadow:0 8px 20px rgba(7,35,58,.2);">
                    <label class="mb-1 font-weight-bold text-dark" style="font-size:.82rem;">Vendedor</label>
                    <select class="form-control form-control-sm mb-2" name="vendedor_id">
                        @foreach($vendedores as $v)
                            <option value="{{ $v->id }}" {{ (int) optional($vendedorSeleccionado)->id === (int) $v->id ? 'selected' : '' }}>
                                {{ $v->codigo }} - {{ $v->nombre }}
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
                        <button class="btn btn-sm btn-primary" type="submit">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.reportes.vendedor.360', ['vendedor_id' => optional($vendedorSeleccionado)->id]) }}">
                            <i class="fas fa-eraser"></i> Limpiar
                        </a>
                        <a class="btn btn-sm btn-outline-success" href="{{ route('admin.reportes.vendedor.360.export.excel', ['vendedor_id' => optional($vendedorSeleccionado)->id, 'fecha_desde' => $filtros['fecha_desde'], 'fecha_hasta' => $filtros['fecha_hasta']]) }}">
                            <i class="fas fa-file-excel"></i> Excel
                        </a>
                        <a class="btn btn-sm btn-outline-danger" target="_blank" href="{{ route('admin.reportes.vendedor.360.export.pdf', ['vendedor_id' => optional($vendedorSeleccionado)->id, 'fecha_desde' => $filtros['fecha_desde'], 'fecha_hasta' => $filtros['fecha_hasta']]) }}">
                            <i class="fas fa-file-pdf"></i> PDF
                        </a>
                    </div>
                    <div class="v360-mini mt-2">
                        <i class="fas fa-calendar-alt mr-1"></i>
                        Puedes filtrar por fechas y exportar el mismo resultado.
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-6 col-lg-3 mb-3">
            <div class="v360-kpi">
                <div class="kpi-top">
                    <div class="kpi-label">Ventas Totales</div>
                    <span class="kpi-icon" style="background:linear-gradient(135deg,#1f5f8b,#0d2b45)"><i class="fas fa-dollar-sign"></i></span>
                </div>
                <div class="kpi-value">${{ number_format($resumen['ventas_usd'], 2, ',', '.') }}</div>
                <div class="kpi-sub">Ticket prom.: ${{ number_format($resumen['ticket_promedio'], 2, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <div class="v360-kpi">
                <div class="kpi-top">
                    <div class="kpi-label">Pedidos</div>
                    <span class="kpi-icon" style="background:linear-gradient(135deg,#1f8a70,#1f5f8b)"><i class="fas fa-file-invoice"></i></span>
                </div>
                <div class="kpi-value">{{ number_format($resumen['pedidos_total']) }}</div>
                <div class="kpi-sub">Aprobados: {{ number_format($resumen['pedidos_aprobados']) }} | Pendientes: {{ number_format($resumen['pedidos_pendientes']) }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <div class="v360-kpi">
                <div class="kpi-top">
                    <div class="kpi-label">Cobranza</div>
                    <span class="kpi-icon" style="background:linear-gradient(135deg,#d4a017,#9a6700)"><i class="fas fa-wallet"></i></span>
                </div>
                <div class="kpi-value">${{ number_format($resumen['pagos_aprobados'], 2, ',', '.') }}</div>
                <div class="kpi-sub">En revision: ${{ number_format($resumen['pagos_revision'], 2, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <div class="v360-kpi">
                <div class="kpi-top">
                    <div class="kpi-label">Saldo Cartera</div>
                    <span class="kpi-icon" style="background:linear-gradient(135deg,#7c3aed,#1f5f8b)"><i class="fas fa-balance-scale"></i></span>
                </div>
                <div class="kpi-value">${{ number_format($resumen['saldo_cartera_usd'], 2, ',', '.') }}</div>
                <div class="kpi-sub">Bs: {{ number_format($resumen['saldo_cartera_bs'], 2, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-8 mb-3">
            <div class="card v360-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-chart-line mr-2"></i>Ventas Mensuales (Ultimos 6 meses)</span>
                    <span class="v360-mini">Total unidades: {{ number_format($resumen['unidades']) }}</span>
                </div>
                <div class="card-body">
                    <canvas id="chartVentasMensuales" height="120"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-3">
            <div class="card v360-card h-100">
                <div class="card-header">
                    <i class="fas fa-tasks mr-2"></i>Distribucion de Estatus
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <canvas id="chartEstatusPedidos" height="210"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-6 mb-3">
            <div class="card v360-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-box-open mr-2"></i>Top Productos Vendidos</span>
                    <span class="v360-mini">Por unidades</span>
                </div>
                <div class="card-body">
                    <canvas id="chartTopProductos" height="180"></canvas>
                    <div class="table-responsive mt-3">
                        <table class="table table-sm v360-table mb-0">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-right">Unid.</th>
                                    <th class="text-right">Ventas USD</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topProductos as $item)
                                    <tr>
                                        <td>
                                            <div class="font-weight-bold">{{ $item->codigo_inven }}</div>
                                            <div class="v360-mini">{{ \Illuminate\Support\Str::limit($item->descripcion, 46) }}</div>
                                        </td>
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
            <div class="card v360-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-file-alt mr-2"></i>Ultimos Pedidos</span>
                    <span class="v360-mini">Ultimos 20 registros</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height:430px;overflow:auto;">
                        <table class="table table-sm v360-table mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th class="text-right">USD</th>
                                    <th>Estatus</th>
                                </tr>
                            </thead>
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
                                        <td>
                                            <div>{{ \Illuminate\Support\Str::limit($pedido->descripcion, 28) }}</div>
                                            <div class="v360-mini">Codcli: {{ $pedido->codcli ?: '-' }}</div>
                                        </td>
                                        <td class="text-right">${{ number_format($pedido->total_usd, 2, ',', '.') }}</td>
                                        <td><span class="v360-status {{ $estatusClass }}">{{ $pedido->estatus ?: 'SIN ESTATUS' }}</span></td>
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
            <div class="card v360-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-receipt mr-2"></i>Estado de Cuenta y Saldos</span>
                    <span class="v360-mini">Cobranza/Venta: {{ number_format($resumen['cobranza_vs_venta'], 2, ',', '.') }}%</span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 mb-2 mb-md-0">
                            <div class="v360-mini mb-1">Porcentaje de cobranza sobre venta</div>
                            <div class="v360-progress">
                                <span style="width: {{ max(0, min(100, $resumen['cobranza_vs_venta'])) }}%;"></span>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="d-flex flex-wrap" style="gap:.5rem;">
                                <span class="v360-status ok"><i class="fas fa-layer-group"></i> Saldo Base: ${{ number_format($resumen['saldo_base_usd'], 2, ',', '.') }}</span>
                                <span class="v360-status warn"><i class="fas fa-receipt"></i> Saldo IVA Bs: {{ number_format($resumen['saldo_iva_bs'], 2, ',', '.') }}</span>
                                <span class="v360-status ok"><i class="fas fa-sliders-h"></i> Ajustes: ${{ number_format($resumen['saldo_ajustes_usd'], 2, ',', '.') }}</span>
                                <span class="v360-status off"><i class="fas fa-exclamation-triangle"></i> Vencidos: {{ number_format($resumen['pedidos_vencidos']) }}</span>
                                <span class="v360-status warn"><i class="fas fa-hourglass-half"></i> Vencen en 7 días: {{ number_format($resumen['pedidos_por_vencer']) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm v360-table mb-0">
                            <thead>
                                <tr>
                                    <th>Pedido</th>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>Vencimiento</th>
                                    <th class="text-right">Saldo USD</th>
                                    <th class="text-right">Saldo Bs</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($carteraDetalle as $item)
                                    <tr>
                                        <td class="font-weight-bold">#{{ $item->id }}</td>
                                        <td>{{ $item->fecha ? \Carbon\Carbon::parse($item->fecha)->format('d/m/Y') : '-' }}</td>
                                        <td>
                                            <div>{{ \Illuminate\Support\Str::limit((string) $item->descripcion, 40) }}</div>
                                            <div class="v360-mini">Codcli: {{ $item->codcli ?: '-' }}</div>
                                        </td>
                                        <td>
                                            @if(!empty($item->fecha_vencimiento))
                                                {{ \Carbon\Carbon::parse($item->fecha_vencimiento)->format('d/m/Y') }}
                                                @if(!is_null($item->dias_restantes))
                                                    <div class="v360-mini">
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
                                                <span class="v360-mini">Sin crédito</span>
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
    const paletaV360 = ['#0d2b45', '#1f5f8b', '#1f8a70', '#d4a017', '#7c3aed', '#c2410c', '#0f766e', '#334155'];

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
                    borderColor: '#1f5f8b',
                    backgroundColor: 'rgba(31, 95, 139, .12)',
                    pointBackgroundColor: '#1f8a70',
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
                    backgroundColor: data.map((_, idx) => paletaV360[idx % paletaV360.length]),
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
                    backgroundColor: data.map((_, idx) => paletaV360[idx % paletaV360.length]),
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
