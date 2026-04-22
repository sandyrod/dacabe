@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-gradient-navy text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-file-invoice-dollar mr-2"></i>Aprobación de Pagos
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body bg-light-silver p-4">
                    <!-- Filtros -->
                    <form method="GET" action="{{ route('pagos.aprobar') }}" class="mb-0">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label for="search" class="font-weight-bold text-primary mb-1">
                                        <i class="fas fa-search mr-1"></i>Buscar
                                    </label>
                                    <input type="text" name="search" id="search" class="form-control"
                                        value="{{ request('search') }}" placeholder="Cliente, ref, pedido...">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-0">
                                    <label for="vendedor" class="font-weight-bold text-primary mb-1">
                                        <i class="fas fa-user-tie mr-1"></i>Vendedor
                                    </label>
                                    <select name="vendedor" id="vendedor" class="form-control">
                                        <option value="">Todos</option>
                                        @foreach ($vendedores as $v)
                                        <option value="{{ $v->email }}"
                                            {{ request('vendedor') == $v->email ? 'selected' : '' }}>
                                            {{ $v->codigo }} - {{ $v->nombre_completo }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-0">
                                    <label for="estado" class="font-weight-bold text-primary mb-1">
                                        <i class="fas fa-tag mr-1"></i>Estado
                                    </label>
                                    <select name="estado" id="estado" class="form-control">
                                        <option value="">Todos (Pendientes)</option>
                                        <option value="PENDIENTE"
                                            {{ request('estado') == 'PENDIENTE' ? 'selected' : '' }}>Pendiente</option>
                                        <option value="EN REVISION"
                                            {{ request('estado') == 'EN REVISION' ? 'selected' : '' }}>En Revisión
                                        </option>
                                        <option value="APROBADO"
                                            {{ request('estado') == 'APROBADO' ? 'selected' : '' }}>Pagado (Aprobado)
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold text-primary mb-1">
                                        <i class="far fa-calendar-alt mr-1"></i>Desde
                                    </label>
                                    <input type="date" name="fecha_inicio" class="form-control"
                                        value="{{ request('fecha_inicio') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold text-primary mb-1">
                                        <i class="far fa-calendar-check mr-1"></i>Hasta
                                    </label>
                                    <input type="date" name="fecha_fin" class="form-control"
                                        value="{{ request('fecha_fin') }}">
                                </div>
                            </div>
                            <div class="col-md-2 d-flex flex-column align-items-end">
                                <div class="d-flex w-100 mt-2">
                                    <button type="submit" class="btn btn-primary flex-grow-1 mr-1" title="Filtrar">
                                        <i class="fas fa-filter"></i>
                                    </button>
                                    <a href="{{ route('pagos.aprobar') }}" class="btn btn-secondary flex-grow-1"
                                        title="Limpiar Filtros">
                                        <i class="fas fa-undo"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Estadísticas -->
                    <div class="row mb-4 mt-4">
                        <div class="col-md-4 mb-4">
                            <div class="stat-card h-100 shadow-sm"
                                style="background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);">
                                <div class="card-body text-white position-relative">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="pr-3">
                                            <h6 class="text-uppercase text-white-50 mb-1 small font-weight-bold">
                                                <i class="fas fa-clock mr-2"></i>Total Pendiente
                                            </h6>
                                            <h3 class="mb-0 font-weight-bold">${{ number_format($totalPendiente, 2) }}
                                            </h3>
                                        </div>
                                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                                            <i class="fas fa-hourglass-half"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-4">
                            <div class="stat-card h-100 shadow-sm"
                                style="background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);">
                                <div class="card-body text-white position-relative">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="pr-3">
                                            <h6 class="text-uppercase text-white-50 mb-1 small font-weight-bold">
                                                <i class="fas fa-search-dollar mr-2"></i>En Revisión
                                            </h6>
                                            <h3 class="mb-0 font-weight-bold">${{ number_format($totalEnRevision, 2) }}
                                            </h3>
                                        </div>
                                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                                            <i class="fas fa-eye"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-4">
                            <div class="stat-card h-100 shadow-sm"
                                style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
                                <div class="card-body text-white position-relative">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="pr-3">
                                            <h6 class="text-uppercase text-white-50 mb-1 small font-weight-bold">
                                                <i class="fas fa-calendar-day mr-2"></i>Recibido Hoy
                                            </h6>
                                            <h3 class="mb-0 font-weight-bold">${{ number_format($totalHoy, 2) }}</h3>
                                        </div>
                                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                                            <i class="fas fa-calendar-check"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Listado de Pagos -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0">
                                    <thead class="bg-light text-navy">
                                        <tr>
                                            <th class="text-center">Fecha</th>
                                            <th class="text-center">Ref</th>
                                            <th class="text-center">Banco/Destino</th>
                                            <th class="text-center">Método</th>
                                            <th>Vendedor / Cliente</th>
                                            <th class="text-right">Total $</th>
                                            <th class="text-right">Total Bs</th>
                                            <th class="text-center">Estado</th>
                                            <th class="text-center" style="width: 150px;">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($pagos as $pago)
                                        @php
                                            $rowClass = 'row-highlight-pending';
                                            if ($pago->tipo_pago && (trim($pago->tipo_pago->DPAGO) === 'DIVISA EFECTIVO' || trim($pago->tipo_pago->DPAGO) === 'DIVISA-$ EFECTIVO' || trim($pago->tipo_pago->DPAGO) === 'BOFA/BANESCO PANA-$')) {
                                                $rowClass = 'row-highlight-divisa-efectivo';
                                            }
                                            // Verificar si algún pedido de este pago tiene retención pendiente
                                            $tieneRetencion = $pago->pago_pedidos->contains(function($pp) {
                                                $p = $pp->pedido;
                                                return $p && (float)$p->porc_retencion > 0 && (float)$p->saldo_iva_bs > 0;
                                            });
                                        @endphp
                                        <tr class="{{ $rowClass }}">
                                            <td class="text-center">
                                                <i class="far fa-calendar-alt text-muted mr-1"></i>
                                                {{ \Carbon\Carbon::parse($pago->fecha)->format('d/m/Y') }}
                                            </td>
                                            <td class="text-center font-weight-bold">
                                                {{ $pago->referencia ?? '-' }}
                                            </td>
                                            <td class="text-center small">
                                                {{ $pago->banco ? $pago->banco->NOMBRE : '-' }}
                                                @if ($pago->observaciones)
                                                <i class="fas fa-info-circle text-info ml-1"
                                                    data-toggle="tooltip"
                                                    title="{{ $pago->observaciones }}"></i>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="badge badge-light border">{{ $pago->tipo_pago ? $pago->tipo_pago->DPAGO : '-' }}</span>
                                                @if ($pago->tipo_pago && (trim($pago->tipo_pago->DPAGO) === 'BOFA/BANESCO PANA-$' || trim($pago->tipo_pago->DPAGO) === 'DIVISA-$ EFECTIVO'))
                                                <div class="mt-1">
                                                    <span class="badge badge-success badge-pill" style="font-size: 0.75rem; background: linear-gradient(45deg, #28a745, #20c997); border: none; box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);">
                                                        <i class="fas fa-dollar-sign mr-1"></i>DIVISA EFECTIVO
                                                    </span>
                                                </div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <small class="text-muted mb-2"><i
                                                            class="fas fa-user-tie mr-1"></i>{{ $pago->user ? $pago->user->name : 'N/A' }}</small>
                                                    @foreach ($pago->pago_pedidos as $pp)
                                                    <div class="d-flex flex-column mb-2">
                                                        <div class="font-weight-bold text-dark mb-1">{{ $pp->pedido->descripcion ?? 'Cliente Desconocido' }}</div>
                                                        <div class="d-flex align-items-center">
                                                            <span class="badge badge-primary mr-2" style="font-size:0.85rem; letter-spacing:0.3px;">
                                                                <i class="fas fa-receipt mr-1"></i>#{{ $pp->pedido_id }}
                                                            </span>
                                                            @php
                                                                $factura = DB::connection('company')
                                                                    ->table('pedidos_facturas')
                                                                    ->where('pedido_id', $pp->pedido_id)
                                                                    ->first();
                                                            @endphp
                                                            @if($factura)
                                                                <span class="badge badge-info badge-pill" style="font-size:0.7rem; background: linear-gradient(45deg, #17a2b8, #138496); border: none; box-shadow: 0 2px 4px rgba(23, 162, 184, 0.3);">
                                                                    <i class="fas fa-file-invoice mr-1"></i>#{{ $factura->factura }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    @if (!$loop->last)
                                                    <hr class="my-2">
                                                    @endif
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td class="text-right text-navy font-weight-bold">
                                                ${{ number_format($pago->monto, 2) }}
                                            </td>
                                            <td class="text-right text-success small">
                                                {{ number_format($pago->monto_bs, 2) }} Bs
                                            </td>
                                            <td class="text-center">
                                                @php
                                                $badgeClass =
                                                $pago->estatus == 'PENDIENTE' ? 'warning' : 'info';
                                                $iconClass =
                                                $pago->estatus == 'PENDIENTE'
                                                ? 'fa-hourglass-start'
                                                : 'fa-search';
                                                @endphp
                                                <span class="badge badge-{{ $badgeClass }} px-3 py-2">
                                                    <i
                                                        class="fas {{ $iconClass }} mr-1"></i>{{ $pago->estatus }}
                                                </span>
                                                @if($tieneRetencion)
                                                <div class="mt-1">
                                                    <span class="badge badge-warning badge-pill" style="font-size:0.72rem; background: linear-gradient(45deg, #e67e22, #f39c12); color:#fff; border:none; box-shadow:0 2px 4px rgba(230,126,34,0.4);" data-toggle="tooltip" title="Este pago tiene retención de IVA pendiente. El pedido no se marcará como PAGADO hasta que el vendedor suba el comprobante y el administrador lo valide.">
                                                        <i class="fas fa-percentage mr-1"></i>Retención Pendiente
                                                    </span>
                                                </div>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <button
                                                    class="btn btn-sm btn-primary btn-ver-detalle pulse-primary"
                                                    data-id="{{ $pago->id }}">
                                                    <i class="fas fa-eye mr-1"></i> Ver
                                                </button>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-5">
                                                <i class="fas fa-search-dollar fa-4x text-muted mb-3"
                                                    style="opacity: 0.3;"></i>
                                                <p class="text-muted font-weight-bold">No hay pagos pendientes por
                                                    aprobar.</p>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="p-3">
                                {{ $pagos->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalle Pago (Reusando estructura moderna) -->
<div class="modal fade modal-premium" id="modalDetallePago" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient-navy text-white border-0">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-money-check-alt mr-2"></i>Detalle del Pago
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body bg-light">
                <div id="loadingDetalle" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                </div>

                <div id="contenidoDetalle" style="display: none;">
                    <!-- Cabecera Monto -->
                    <div class="payment-amount-header text-center">
                        <h6 class="payment-amount-title mb-2">Monto Total del Pago</h6>
                        <div class="payment-amount-primary mb-1" id="modalMonto">$0,00</div>
                        <div class="payment-amount-secondary" id="modalMontoBs">0,00 Bs.</div>
                    </div>

                    <!-- Detalles Grid -->
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <div class="payment-detail-item">
                                <div class="detail-label"><i class="far fa-calendar-alt mr-1"></i>Fecha</div>
                                <div class="detail-value" id="modalFecha">-</div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="payment-detail-item">
                                <div class="detail-label"><i class="fas fa-hashtag mr-1"></i>Referencia</div>
                                <div class="detail-value text-primary" id="modalReferencia">-</div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="payment-detail-item">
                                <div class="detail-label"><i class="fas fa-exchange-alt mr-1"></i>Tasa</div>
                                <div class="detail-value" id="modalTasa">-</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="payment-detail-item">
                                <div class="detail-label"><i class="fas fa-university mr-1"></i>Banco / Destino</div>
                                <div class="detail-value" id="modalBanco">-</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="payment-detail-item">
                                <div class="detail-label"><i class="far fa-credit-card mr-1"></i>Método</div>
                                <div class="detail-value" id="modalMetodo">-</div>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="payment-detail-item">
                                <div class="detail-label"><i class="far fa-sticky-note mr-1"></i>Detalle / Observaciones</div>
                                <div class="detail-value" id="modalDetallePagoTexto">Sin observaciones</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="card border-0 shadow-sm mb-3 ajustes-resumen-card">
                            <div class="ajustes-resumen-head d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-sliders-h mr-2"></i>
                                    <strong class="ajustes-resumen-title">AJUSTES APLICADOS</strong>
                                </div>
                                <span class="badge ajustes-counter-badge" id="ajustesResumenCount">0 registros</span>
                            </div>
                            <div class="card-body py-2 px-3">
                                <div id="ajustesResumenListado"></div>
                                <div id="ajustesResumenTotales" class="ajustes-resumen-totales d-none">
                                    <div class="d-flex justify-content-between align-items-center small mb-1">
                                        <span class="text-muted">Cargos</span>
                                        <span class="font-weight-bold text-danger" id="ajustesTotalCargos">+0,00 $ | +0,00 Bs.</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center small mb-1">
                                        <span class="text-muted">Descuentos</span>
                                        <span class="font-weight-bold text-success" id="ajustesTotalDescuentos">-0,00 $ | -0,00 Bs.</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center ajustes-neto-row">
                                        <span class="font-weight-bold text-slate">Neto ajustes</span>
                                        <span class="font-weight-bold" id="ajustesTotalNeto">0,00 $ | 0,00 Bs.</span>
                                    </div>
                                </div>
                                <div id="ajustesResumenVacio" class="text-center py-3 ajustes-empty-state">
                                    <i class="fas fa-check-circle text-success mr-1"></i>
                                    No hay cargos o descuentos adicionales en los pedidos seleccionados.
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm border-0 resumen-calculos-card">
                            <div class="card-body py-3 px-4">
                                <div class="row text-center align-items-end text-white">
                                    <div class="col-6 col-md-3 mb-2 mb-md-0">
                                        <div class="font-weight-semibold small">Subtotal</div>
                                        <div class="font-weight-bold small" id="calculoSubtotal">0,00 Bs.</div>
                                    </div>
                                    <div class="col-6 col-md-3 mb-2 mb-md-0">
                                        <div class="font-weight-semibold small">Exento</div>
                                        <div class="font-weight-bold small" id="calculoExento">0,00 Bs.</div>
                                    </div>
                                    <div class="col-6 col-md-3 mb-2 mb-md-0">
                                        <div class="font-weight-semibold small">Base</div>
                                        <div class="font-weight-bold small" id="calculoBase">0,00 Bs.</div>
                                    </div>
                                    <div class="col-6 col-md-3 mb-2 mb-md-0">
                                        <div class="font-weight-semibold small">Impuesto (16%)</div>
                                        <div class="font-weight-bold small" id="calculoImpuesto">0,00 Bs.</div>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-12 text-right text-white">
                                        <div class="font-weight-semibold small">- Retención</div>
                                        <div class="font-weight-bold small" id="calculoRetencion">0,00 Bs.</div>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-end align-items-center">
                                            <div class="text-right w-100">
                                                <div class="font-weight-semibold small text-white-50 text-right mr-3">TOTAL</div>
                                                <div class="resumen-total-highlight" id="calculoTotal">0,00 Bs.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Archivos Adjuntos -->
                    <div id="sectionArchivos" style="display: none;" class="mb-4">
                        <h6 class="font-weight-bold text-navy mb-3"><i class="fas fa-paperclip mr-2"></i>Archivos Adjuntos</h6>
                        <div id="listaArchivos" class="row"></div>
                    </div>

                    <!-- Pedidos Afectados -->

                    <h6 class="font-weight-bold text-navy mb-3"><i class="fas fa-shopping-cart mr-2"></i>Pedidos
                        Relacionados</h6>
                    <div class="table-responsive bg-white rounded shadow-sm mb-4">
                        <table class="table table-sm table-borderless mb-0">
                            <thead class="border-bottom">
                                <tr class="text-muted small text-uppercase">
                                    <th class="pl-3">Pedido</th>
                                    <th>Cliente</th>
                                    <th>Fecha Pedido</th>
                                    <th class="text-right pr-3">Monto Abonado</th>
                                </tr>
                            </thead>
                            <tbody id="listaPedidosBody">
                                <!-- JS Load -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Botones Acción -->
                    <div class="row mt-4">
                        @if(!auth()->user()->hasRole('facturacion_dacabe'))
                            {{-- Botones para usuarios con permisos completos --}}
                            <div class="col-md-6">
                                <button type="button" class="btn btn-outline-danger btn-block btn-lg" id="btnRechazar">
                                    <i class="fas fa-times-circle mr-2"></i>Rechazar Pago
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button type="button" class="btn btn-success btn-block btn-lg shadow-sm"
                                    id="btnAprobar">
                                    <i class="fas fa-check-circle mr-2"></i>Aprobar Pago
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .bg-gradient-navy {
        background: linear-gradient(45deg, #001f3f, #003366);
    }

    .text-navy {
        color: #001f3f;
    }

    .bg-light-silver {
        background-color: #f4f6f9;
    }

    .stat-card {
        border-radius: 12px;
        transition: transform 0.3s;
        border: none;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .row-highlight-pending {
        background-color: #fff;
        border-left: 4px solid #ffc107;
        transition: background-color 0.2s;
    }

    .row-highlight-pending:hover {
        background-color: #fff8e1;
    }

    .row-highlight-divisa-efectivo {
        background: linear-gradient(135deg, #e8f5e8 0%, #d1fae5 100%) !important;
        border-left: 4px solid #28a745;
        box-shadow: 0 1px 3px rgba(40, 167, 69, 0.2);
        position: relative;
        transition: all 0.3s ease;
    }

    .row-highlight-divisa-efectivo2::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: linear-gradient(180deg, #28a745 0%, #20c997 100%);
        animation: shimmer 2s infinite;
    }

    .row-highlight-divisa-efectivo:hover {
        background: linear-gradient(135deg, #d1fae5 0%, #e8f5e8 100%) !important;
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(40, 167, 69, 0.3);
    }

    .row-highlight-divisa-efectivo td {
        background: transparent !important;
        border-color: rgba(40, 167, 69, 0.1) !important;
    }

    @keyframes shimmer {
        0% { opacity: 0.3; }
        50% { opacity: 0.7; }
        100% { opacity: 0.3; }
    }

    .modal-premium .modal-content {
        border-radius: 20px;
        overflow: hidden;
    }

    .payment-amount-header {
        background: linear-gradient(135deg, #3275b8 0%, #1f5f8b 100%);
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        color: white;
        box-shadow: 0 4px 15px rgba(50, 117, 184, 0.3);
    }

    .payment-amount-title {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 1.8px;
        opacity: 0.84;
    }

    .payment-amount-primary {
        font-size: 1.6rem;
        font-weight: 700;
        line-height: 1.1;
        opacity: 0.95;
    }

    .payment-amount-secondary {
        font-size: 2.35rem;
        font-weight: 800;
        line-height: 1.1;
        letter-spacing: 0.5px;
    }

    .payment-detail-item {
        padding: 1rem;
        border-radius: 12px;
        background: #fff;
        border: 1px solid #eee;
        height: 100%;
    }

    .detail-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #6c757d;
        font-weight: 700;
    }

    .detail-value {
        font-size: 1rem;
        color: #2d3436;
        font-weight: 600;
    }

    @keyframes pulse-primary {
        0% {
            box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.7);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(0, 123, 255, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(0, 123, 255, 0);
        }
    }

    .pulse-primary {
        animation: pulse-primary 2s infinite;
    }

    .comprobante-container {
        border: 2px dashed #ddd;
        min-height: 150px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
        overflow: hidden;
    }

    .comprobante-container:hover {
        border-color: #3275b8;
        background-color: #f8f9fa;
    }

    .img-comprobante {
        max-width: 100%;
        max-height: 450px;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s;
    }

    .img-comprobante:hover {
        transform: scale(1.02);
    }

    #listaArchivos .comprobante-container {
        min-height: 120px;
        border: 2px dashed #ddd;
        border-radius: 10px;
        transition: border-color 0.3s;
    }

    #listaArchivos .comprobante-container:hover {
        border-color: #3275b8;
    }

    .ajustes-resumen-card {
        border-radius: 18px;
        overflow: hidden;
    }

    .ajustes-resumen-head {
        background: linear-gradient(90deg, #4c1d95 0%, #6d28d9 100%);
        color: #fff;
        padding: 1rem 1.25rem;
    }

    .ajustes-resumen-title {
        font-size: 0.82rem;
        letter-spacing: 0.5px;
    }

    .ajustes-counter-badge {
        background: rgba(255, 255, 255, 0.92);
        color: #1d4ed8;
        border-radius: 999px;
        font-size: 0.75rem;
        padding: 0.35rem 0.7rem;
    }

    .ajustes-resumen-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.7rem 0;
        border-bottom: 1px solid #eef2f7;
    }

    .ajustes-resumen-item:last-child {
        border-bottom: 0;
    }

    .ajuste-item-title {
        font-size: 0.8rem;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.2;
    }

    .ajuste-item-subtitle {
        font-size: 0.73rem;
        color: #64748b;
        line-height: 1.25;
    }

    .ajuste-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        padding: 0.3rem 0.75rem;
        font-size: 0.75rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .ajuste-badge-cargo {
        background: #fee2e2;
        color: #b91c1c;
    }

    .ajuste-badge-descuento {
        background: #dcfce7;
        color: #15803d;
    }

    .ajustes-resumen-totales {
        margin-top: 0.75rem;
        padding-top: 0.85rem;
        border-top: 1px solid #e2e8f0;
    }

    .ajustes-neto-row {
        font-size: 0.82rem;
    }

    .ajustes-empty-state {
        font-size: 0.92rem;
        color: #64748b;
    }

    .text-slate {
        color: #334155;
    }

    .resumen-calculos-card {
        background: #1e293b;
        border-radius: 18px;
    }

    .resumen-total-highlight {
        color: #38bdf8;
        letter-spacing: 1px;
        background: rgba(56, 189, 248, 0.12);
        border-radius: 0.5rem;
        padding: 0.35rem 1rem;
        display: inline-block;
        font-size: 1.8rem;
        font-weight: 700;
    }

    @media (max-width: 767.98px) {
        .payment-amount-primary {
            font-size: 1.35rem;
        }

        .payment-amount-secondary {
            font-size: 1.9rem;
        }

        .ajustes-resumen-item {
            align-items: flex-start;
            flex-direction: column;
        }

        .resumen-total-highlight {
            font-size: 1.45rem;
            width: 100%;
        }
    }
</style>

@endsection

@section('scripts')
<script>
    $(function() {
        $('[data-toggle="tooltip"]').tooltip();

        let currentPagoId = null;
        const amountFormatter = new Intl.NumberFormat('es-VE', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        function parseAmount(value) {
            const parsed = parseFloat(value);

            return Number.isFinite(parsed) ? parsed : 0;
        }

        function formatAmount(value, suffix = '') {
            const formatted = amountFormatter.format(parseAmount(value));

            return suffix ? formatted + ' ' + suffix : formatted;
        }

        function formatDollar(value) {
            return '$' + formatAmount(value);
        }

        function formatBolivares(value) {
            return formatAmount(value, 'Bs.');
        }

        function renderAjustesResumen(resumen) {
            const ajustes = resumen && Array.isArray(resumen.ajustes) ? resumen.ajustes : [];
            const totales = resumen && resumen.totales_ajustes ? resumen.totales_ajustes : {};
            const listado = $('#ajustesResumenListado');
            const vacio = $('#ajustesResumenVacio');
            const totalesBox = $('#ajustesResumenTotales');

            listado.empty();
            $('#ajustesResumenCount').text(ajustes.length + ' ' + (ajustes.length === 1 ? 'registro' : 'registros'));

            if (!ajustes.length) {
                vacio.removeClass('d-none');
                totalesBox.addClass('d-none');
                return;
            }

            vacio.addClass('d-none');
            totalesBox.removeClass('d-none');

            ajustes.forEach(function(ajuste) {
                const badgeClass = ajuste.es_cargo ? 'ajuste-badge-cargo' : 'ajuste-badge-descuento';
                const sign = ajuste.es_cargo ? '+' : '-';

                listado.append(`
                    <div class="ajustes-resumen-item">
                        <div>
                            <div class="ajuste-item-title">Pedido #${ajuste.pedido_id}</div>
                            <div class="ajuste-item-subtitle">${ajuste.concepto}</div>
                        </div>
                        <span class="ajuste-badge ${badgeClass}">
                            ${sign}${formatAmount(ajuste.monto)} $ | ${sign}${formatAmount(ajuste.monto_bs)} Bs.
                        </span>
                    </div>
                `);
            });

            $('#ajustesTotalCargos').text('+' + formatAmount(totales.cargos_usd) + ' $ | +' + formatAmount(totales.cargos_bs) + ' Bs.');
            $('#ajustesTotalDescuentos').text('-' + formatAmount(totales.descuentos_usd) + ' $ | -' + formatAmount(totales.descuentos_bs) + ' Bs.');

            const netoUsd = parseAmount(totales.neto_usd);
            const netoBs = parseAmount(totales.neto_bs);
            $('#ajustesTotalNeto')
                .text((netoUsd >= 0 ? '+' : '') + formatAmount(netoUsd) + ' $ | ' + (netoBs >= 0 ? '+' : '') + formatAmount(netoBs) + ' Bs.')
                .css('color', netoUsd >= 0 ? '#dc2626' : '#059669');
        }

        function renderResumenCalculos(resumen) {
            const calculos = resumen || {};
            $('#calculoSubtotal').text(formatBolivares(calculos.subtotal_bs));
            $('#calculoExento').text(formatBolivares(calculos.exento_bs));
            $('#calculoBase').text(formatBolivares(calculos.base_bs));
            $('#calculoImpuesto').text(formatBolivares(calculos.impuesto_bs));
            $('#calculoRetencion').text(formatBolivares(calculos.retencion_bs));
            $('#calculoTotal').text(formatBolivares(calculos.total_bs));
        }

        $('.btn-ver-detalle').on('click', function() {
            const pagoId = $(this).data('id');
            currentPagoId = pagoId;

            $('#loadingDetalle').show();
            $('#contenidoDetalle').hide();
            $('#modalMonto').text('$0,00');
            $('#modalMontoBs').text('0,00 Bs.');
            $('#modalDetallePagoTexto').text('Sin observaciones');
            renderAjustesResumen({ ajustes: [], totales_ajustes: {} });
            renderResumenCalculos({});
            $('#modalDetallePago').modal('show');

            // Cargar detalles vía AJAX (usando la ruta nueva o existente)
            $.get('{{ url("pagos") }}/' + pagoId + '/detalle',
                function(response) {
                    if (response.pago) {
                        const p = response.pago;
                        const montoUsd = parseAmount(p.monto || response.detalles.reduce(function(acumulado, detalle) {
                            return acumulado + parseAmount(detalle.monto);
                        }, 0));
                        const montoBs = parseAmount(p.monto_bs || (montoUsd * parseAmount(p.rate)));
                        $('#modalMonto').text(formatDollar(montoUsd));
                        $('#modalMontoBs').text(formatBolivares(montoBs));

                        $('#modalFecha').text(p.fecha); // Ajustar formato si es necesario
                        $('#modalReferencia').text(p.referencia);
                        $('#modalTasa').text('Bs. ' + formatAmount(p.rate));
                        $('#modalBanco').text((p.banco ? p.banco.nombre : '') + (p.pago_destino ? ' / ' + p.pago_destino.nombre : ''));
                        $('#modalMetodo').text(p.tipo_pago ? p.tipo_pago.DPAGO : '');
                        const detallePago = (response.detalle_pago || '').trim();
                        $('#modalDetallePagoTexto').text(detallePago !== '' ? detallePago : 'Sin observaciones');
                        renderAjustesResumen(response.resumen_calculos);
                        renderResumenCalculos(response.resumen_calculos);

                        // Manejo de Archivos Adjuntos
                        const archivos = response.archivos || [];
                        const listaArchivos = $('#listaArchivos');
                        listaArchivos.empty();
                        if (archivos.length > 0) {
                            archivos.forEach(function(archivo) {
                                let contenido = '';
                                if (archivo.es_imagen) {
                                    contenido = `
                                        <a href="${archivo.ruta}" target="_blank" title="Ver imagen completa">
                                            <img src="${archivo.ruta}" class="img-comprobante" alt="${archivo.nombre_original}">
                                        </a>`;
                                } else if (archivo.es_pdf) {
                                    contenido = `
                                        <a href="${archivo.ruta}" target="_blank" class="btn btn-outline-danger btn-sm">
                                            <i class="fas fa-file-pdf fa-2x d-block mb-1"></i>
                                            ${archivo.nombre_original}
                                        </a>`;
                                } else {
                                    contenido = `
                                        <a href="${archivo.ruta}" target="_blank" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-file fa-2x d-block mb-1"></i>
                                            ${archivo.nombre_original}
                                        </a>`;
                                }
                                listaArchivos.append(`
                                    <div class="col-6 col-md-4 mb-3">
                                        <div class="comprobante-container text-center bg-white p-2 rounded shadow-sm h-100 d-flex flex-column align-items-center justify-content-center">
                                            ${contenido}
                                            <div class="mt-1 small text-muted text-truncate w-100 text-center" title="${archivo.nombre_original}">${archivo.nombre_original}</div>
                                        </div>
                                    </div>
                                `);
                            });
                            $('#sectionArchivos').show();
                        } else {
                            $('#sectionArchivos').hide();
                        }

                        const tbody = $('#listaPedidosBody');
                        tbody.empty();

                        response.detalles.forEach(d => {
                            tbody.append(`
                                <tr>
                                    <td class="pl-3 font-weight-bold">#${d.id}</td>
                                    <td>${d.cliente}</td>
                                    <td>${d.fecha_pedido}</td>
                                    <td class="text-right pr-3 font-weight-bold">${formatDollar(d.monto)}</td>
                                </tr>
                            `);
                        });

                        $('#loadingDetalle').hide();
                        $('#contenidoDetalle').fadeIn();
                    }
                }).fail(function() {
                toastr.error('Error al cargar detalles del pago');
                $('#modalDetallePago').modal('hide');
            });
        });

        $('#btnAprobar').on('click', function() {
            if (!currentPagoId) return;
            procesarPago(currentPagoId, 'APROBADO', '¿Está seguro de APROBAR este pago?');
        });

        $('#btnRechazar').on('click', function() {
            if (!currentPagoId) return;
            procesarPago(currentPagoId, 'RECHAZADO',
                '¿Está seguro de RECHAZAR este pago? Esta acción revertirá el estatus de los pedidos.'
            );
        });

        function procesarPago(id, estatus, confirmMsg) {
            if (!confirm(confirmMsg)) return;

            const btn = estatus === 'APROBADO' ? $('#btnAprobar') : $('#btnRechazar');
            const originalText = btn.html();
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            $.post('{{ url("pagos") }}/' + id + '/cambiar-estatus', {
                    _token: '{{ csrf_token() }}',
                    estatus: estatus
                },
                function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#modalDetallePago').modal('hide');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        toastr.error(response.message);
                        btn.prop('disabled', false).html(originalText);
                    }
                }).fail(function() {
                toastr.error('Error al procesar la solicitud');
                btn.prop('disabled', false).html(originalText);
            });
        }
    });
</script>
@endsection