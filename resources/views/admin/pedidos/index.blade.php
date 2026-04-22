@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel') . ' - Gestión de Pedidos')

@section('titulo_header', 'Gestión Ejecutiva de Pedidos')
@section('subtitulo_header', 'Orders Management - Executive Dashboard')

@section('styles')
<link rel="stylesheet" href="{{ asset('theme/plugins/sweetalert2/sweetalert2.min.css') }}">
<link rel="stylesheet" href="{{ asset('theme/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<style>
    :root {
        --primary-navy: #1a237e;
        --secondary-silver: #f5f7fa;
        --accent-gold: #c5a02e;
        --success-green: #2ecc71;
        --warning-amber: #f39c12;
        --glass-bg: rgba(255, 255, 255, 0.9);
    }

    .exec-card {
        background: var(--glass-bg);
        backdrop-filter: blur(15px);
        border-radius: 15px;
        border: 1px solid rgba(255, 255, 255, 0.4);
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.1);
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .stat-widget {
        background: white;
        border-radius: 15px;
        padding: 20px;
        border-left: 5px solid var(--primary-navy);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease;
    }

    .stat-widget:hover {
        transform: translateY(-3px);
    }

    .stat-label {
        font-size: 0.75rem;
        font-weight: 700;
        color: #7f8c8d;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--primary-navy);
    }

    .gradient-header {
        background: linear-gradient(135deg, #1a237e 0%, #3949ab 100%);
        color: white;
        padding: 20px;
        border-bottom: none;
    }

    .exec-table thead {
        background-color: var(--secondary-silver);
        color: var(--primary-navy);
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 1px;
    }

    .exec-table th {
        border-top: none !important;
        font-weight: 700;
        padding: 15px !important;
    }

    .btn-exec {
        background: var(--primary-navy);
        color: white;
        border-radius: 8px;
        padding: 10px 25px;
        font-weight: 600;
        border: none;
        transition: all 0.3s ease;
    }

    .btn-exec:hover {
        background: #283593;
        transform: scale(1.02);
        color: white;
    }

    /* Pagination Styling */
    .card-footer svg {
        width: 1.25rem !important;
        height: 1.25rem !important;
        display: inline-block !important;
        vertical-align: middle;
    }

    .card-footer nav {
        display: block !important;
    }

    .card-footer nav div.flex.justify-between.flex-1 {
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
    }

    .card-footer nav p.text-sm {
        display: none !important;
    }

    .card-footer nav .hidden.sm\:flex-1.sm\:flex {
        display: flex !important;
        justify-content: center !important;
        width: 100%;
    }

    .card-footer .relative.z-0.inline-flex {
        display: flex !important;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .card-footer .relative.z-0.inline-flex a,
    .card-footer .relative.z-0.inline-flex span {
        padding: 10px 18px !important;
        font-size: 0.9rem !important;
        font-weight: 600 !important;
        border: 1px solid #e2e8f0 !important;
        background: white;
        color: var(--primary-navy) !important;
        text-decoration: none !important;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
    }

    .card-footer .relative.z-0.inline-flex span[aria-current="page"] span {
        background-color: var(--primary-navy) !important;
        color: white !important;
        border-color: var(--primary-navy) !important;
    }

    .btn-gradient-purple {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        transition: all 0.3s ease;
    }

    .btn-gradient-purple:hover {
        background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        color: white;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    @include('layouts.partials.info')

    <!-- Dashboard de Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-widget exec-card">
                <div class="stat-label"><i class="fas fa-shopping-cart mr-1"></i> Pedidos del Mes</div>
                <div class="stat-value">{{ number_format($stats['total_orders_month']) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-widget exec-card" style="border-left-color: var(--warning-amber);">
                <div class="stat-label"><i class="fas fa-clock mr-1"></i> Pendientes / Revisión</div>
                <div class="stat-value">{{ number_format($stats['pending_orders']) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-widget exec-card" style="border-left-color: var(--success-green);">
                <div class="stat-label"><i class="fas fa-check-circle mr-1"></i> Pedidos Aprobados</div>
                <div class="stat-value">{{ number_format($stats['approved_orders']) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-widget exec-card" style="border-left-color: var(--accent-gold);">
                <div class="stat-label"><i class="fas fa-users mr-1"></i> Vendedores Activos</div>
                <div class="stat-value">{{ number_format($stats['active_sellers']) }}</div>
            </div>
        </div>
    </div>

    <!-- Filtros de Búsqueda -->
    <div class="exec-card p-4 mb-4">
        <h5 class="font-weight-bold mb-3" style="color: var(--primary-navy);">
            <i class="fas fa-filter mr-2"></i> Filtros Avanzados
        </h5>
        <form method="GET" action="{{ route('admin.pedidos.gestion') }}">
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="text-muted small font-weight-bold">NRO PEDIDO</label>
                        <input type="text" name="nro" class="form-control" value="{{ request('nro') }}" placeholder="0001">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="text-muted small font-weight-bold">CLIENTE</label>
                        <input type="text" name="cliente" class="form-control" value="{{ request('cliente') }}" placeholder="Nombre o RIF...">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="text-muted small font-weight-bold">VENDEDOR</label>
                        <input type="text" name="vendedor" class="form-control" value="{{ request('vendedor') }}" placeholder="Código o email...">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="text-muted small font-weight-bold">ESTATUS</label>
                        <select name="estatus" class="form-control">
                            <option value="TODOS">Todos</option>
                            <option value="CARGANDO" {{ request('estatus') == 'CARGANDO' ? 'selected' : '' }}>CARGANDO</option>
                            <option value="REVISION" {{ request('estatus') == 'REVISION' ? 'selected' : '' }}>REVISION</option>
                            <option value="APROBADO" {{ request('estatus') == 'APROBADO' ? 'selected' : '' }}>APROBADO</option>
                            <option value="RECHAZADO" {{ request('estatus') == 'RECHAZADO' ? 'selected' : '' }}>RECHAZADO</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="text-muted small font-weight-bold">RANGO DE FECHAS</label>
                        <div class="d-flex" style="gap: 5px;">
                            <input type="date" name="desde" class="form-control" value="{{ request('desde') }}">
                            <input type="date" name="hasta" class="form-control" value="{{ request('hasta') }}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-12 text-right">
                    <a href="{{ route('admin.pedidos.gestion') }}" class="btn btn-outline-secondary mr-2">
                        <i class="fas fa-undo mr-1"></i> Limpiar
                    </a>
                    <button type="submit" class="btn btn-exec">
                        <i class="fas fa-search mr-1"></i> Aplicar Filtros
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Tabla de Pedidos -->
    <div class="exec-card overflow-hidden mb-5">
        <div class="gradient-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0 font-weight-bold">
                <i class="fas fa-list mr-2"></i> Listado de Pedidos
            </h4>
            <div class="d-flex align-items-center">
                <div class="bg-white text-primary rounded-pill px-3 py-1 font-weight-bold" style="font-size: 0.8rem;">
                    Página {{ $pedidos->currentPage() }} de {{ $pedidos->lastPage() }}
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table exec-table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="80">NRO.</th>
                            <th width="120">FACTURA</th>
                            <th width="110">FECHA / VALID.</th>
                            <th>CLIENTE</th>
                            <th width="200">VENDEDOR / DEPÓSITO</th>
                            <th width="100">RETENC.</th>
                            <th width="120">DÍAS CRÉDITO</th>
                            <th>OBSERVACIONES</th>
                            <th>CONDICIONES</th>
                            <th width="120">ESTATUS</th>
                            <th width="200" class="text-center">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pedidos as $pedido)
                        <tr>
                            <td class="align-middle font-weight-bold">{{ str_pad($pedido->id, 5, '0', STR_PAD_LEFT) }}</td>
                            <td class="align-middle text-center">
                                @if($pedido->pedido_factura)
                                <span class="badge badge-success badge-pill d-block mb-1">Fact: {{ $pedido->pedido_factura->factura }}</span>
                                @endif
                                <a href="javascript:void(0)" onclick="toggle_factura({{ $pedido->id }})" title="Cambiar estado de factura">
                                    @if($pedido->factura == 'SI')
                                    <span class="badge badge-primary badge-pill d-block">Con Factura</span>
                                    @else
                                    <span class="badge badge-secondary badge-pill d-block">Sin Factura</span>
                                    @endif
                                </a>
                            </td>
                            <td class="align-middle">
                                <div class="mb-2">{{ \Carbon\Carbon::parse($pedido->fecha)->format('d/m/Y') }}</div>
                                <div class="d-flex">
                                    <a href="#" class="btn btn-sm btn-outline-{{ $pedido->cliente_verificado == 'SI' ? 'success' : 'danger' }} cliente_verificado mr-1" data-iddata="{{ $pedido->id }}" title="Validar Cliente">
                                        <i class="fas fa-check"></i>
                                    </a>
                                    @if($pedido->rif_foto)
                                    <a href="{{ asset('storage/products/' . $pedido->rif_foto) }}" target="_blank" class="btn btn-sm btn-outline-info" title="Ver RIF">
                                        <i class="fas fa-camera"></i>
                                    </a>
                                    @endif
                                </div>
                            </td>
                            <td class="align-middle">
                                <div class="font-weight-bold text-{{ $pedido->cliente_verificado == 'SI' ? 'success' : 'danger' }}">{{ $pedido->descripcion }}</div>
                                <small class="text-muted">{{ $pedido->rif }}</small>
                            </td>
                            <td class="align-middle">
                                <div class="font-weight-bold">{{ $pedido->seller_code }}</div>
                                <small class="badge badge-light border" title="{{ optional($pedido->deposito)->DDEPOS }}">
                                    {{ $pedido->cdepos }} - {{ optional($pedido->deposito)->DDEPOS ?? 'N/A' }}
                                </small>
                            </td>
                            <td class="align-middle text-center">
                                <span class="badge badge-{{ $pedido->porc_retencion > 0 ? 'warning' : 'light border' }}">
                                    {{ (float)$pedido->porc_retencion }}%
                                </span>
                            </td>
                            <td class="align-middle text-center">
                                <div class="dias-credito-container" data-pedido-id="{{ $pedido->id }}">
                                    @if($pedido->dias_credito !== null)
                                        <span class="dias-credito-badge badge badge-info badge-pill" style="cursor: pointer;" title="Clic para editar">
                                            <i class="fas fa-clock mr-1"></i>
                                            <span class="dias-credito-value">{{ $pedido->dias_credito }}</span> días
                                            <i class="fas fa-edit ml-1" style="font-size: 0.7em;"></i>
                                        </span>
                                    @else
                                        <span class="dias-credito-badge badge badge-light border" style="cursor: pointer;" title="Clic para definir">
                                            <i class="fas fa-plus mr-1"></i>
                                            <span class="dias-credito-value">Definir</span>
                                            <i class="fas fa-edit ml-1" style="font-size: 0.7em;"></i>
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="align-middle small">{{ $pedido->observations }}</td>
                            <td class="align-middle small">{{ $pedido->conditions }}</td>
                            <td class="align-middle">
                                @php
                                $badgeClass = 'badge-secondary';
                                if($pedido->estatus == 'APROBADO') $badgeClass = 'badge-success';
                                if($pedido->estatus == 'RECHAZADO') $badgeClass = 'badge-danger';
                                if($pedido->estatus == 'REVISION') $badgeClass = 'badge-warning';
                                if($pedido->estatus == 'CARGANDO') $badgeClass = 'badge-primary';
                                @endphp
                                <span class="badge {{ $badgeClass }} p-2 w-100">{{ $pedido->estatus }}</span>
                            </td>
                            <td class="align-middle text-center">
                                <a href="javascript:void(0)" class="btn btn-sm btn-outline-primary m-1 view" data-iddata="{{ $pedido->id }}" title="Ver Detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="javascript:void(0)" class="btn btn-sm btn-outline-info m-1 print" data-iddata="{{ $pedido->id }}" title="Imprimir">
                                    <i class="fas fa-print"></i>
                                </a>
                                <a href="javascript:void(0)" class="btn btn-sm btn-outline-dark m-1" onclick="update_retention({{ $pedido->id }})" title="Actualizar Retención">
                                    <i class="fas fa-percent"></i>
                                </a>
                                @if($pedido->estatus == 'PAGADO')
                                <a href="javascript:void(0)" class="btn btn-sm btn-gradient-purple m-1 shadow-sm" onclick="ver_trazabilidad_pagos({{ $pedido->id }})" title="Ver Trazabilidad de Pagos">
                                    <i class="fas fa-search-dollar mr-1"></i> Trazabilidad
                                </a>
                                @endif
                                @if($pedido->estatus == 'PENDIENTE')
                                <a href="javascript:void(0)" class="btn btn-sm btn-outline-success m-1 approve" data-iddata="{{ $pedido->id }}" title="Aprobar">
                                    <i class="fas fa-check"></i>
                                </a>
                                <a href="javascript:void(0)" class="btn btn-sm btn-outline-danger m-1 delete" data-iddata="{{ $pedido->id }}" title="Rechazar">
                                    <i class="fas fa-times"></i>
                                </a>
                                @endif
                                @if($pedido->estatus == 'APROBADO')
                                <a href="javascript:void(0)" class="btn btn-sm btn-outline-warning m-1 anular" data-iddata="{{ $pedido->id }}" title="Anular Pedido">
                                    <i class="fas fa-ban"></i>
                                </a>
                                @endif
                                @if(in_array($pedido->estatus, ['APROBADO', 'PENDIENTE', 'EN REVISION']))
                                <a href="javascript:void(0)" class="btn btn-sm btn-outline-purple m-1 btn-ajustes"
                                   data-id="{{ $pedido->id }}"
                                   title="Cargos / Notas de Crédito"
                                   style="border-color:#6f42c1;color:#6f42c1;">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                </a>
                                @endif
                                <a href="javascript:void(0)" class="btn btn-sm btn-outline-secondary m-1 email" data-iddata="{{ $pedido->id }}" title="Enviar Correo">
                                    <i class="fas fa-envelope"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                <p class="text-muted font-weight-bold">No se encontraron pedidos con los criterios seleccionados.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Mostrando {{ $pedidos->firstItem() ?? 0 }} al {{ $pedidos->lastItem() ?? 0 }} de {{ $pedidos->total() }} pedidos.
                </div>
                <div>
                    {{ $pedidos->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Ajustes (Cargos / Notas de Crédito) --}}
<div class="modal fade" id="modalAjustes" tabindex="-1" role="dialog" aria-labelledby="modalAjustesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#6f42c1,#8e44ad);">
                <h5 class="modal-title text-white font-weight-bold" id="modalAjustesLabel">
                    <i class="fas fa-file-invoice-dollar mr-2"></i>Cargos / Notas de Crédito — Pedido #<span id="ajuste-pedido-id"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">

                {{-- Lista de ajustes existentes --}}
                <div id="ajustes-loading" class="text-center py-3">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
                <div id="ajustes-lista" class="d-none">
                    <table class="table table-sm table-bordered mb-3">
                        <thead class="thead-light">
                            <tr>
                                <th>Tipo</th>
                                <th>Concepto</th>
                                <th class="text-right">Monto ($)</th>
                                <th>Fecha</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="ajustes-tbody">
                        </tbody>
                        <tfoot>
                            <tr class="font-weight-bold">
                                <td colspan="2" class="text-right">Neto:</td>
                                <td class="text-right" id="ajustes-neto"></td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    </table>
                    <div id="ajustes-empty" class="alert alert-info d-none">
                        <i class="fas fa-info-circle mr-1"></i> Este pedido no tiene ajustes registrados.
                    </div>
                </div>

                <hr>

                {{-- Formulario agregar ajuste --}}
                <h6 class="font-weight-bold mb-2"><i class="fas fa-plus-circle mr-1"></i>Agregar ajuste</h6>
                <form id="formAjuste">
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label class="small font-weight-bold">Tipo</label>
                            <select class="form-control form-control-sm" id="ajuste_tipo" required>
                                <option value="cargo">Cargo (Nota Débito)</option>
                                <option value="descuento">Descuento (Nota Crédito)</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="small font-weight-bold">Concepto</label>
                            <input type="text" class="form-control form-control-sm" id="ajuste_concepto" placeholder="Ej: Flete, Devolución parcial..." required>
                        </div>
                        <div class="form-group col-md-3">
                            <label class="small font-weight-bold">Monto ($)</label>
                            <input type="number" class="form-control form-control-sm" id="ajuste_monto" min="0.01" step="0.01" placeholder="0.00" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-save mr-1"></i>Guardar ajuste
                    </button>
                </form>

            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="{{ asset('theme/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
    // Use Laravel's URL helper for reliable URL generation
    const BASE_URL = '{{ url('/') }}';
    const TOKEN = $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}';

    $(document).ready(function() {
        console.log('Order Management Scripts loaded. Base URL:', BASE_URL);

        // View Order
        $(document).on('click', '.view', function() {
            let id = $(this).data('iddata');
            window.open(BASE_URL + '/view-order/' + id);
        });

        // Print Order
        $(document).on('click', '.print', function() {
            let id = $(this).data('iddata');
            window.open(BASE_URL + '/print-order/' + id);
        });

        // Approve Order
        $(document).on('click', '.approve', function() {
            if ($(this).hasClass('disabled')) return;
            update_order_status($(this).data('iddata'), 'APROBADO');
        });

        // Reject Order
        $(document).on('click', '.delete', function() {
            if ($(this).hasClass('disabled')) return;
            update_order_status($(this).data('iddata'), 'RECHAZADO');
        });

        // Cancel Order
        $(document).on('click', '.anular', function() {
            if ($(this).hasClass('disabled')) return;
            let id = $(this).data('iddata');
            
            Swal.fire({
                title: '¿Anular Pedido?',
                html: `
                    <div class="text-left">
                        <p><strong>¿Está seguro que desea anular este pedido?</strong></p>
                        <ul class="text-left" style="margin-top: 10px;">
                            <li>Se cambiará el estatus a <span class="badge badge-danger">RECHAZADO</span></li>
                            <li>Se descontará la reserva de los productos en ARTDEPOS</li>
                            <li>Esta acción no se puede deshacer</li>
                        </ul>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, anular pedido',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return $.ajax({
                        url: BASE_URL + '/admin/anular-pedido',
                        type: 'POST',
                        data: {
                            _token: TOKEN,
                            order_id: id
                        }
                    }).catch(error => {
                        console.error('Anulation error:', error);
                        if (error.responseJSON && error.responseJSON.message) {
                            Swal.showValidationMessage(error.responseJSON.message);
                        } else {
                            Swal.showValidationMessage('Error al anular el pedido');
                        }
                    });
                }
            }).then((result) => {
                console.log('First Swal result:', result);
                console.log('result.isConfirmed:', result.isConfirmed);
                console.log('result.dismiss:', result.dismiss);
                console.log('result.value:', result.value);
                
                // Si el usuario confirmó o si hay un valor (para manejar warnings)
                if (result.isConfirmed || (result.value && !result.dismiss)) {
                    let response = result.value;
                    console.log('Response from first AJAX:', response);
                    
                    if (response.type === 'success') {
                        console.log('Handling success case');
                        Swal.fire({
                            title: '¡Pedido Anulado!',
                            html: response.message,
                            icon: 'success',
                            confirmButtonColor: '#28a745'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else if (response.type === 'warning' && response.permitir_continuar) {
                        console.log('Handling warning case - showing second Swal');
                        // Caso de reserva insuficiente - mostrar opción de continuar
                        Swal.fire({
                            title: 'Reserva Insuficiente',
                            html: response.message,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Sí, anular sin modificar reservas',
                            cancelButtonText: 'Cancelar',
                            confirmButtonColor: '#ffc107',
                            cancelButtonColor: '#6c757d',
                            showLoaderOnConfirm: true,
                            preConfirm: () => {
                                console.log('Making second AJAX call for:', response.pedido_id);
                                return $.ajax({
                                    url: BASE_URL + '/admin/anular-pedido-sin-reserva',
                                    type: 'POST',
                                    data: {
                                        _token: TOKEN,
                                        order_id: response.pedido_id
                                    }
                                }).catch(error => {
                                    console.error('Anulation without reservation error:', error);
                                    if (error.responseJSON && error.responseJSON.message) {
                                        Swal.showValidationMessage(error.responseJSON.message);
                                    } else {
                                        Swal.showValidationMessage('Error al anular el pedido');
                                    }
                                });
                            }
                        }).then((result2) => {
                            console.log('Second Swal result:', result2);
                            if (result2.isConfirmed) {
                                let response2 = result2.value;
                                console.log('Response from second AJAX:', response2);
                                if (response2.type === 'success') {
                                    console.log('Second AJAX success - showing success message and reloading');
                                    Swal.fire({
                                        title: '¡Pedido Anulado!',
                                        html: response2.message,
                                        icon: 'success',
                                        confirmButtonColor: '#28a745'
                                    }).then(() => {
                                        console.log('About to reload page');
                                        window.location.reload();
                                    });
                                } else if (response2.type === 'error') {
                                    console.log('Second AJAX error:', response2);
                                    Swal.fire({
                                        title: 'Error',
                                        html: response2.message,
                                        icon: 'error',
                                        confirmButtonColor: '#dc3545'
                                    });
                                } else {
                                    console.log('Unknown second response type:', response2.type);
                                }
                            } else if (result2.dismiss === Swal.DismissReason.cancel) {
                                // Usuario canceló, no hacer nada
                                console.log('Usuario canceló la anulación sin reserva');
                            } else {
                                console.log('Second Swal dismissed for other reason:', result2.dismiss);
                            }
                        });
                    } else if (response.type === 'error') {
                        console.log('Handling error case');
                        Swal.fire({
                            title: 'Error',
                            html: response.message,
                            icon: 'error',
                            confirmButtonColor: '#dc3545'
                        });
                    } else {
                        console.log('Unknown response type:', response.type);
                    }
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    // Usuario canceló la anulación inicial
                    console.log('Usuario canceló la anulación inicial');
                } else {
                    console.log('First Swal dismissed for other reason:', result.dismiss);
                }
            });
        });

        // Send Email
        $(document).on('click', '.email', function() {
            let id = $(this).data('iddata');
            Swal.fire({
                title: '¿Enviar correo?',
                text: 'Se enviará el comprobante del pedido al cliente.',
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Sí, enviar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    send_email(id);
                }
            });
        });

        // Verify Client
        $(document).on('click', '.cliente_verificado', function(e) {
            e.preventDefault();
            let id = $(this).data('iddata');
            console.log('Client validation initiated for order:', id);

            Swal.fire({
                title: '¿Validar cliente?',
                text: 'El cliente será marcado como verificado.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, validar',
                cancelButtonText: 'Cancelar',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    const verifyUrl = BASE_URL + '/pedidos/verify-client/' + id;
                    console.log('Ajax call to:', verifyUrl);
                    return $.ajax({
                        url: verifyUrl,
                        type: 'GET'
                    }).catch(error => {
                        console.error('Validation error:', error);
                        Swal.showValidationMessage(`Error: ${error.responseJSON ? error.responseJSON.message : 'Error desconocido'}`);
                    });
                }
            }).then((result) => {
                console.log(result.value)
                if (result.value.type === 'success') {
                    Swal.fire('¡Validado!', 'El cliente ha sido verificado con éxito.', 'success').then(() => {
                        window.location.reload();
                    });
                }
            });
        });
    });

    function toggle_factura(id) {
        $.ajax({
            url: BASE_URL + '/admin/toggle-factura-order',
            type: 'POST',
            data: {
                _token: TOKEN,
                order_id: id
            },
            success: function(response) {
                if (response.type === 'success') {
                    location.reload();
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }
        });
    }

    function update_retention(id) {
        console.log('Opening retention modal for order:', id);
        Swal.fire({
            title: 'Actualizar Retención',
            text: 'Seleccione el porcentaje de retención:',
            icon: 'info',
            input: 'select',
            inputOptions: {
                '100': 'Contribuyente Especial (100%)',
                '75': 'Contribuyente Especial (75%)',
                '0': 'No Contribuyente (0%)'
            },
            inputPlaceholder: 'Seleccione un porcentaje',
            showCancelButton: true,
            confirmButtonText: 'Actualizar',
            cancelButtonText: 'Cancelar',
            inputValidator: (value) => {
                if (value === null || value === '') {
                    return 'Debe seleccionar una opción'
                }
            }
        }).then((result) => {
            console.log('Update retention result...:', result);
            if (result && result.value && result.value >= 0 && result.value <= 100) {
                console.log('Updating retention to:', result.value);
                Swal.fire({
                    title: 'Actualizando...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: BASE_URL + '/admin/update-retention-order',
                    type: 'POST',
                    data: {
                        _token: TOKEN,
                        order_id: id,
                        retencion: result.value
                    },
                    success: function(response) {
                        console.log('Retention update response:', response);
                        if (response.type === 'success') {
                            Swal.fire({
                                title: '¡Actualizado!',
                                text: response.message,
                                icon: 'success'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        console.error('Retention update error:', xhr);
                        Swal.fire('Error', xhr.responseJSON ? xhr.responseJSON.message : 'No se pudo actualizar la retención.', 'error');
                    }
                });
            }
        });
    }

    function update_order_status(id, status) {
        const title = status === 'APROBADO' ? '¿Aprobar pedido?' : '¿Rechazar pedido?';
        const text = status === 'APROBADO' ? 'El pedido será marcado como aprobado.' : 'El pedido será marcado como rechazado.';
        const type = status === 'APROBADO' ? 'success' : 'warning';

        Swal.fire({
            title: title,
            text: text,
            icon: type,
            showCancelButton: true,
            confirmButtonText: 'Sí, confirmar',
            cancelButtonText: 'Cancelar',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return $.ajax({
                    url: BASE_URL + '/update-estatus-order',
                    type: 'POST',
                    data: {
                        _token: TOKEN,
                        order_id: id,
                        estatus: status
                    }
                }).catch(error => {
                    Swal.showValidationMessage(`Error: ${error.responseJSON ? error.responseJSON.message : 'Error desconocido'}`);
                });
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire('¡Actualizado!', result.value.message, 'success').then(() => {
                    window.location.reload();
                });
            }
        });
    }

    function send_email(id) {
        Swal.fire({
            title: 'Enviando...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: BASE_URL + '/pedidos/pdf-mail/' + id,
            type: 'GET',
            success: function(response) {
                Swal.fire(response.title, response.text, response.type).then(() => {
                    location.reload();
                });
            },
            error: function() {
                Swal.fire('Error', 'No se pudo enviar el correo.', 'error');
            }
        });
    }

    function ver_trazabilidad_pagos(id) {
        console.log('Opening payment traceability modal for order:', id);
        
        // Show loading modal
        Swal.fire({
            title: 'Trazabilidad de Pagos',
            html: `
                <div class="text-center mb-3">
                    <i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i>
                    <p class="mb-0">Cargando información de pagos...</p>
                </div>
            `,
            icon: 'info',
            showConfirmButton: false,
            showCancelButton: true,
            cancelButtonText: 'Cerrar',
            customClass: {
                popup: 'swal2-popup'
            }
        });

        // Fetch payment data
        console.log('Making AJAX call to:', BASE_URL + '/admin/pagos/trazabilidad/' + id);
        console.log('CSRF Token:', TOKEN);
        
        $.ajax({
            url: BASE_URL + '/admin/pagos/trazabilidad/' + id,
            type: 'GET',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', TOKEN);
            },
            success: function(response) {
                console.log('Payment traceability response:', response);
                
                if (response.type === 'success') {
                    let paymentsHtml = '';
                    if (response.pagos && response.pagos.length > 0) {
                        paymentsHtml = `
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Monto</th>
                                            <th>Moneda</th>
                                            <th>Banco Origen</th>
                                            <th>Banco Destino</th>
                                            <th>Tipo Pago</th>
                                            <th>Referencia</th>
                                            <th>Estatus</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;
                        
                        response.pagos.forEach(function(pago) {
                            const fecha = pago.fecha ? new Date(pago.fecha).toLocaleDateString('es-ES') : 'N/A';
                            const monto = pago.monto ? parseFloat(pago.monto).toFixed(2) : '0.00';
                            const moneda = pago.moneda_pago || 'USD';
                            const bancoOrigen = pago.banco_origen || 'N/A';
                            const bancoDestino = pago.banco_destino || 'N/A';
                            const tipoPago = pago.tipo_pago || 'N/A';
                            const referencia = pago.referencia || 'N/A';
                            const estatus = pago.estatus || 'PENDIENTE';
                            const estatusClass = estatus === 'APROBADO' ? 'success' : (estatus === 'EN REVISION' ? 'warning' : 'secondary');
                            
                            paymentsHtml += `
                                <tr>
                                    <td>${fecha}</td>
                                    <td>${monto}</td>
                                    <td>${moneda}</td>
                                    <td>${bancoOrigen}</td>
                                    <td>${bancoDestino}</td>
                                    <td>${tipoPago}</td>
                                    <td>${referencia}</td>
                                    <td><span class="badge badge-${estatusClass}">${estatus}</span></td>
                                </tr>
                            `;
                        });
                        
                        paymentsHtml += `
                                    </tbody>
                                </table>
                            </div>
                        `;
                    } else {
                        paymentsHtml = `
                            <div class="text-center py-4">
                                <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No se encontraron pagos asociados a este pedido.</p>
                            </div>
                        `;
                    }
                    
                    Swal.fire({
                        title: 'Trazabilidad de Pagos - Pedido #' + id,
                        html: paymentsHtml,
                        icon: 'success',
                        showConfirmButton: true,
                        confirmButtonText: 'Cerrar',
                        customClass: {
                            popup: 'swal2-popup'
                        },
                        width: '800px'
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: response.message || 'No se pudo cargar la información de pagos.',
                        icon: 'error',
                        confirmButtonText: 'Cerrar'
                    });
                }
            },
            error: function(xhr) {
                console.error('Payment traceability error:', xhr);
                Swal.fire({
                    title: 'Error',
                    text: 'No se pudo conectar con el servidor para obtener la información de pagos.',
                    icon: 'error',
                    confirmButtonText: 'Cerrar'
                });
            }
        });
    }

    // ─── Ajustes (Cargos / Notas de Crédito) ────────────────────────────────
    let ajustePedidoActual = null;

    $(document).on('click', '.btn-ajustes', function () {
        ajustePedidoActual = $(this).data('id');
        $('#ajuste-pedido-id').text(ajustePedidoActual);
        cargarAjustes();
        $('#modalAjustes').modal('show');
    });

    function cargarAjustes() {
        $('#ajustes-loading').show();
        $('#ajustes-lista').addClass('d-none');

        $.get('{{ url("admin/pedidos") }}/' + ajustePedidoActual + '/ajustes', function (res) {
            $('#ajustes-loading').hide();
            $('#ajustes-lista').removeClass('d-none');

            const tbody = $('#ajustes-tbody').empty();
            if (res.ajustes.length === 0) {
                $('#ajustes-empty').removeClass('d-none');
            } else {
                $('#ajustes-empty').addClass('d-none');
                res.ajustes.forEach(function (a) {
                    const tipoBadge = a.tipo === 'cargo'
                        ? '<span class="badge badge-danger">Cargo</span>'
                        : '<span class="badge badge-success">Descuento</span>';
                    const estadoBadge = a.pagado
                        ? '<span class="badge badge-secondary">Pagado</span>'
                        : '<span class="badge badge-warning">Pendiente</span>';
                    const btnEliminar = a.pagado
                        ? ''
                        : `<button class="btn btn-xs btn-outline-danger btn-del-ajuste" data-id="${a.id}" style="padding:1px 6px;font-size:.75rem;"><i class="fas fa-trash"></i></button>`;
                    tbody.append(`<tr>
                        <td>${tipoBadge}</td>
                        <td>${a.concepto}</td>
                        <td class="text-right">$${parseFloat(a.monto).toFixed(2)}</td>
                        <td>${a.fecha}</td>
                        <td class="text-center">${estadoBadge}</td>
                        <td class="text-center">${btnEliminar}</td>
                    </tr>`);
                });
            }

            const neto = parseFloat(res.neto) || 0;
            const netoColor = neto > 0.001 ? 'text-danger' : (neto < -0.001 ? 'text-success' : 'text-muted');
            const netoSign = neto > 0.001 ? '+' : '';
            $('#ajustes-neto').attr('class', 'text-right font-weight-bold ' + netoColor)
                .text(netoSign + '$' + neto.toFixed(2));
        }).fail(function () {
            $('#ajustes-loading').hide();
            $('#ajustes-lista').removeClass('d-none');
            toastr.error('No se pudieron cargar los ajustes.');
        });
    }

    // Eliminar ajuste
    $(document).on('click', '.btn-del-ajuste', function () {
        const id = $(this).data('id');
        if (!confirm('¿Eliminar este ajuste?')) return;
        $.ajax({
            url: '{{ url("admin/pedidos-ajustes") }}/' + id,
            type: 'DELETE',
            headers: { 'X-CSRF-TOKEN': TOKEN },
            success: function () { cargarAjustes(); },
            error: function (xhr) {
                const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error al eliminar';
                toastr.error(msg);
            }
        });
    });

    // Guardar nuevo ajuste
    $('#formAjuste').on('submit', function (e) {
        e.preventDefault();
        const data = {
            tipo: $('#ajuste_tipo').val(),
            concepto: $('#ajuste_concepto').val().trim(),
            monto: $('#ajuste_monto').val(),
            _token: TOKEN
        };
        if (!data.concepto || !data.monto) return;

        $.post('{{ url("admin/pedidos") }}/' + ajustePedidoActual + '/ajustes', data, function () {
            $('#ajuste_concepto').val('');
            $('#ajuste_monto').val('');
            cargarAjustes();
            toastr.success('Ajuste guardado.');
        }).fail(function (xhr) {
            const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error al guardar';
            toastr.error(msg);
        });
    });

    // Editar días de crédito
    $(document).on('click', '.dias-credito-badge', function() {
        const container = $(this).closest('.dias-credito-container');
        const pedidoId = container.data('pedido-id');
        const currentValue = container.find('.dias-credito-value').text();
        
        Swal.fire({
            title: 'Editar Días de Crédito',
            html: `
                <div class="text-left">
                    <p><strong>Pedido ID:</strong> ${pedidoId}</p>
                    <div class="form-group">
                        <label><strong>Días de crédito:</strong></label>
                        <input type="number" id="dias-credito-input" class="form-control" 
                               value="${currentValue === 'Definir' ? '' : currentValue}" 
                               min="0" step="1" placeholder="Ingrese días de crédito">
                        <small class="text-muted">Ingrese 0 para quitar días de crédito</small>
                    </div>
                </div>
            `,
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                const diasCredito = $('#dias-credito-input').val();
                if (diasCredito === '') {
                    diasCredito = null;
                } else if (diasCredito < 0 || !Number.isInteger(Number(diasCredito))) {
                    Swal.showValidationMessage('Los días de crédito deben ser un número entero no negativo');
                    return false;
                }
                
                return $.ajax({
                    url: BASE_URL + '/admin/update-dias-credito-order',
                    type: 'POST',
                    data: {
                        _token: TOKEN,
                        order_id: pedidoId,
                        dias_credito: diasCredito
                    }
                }).catch(error => {
                    console.error('Error updating dias credito:', error);
                    Swal.showValidationMessage(`Error: ${error.responseJSON ? error.responseJSON.message : 'Error desconocido'}`);
                });
            }
        }).then((result) => {
            if (result.value && result.value.type === 'success') {
                Swal.fire('¡Actualizado!', 'Días de crédito actualizados correctamente.', 'success').then(() => {
                    window.location.reload();
                });
            }
        });
    });
</script>
@endsection