@extends('layouts.app')
@section('titulo', 'Historial de Pedidos')

@section('content')
<div class="container-fluid py-3 historial-wrapper">
    <div class="historial-hero mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
                <h2 class="mb-1"><i class="fas fa-clipboard-list me-2"></i>Historial de Pedidos</h2>
                <p class="mb-0">Vista ejecutiva de pedidos con estatus, cliente, productos, condiciones y despacho.</p>
            </div>
            <div>
                <a href="{{ route('vendedor.pedidos.historial.exportar', request()->query()) }}" class="btn btn-excel">
                    <i class="fas fa-file-excel me-2"></i>Exportar a Excel
                </a>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4 kpi-grid">
        <div class="col-6 col-lg-3">
            <div class="kpi-card kpi-card-total">
                <span class="kpi-icon"><i class="fas fa-shopping-cart"></i></span>
                <div>
                    <p class="kpi-label">Total Pedidos</p>
                    <h4 class="kpi-value">{{ $kpi_total ?? 0 }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="kpi-card kpi-card-monto">
                <span class="kpi-icon"><i class="fas fa-dollar-sign"></i></span>
                <div>
                    <p class="kpi-label">Monto Total</p>
                    <h4 class="kpi-value">${{ number_format($kpi_monto ?? 0, 2, ',', '.') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="kpi-card kpi-card-pendientes">
                <span class="kpi-icon"><i class="fas fa-hourglass-half"></i></span>
                <div>
                    <p class="kpi-label">Pendientes</p>
                    <h4 class="kpi-value">{{ $kpi_pendientes ?? 0 }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="kpi-card kpi-card-entregados">
                <span class="kpi-icon"><i class="fas fa-check-circle"></i></span>
                <div>
                    <p class="kpi-label">Entregados</p>
                    <h4 class="kpi-value">{{ $kpi_entregados ?? 0 }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card filtro-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('vendedor.pedidos.historial') }}" class="row g-3 align-items-end">
                <div class="col-12 col-md-3">
                    <label for="filtro-estado" class="form-label">Estatus</label>
                    <select class="form-select" id="filtro-estado" name="estado">
                        <option value="">Todos</option>
                        <option value="PENDIENTE" {{ request('estado') == 'PENDIENTE' ? 'selected' : '' }}>Pendiente</option>
                        <option value="CARGANDO" {{ request('estado') == 'CARGANDO' ? 'selected' : '' }}>Cargando</option>
                        <option value="REVISION" {{ request('estado') == 'REVISION' ? 'selected' : '' }}>En revision</option>
                        <option value="APROBADO" {{ request('estado') == 'APROBADO' ? 'selected' : '' }}>Aprobado</option>
                        <option value="ENTREGADO" {{ request('estado') == 'ENTREGADO' ? 'selected' : '' }}>Entregado</option>
                        <option value="FACTURADO" {{ request('estado') == 'FACTURADO' ? 'selected' : '' }}>Facturado</option>
                        <option value="RECHAZADO" {{ request('estado') == 'RECHAZADO' ? 'selected' : '' }}>Rechazado</option>
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label for="filtro-fecha" class="form-label">Fecha</label>
                    <input type="date" class="form-control" id="filtro-fecha" name="fecha" value="{{ request('fecha') }}">
                </div>
                <div class="col-12 col-md-4">
                    <label for="filtro-cliente" class="form-label">Cliente</label>
                    <input type="text" class="form-control" id="filtro-cliente" name="cliente" value="{{ request('cliente') }}" placeholder="Nombre o RIF">
                </div>
                <div class="col-6 col-md-1 d-grid">
                    <button type="submit" class="btn btn-navy"><i class="fas fa-filter"></i></button>
                </div>
                <div class="col-6 col-md-1 d-grid">
                    <a href="{{ route('vendedor.pedidos.historial') }}" class="btn btn-light border">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card table-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Pedido</th>
                        <th>Fecha</th>
                        <th>Estatus</th>
                        <th>Cliente</th>
                        <th>Monto</th>
                        <th>Despacho</th>
                        <th class="text-center">Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pedidos as $pedido)
                        @php
                            $cli = $clientes[$pedido->id] ?? null;
                            $status = strtoupper((string) $pedido->estatus);
                            $statusClass = [
                                'PENDIENTE' => 'badge-warning',
                                'CARGANDO' => 'badge-warning',
                                'REVISION' => 'badge-info',
                                'EN REVISION' => 'badge-info',
                                'APROBADO' => 'badge-primary',
                                'ENTREGADO' => 'badge-success',
                                'FACTURADO' => 'badge-success',
                                'RECHAZADO' => 'badge-danger',
                            ][$status] ?? 'badge-secondary';
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">#{{ $pedido->id }}</div>
                                <small class="text-muted">Ref: {{ $pedido->referencia ?? 'N/A' }}</small>
                            </td>
                            <td>{{ $pedido->created_at ? $pedido->created_at->format('d/m/Y H:i') : '-' }}</td>
                            <td><span class="status-pill {{ $statusClass }}">{{ $pedido->estatus }}</span></td>
                            <td>
                                <div class="fw-semibold">{{ $cli['NOMBRE'] ?? 'Sin cliente' }}</div>
                                <small class="text-muted">{{ $pedido->rif ?? '' }}</small>
                            </td>
                            <td class="fw-semibold">${{ number_format((float) $pedido->base + (float) $pedido->iva_bs, 2, ',', '.') }}</td>
                            <td>
                                @if($pedido->fecha_despacho)
                                    <i class="fas fa-truck text-success me-1"></i>{{ \Carbon\Carbon::parse($pedido->fecha_despacho)->format('d/m/Y') }}
                                @else
                                    <span class="text-muted">Sin fecha</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-navy btn-pedido-detalle"
                                    data-pedido-id="{{ $pedido->id }}"
                                    data-pedido-label="#{{ $pedido->id }}"
                                >
                                    <i class="fas fa-eye me-1"></i>Ver
                                </button>

                                <div id="detalle-html-{{ $pedido->id }}" class="d-none">
                                    <div class="detalle-content">
                                        <div class="row g-3">
                                            <div class="col-12 col-lg-4">
                                                <h6><i class="fas fa-user me-2"></i>Cliente</h6>
                                                <p class="mb-1"><strong>Nombre:</strong> {{ $cli['NOMBRE'] ?? 'Sin cliente' }}</p>
                                                <p class="mb-1"><strong>RIF:</strong> {{ $pedido->rif ?? '-' }}</p>
                                                <p class="mb-1"><strong>Telefono:</strong> {{ $cli['TELEFONO'] ?? $pedido->telefono ?? '-' }}</p>
                                                <p class="mb-0"><strong>Email:</strong> {{ $cli['EMAIL'] ?? $pedido->email ?? '-' }}</p>
                                            </div>

                                            <div class="col-12 col-lg-4">
                                                <h6><i class="fas fa-receipt me-2"></i>Condiciones</h6>
                                                <p class="mb-1"><strong>Condiciones:</strong> {{ $pedido->conditions ?: '-' }}</p>
                                                <p class="mb-1"><strong>Factura:</strong> {{ $pedido->factura ?: '-' }}</p>
                                                <p class="mb-1"><strong>Retencion %:</strong> {{ $pedido->porc_retencion ?? 0 }}</p>
                                                <p class="mb-0"><strong>Observaciones:</strong> {{ $pedido->observations ?: '-' }}</p>
                                            </div>

                                            <div class="col-12 col-lg-4">
                                                <h6><i class="fas fa-truck-loading me-2"></i>Despacho y Totales</h6>
                                                <p class="mb-1"><strong>Deposito:</strong> {{ optional($pedido->deposito)->DESCRIPCION ?? $pedido->cdepos ?? '-' }}</p>
                                                <p class="mb-1"><strong>Fecha despacho:</strong> {{ $pedido->fecha_despacho ? \Carbon\Carbon::parse($pedido->fecha_despacho)->format('d/m/Y') : '-' }}</p>
                                                <p class="mb-1"><strong>Base:</strong> ${{ number_format((float) $pedido->base, 2, ',', '.') }}</p>
                                                <p class="mb-0"><strong>IVA Bs:</strong> {{ number_format((float) $pedido->iva_bs, 2, ',', '.') }}</p>
                                            </div>
                                        </div>

                                        <div class="mt-3">
                                            <h6><i class="fas fa-boxes me-2"></i>Productos</h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Codigo</th>
                                                            <th>Descripcion</th>
                                                            <th>Cant.</th>
                                                            <th>Precio</th>
                                                            <th>IVA %</th>
                                                            <th>Subtotal</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($pedido->pedido_detalle as $item)
                                                            <tr>
                                                                <td>{{ $item->codigo_inven }}</td>
                                                                <td>{{ $item->inven_descr }}</td>
                                                                <td>{{ $item->cantidad }}</td>
                                                                <td>${{ number_format((float) $item->precio_dolar, 2, ',', '.') }}</td>
                                                                <td>{{ number_format((float) $item->iva, 2, ',', '.') }}</td>
                                                                <td>${{ number_format(((float) $item->cantidad * (float) $item->precio_dolar), 2, ',', '.') }}</td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="6" class="text-center text-muted">Sin productos en este pedido.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No hay pedidos para mostrar con estos filtros.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3 d-flex justify-content-end">
        {{ $pedidos->links() }}
    </div>

    <div class="modal fade" id="pedidoDetalleModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header modal-header-navy">
                    <h5 class="modal-title" id="pedidoDetalleModalLabel">Detalle de pedido</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="pedidoDetalleModalBody"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    .historial-wrapper {
        --navy-900: #0b1f3a;
        --navy-800: #12325a;
        --navy-700: #1a4375;
        --navy-100: #eaf1fa;
        --line: #d9e2ef;
        --text-soft: #5b6f89;
    }

    .historial-hero {
        background: linear-gradient(135deg, var(--navy-900), var(--navy-700));
        color: #fff;
        border-radius: 14px;
        padding: 1.2rem 1.4rem;
        box-shadow: 0 12px 24px rgba(11, 31, 58, 0.22);
    }

    .historial-hero h2 {
        font-weight: 700;
        letter-spacing: 0.2px;
    }

    .historial-hero p {
        color: #d8e6fb;
    }

    .btn-excel {
        background: #1d8f4f;
        border: 0;
        color: #fff;
        font-weight: 600;
    }

    .btn-excel:hover {
        background: #157240;
        color: #fff;
    }

    .kpi-card {
        position: relative;
        background: linear-gradient(155deg, #ffffff 15%, #f1f7ff 100%);
        border: 1px solid #d5e0ef;
        border-radius: 12px;
        padding: 1rem;
        display: flex;
        align-items: center;
        gap: 0.95rem;
        height: 100%;
        overflow: hidden;
        box-shadow: 0 8px 18px rgba(15, 45, 86, 0.08);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .kpi-card::after {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: #204b7f;
    }

    .kpi-card::before {
        content: "";
        position: absolute;
        right: -12px;
        top: -12px;
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(18, 50, 90, 0.10) 0%, rgba(18, 50, 90, 0.00) 70%);
        pointer-events: none;
    }

    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 24px rgba(15, 45, 86, 0.16);
    }

    .kpi-card-total::after { background: #12325a; }
    .kpi-card-monto::after { background: #1d8f4f; }
    .kpi-card-pendientes::after { background: #c58a17; }
    .kpi-card-entregados::after { background: #2d935d; }

    .kpi-card > div {
        min-width: 0;
    }

    .kpi-icon {
        width: 46px;
        height: 46px;
        border-radius: 10px;
        background: linear-gradient(140deg, #edf4ff, #d9e7fb);
        color: var(--navy-800);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }

    .kpi-label {
        margin: 0;
        font-size: 0.78rem;
        letter-spacing: 0.3px;
        text-transform: uppercase;
        color: #607998;
        font-weight: 600;
    }

    .kpi-value {
        margin: 0;
        color: var(--navy-900);
        font-weight: 800;
        line-height: 1.1;
        font-size: clamp(1.05rem, 1.5vw, 1.45rem);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        text-shadow: 0 1px 0 rgba(255, 255, 255, 0.65);
    }

    .filtro-card,
    .table-card {
        border: 1px solid var(--line);
        border-radius: 12px;
        overflow: hidden;
    }

    .btn-navy {
        background: var(--navy-800);
        color: #fff;
        border: 0;
    }

    .btn-navy:hover {
        background: var(--navy-900);
        color: #fff;
    }

    .table-card table thead th {
        background: #f1f5fb;
        color: var(--navy-900);
        font-weight: 700;
        border-bottom: 1px solid var(--line);
    }

    .table-card table tbody tr {
        border-color: #eef3fb;
    }

    .historial-wrapper nav[role="navigation"] {
        display: block;
    }

    .historial-wrapper nav[role="navigation"] .pagination {
        gap: 0.35rem;
        margin-bottom: 0;
    }

    .historial-wrapper nav[role="navigation"] .page-item .page-link,
    .historial-wrapper nav[role="navigation"] .page-item span.page-link {
        border-radius: 9px !important;
        border: 1px solid #d5e0ef !important;
        color: #12325a !important;
        font-weight: 700;
        min-width: 38px;
        text-align: center;
        background: #ffffff !important;
        box-shadow: 0 2px 6px rgba(18, 50, 90, 0.06);
        transition: all 0.2s ease;
    }

    .historial-wrapper nav[role="navigation"] .page-item .page-link:hover {
        color: #0b1f3a !important;
        border-color: #12325a !important;
        background: #eef4fd !important;
    }

    .historial-wrapper nav[role="navigation"] .page-item.active .page-link,
    .historial-wrapper nav[role="navigation"] .page-item.active span.page-link {
        background: linear-gradient(135deg, #12325a, #1a4375) !important;
        border-color: #12325a !important;
        color: #fff !important;
        box-shadow: 0 6px 14px rgba(18, 50, 90, 0.25);
    }

    .historial-wrapper nav[role="navigation"] .page-item.disabled .page-link,
    .historial-wrapper nav[role="navigation"] .page-item.disabled span.page-link {
        color: #8aa0bc !important;
        background: #f6f9fd !important;
        border-color: #e1e8f2 !important;
        box-shadow: none;
    }

    .modal-header-navy {
        background: linear-gradient(135deg, #0b1f3a, #1a4375);
        color: #fff;
    }

    .modal-header-navy .close {
        opacity: 1;
    }

    #pedidoDetalleModalBody {
        background: #f8fbff;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        padding: 0.32rem 0.62rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 700;
    }

    .badge-warning { background: #fff3cd; color: #856404; }
    .badge-info { background: #d1ecf1; color: #0c5460; }
    .badge-primary { background: #d8e6fb; color: #12325a; }
    .badge-success { background: #d4edda; color: #155724; }
    .badge-danger { background: #f8d7da; color: #721c24; }
    .badge-secondary { background: #e2e3e5; color: #383d41; }

    .btn-outline-navy {
        border-color: var(--navy-700);
        color: var(--navy-700);
    }

    .btn-outline-navy:hover {
        background: var(--navy-700);
        color: #fff;
    }

    .detalle-box {
        background: #f8fbff;
        border-top: 1px solid var(--line);
    }

    .detalle-content {
        padding: 1rem;
        border-left: 4px solid var(--navy-700);
        background: #ffffff;
        border-radius: 8px;
    }

    .detalle-content h6 {
        color: var(--navy-900);
        margin-bottom: 0.5rem;
        font-weight: 700;
    }

    @media (max-width: 767px) {
        .historial-hero {
            padding: 1rem;
        }

        .kpi-card {
            padding: 0.85rem;
            gap: 0.7rem;
        }

        .kpi-icon {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }

        .kpi-label {
            font-size: 0.72rem;
        }

        .kpi-value {
            font-size: 1.02rem;
        }

        .detalle-content {
            padding: 0.8rem;
        }
    }
</style>
@endsection

@section('scripts')
<script>
    $(function () {
        $('.btn-pedido-detalle').on('click', function (event) {
            event.preventDefault();

            var pedidoId = $(this).data('pedido-id');
            var pedidoLabel = $(this).data('pedido-label') || '';
            var $source = $('#detalle-html-' + pedidoId);

            if (!$source.length) {
                return;
            }

            $('#pedidoDetalleModalLabel').text('Detalle de pedido ' + pedidoLabel);
            $('#pedidoDetalleModalBody').html($source.html());
            $('#pedidoDetalleModal').modal('show');
        });
    });
</script>
@endsection
