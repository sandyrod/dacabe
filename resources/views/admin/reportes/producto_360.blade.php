@extends('layouts.app')

@section('titulo', 'Vision 360 Producto')
@section('titulo_header', 'Vision 360 Producto')
@section('subtitulo_header', 'Analitica integral comercial por producto')

@section('styles')
<style>
    :root {
        --p360-ink: #13293d;
        --p360-sea: #1f6f8b;
        --p360-mint: #0f766e;
        --p360-gold: #b88726;
        --p360-soft: #f2f7fb;
        --p360-muted: #6b7f93;
    }

    .p360-hero {
        border-radius: 22px;
        background: linear-gradient(130deg, #12263a 0%, #1f6f8b 52%, #0f766e 100%);
        color: #fff;
        box-shadow: 0 20px 45px rgba(10, 32, 50, .25);
        overflow: hidden;
        position: relative;
    }

    .p360-hero::before,
    .p360-hero::after {
        content: "";
        position: absolute;
        border-radius: 50%;
        pointer-events: none;
    }

    .p360-hero::before {
        width: 250px;
        height: 250px;
        right: -85px;
        top: -110px;
        background: rgba(255,255,255,.1);
    }

    .p360-hero::after {
        width: 180px;
        height: 180px;
        left: -65px;
        bottom: -75px;
        background: rgba(184, 135, 38, .2);
    }

    .p360-kpi {
        border: 1px solid #deebf7;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 8px 20px rgba(8, 30, 48, .08);
        padding: 14px;
        height: 100%;
    }

    .p360-kpi .lbl {
        font-size: .71rem;
        text-transform: uppercase;
        letter-spacing: .8px;
        color: var(--p360-muted);
        font-weight: 800;
    }

    .p360-kpi .val {
        font-size: 1.35rem;
        font-weight: 900;
        color: #17334e;
        line-height: 1.1;
    }

    .p360-kpi .sub {
        font-size: .78rem;
        color: var(--p360-muted);
    }

    .p360-card {
        border-radius: 16px;
        border: 1px solid #dde8f3;
        box-shadow: 0 8px 20px rgba(8, 30, 48, .08);
        overflow: hidden;
        background: #fff;
    }

    .p360-card .card-header {
        background: linear-gradient(180deg, #f8fbff 0%, #eef4fb 100%);
        border-bottom: 1px solid #dde8f3;
        color: #193a5a;
        font-weight: 800;
    }

    .p360-table thead th {
        background: #f7fbff;
        color: #244566;
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .8px;
        border-bottom: 1px solid #dde8f3;
        white-space: nowrap;
    }

    .p360-table tbody td {
        font-size: .86rem;
        color: #27435e;
        vertical-align: middle;
    }

    .p360-badge {
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 800;
        padding: .24rem .62rem;
        display: inline-flex;
        align-items: center;
    }

    .p360-ok { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
    .p360-warn { background: #fff7d1; color: #92400e; border: 1px solid #f6d978; }
    .p360-risk { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

    .p360-progress {
        height: 8px;
        border-radius: 999px;
        background: #e6eef6;
        overflow: hidden;
    }

    .p360-progress > span {
        height: 100%;
        display: block;
        border-radius: inherit;
        background: linear-gradient(90deg, #0f766e, #b88726);
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="p360-hero p-4 mb-4">
        <div class="row align-items-center">
            <div class="col-lg-8 mb-3 mb-lg-0" style="z-index:2; position:relative;">
                <span class="badge badge-light mb-2" style="font-size:.72rem;">Reporte Gerencial</span>
                <h4 class="mb-1 font-weight-bold">Vision 360&ordm; Producto</h4>
                @if($productoSeleccionado)
                    <div style="font-size:.94rem;opacity:.95;">
                        {{ $productoSeleccionado->codigo }}
                        <span class="mx-2">|</span>
                        {{ $productoSeleccionado->descripcion }}
                    </div>
                @endif
                <div style="font-size:.82rem;opacity:.85;">Desempeno comercial, tendencia de ventas, clientes top y estado operativo de pedidos.</div>
            </div>
            <div class="col-lg-4" style="z-index:2; position:relative;">
                <form method="GET" action="{{ route('admin.reportes.producto.360') }}" class="bg-white rounded p-3" style="box-shadow:0 8px 20px rgba(7,35,58,.2);">
                    <label class="mb-1 font-weight-bold text-dark" style="font-size:.8rem;">Producto</label>
                    <select class="form-control form-control-sm mb-2" name="producto_codigo">
                        @foreach($productos as $p)
                            <option value="{{ $p->codigo }}" {{ (string) optional($productoSeleccionado)->codigo === (string) $p->codigo ? 'selected' : '' }}>
                                {{ $p->codigo }} - {{ \Illuminate\Support\Str::limit($p->descripcion, 40) }}
                            </option>
                        @endforeach
                    </select>
                    <div class="row">
                        <div class="col-6">
                            <label class="mb-1 text-dark" style="font-size:.72rem;">Desde</label>
                            <input type="date" class="form-control form-control-sm" name="fecha_desde" value="{{ $filtros['fecha_desde'] }}">
                        </div>
                        <div class="col-6">
                            <label class="mb-1 text-dark" style="font-size:.72rem;">Hasta</label>
                            <input type="date" class="form-control form-control-sm" name="fecha_hasta" value="{{ $filtros['fecha_hasta'] }}">
                        </div>
                    </div>
                    <div class="d-flex flex-wrap mt-3" style="gap:.4rem;">
                        <button class="btn btn-sm btn-primary" type="submit"><i class="fas fa-filter"></i> Filtrar</button>
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.reportes.producto.360', ['producto_codigo' => optional($productoSeleccionado)->codigo]) }}"><i class="fas fa-eraser"></i> Limpiar</a>
                        <a class="btn btn-sm btn-outline-success" href="{{ route('admin.reportes.producto.360.export.excel', ['producto_codigo' => optional($productoSeleccionado)->codigo, 'fecha_desde' => $filtros['fecha_desde'], 'fecha_hasta' => $filtros['fecha_hasta']]) }}"><i class="fas fa-file-excel"></i> Excel</a>
                        <a class="btn btn-sm btn-outline-danger" target="_blank" href="{{ route('admin.reportes.producto.360.export.pdf', ['producto_codigo' => optional($productoSeleccionado)->codigo, 'fecha_desde' => $filtros['fecha_desde'], 'fecha_hasta' => $filtros['fecha_hasta']]) }}"><i class="fas fa-file-pdf"></i> PDF</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-6 col-lg-3 mb-3"><div class="p360-kpi"><div class="lbl">Ventas USD</div><div class="val">${{ number_format($resumen['ventas_usd'], 2, ',', '.') }}</div><div class="sub">Ticket: ${{ number_format($resumen['ticket_promedio'], 2, ',', '.') }}</div></div></div>
        <div class="col-6 col-lg-3 mb-3"><div class="p360-kpi"><div class="lbl">Unidades</div><div class="val">{{ number_format($resumen['unidades'], 0, ',', '.') }}</div><div class="sub">Precio prom.: ${{ number_format($resumen['precio_promedio'], 2, ',', '.') }}</div></div></div>
        <div class="col-6 col-lg-3 mb-3"><div class="p360-kpi"><div class="lbl">Pedidos</div><div class="val">{{ number_format($resumen['pedidos_total']) }}</div><div class="sub">Aprobados {{ number_format($resumen['pedidos_aprobados']) }}</div></div></div>
        <div class="col-6 col-lg-3 mb-3"><div class="p360-kpi"><div class="lbl">Clientes Activos</div><div class="val">{{ number_format($resumen['clientes_activos']) }}</div><div class="sub">Pendientes {{ number_format($resumen['pedidos_pendientes']) }}</div></div></div>
    </div>

    <div class="row mb-3">
        <div class="col-lg-4 mb-3"><div class="p360-kpi"><div class="lbl">Stock Actual</div><div class="val">{{ number_format($resumen['stock_actual'], 0, ',', '.') }}</div><div class="sub">Min {{ number_format($resumen['stock_minimo'], 0, ',', '.') }} | Max {{ number_format($resumen['stock_maximo'], 0, ',', '.') }}</div><div class="p360-progress mt-2"><span style="width: {{ max(0, min(100, $resumen['stock_status_pct'])) }}%;"></span></div></div></div>
        <div class="col-lg-8 mb-3">
            <div class="card p360-card h-100">
                <div class="card-header"><i class="fas fa-chart-line mr-2"></i>Tendencia Mensual (6 meses)</div>
                <div class="card-body"><canvas id="chartVentasMensuales" height="115"></canvas></div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-lg-5 mb-3">
            <div class="card p360-card h-100">
                <div class="card-header"><i class="fas fa-chart-pie mr-2"></i>Estatus de Pedidos</div>
                <div class="card-body d-flex align-items-center justify-content-center"><canvas id="chartEstadoPedidos" height="200"></canvas></div>
            </div>
        </div>
        <div class="col-lg-7 mb-3">
            <div class="card p360-card h-100">
                <div class="card-header"><i class="fas fa-users mr-2"></i>Top Clientes por Ventas</div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height:350px;overflow:auto;">
                        <table class="table table-sm p360-table mb-0">
                            <thead><tr><th>Cliente</th><th class="text-right">Pedidos</th><th class="text-right">Unidades</th><th class="text-right">USD</th></tr></thead>
                            <tbody>
                            @forelse($topClientes as $c)
                                <tr>
                                    <td><strong>{{ $c->codcli }}</strong><div style="font-size:.75rem;color:#6b7f93;">{{ \Illuminate\Support\Str::limit($c->cliente, 40) }}</div></td>
                                    <td class="text-right">{{ number_format($c->pedidos) }}</td>
                                    <td class="text-right">{{ number_format($c->unidades, 0, ',', '.') }}</td>
                                    <td class="text-right">${{ number_format($c->ventas_usd, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">Sin datos</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card p360-card">
        <div class="card-header"><i class="fas fa-file-invoice mr-2"></i>Ultimos Pedidos del Producto</div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height:400px;overflow:auto;">
                <table class="table table-sm p360-table mb-0">
                    <thead><tr><th>#</th><th>Fecha</th><th>Cliente</th><th>Estatus</th><th class="text-right">Unidades</th><th class="text-right">USD</th></tr></thead>
                    <tbody>
                    @forelse($ultimosPedidos as $p)
                        @php
                            $s = strtoupper((string) $p->estatus);
                            $class = 'p360-warn';
                            if ($s === 'APROBADO') $class = 'p360-ok';
                            elseif (in_array($s, ['ANULADO', 'CANCELADO'])) $class = 'p360-risk';
                        @endphp
                        <tr>
                            <td><strong>{{ $p->id }}</strong></td>
                            <td>{{ $p->fecha ? \Carbon\Carbon::parse($p->fecha)->format('d/m/Y') : '-' }}</td>
                            <td><strong>{{ $p->codcli ?: 'SIN CODCLI' }}</strong><div style="font-size:.75rem;color:#6b7f93;">{{ \Illuminate\Support\Str::limit($p->cliente, 45) }}</div></td>
                            <td><span class="p360-badge {{ $class }}">{{ $p->estatus ?: 'SIN ESTATUS' }}</span></td>
                            <td class="text-right">{{ number_format($p->unidades, 0, ',', '.') }}</td>
                            <td class="text-right">${{ number_format($p->total_usd, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">Sin pedidos para mostrar</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('theme/plugins/chart.js/Chart.bundle.min.js') }}"></script>
<script>
const paletaP360 = ['#13293d', '#1f6f8b', '#0f766e', '#b88726', '#2563eb', '#d97706', '#7c3aed', '#ef4444'];

(function(){
    const el = document.getElementById('chartVentasMensuales');
    if (!el) return;
    const data = @json($ventasMensuales);

    new Chart(el.getContext('2d'), {
        type: 'line',
        data: {
            labels: data.map(i => i.etiqueta),
            datasets: [{
                label: 'Ventas USD',
                data: data.map(i => Number(i.ventas_usd || 0)),
                borderColor: '#1f6f8b',
                backgroundColor: 'rgba(31,111,139,.14)',
                borderWidth: 3,
                tension: .35,
                fill: true,
                pointRadius: 4,
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } } }
    });
})();

(function(){
    const el = document.getElementById('chartEstadoPedidos');
    if (!el) return;
    const data = @json($estadoPedidos);

    new Chart(el.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: data.map(i => i.estatus),
            datasets: [{
                data: data.map(i => Number(i.cantidad || 0)),
                backgroundColor: data.map((_, x) => paletaP360[x % paletaP360.length]),
                borderColor: '#fff',
                borderWidth: 2,
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
})();
</script>
@endsection
