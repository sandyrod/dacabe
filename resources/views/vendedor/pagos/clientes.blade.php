@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel') . ' - Gestión de Pagos')
@section('titulo_header', 'Gestión de Pagos')
@section('subtritulo_header', 'Seleccione un cliente y sus pedidos a pagar')

@push('styles')
    <style>
        .payment-summary {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .payment-detail {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .payment-label {
            font-weight: 600;
            color: #495057;
        }

        .payment-value {
            color: #212529;
        }

        /* Mobile-first table styles */
        @media (max-width: 767.98px) {
                    .table-responsive {
                        width: 100%;
                        margin: 0;
                        padding: 0;
                    }

                    .table {
                        width: 100%;
                        margin: 0 0 1.5rem;
                        border-collapse: separate;
                        border-spacing: 0 0.75rem;
                        background: transparent;
                    }

                    .table thead {
                        display: none;
                    }

                    .table tbody {
                        display: flex;
                        flex-direction: column;
                        gap: 0.75rem;
                    }

                    .table tr {
                        display: flex;
                        flex-direction: column;
                        background: #fff;
                        border-radius: 0.75rem;
                        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
                        overflow: hidden;
                        transition: transform 0.2s ease, box-shadow 0.2s ease;
                    }

                    .table tr:active {
                        transform: translateY(2px);
                        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
                    }

                    .table td {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        padding: 0.85rem 1rem;
                        border: none;
                        border-bottom: 1px solid #f0f4f8;
                        min-height: 3rem;
                        font-size: 0.95rem;
                        line-height: 1.4;
                    }

                    .table td::before {
                        content: attr(data-label);
                        font-weight: 600;
                        color: #4a5568;
                        margin-right: 1rem;
                        flex: 0 0 45%;
                        font-size: 0.9rem;
                    }

                    .table td:last-child {
                        background: #f8fafc;
                        justify-content: flex-end;
                        padding: 1rem;
                        border-bottom: none;
                        border-top: 1px solid #e2e8f0;
                    }

                    /* Checkbox cell specific styles */
                    .table .form-check {
                        margin: 0;
                        padding: 0;
                        width: 100%;
                        display: flex;
                        justify-content: flex-end;
                        align-items: center;
                    }

                    .table .form-check-input {
                        width: 1.8em;
                        height: 1.8em;
                        margin: 0;
                        border: 2px solid #cbd5e0;
                        transition: all 0.2s ease;
                        cursor: pointer;
                    }

                    .table .form-check-input:checked {
                        background-color: #2563eb;
                        border-color: #2563eb;
                        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M6 10l3 3l6-6'/%3e%3c/svg%3e");
                        background-size: 1.2em;
                        background-position: center;
                        background-repeat: no-repeat;
                    }

                    .table .form-check-input:disabled {
                        background-color: #f1f5f9;
                        border-color: #e2e8f0;
                        opacity: 0.8;
                    }

                    /* Improve touch feedback */
                    .table tr:active .form-check-input:not(:disabled) {
                        transform: scale(0.96);
                    }

                    /* Better visual feedback for selected rows */
                    .table tr.selected {
                        box-shadow: 0 0 0 2px #3b82f6;
                    }

                    /* Hide table header on mobile */
                    .table thead {
                        display: none;
                    }

                    /* Make table rows stack on mobile */
                    .table tbody tr {
                        display: block;
                        margin-bottom: 1rem;
                        border: 1px solid #dee2e6;
                        border-radius: 0.25rem;
                    }

                    /* Style table cells for mobile */
                    .table tbody td {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        padding: 0.75rem;
                        text-align: right;
                        border-bottom: 1px solid #dee2e6;
                    }

                    /* Add labels to table cells on mobile */
                    .table tbody td::before {
                        content: attr(data-label);
                        font-weight: 600;
                        text-align: left;
                        padding-right: 1rem;
                        flex: 1;
                    }

                    /* Fix for checkboxes */
                    .table tbody td:has(input[type="checkbox"]) {
                        justify-content: flex-end;
                    }

                    .table tbody td:has(input[type="checkbox"])::before {
                        content: "Seleccionar";
                    }

                    /* Fix summary bar on mobile */
                    #resumen-pago .card-body {
                        padding: 1rem !important;
                    }

                    #resumen-pago .d-flex {
                        flex-direction: column;
                        align-items: stretch;
                        gap: 1rem;
                    }

                    #resumen-pago .text-end {
                        text-align: left !important;
                        margin-top: 0.5rem;
                    }

                    #resumen-pago .btn {
                        width: 100%;
                        margin-top: 0.5rem;
                    }
                }
            </style>
            @endpush

            @section('content')
            <div class="container-fluid">
                @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif
                @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                @if (isset($pagoData))
                <div class="modal fade show" id="paymentConfirmationModal" tabindex="-1"
                    aria-labelledby="paymentConfirmationModalLabel" aria-modal="true" role="dialog"
                    style="display: block; padding-right: 17px;">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title" id="paymentConfirmationModalLabel">Confirmar Pago</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                                    onclick="closeModal()"></button>
                            </div>
                            <div class="modal-body">
                                <div class="payment-summary">
                                    <h5 class="mb-4">Resumen del Pago</h5>
                                    <div class="payment-detail">
                                        <span class="payment-label">Cliente:</span>
                                        <span class="payment-value">{{ $pagoData['rif'] }}</span>
                                    </div>
                                    <div class="payment-detail">
                                        <span class="payment-label">Tipo de Pago:</span>
                                        <span class="payment-value">{{ ucfirst($pagoData['tipo_pago']) }}</span>
                                    </div>
                                    @if ($pagoData['tipo_pago'] === 'divisa_parcial')
                                    <div class="payment-detail">
                                        <span class="payment-label">Monto en Divisa:</span>
                                        <span
                                            class="payment-value">${{ number_format($pagoData['monto_divisa'], 2, ',', '.') }}</span>
                                    </div>
                                    @endif
                                    <div class="payment-detail">
                                        <span class="payment-label">Total a Pagar:</span>
                                        <span
                                            class="payment-value fw-bold">${{ number_format($pagoData['total_pagar'], 2, ',', '.') }}</span>
                                    </div>
                                    @if ($pagoData['total_descuento'] > 0)
                                    <div class="payment-detail">
                                        <span class="payment-label">Descuento Aplicado:</span>
                                        <span
                                            class="payment-value text-success">${{ number_format($pagoData['total_descuento'], 2, ',', '.') }}</span>
                                    </div>
                                    @endif
                                </div>

                                <form id="confirmPaymentForm" action="{{ route('vendedores.pagos.confirmar') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="rif" value="{{ $pagoData['rif'] }}">
                                    <input type="hidden" name="pedidos" value="{{ $pagoData['pedidos'] }}">
                                    <input type="hidden" name="tipo_pago" value="{{ $pagoData['tipo_pago'] }}">
                                    <input type="hidden" name="total_pagar" value="{{ $pagoData['total_pagar'] }}">
                                    <input type="hidden" name="total_descuento" value="{{ $pagoData['total_descuento'] }}">
                                    <input type="hidden" name="monto_divisa" value="{{ $pagoData['monto_divisa'] ?? '' }}">
                                    <input type="hidden" name="banco_id" value="{{ $pagoData['banco_id'] ?? '' }}">
                                    <input type="hidden" name="referencia" value="{{ $pagoData['referencia'] ?? '' }}">
                                    <input type="hidden" name="fecha" value="{{ $pagoData['fecha'] ?? '' }}">

                                    <div class="text-end mt-4">
                                        <button type="button" class="btn btn-secondary me-2"
                                            onclick="closeModal()">Cancelar</button>
                                        <button type="submit" class="btn btn-primary">Confirmar Pago</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-backdrop fade show"></div>
                @endif

                <div class="row">
                    <!-- Lista de Clientes -->
                    <div class="col-md-4">

                        <div class="card shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Clientes con Deuda</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="p-3 border-bottom">
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                                        <input type="text" id="searchClientes" class="form-control"
                                            placeholder="Buscar cliente...">
                                    </div>
                                </div>
                                <div id="lista-clientes" class="list-group list-group-flush"
                                    style="max-height: 70vh; overflow-y: auto; overflow-x: hidden;">
                                    @forelse($clientes as $cliente)
                                    <a href="#" class="list-group-item list-group-item-action cliente-item @if(isset($cliente->tiene_pedidos_vencidos) && $cliente->tiene_pedidos_vencidos > 0) cliente-vencido @endif"
                                        data-rif="{{ $cliente->RIF }}" data-nombre="{{ strtolower($cliente->NOMBRE) }}">
                                        <div class="d-flex w-100 justify-content-between cliente-item-top">
                                            <h6 class="mb-1">{{ $cliente->NOMBRE }}</h6>
                                            <small class="text-muted cliente-rif">{{ $cliente->RIF }}</small>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center cliente-item-bottom">
                                            <small class="text-muted">
                                                {{ $cliente->pedidos_pendientes }}
                                                {{ $cliente->pedidos_pendientes == 1 ? 'pedido' : 'pedidos' }}
                                                @if(isset($cliente->tiene_pedidos_vencidos) && $cliente->tiene_pedidos_vencidos > 0)
                                                    <span class="badge bg-danger text-white ms-2">
                                                        <i class="fas fa-exclamation-triangle"></i> 
                                                        {{ $cliente->tiene_pedidos_vencidos }} vencido(s)
                                                    </span>
                                                @endif
                                            </small>
                                            <span class="badge bg-primary rounded-pill cliente-total">
                                                {{ number_format($cliente->total_pendiente, 2, ',', '.') }} $
                                            </span>
                                        </div>
                                    </a>
                                    @empty
                                    <div class="p-3 text-center text-muted">
                                        No hay clientes con deudas pendientes
                                    </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detalle de Pedidos -->
                    <div class="col-md-8" id="pedidos-container-main">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white">
                                <ul class="nav nav-tabs card-header-tabs" id="pedidosTabs" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="pendientes-tab" data-toggle="tab" href="#pendientes"
                                            role="tab" aria-controls="pendientes" aria-selected="true">
                                            Pedidos Pendientes
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="revision-tab" data-toggle="tab" href="#revision" role="tab"
                                            aria-controls="revision" aria-selected="false">
                                            En Revisión
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="pagados-tab" data-toggle="tab" href="#pagados" role="tab"
                                            aria-controls="pagados" aria-selected="false">
                                            Pagados
                                        </a>
                                    </li>
                                </ul>
                                <div id="cliente-seleccionado" class="mt-4 d-none">
                                    <span class="text-muted me-2">Cliente:</span>
                                    <span id="nombre-cliente" class="fw-bold"></span>
                                    <span id="rif-cliente" class="ms-2 badge bg-secondary"></span>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="tab-content" id="pedidosTabsContent">
                                    <!-- Pestaña de Pedidos Pendientes -->
                                    <div class="tab-pane fade show active" id="pendientes" role="tabpanel"
                                        aria-labelledby="pendientes-tab">
                                        <div id="aviso-retencion-pendiente" class="d-none" style="margin: 12px 12px 0;"></div>
                                        <div id="pedidos-container" class="d-none">
                                            <div class="table-responsive">
                                                <table class="table table-hover mb-0">
                                                    <thead class="table-lightd-md-table-header-group">
                                                        <tr>
                                                            <th class="text-center" width="60">Seleccionar</th>
                                                            <th>Pedido</th>
                                                            <th class="text-end">Total ($)</th>
                                                            <th class="text-end">Abonado ($)</th>
                                                            <th class="text-end">Saldo ($)</th>
                                                            <th class="text-end">IVA (Bs.)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="tabla-pedidos">
                                                        <!-- Se llenará con JavaScript -->
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div id="sin-pedidos" class="text-center p-5 text-muted">
                                            <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                                            <p>Seleccione un cliente para ver sus pedidos pendientes</p>
                                        </div>
                                    </div>

                                    <!-- Pestaña de En Revisión -->
                                    <div class="tab-pane fade" id="revision" role="tabpanel" aria-labelledby="revision-tab">
                                        <div class="alert alert-warning m-3">
                                            <h5 class="alert-heading"><i class="fas fa-clock me-2"></i> Pedidos en Revisión</h5>
                                            <hr>
                                            <p class="mb-0">No hay pedidos en revisión en este momento.</p>
                                        </div>
                                    </div>

                                    <!-- Pestaña de Pagados -->
                                    <div class="tab-pane fade" id="pagados" role="tabpanel" aria-labelledby="pagados-tab">
                                        <div class="alert alert-success m-3">
                                            <h5 class="alert-heading"><i class="fas fa-check-circle me-2"></i> Pedidos Pagados
                                            </h5>
                                            <hr>
                                            <p class="mb-0">No hay historial de pedidos pagados para mostrar.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resumen de Pago Fijo -->
                <div class="row mt-4 mb-5 justify-content-center d-none" id="resumen-pago">
                    <div class="col-12 col-lg-10 col-xxl-8">
                        <div class="card border-0 shadow" style="border-radius: 12px; overflow: hidden;">
                            <div class="card-body py-3 py-md-4 px-3 px-md-4"
                                style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                                    <div class="mb-3 mb-md-0 text-center text-md-start">
                                        <h5 class="mb-1 text-white">Resumen de Pago</h5>
                                        <small class="text-white-50">Pedidos seleccionados</small>
                                    </div>
                                    <div class="d-flex flex-column flex-md-row align-items-center w-100 w-md-auto">
                                        <div class="text-end me-0 me-md-4 mb-3 mb-md-0 w-100">
                                            <div
                                                class="d-flex justify-content-between justify-content-md-end align-items-center mb-1">
                                                <span class="text-white-50 small me-2 d-md-none">Total IVA (Bs.):</span>
                                                <div class="d-flex align-items-center">
                                                    <span class="text-white-50 small me-2 d-none d-md-inline">Total IVA (Bs.):</span>
                                                    <span class="text-white fw-bold" id="total-iva-bs">0,00 Bs.</span>
                                                </div>
                                            </div>
                                            <div
                                                class="d-flex justify-content-between justify-content-md-end align-items-center mt-2">
                                                <span class="text-white-50 small me-2 d-md-none">Total Ajustes ($):</span>
                                                <div class="d-flex align-items-center">
                                                    <span class="text-white-50 small me-2 d-none d-md-inline">Total Ajustes ($):</span>
                                                    <span class="text-white fw-bold" id="total-ajustes">0,00 $</span>
                                                </div>
                                            </div>
                                            <div
                                                class="d-flex justify-content-between justify-content-md-end align-items-center mt-2">
                                                <span class="text-white-50 small me-2 d-md-none">Total a Pagar:</span>
                                                <div class="d-flex align-items-center">
                                                    <span class="text-white-50 small me-2 d-none d-md-inline">Total a Pagar:</span>
                                                    <h4 class="mb-0 text-white fw-bold" id="total-pagar"
                                                        style="font-size: 1.4rem; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">0,00 $
                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="vr d-none d-md-block mx-4"
                                            style="height: 50px; background: rgba(255,255,255,0.3);"></div>
                                        <div class="w-100 w-md-auto mt-3 mt-md-0">
                                            <div class="mb-3">
                                                <label for="tasa-cambio" class="form-label text-white">Tasa BCV <span
                                                        class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text">Bs.</span>
                                                    <input type="number" class="form-control" id="tasa-cambio"
                                                        name="tasa_cambio" step="0.0001" min="0.0001" required
                                                        value="{{ old('tasa_cambio', $tasaSugerida) }}" placeholder="Ej: 151.12">
                                                </div>
                                            </div>
                                            <button id="btn-procesar-pago"
                                                class="btn btn-light w-100 w-md-auto btn-lg px-3 px-md-4 py-2" disabled
                                                style="border-radius: 8px; font-weight: 600; min-width: 180px; transition: all 0.2s;">
                                                <i class="fas fa-credit-card me-2"></i>Procesar Pago
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Subir Comprobante de Retención -->
            <div class="modal fade" id="modalSubirRetencion" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" style="border-radius:14px;border:none;box-shadow:0 20px 60px rgba(0,0,0,.2);">
                        <div class="modal-header" style="background:linear-gradient(135deg,#1e3a5f 0%,#2a5298 100%);border-radius:14px 14px 0 0;">
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:36px;height:36px;border-radius:8px;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-file-invoice" style="color:#fff;font-size:16px;"></i>
                                </div>
                                <div>
                                    <h5 class="modal-title mb-0" style="color:#fff;font-weight:700;">Comprobante de Retención</h5>
                                    <small style="color:rgba(255,255,255,.7);" id="modalRetencionSubtitulo"></small>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div id="alertComprobanteExistente" class="d-none mb-3 p-3" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;">
                                <div style="font-size:13px;color:#166534;font-weight:600;margin-bottom:6px;">
                                    <i class="fas fa-check-circle me-1"></i>Ya tiene un comprobante cargado
                                </div>
                                <a id="linkComprobanteActual" href="#" target="_blank" class="btn btn-sm btn-success" style="font-size:12px;">
                                    <i class="fas fa-eye me-1"></i>Ver comprobante actual
                                </a>
                            </div>
                            <form id="formSubirRetencion" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-semibold" style="font-size:13px;">Seleccione el comprobante (PDF, JPG o PNG, máx. 10 MB)</label>
                                    <input type="file" class="form-control" id="inputComprobanteRetencion" name="comprobante_retencion"
                                        accept=".pdf,.jpg,.jpeg,.png" required>
                                    <div id="comprobanteError" class="text-danger mt-1" style="font-size:12px;display:none;"></div>
                                </div>
                                <div class="alert alert-warning py-2" style="font-size:13px;">
                                    <i class="fas fa-info-circle me-1"></i>
                                    El administrador recibirá y validará el comprobante para liberar el saldo de IVA pendiente.
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary btn-sm" id="btnConfirmarSubirRetencion" onclick="confirmarSubirRetencion()">
                                <i class="fas fa-upload me-1"></i>Cargar comprobante
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal de Confirmación -->
            <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Confirmar Pago</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>¿Está seguro de procesar el pago de <strong><span id="monto-confirmar">0,00</span> </strong>?</p>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Se le redirigirá al formulario de pago para completar la transacción.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary d-none"
                                onclick="closeConfirmModal()">Cancelar</button>
                            <a href="#" id="confirmar-pago" class="btn btn-primary">Continuar al Pago</a>
                        </div>
                    </div>
                </div>
            </div>
            @endsection

            @section('styles')
            <style>
                .cliente-item {
                    transition: all 0.2s;
                    border-left: 3px solid transparent;
                    color: #212529;
                    display: block;
                    padding: 0.85rem 1rem;
                    line-height: 1.35;
                    position: relative;
                    z-index: 1;
                    /* Ensure text color is dark by default */
                }

                #lista-clientes .list-group-item {
                    margin: 0;
                }

                #lista-clientes .list-group-item+.list-group-item {
                    border-top-width: 1px;
                }

                #lista-clientes .list-group-item.active {
                    margin-top: 0;
                }

                .cliente-item h6,
                .cliente-item small {
                    white-space: normal;
                    overflow-wrap: anywhere;
                }

                .cliente-item .badge {
                    white-space: nowrap;
                }

                .cliente-item-top,
                .cliente-item-bottom {
                    gap: 0.5rem;
                }

                .cliente-rif {
                    flex: 0 0 auto;
                }

                .cliente-item h6 {
                    flex: 1 1 auto;
                    min-width: 0;
                }

                @media (max-width: 767.98px) {
                    #lista-clientes {
                        display: flex;
                        flex-direction: column;
                        gap: 0.55rem;
                        padding: 0.5rem;
                        background: #f3f4f6;
                    }

                    #lista-clientes .cliente-item {
                        display: block !important;
                        width: 100%;
                        height: auto !important;
                        min-height: 98px;
                        border: 1px solid #d8dde3;
                        border-left-width: 4px;
                        border-radius: 10px;
                        margin-bottom: 0;
                        padding: 0.75rem 0.85rem;
                        background-color: #fff;
                        overflow: visible !important;
                    }

                    #lista-clientes .list-group-item:last-child {
                        margin-bottom: 0;
                    }

                    #lista-clientes .list-group-item + .list-group-item {
                        border-top-width: 1px;
                        margin-top: 0;
                    }

                    #lista-clientes .cliente-item-top,
                    #lista-clientes .cliente-item-bottom {
                        display: flex !important;
                        width: 100%;
                        flex-wrap: wrap;
                        align-items: flex-start !important;
                    }

                    #lista-clientes .cliente-item-top {
                        flex-direction: column;
                        margin-bottom: 0.45rem;
                    }

                    #lista-clientes .cliente-item h6 {
                        width: 100%;
                        margin-bottom: 0.15rem !important;
                        font-size: 1rem;
                        line-height: 1.25;
                    }

                    #lista-clientes .cliente-rif {
                        width: 100%;
                        text-align: left;
                        font-size: 0.85rem;
                        line-height: 1.25;
                        word-break: break-word;
                    }

                    #lista-clientes .cliente-item-bottom > small {
                        display: block;
                        flex: 1 1 auto;
                        line-height: 1.25;
                    }

                    #lista-clientes .cliente-total {
                        flex: 0 0 auto;
                        margin-left: auto;
                    }

                    #lista-clientes .cliente-vencido {
                        background-color: #ffffff !important;
                        color: #5f4700 !important;
                    }
                }

                .cliente-item:hover {
                    background-color: #f8f9fa;
                    border-left-color: #0d6efd;
                    color: #212529;
                    /* Ensure text color remains dark on hover */
                }

                .cliente-item.active {
                    background-color: #f0f7ff;
                    border-left-color: #0d6efd;
                    font-weight: 500;
                    color: #212529 !important;
                    /* Force text color to be dark when active */
                }

                .cliente-vencido {
                    background-color: #e9d8a6 !important;
                    border-left-color: #c7a85a !important;
                    color: #5f4700 !important;
                    font-weight: 600;
                    position: relative;
                    overflow: visible;
                }

                .cliente-vencido::before {
                    content: none;
                }

                .cliente-vencido:hover {
                    background-color: #fffdf5 !important;
                    border-left-color: #d39e00 !important;
                    box-shadow: none;
                }

                .table th {
                    font-weight: 500;
                    font-size: 0.85rem;
                    text-transform: uppercase;
                    color: #6c757d;
                }

                .table td {
                    vertical-align: middle;
                }

                .form-check-input {
                    width: 1.2em;
                    height: 1.2em;
                }

                .form-check-input:checked {
                    background-color: #0d6efd;
                    border-color: #0d6efd;
                }

                #resumen-pago {
                    position: sticky;
                    bottom: 1rem;
                    z-index: 1030;
                }

                .card {
                    border: none;
                    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
                }

                /* Estilos para pedidos vencidos en tabla */
                .pedido-vencido {
                    background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%) !important;
                    position: relative;
                    animation: pulse-red 2s infinite;
                }

                .pedido-vencido td {
                    color: #721c24 !important;
                    font-weight: 600;
                }

                .pedido-vencido:hover {
                    background: linear-gradient(135deg, #ffcdd2 0%, #ffb3ba 100%) !important;
                    box-shadow: 0 4px 15px rgba(220, 53, 69, 0.2);
                }

                .pedido-vencido .badge {
                    animation: pulse-badge 1.5s infinite;
                }

                @keyframes pulse-red {
                    0% {
                        background-color: #fff;
                    }
                    50% {
                        background-color: #ffebee;
                    }
                    100% {
                        background-color: #fff;
                    }
                }

                @keyframes pulse-badge {
                    0% {
                        transform: scale(1);
                    }
                    50% {
                        transform: scale(1.05);
                    }
                    100% {
                        transform: scale(1);
                    }
                }
            </style>
            @endsection

            @section('scripts')
            <script>
                // Define closeModal globally so it's always available
                function closeModal() {
                    $('#paymentConfirmationModal').removeClass('show').hide();
                    $('.modal-backdrop').remove();
                    window.history.pushState({}, document.title, window.location.pathname);
                    // Also hide the generic confirmModal if visible
                    closeConfirmModal();
                }

                // Close confirmModal with Bootstrap 4/5 compatibility
                function closeConfirmModal() {
                    var modalEl = document.getElementById('confirmModal');
                    if (modalEl) {
                        if (typeof bootstrap !== 'undefined' && bootstrap.Modal && typeof bootstrap.Modal.getOrCreateInstance ===
                            'function') {
                            // Bootstrap 5.2+
                            var modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
                            modalInstance.hide();
                        } else if (typeof $ !== 'undefined' && $(modalEl).modal) {
                            // Bootstrap 4 fallback
                            // Ensure modal is initialized before hiding
                            if (!$(modalEl).data('bs.modal')) {
                                $(modalEl).modal({
                                    show: false
                                });
                            }
                            $(modalEl).modal('hide');
                        }
                    }
                }



                $(document).ready(function() {
                    var cliente_seleccionado = null;
                    // Inicializar pestañas de Bootstrap 4
                    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
                        // Actualizar la pestaña activa
                        $(e.target).addClass('active').siblings().removeClass('active');
                    });

                    // Filtro de búsqueda
                    let pedidosCliente = [];
                    let pedidosSeleccionados = [];
                    let clienteActual = null;

                    // Búsqueda de clientes
                    $('#searchClientes').on('input', function() {
                        const searchTerm = $(this).val().toLowerCase();
                        $('.cliente-item').each(function() {
                            const nombre = $(this).data('nombre');
                            if (nombre.includes(searchTerm)) {
                                $(this).show();
                            } else {
                                $(this).hide();
                            }
                        });
                    });

                    $('#revision-tab').on('click', function() {
                        $.ajax({
                            url: '{{ route('vendedores.pagos.pedidos.revision', '') }}/' + cliente_seleccionado,
                            type: 'GET',
                            success: function(response) {
                                if (response.success && response.pedidos) {
                                    pedidosCliente = response.pedidos;
                                    mostrarPedidos(pedidosCliente, 'revision');
                                } else {
                                    $('#sin-pedidos').html(
                                        '<i class="fas fa-inbox fa-3x mb-3"></i><p>El cliente no tiene pedidos pendientes</p>'
                                    );
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('Error loading orders:', xhr.responseJSON);
                            }
                        })
                    })

                    $('#pagados-tab').on('click', function() {
                        $.ajax({
                            url: '{{ route('vendedores.pagos.pedidos.aprobadas', '') }}/' + cliente_seleccionado,
                            type: 'GET',
                            success: function(response) {
                                console.log('response')
                                console.log(response)
                                if (response.success && response.pedidos) {
                                    console.log('inside')
                                    console.log('Pedidos recibidos:', response.pedidos);
                                    pedidosCliente = response.pedidos;
                                    console.log('pedidosCliente asignado:', pedidosCliente);
                                    mostrarPedidos(pedidosCliente, 'pagadas');
                                    console.log('outside')
                                } else {
                                    $('#sin-pedidos').html(
                                        '<i class="fas fa-inbox fa-3x mb-3"></i><p>El cliente no tiene pedidos pendientes</p>'
                                    );
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('Error loading orders:', xhr.responseJSON);
                            }
                        })
                    })

                    // Cargar pedidos del cliente
                    $(document).on('click', '.cliente-item', function(e) {
                        e.preventDefault();

                        const rif = $(this).data('rif');
                        const nombre = $(this).find('h6').text();

                        cliente_seleccionado = rif;

                        // Actualizar UI
                        $('.cliente-item').removeClass('active');
                        $(this).addClass('active');

                        // Mostrar datos del cliente seleccionado
                        $('#cliente-seleccionado').removeClass('d-none');
                        $('#nombre-cliente').text(nombre);
                        $('#rif-cliente').text(rif);

                        // Limpiar selección anterior
                        pedidosSeleccionados = [];
                        actualizarResumenPago();

                        // Mostrar loading
                        $('#pedidos-container').addClass('d-none');
                        $('#sin-pedidos').html(
                            '<div class="spinner-border text-primary" role="status"><span class="visually-hidden"></span></div><p class="mt-2"></p>'
                        );

                        // Hacer scroll a la sección de pedidos en móviles
                        if (window.innerWidth <= 767.98) {
                            $('html, body').animate({
                                scrollTop: $('#pedidos-container-main').offset().top - 20
                            }, 500);
                        }

                        // Guardar referencia al cliente actual
                        clienteActual = rif;



                        // Cargar pedidos del cliente
                        $.ajax({
                            url: '{{ route('vendedores.pagos.pedidos', '') }}/' + rif,
                            type: 'GET',
                            success: function(response) {
                                if (response.success && response.pedidos.length > 0) {
                                    pedidosCliente = response.pedidos;
                                    mostrarPedidos(pedidosCliente);
                                } else {
                                    $('#sin-pedidos').html(
                                        '<i class="fas fa-inbox fa-3x mb-3"></i><p>El cliente no tiene pedidos pendientes</p>'
                                    );
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('Error loading orders:', xhr.responseJSON);
                                let errorMsg = 'Error al cargar los pedidos';
                                if (xhr.responseJSON && xhr.responseJSON.debug) {
                                    errorMsg += `<br><small>${xhr.responseJSON.debug.error}</small>`;
                                }
                                $('#sin-pedidos').html(
                                    `<i class="fas fa-exclamation-triangle text-danger fa-3x mb-3"></i><p>${errorMsg}</p>`
                                );
                            }
                        });
                    });

                    // Mostrar lista de pedidos
                    function mostrarPedidos(pedidos, tab = null) {
                        if (tab == 'revision') {
                            return mostrarPedidosRevision(pedidos);
                        }
                        if (tab == 'pagadas') {
                            return mostrarPedidosPagados(pedidos);
                        }
                        const tbody = $('#tabla-pedidos');
                        tbody.empty();

                        // Update the order counter
                        $('#contador-pedidos').text(pedidos.length);

                        if (pedidos.length === 0) {
                            $('#pedidos-container').addClass('d-none');
                            $('#sin-pedidos').html(
                                '<i class="fas fa-inbox fa-3x mb-3"></i><p>No hay pedidos pendientes</p>');
                            return;
                        }

                        const pedidosConRetencionPendiente = [];

                        pedidos.forEach((pedido, index) => {
                            const saldoBase = parseFloat(pedido.saldo_base) || 0;
                            const saldoIvaBs = parseFloat(pedido.saldo_iva_bs) || 0;
                            const saldoAjustes = parseFloat(pedido.saldo_ajustes) || 0;
                            const saldo = saldoAjustes + saldoBase; // Total pendiente incluyendo ajustes
                            const ajustesNeto = parseFloat(pedido.ajustes_neto) || 0;
                            const porcRetencion = parseFloat(pedido.porc_retencion) || 0;
                            const tieneComprobanteRetencion = !!(pedido.comprobante_retencion && pedido.comprobante_retencion !== 'null');
                            
                            // Validación completa: porc_retencion > 0 && saldo_base <= 0.01 && saldo_iva_bs > 0.01
                            const tieneRetencionPendiente = porcRetencion > 0 && saldoBase <= 0.01 && saldoIvaBs > 0.01;
                            
                            if (tieneRetencionPendiente) {
                                pedidosConRetencionPendiente.push(pedido);
                            }

                            const totalmentePagado = saldo <= 0.01 && saldoIvaBs <= 0.01;
                            // Caso especial: base y ajustes pagados, pero saldo_iva_bs > 0 porque es el monto de retención
                            // que quedó pendiente tras aplicar "Aplicar Retención" al IVA. Solo falta subir comprobante.
                            const soloFaltaComprobanteRetencion = porcRetencion > 0 && saldoBase <= 0.01 && saldoAjustes <= 0.01 && saldoIvaBs > 0.01;
                            const deshabilitarSeleccion = totalmentePagado || soloFaltaComprobanteRetencion;

                            // Ajustes pendientes del pedido
                            const ajustesDetalle = pedido.ajustes_detalle || [];
                            let ajustesHtml = '';
                            let totalAjustes = 0;
                            const saldoConAjustes = saldoBase + saldoAjustes; // Total pendiente incluyendo ajustes
                            
                            // Mostrar información de ajustes siempre que existan (con o sin detalles)
                            if (ajustesNeto !== 0 || ajustesDetalle.length > 0) {
                                const items = ajustesDetalle.map(a => {
                                    const color = a.tipo === 'cargo' ? '#dc3545' : '#28a745';
                                    const sign  = a.tipo === 'cargo' ? '+' : '-';
                                    totalAjustes = a.tipo === 'cargo' ? totalAjustes + parseFloat(a.monto) : totalAjustes - parseFloat(a.monto);
                                    return `<div class="small" style="color:${color};">${sign}$${parseFloat(a.monto).toFixed(2)} — ${a.concepto}</div>`;
                                }).join('');
                                const netoColor = ajustesNeto > 0 ? '#dc3545' : '#a5a728';
                                const netoSign  = ajustesNeto > 0 ? '+' : '';
                                ajustesHtml = `
                                    <div class="mt-1 p-1 border rounded" style="background:#f2f3a2;font-size:.78rem;">
                                        <div class="font-weight-bold text-muted mb-1"><i class="fas fa-file-invoice-dollar mr-1"></i>Ajustes:</div>
                                        ${ajustesHtml}
                                        <div class="fw-bold mt-1" style="color:${netoColor};">Neto: ${netoSign}$${Math.abs(totalAjustes).toFixed(2)}</div>
                                        <div class="fw-bold mt-1" style="font-size:.85rem;">Total: $${saldoConAjustes.toFixed(2)}</div>
                                    </div>`;
                            }

                            const estaVencido = pedido.esta_vencido === 1;
                            const mensajeNoSeleccionable = soloFaltaComprobanteRetencion
                                ? 'No seleccionable: este pedido no tiene saldo pendiente, solo falta subir comprobante de retención.'
                                : 'No seleccionable';
                            const etiquetaBloqueoHtml = soloFaltaComprobanteRetencion
                                ? '<small class="d-block mt-1" style="font-size:10px;color:#9ca3af;line-height:1.1;">Sin saldo pendiente</small>'
                                : '';
                            const controlSeleccionHtml = `
                                <input type="checkbox" class="form-check-input check-pedido"
                                    data-id="${pedido.id}"
                                    data-monto="${saldoConAjustes}"
                                    data-saldo-iva-bs="${saldoIvaBs}"
                                    data-ajustes-neto="${parseFloat(pedido.ajustes_neto) || 0}"
                                    ${deshabilitarSeleccion ? 'disabled' : ''}
                                    title="${deshabilitarSeleccion ? mensajeNoSeleccionable : 'Seleccionar pedido para pago'}"
                                    style="width: 1.5em; height: 1.5em; ${soloFaltaComprobanteRetencion ? 'cursor:not-allowed;opacity:.55;' : ''}">
                                ${etiquetaBloqueoHtml}
                            `;

                            const tr = $(`
                                <tr data-id="${pedido.id}" class="${totalmentePagado ? 'table-success' : ''} ${pedido.descuento_aplicado > 0 ? 'table-warning' : ''} ${estaVencido ? 'pedido-vencido' : ''}">
                                    <td data-label="Seleccionar">
                                        <div class="form-check d-flex justify-content-center">
                                            ${controlSeleccionHtml}
                                        </div>
                                    </td>
                                    <td data-label="Pedido">
                                        <div class="fw-bold">Pedido #${pedido.numero}</div>
                                        ${pedido.factura_numero ? `<div class="text-success small">Factura: ${pedido.factura_numero}</div>` : ''}
                                        <small class="text-muted">${pedido.fecha}</small>
                                        ${pedido.fecha_despacho ? `<div class="text-muted small">Despacho: ${pedido.fecha_despacho}</div>` : ''}
                                        ${estaVencido ? `<div class="badge bg-danger text-white mt-1"><i class="fas fa-exclamation-triangle me-1"></i>VENCIDO</div>` : ''}
                                    </td>
                                    <td class="text-end" data-label="Total ($)">${formatMoney(pedido.total)}</td>
                                    <td class="text-end" data-label="Abonado ($)">${formatMoney(pedido.monto_pagado)}</td>
                                    <td class="text-end" data-label="Saldo ($)">
                                        <span class="fw-bold text-primary">${formatMoney(pedido.saldo_base)}</span>
                                        ${ajustesHtml}
                                    </td>
                                    <td class="text-end" data-label="IVA (Bs.)">
                                         <span class="fw-bold ${tieneRetencionPendiente ? 'text-warning' : 'text-success'}">${formatBS(pedido.saldo_iva_bs)}</span>
                                         ${tieneRetencionPendiente ? '<br><small class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i>Retención pendiente</small>' : ''}
                                         ${tieneRetencionPendiente ? `<br><button type="button" class="btn btn-sm mt-1" style="font-size:11px;padding:2px 8px;border:1.5px solid #ea580c;color:#ea580c;background:white;border-radius:6px;cursor:pointer;" onclick="abrirModalSubirRetencion(${pedido.id}, ${pedido.numero}, ${JSON.stringify(pedido.comprobante_retencion || null)})"><i class="fas fa-upload me-1"></i>${pedido.comprobante_retencion ? 'Actualizar comprobante' : 'Subir comprobante'}</button>` : ''}
                                    </td>
                                </tr>
                            `);
                            tbody.append(tr);
                        });

                        // Mostrar aviso global si hay pedidos con retención de IVA pendiente
                        if (pedidosConRetencionPendiente.length > 0) {
                            const totalRetencion = pedidosConRetencionPendiente.reduce((s, p) => s + parseFloat(p.saldo_iva_bs), 0);
                            const ids = pedidosConRetencionPendiente.map(p => '#' + p.numero).join(', ');

                            const ordenesHtml = pedidosConRetencionPendiente.map(p => {
                                const tieneComprobante = p.comprobante_retencion && p.comprobante_retencion !== 'null';
                                const btnLabel = tieneComprobante ? 'Actualizar comprobante' : 'Subir comprobante';
                                const badge = tieneComprobante
                                    ? `<span style="font-size:11px;color:#16a34a;font-weight:700;margin-right:8px;"><i class="fas fa-check-circle me-1"></i>Cargado</span>`
                                    : '';
                                return `
                                    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:8px;padding:8px 12px;background:rgba(255,255,255,.6);border-radius:8px;flex-wrap:wrap;gap:6px;">
                                        <span style="font-size:13px;color:#92400e;font-weight:600;">Pedido #${p.numero} — Bs. ${formatBS(p.saldo_iva_bs)} ${badge}</span>
                                        <button type="button"
                                            style="border:1.5px solid #ea580c;color:#ea580c;background:white;border-radius:8px;padding:4px 12px;font-size:12px;font-weight:700;cursor:pointer;"
                                            onclick="abrirModalSubirRetencion(${p.id}, ${p.numero}, ${JSON.stringify(p.comprobante_retencion || null)})">
                                            <i class="fas fa-upload me-1"></i>${btnLabel}
                                        </button>
                                    </div>`;
                            }).join('');

                            $('#aviso-retencion-pendiente').html(`
                                <div style="background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); border: 2px solid #f97316; border-radius: 10px; padding: 14px 18px;">
                                    <div style="display:flex;align-items:flex-start;gap:14px;">
                                        <div style="flex-shrink:0;width:38px;height:38px;border-radius:8px;background:linear-gradient(135deg,#f97316,#ea580c);display:flex;align-items:center;justify-content:center;">
                                            <i class="fas fa-file-invoice" style="color:white;font-size:16px;"></i>
                                        </div>
                                        <div style="flex:1;">
                                            <div style="font-weight:700;color:#9a3412;font-size:14px;margin-bottom:4px;">
                                                IVA retenido pendiente de validación — Pedido${pedidosConRetencionPendiente.length > 1 ? 's' : ''} ${ids}
                                            </div>
                                            <div style="color:#c2410c;font-size:13px;line-height:1.5;">
                                                El IVA retenido (Bs. ${formatBS(totalRetencion)}) quedará pendiente hasta que suba el comprobante y el administrador lo valide.
                                            </div>
                                            ${ordenesHtml}
                                        </div>
                                    </div>
                                </div>
                            `).removeClass('d-none');
                        } else {
                            $('#aviso-retencion-pendiente').addClass('d-none');
                        }

                        $('#sin-pedidos').addClass('d-none');
                        $('#pedidos-container').removeClass('d-none');
                    }

                    // Mostrar lista de pedidos en revision
                    function mostrarPedidosRevision(pedidos) {

                        if (pedidos.length === 0) {
                            $('#revision').html(
                                '<div class="alert alert-warning m-3"><h5 class="alert-heading"><i class="fas fa-clock me-2"></i> Pedidos en Revisión</h5><hr><p class="mb-0">No hay pedidos en revisión en este momento.</p></div>'
                            );
                            return;
                        }
                        let html =
                            '<div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-lightd-md-table-header-group"><tr><th>Pedido</th><th>Fecha</th><th class="text-end">Total</th><th class="text-end">Estatus</th><th class="text-end">Comprobante</th></tr></thead><tbody id="tabla-pedidos-revision">';


                        pedidos.forEach((pedido, index) => {
                            const saldo = pedido.saldo_ajustes + pedido.saldo_base; // Total pendiente incluyendo ajustes
                            const base_url = '{{ url('vendedores/pagos/comprobante-pedido') }}/';
                            const urlComprobante = typeof pedido?.id !== 'undefined' && pedido?.id
                                ? `${base_url}${encodeURIComponent(pedido?.id)}` : null;
                            const comprobanteHtml = urlComprobante ?
                                `<a target="_blank" href="${urlComprobante}" class="badge badge-info"><i class="fas fa-receipt me-2"></i> Ver Pagos</a>` :
                                '<span class="badge badge-warning">Sin Comprobante</span>';

                            html += `
                                <tr data-id="${pedido.id}" class="${(pedido.saldo_ajustes + pedido.saldo_base) <= 0 ? 'table-success' : ''} ${pedido.descuento_aplicado > 0 ? 'table-warning' : ''}">
                                    <td data-label="Pedido">
                                    <div class="fw-bold">Pedido #${pedido.numero}</div>
                                    ${pedido.fecha_despacho ? `<div class="text-muted small">Despacho: ${pedido.fecha_despacho}</div>` : ''}
                                    </td>
                                    <td data-label="Fecha">${pedido.fecha}</td>
                                    <td class="text-end" data-label="Total">${formatMoney(pedido.total)}</td>
                                    <td class="text-end" data-label="Estatus"><span class="badge badge-warning">EN REVISIÓN</span></td>
                                    <td class="text-end" data-label="Estatus">${comprobanteHtml}</td>
                                </tr>
                            `;
                            //html+=tr;
                        });
                        html += '</tbody></table></div>';

                        $('#revision').html(html);
                    }

                    // Mostrar lista de pedidos pagados
                    function mostrarPedidosPagados(pedidos) {

                        if (pedidos.length === 0) {
                            $('#pagados').html(
                                '<div class="alert alert-success m-3"><h5 class="alert-heading"><i class="fas fa-check-circle me-2"></i> Pedidos Pagados</h5><hr><p class="mb-0">No hay historial de pedidos pagados para mostrar.</p></div>'
                            );
                            return;
                        }
                        let html =
                            '<div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-lightd-md-table-header-group"><tr><th>Pedido</th><th>Fecha</th><th class="text-end">Total</th><th class="text-end">Estatus</th><th class="text-end">Comprobante</th></tr></thead><tbody id="tabla-pedidos-pagados">';


                        pedidos.forEach((pedido, index) => {
                            const saldo = pedido.saldo_ajustes + pedido.saldo_base; // Total pendiente incluyendo ajustes
                            const base_url = '{{ url('vendedores/pagos/comprobante-pedido') }}/';
                            const urlComprobante = typeof pedido?.id !== 'undefined' && pedido?.id
                                ? `${base_url}${encodeURIComponent(pedido?.id)}` : null;
                            const comprobanteHtml = urlComprobante ?
                                `<a target="_blank" href="${urlComprobante}" class="badge badge-info"><i class="fas fa-receipt me-2"></i> Ver Pagos</a>` :
                                '<span class="badge badge-warning">Sin Comprobante</span>';

                            html += `
                                <tr data-id="${pedido.id}" class="${(pedido.saldo_ajustes + pedido.saldo_base) <= 0 ? 'table-success' : ''} ${pedido.descuento_aplicado > 0 ? 'table-warning' : ''}">
                                    <td data-label="Pedido">
                                    <div class="fw-bold">Pedido #${pedido.numero}</div>
                                    ${pedido.fecha_despacho ? `<div class="text-muted small">Despacho: ${pedido.fecha_despacho}</div>` : ''}
                                    </td>
                                    <td data-label="Fecha">${pedido.fecha}</td>
                                    <td class="text-end" data-label="Total">${formatMoney(pedido.monto_pagado)}</td>
                                    <td class="text-end" data-label="Estatus"><span class="badge badge-success">PAGADO</span></td>
                                    <td class="text-end" data-label="Estatus">${comprobanteHtml}</td>
                                </tr>
                            `;
                            //html+=tr;
                        });
                        html += '</tbody></table></div>';

                        $('#pagados').html(html);
                    }

                    // Formatear moneda
                    function formatMoney(amount) {
                        if (amount === undefined || amount === null) return '0.00 $';
                        return new Intl.NumberFormat('es-VE', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }).format(amount) + ' $';
                    }

                    // Formatear Bolívares
                    function formatBS(amount) {
                        if (amount === undefined || amount === null) return '0.00 Bs.';
                        return new Intl.NumberFormat('es-VE', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }).format(amount) + ' Bs.';
                    }

                    // Manejar selección de pedidos
                    $(document).on('change', '.check-pedido', function() {
                        const pedidoId = $(this).data('id');
                        const monto = parseFloat($(this).data('monto'));
                        const saldoIvaBs = parseFloat($(this).attr('data-saldo-iva-bs')) || 0;
                        const ajustesNeto = parseFloat($(this).attr('data-ajustes-neto')) || 0;

                        if ($(this).is(':checked')) {
                            // Solo permitir seleccionar pedidos del mismo cliente
                            if (clienteActual && pedidosSeleccionados.length > 0 &&
                                !pedidosSeleccionados.some(p => p.rif === clienteActual)) {
                                $(this).prop('checked', false);
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Atención',
                                    text: 'Solo puede seleccionar pedidos de un mismo cliente a la vez',
                                    confirmButtonText: 'Entendido'
                                });
                                return;
                            }

                            pedidosSeleccionados.push({
                                'id': pedidoId,
                                'monto': monto,
                                'saldo_iva_bs': saldoIvaBs,
                                'ajustes_neto': ajustesNeto,
                                'rif': clienteActual
                            });
                        } else {
                            pedidosSeleccionados = pedidosSeleccionados.filter(p => p.id !== pedidoId);
                        }

                        actualizarResumenPago();
                    });

                    // Actualizar resumen de pago
                    function actualizarResumenPago() {
                        // Calcular totales directamente desde los datos del checkbox seleccionado
                        // para no depender de la solapa activa (pendientes/revision/pagados).
                        const totalUSD = pedidosSeleccionados.reduce((sum, p) => {
                            const saldoTotal = parseFloat(p?.monto) || 0;
                            return sum + (saldoTotal > 0 ? saldoTotal : 0);
                        }, 0);

                        const totalIvaBs = pedidosSeleccionados.reduce((sum, p) => {
                            return sum + (parseFloat(p?.saldo_iva_bs) || 0);
                        }, 0);

                        const totalAjustesNetos = pedidosSeleccionados.reduce((sum, p) => {
                            return sum + (parseFloat(p?.ajustes_neto) || 0);
                        }, 0);

                        if (totalUSD > 0 || totalIvaBs > 0 || totalAjustesNetos > 0) {
                            $('#resumen-pago').removeClass('d-none');
                            $('#total-iva-bs').text(formatBS(totalIvaBs));
                            $('#total-ajustes').text(formatMoney(totalAjustesNetos));
                            $('#total-pagar').text(formatMoney(totalUSD));
                            $('#monto-confirmar').text(formatMoney(totalUSD));
                            $('#btn-procesar-pago').prop('disabled', false);

                            // Actualizar la URL con los pedidos seleccionados y ajustes
                            const rifCliente = $('#rif-cliente').text();
                            const idsPedidos = pedidosSeleccionados.map(p => p.id);
                            const ajustesData = pedidosSeleccionados.map(p => {
                                return {
                                    id: p.id,
                                    ajustes_neto: parseFloat(p?.ajustes_neto) || 0
                                };
                            });
                            
                            const url = '{{ route('vendedores.pagos.metodo', '') }}/' + rifCliente + 
                                '?pedidos=' + idsPedidos.join(',') + 
                                '&ajustes=' + encodeURIComponent(JSON.stringify(ajustesData));
                            $('#btn-procesar-pago').attr('href', url);

                            $('#btn-procesar-pago')
                                .removeClass('btn-success')
                                .addClass('btn-light')
                                .html('<i class="fas fa-credit-card me-2"></i> Registrar Pago');
                        } else {
                            // Solo ocultar el resumen si no hay pedidos seleccionados
                            if (pedidosSeleccionados.length === 0) {
                                $('#resumen-pago').addClass('d-none');
                                $('#btn-procesar-pago').prop('disabled', true);
                                $('#btn-procesar-pago').removeAttr('href');
                            }
                        }
                    }

                    // Procesar pago
                    $('#btn-procesar-pago').click(function(e) {
                        e.preventDefault();

                        if (pedidosSeleccionados.length === 0) {
                            return;
                        }

                        // Validar tasa de cambio
                        const tasaCambio = parseFloat($('#tasa-cambio').val());
                        if (isNaN(tasaCambio) || tasaCambio <= 0) {
                            swal({
                                type: 'error',
                                title: 'Tasa de cambio requerida',
                                text: 'Por favor ingrese una tasa de cambio válida en Bs.',
                                confirmButtonText: 'Entendido'
                            });
                            $('#tasa-cambio').focus();
                            return;
                        }

                        // Mostrar modal de confirmación
                        const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
                        modal.show();
                    });

                    // Confirmar pago
                    $('#confirmar-pago').click(function(e) {
                        e.preventDefault();

                        const rifCliente = $('#rif-cliente').text();
                        const idsPedidos = pedidosSeleccionados.map(p => p.id);
                        const tasaCambio = parseFloat($('#tasa-cambio').val());

                        // Redirigir a la vista de método de pago con los pedidos seleccionados y la tasa de cambio
                        const url = '{{ route('vendedores.pagos.metodo', '') }}/' + rifCliente + '?pedidos=' + idsPedidos.join(',') + '&tasa_cambio=' + encodeURIComponent(tasaCambio);
                        window.location.href = url;
                    });

                    });

                // ── Comprobante de retención ─────────────────────────────────────────
                let pedidoRetencionActual = null;

                function abrirModalSubirRetencion(pedidoId, pedidoNum, comprobanteActual) {
                    pedidoRetencionActual = pedidoId;
                    document.getElementById('modalRetencionSubtitulo').textContent = 'Pedido #' + pedidoNum;
                    document.getElementById('inputComprobanteRetencion').value = '';
                    document.getElementById('comprobanteError').style.display = 'none';

                    const alertExistente = document.getElementById('alertComprobanteExistente');
                    if (comprobanteActual && comprobanteActual !== 'null') {
                        alertExistente.classList.remove('d-none');
                        document.getElementById('linkComprobanteActual').href = '{{ asset('storage') }}/' + comprobanteActual;
                    } else {
                        alertExistente.classList.add('d-none');
                    }

                    $('#modalSubirRetencion').modal('show');
                }

                function confirmarSubirRetencion() {
                    const fileInput = document.getElementById('inputComprobanteRetencion');
                    const errorDiv  = document.getElementById('comprobanteError');
                    errorDiv.style.display = 'none';

                    if (!fileInput.files || fileInput.files.length === 0) {
                        errorDiv.textContent = 'Debe seleccionar un archivo.';
                        errorDiv.style.display = 'block';
                        return;
                    }

                    const btn = document.getElementById('btnConfirmarSubirRetencion');
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Cargando…';

                    const formData = new FormData();
                    formData.append('comprobante_retencion', fileInput.files[0]);
                    formData.append('_token', '{{ csrf_token() }}');

                    fetch('{{ url('vendedores/pedidos') }}/' + pedidoRetencionActual + '/subir-retencion', {
                        method: 'POST',
                        body: formData,
                    })
                    .then(function(r) {
                        if (!r.ok && r.status !== 422) {
                            return r.text().then(function(t) { throw new Error('HTTP ' + r.status); });
                        }
                        return r.json();
                    })
                    .then(function(data) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-upload me-1"></i>Cargar comprobante';
                        if (data.success) {
                            $('#modalSubirRetencion').modal('hide');
                            // Recargar pedidos del cliente activo para reflejar el nuevo comprobante
                            const rifActivo = $('#rif-cliente').text().trim();
                            if (rifActivo) cargarPedidosCliente(rifActivo);
                            alert(data.message);
                        } else {
                            errorDiv.textContent = data.message || 'Error al cargar el comprobante.';
                            errorDiv.style.display = 'block';
                        }
                    })
                    .catch(function(err) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-upload me-1"></i>Cargar comprobante';
                        errorDiv.textContent = 'Error al procesar la respuesta. Por favor recargue la página y verifique si el comprobante fue cargado.';
                        errorDiv.style.display = 'block';
                    });
                }
            </script>
            @endsection
