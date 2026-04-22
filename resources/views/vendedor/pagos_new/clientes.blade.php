@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel') . ' - Gestión de Pagos v2')
@section('titulo_header', 'Gestión de Pagos v2')
@section('subtitulo_header', 'Seleccione un cliente y sus pedidos a pagar')

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

    .cliente-item {
        transition: all 0.2s;
        border-left: 3px solid transparent;
        color: #212529;
    }

    .cliente-item:hover {
        background-color: #f8f9fa;
        border-left-color: #0d6efd;
        color: #212529;
    }

    .cliente-item.active {
        background-color: #f0f7ff;
        border-left-color: #0d6efd;
        font-weight: 500;
        color: #212529 !important;
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
        border-radius: 12px;
    }
</style>
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css" rel="stylesheet">
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

    <div class="row">
        <!-- Lista de Clientes -->
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-users me-2"></i> Clientes con Deuda (v2)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="p-3 border-bottom bg-light">
                        <div class="input-group shadow-sm">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" id="searchClientes" class="form-control border-start-0"
                                placeholder="Buscar cliente por nombre o RIF...">
                        </div>
                    </div>
                    <div class="list-group list-group-flush" style="max-height: 70vh; overflow-y: auto;" id="lista-clientes">
                        @forelse($clientes as $cliente)
                        <a href="#" class="list-group-item list-group-item-action cliente-item py-3"
                            data-rif="{{ $cliente->RIF }}" data-nombre="{{ strtolower($cliente->NOMBRE) }}">
                            <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                <h6 class="mb-0 fw-bold">{{ $cliente->NOMBRE }}</h6>
                                <small class="text-primary fw-bold">{{ $cliente->RIF }}</small>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <div class="small text-muted">
                                    <i class="fas fa-file-invoice me-1"></i>
                                    {{ $cliente->pedidos_pendientes }} {{ $cliente->pedidos_pendientes == 1 ? 'pedido' : 'pedidos' }}
                                </div>
                                <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">
                                    {{ number_format($cliente->total_pendiente, 2, ',', '.') }} $
                                </span>
                            </div>
                        </a>
                        @empty
                        <div class="p-5 text-center text-muted">
                            <i class="fas fa-user-slash fa-3x mb-3 opacity-25"></i>
                            <p>No hay clientes con deudas pendientes</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Detalle de Pedidos -->
        <div class="col-md-8" id="pedidos-container-main">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white pt-3 px-0">
                    <ul class="nav nav-tabs card-header-tabs mx-3 border-bottom-0" id="pedidosTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active fw-bold px-4" id="pendientes-tab" data-toggle="tab" href="#pendientes"
                                role="tab" aria-controls="pendientes" aria-selected="true">
                                <i class="fas fa-clock me-2"></i>Pendientes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold px-4" id="revision-tab" data-toggle="tab" href="#revision" role="tab"
                                aria-controls="revision" aria-selected="false">
                                <i class="fas fa-search-dollar me-2"></i>En Revisión
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold px-4" id="pagados-tab" data-toggle="tab" href="#pagados" role="tab"
                                aria-controls="pagados" aria-selected="false">
                                <i class="fas fa-check-circle me-2"></i>Pagados
                            </a>
                        </li>
                    </ul>
                    <div id="cliente-seleccionado" class="mt-4 px-4 pb-3 border-bottom d-none bg-light pt-3">
                        <div class="row align-items-center">
                            <div class="col">
                                <span class="text-muted small text-uppercase fw-bold">Cliente Seleccionado</span>
                                <h4 id="nombre-cliente" class="mb-0 fw-bold text-primary"></h4>
                                <span id="rif-cliente" class="badge bg-secondary"></span>
                            </div>
                            <div class="col-auto">
                                <div class="text-end">
                                    <span class="text-muted small text-uppercase d-block fw-bold">Total Deuda</span>
                                    <span id="total-deuda-cliente" class="h4 mb-0 fw-bold text-danger">0,00 $</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="tab-content" id="pedidosTabsContent">
                        <!-- Pestaña de Pedidos Pendientes -->
                        <div class="tab-pane fade show active" id="pendientes" role="tabpanel"
                            aria-labelledby="pendientes-tab">
                            <div id="pedidos-container" class="d-none">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="text-center" width="80">Seleccionar</th>
                                                <th>Pedido / Factura</th>
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
                            <div id="sin-pedidos" class="text-center p-5 text-muted my-5">
                                <i class="fas fa-hand-pointer fa-4x mb-4 opacity-25"></i>
                                <h4 class="fw-bold">Esperando Selección</h4>
                                <p>Seleccione un cliente de la lista de la izquierda para ver y gestionar sus pedidos.</p>
                            </div>
                        </div>

                        <!-- Pestaña de En Revisión -->
                        <div class="tab-pane fade" id="revision" role="tabpanel" aria-labelledby="revision-tab">
                            <div class="text-center p-5 text-muted my-5" id="revision-empty">
                                <i class="fas fa-spinner fa-3x fa-spin mb-4 d-none" id="revision-loading"></i>
                                <i class="fas fa-info-circle fa-4x mb-4 opacity-25" id="revision-icon"></i>
                                <p id="revision-message">Seleccione un cliente para ver sus pedidos en revisión.</p>
                            </div>
                        </div>

                        <!-- Pestaña de Pagados -->
                        <div class="tab-pane fade" id="pagados" role="tabpanel" aria-labelledby="pagados-tab">
                            <div class="text-center p-5 text-muted my-5" id="pagados-empty">
                                <i class="fas fa-spinner fa-3x fa-spin mb-4 d-none" id="pagados-loading"></i>
                                <i class="fas fa-history fa-4x mb-4 opacity-25" id="pagados-icon"></i>
                                <p id="pagados-message">Seleccione un cliente para ver su historial de pagos.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen de Pago Fijo -->
    <div class="row mt-4 mb-5 d-none justify-content-center" id="resumen-pago">
        <div class="col-12 col-lg-11 col-xl-10">
            <div class="card border-0 shadow-lg overflow-hidden" style="border-radius: 12px;">
                <div class="card-body py-4 px-4"
                    style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                    <div class="row align-items-center">
                        <div class="col-md-4 text-center text-md-start mb-3 mb-md-0">
                            <span class="text-white-50 small text-uppercase fw-bold">Resumen de Selección</span>
                            <h3 class="text-white mb-0 fw-bold">REGISTRAR PAGO</h3>
                            <div class="badge bg-white bg-opacity-20 text-white mt-1" id="contador-pedidos-badge">
                                0 pedidos seleccionados
                            </div>
                        </div>

                        <div class="col-md-4 text-center text-md-end mb-3 mb-md-0 border-end border-white border-opacity-10">
                            <div class="pe-md-4">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-white-50 small">Total USD:</span>
                                    <span class="text-white fw-bold" id="total-usd-resumen">0,00 $</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-white-50 small">Total IVA (Bs.):</span>
                                    <span class="text-success fw-bold" id="total-iva-bs-resumen">0,00 Bs.</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top border-white border-opacity-10">
                                    <span class="text-white fw-bold h5 mb-0">TOTAL A PAGAR:</span>
                                    <span class="text-white fw-bold h4 mb-0" id="total-pagar-combinado">0,00 $</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 ps-md-4">
                            <div class="mb-3">
                                <label for="tasa-cambio" class="form-label text-white-50 small fw-bold text-uppercase">Tasa BCV del día</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text bg-white border-0 fw-bold">Bs.</span>
                                    <input type="number" class="form-control border-0 fw-bold" id="tasa-cambio"
                                        name="tasa_cambio" step="0.01" min="0.01"
                                        value="{{ old('tasa_cambio', $tasaSugerida) }}" placeholder="0.00">
                                </div>
                            </div>
                            <button id="btn-procesar-pago"
                                class="btn btn-light w-100 btn-lg shadow fw-bold text-primary" disabled
                                style="border-radius: 8px; transition: all 0.3s ease;">
                                <i class="fas fa-credit-card me-2"></i>Continuar al Pago
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
<script>
    $(document).ready(function() {
        let pedidosCliente = [];
        let pedidosSeleccionados = [];
        let clienteActual = null;

        // Búsqueda de clientes
        $('#searchClientes').on('input', function() {
            const searchTerm = $(this).val().toLowerCase();
            $('.cliente-item').each(function() {
                const nombre = $(this).data('nombre');
                const rif = $(this).data('rif').toString().toLowerCase();
                if (nombre.includes(searchTerm) || rif.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        // Cargar pedidos del cliente
        $(document).on('click', '.cliente-item', function(e) {
            e.preventDefault();
            const rif = $(this).data('rif');
            const nombre = $(this).find('h6').text();
            const totalPendiente = $(this).find('.badge').text();

            // Actualizar UI
            $('.cliente-item').removeClass('active');
            $(this).addClass('active');

            $('#cliente-seleccionado').removeClass('d-none');
            $('#nombre-cliente').text(nombre);
            $('#rif-cliente').text(rif);
            $('#total-deuda-cliente').text(totalPendiente);

            // Limpiar selección anterior
            pedidosSeleccionados = [];
            actualizarResumenPago();

            // Mostrar loading
            $('#pedidos-container').addClass('d-none');
            $('#sin-pedidos').addClass('d-none');

            // Mostrar spinner de carga si es necesario (opcional)

            clienteActual = rif;

            // Cargar pedidos pendientes
            cargarPedidosPendientes(rif);
        });

        function cargarPedidosPendientes(rif) {
            $.ajax({
                url: '{{ route('
                vendedores.pagos_new.pedidos ', '
                ') }}/' + rif,
                type: 'GET',
                success: function(response) {
                    if (response.success && response.pedidos.length > 0) {
                        pedidosCliente = response.pedidos;
                        mostrarPedidos(pedidosCliente);
                    } else {
                        $('#pedidos-container').addClass('d-none');
                        $('#sin-pedidos').removeClass('d-none').html(
                            '<i class="fas fa-check-circle fa-4x mb-4 text-success opacity-25"></i><h4 class="fw-bold">Al día</h4><p>El cliente no tiene pedidos pendientes.</p>'
                        );
                    }
                },
                error: function() {
                    Swal.fire('Error', 'No se pudieron cargar los pedidos del cliente', 'error');
                }
            });
        }

        function mostrarPedidos(pedidos) {
            const tbody = $('#tabla-pedidos');
            tbody.empty();

            pedidos.forEach((pedido) => {
                const saldo = pedido.saldo_con_descuento || pedido.saldo_pendiente;
                const tr = $(`
                        <tr data-id="${pedido.id}">
                            <td class="text-center" data-label="Seleccionar">
                                <div class="form-check d-flex justify-content-center">
                                    <input type="checkbox" class="form-check-input check-pedido" 
                                           data-id="${pedido.id}" 
                                           data-monto="${pedido.saldo_pendiente}"
                                           data-iva-bs="${pedido.saldo_iva_bs}">
                                </div>
                            </td>
                            <td data-label="Pedido / Factura">
                                <div class="fw-bold">#${pedido.numero}</div>
                                ${pedido.factura_numero ? `<div class="text-success small"><i class="fas fa-file-invoice me-1"></i>Fact: ${pedido.factura_numero}</div>` : ''}
                                <small class="text-muted"><i class="far fa-calendar-alt me-1"></i>${pedido.fecha}</small>
                            </td>
                            <td class="text-end" data-label="Total ($)">${formatMoney(pedido.total)}</td>
                            <td class="text-end" data-label="Abonado ($)">${formatMoney(pedido.monto_pagado)}</td>
                            <td class="text-end" data-label="Saldo ($)">
                                <span class="fw-bold text-danger">${formatMoney(pedido.saldo_pendiente)}</span>
                            </td>
                            <td class="text-end" data-label="IVA (Bs.)">
                                <span class="fw-bold text-primary">${formatBS(pedido.saldo_iva_bs)}</span>
                            </td>
                        </tr>
                    `);
                tbody.append(tr);
            });

            $('#pedidos-container').removeClass('d-none');
        }

        // Manejar selección de pedidos
        $(document).on('change', '.check-pedido', function() {
            const pedidoId = $(this).data('id');
            const monto = parseFloat($(this).data('monto'));
            const ivaBS = parseFloat($(this).data('iva-bs'));

            if ($(this).is(':checked')) {
                pedidosSeleccionados.push({
                    'id': pedidoId,
                    'monto': monto,
                    'ivaBS': ivaBS
                });
            } else {
                pedidosSeleccionados = pedidosSeleccionados.filter(p => p.id != pedidoId);
            }
            actualizarResumenPago();
        });

        function actualizarResumenPago() {
            const totalUSD = pedidosSeleccionados.reduce((sum, p) => sum + p.monto, 0);
            const totalIVABs = pedidosSeleccionados.reduce((sum, p) => sum + (p.ivaBS || 0), 0);

            if (pedidosSeleccionados.length > 0) {
                $('#resumen-pago').removeClass('d-none').addClass('animate__animated animate__slideInUp');
                $('#total-usd-resumen').text(formatMoney(totalUSD));
                $('#total-iva-bs-resumen').text(formatBS(totalIVABs));
                $('#total-pagar-combinado').text(formatMoney(totalUSD));
                $('#btn-procesar-pago').prop('disabled', false);
                $('#contador-pedidos-badge').text(pedidosSeleccionados.length + (pedidosSeleccionados.length == 1 ? ' pedido seleccionado' : ' pedidos seleccionados'));
            } else {
                $('#resumen-pago').addClass('d-none');
                $('#btn-procesar-pago').prop('disabled', true);
            }
        }

        function formatMoney(amount) {
            return new Intl.NumberFormat('es-VE', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(amount) + ' $';
        }

        function formatBS(amount) {
            return new Intl.NumberFormat('es-VE', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(amount) + ' Bs.';
        }

        $('#btn-procesar-pago').on('click', function() {
            const tasa = $('#tasa-cambio').val();
            if (!tasa || tasa <= 0) {
                Swal.fire('Atención', 'Por favor, ingrese una tasa de cambio válida.', 'warning');
                return;
            }

            const ids = pedidosSeleccionados.map(p => p.id).join(',');
            const url = '{{ route('
            vendedores.pagos_new.metodo ', '
            ') }}/' + clienteActual + '?pedidos=' + ids + '&tasa_cambio=' + tasa;

            window.location.href = url;
        });

        // Pestañas (AJAX para Revisión y Pagados)
        $('#revision-tab').on('click', function() {
            if (!clienteActual) return;

            $('#revision-loading').removeClass('d-none');
            $('#revision-icon').addClass('d-none');
            $('#revision-message').text('Cargando pedidos...');

            $.ajax({
                url: '{{ route('
                vendedores.pagos_new.pedidos.revision ', '
                ') }}/' + clienteActual,
                type: 'GET',
                success: function(response) {
                    $('#revision-loading').addClass('d-none');
                    if (response.success && response.pedidos.length > 0) {
                        mostrarTablaEstatica('#revision', response.pedidos, 'EN REVISIÓN', 'warning');
                    } else {
                        $('#revision-icon').removeClass('d-none');
                        $('#revision-message').text('No tiene pedidos en revisión.');
                    }
                }
            });
        });

        $('#pagados-tab').on('click', function() {
            if (!clienteActual) return;

            $('#pagados-loading').removeClass('d-none');
            $('#pagados-icon').addClass('d-none');
            $('#pagados-message').text('Cargando historial...');

            $.ajax({
                url: '{{ route('
                vendedores.pagos_new.pedidos.aprobadas ', '
                ') }}/' + clienteActual,
                type: 'GET',
                success: function(response) {
                    $('#pagados-loading').addClass('d-none');
                    if (response.success && response.pedidos.length > 0) {
                        mostrarTablaEstatica('#pagados', response.pedidos, 'PAGADO', 'success');
                    } else {
                        $('#pagados-icon').removeClass('d-none');
                        $('#pagados-message').text('No tiene historial de pagos.');
                    }
                }
            });
        });

        function mostrarTablaEstatica(containerId, pedidos, label, labelClass) {
            let html = `
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Pedido</th>
                                    <th>Fecha</th>
                                    <th class="text-end">Monto/Total</th>
                                    <th class="text-center">Estatus</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

            pedidos.forEach(p => {
                html += `
                        <tr>
                            <td><span class="fw-bold">#${p.numero}</span></td>
                            <td>${p.fecha}</td>
                            <td class="text-end">${formatMoney(p.monto_pagado || p.total)}</td>
                            <td class="text-center"><span class="badge bg-${labelClass}">${label}</span></td>
                        </tr>
                    `;
            });

            html += `</tbody></table></div>`;
            $(containerId).html(html);
        }
    });
</script>
@endsection