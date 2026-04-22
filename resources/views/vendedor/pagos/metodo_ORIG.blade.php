@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel') . ' - Método de Pago')
@section('titulo_header', 'Método de Pago')
@section('subtitulo_header', 'Procesar pago para ' . $cliente->NOMBRE)


@section('styles')
    <style>
        .card {
            border-radius: 0.75rem;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Estilos para las opciones de pago */
        .payment-option-card {
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            background-color: #fff;
            border: 1px solid rgba(0, 0, 0, 0.05) !important;
            position: relative;
            overflow: hidden;
        }

        .payment-option-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border-color: transparent !important;
        }

        .form-check-input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .payment-option-card .form-check-input:checked+label {
            border-color: #4361ee !important;
            background-color: rgba(67, 97, 238, 0.03);
        }

        .form-check-label {
            width: 100%;
            display: block;
            margin: 0;
            padding: 0;
        }

        /* Estilos para el círculo de selección */
        .form-check-circle {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            border: 2px solid #dee2e6;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .form-check-circle .checkmark {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background-color: #4361ee;
            opacity: 0;
            transform: scale(0.5);
            transition: all 0.2s ease;
        }

        .payment-option-card .form-check-input:checked+label .form-check-circle {
            border-color: #4361ee;
        }

        .payment-method-option input[type="radio"]:checked+label .form-check-circle .checkmark {
            opacity: 1;
            transform: scale(1);
        }

        .payment-method-option input[type="radio"]:checked+label {
            border-color: #0d6efd;
            background-color: #f8f9fa;
        }

        .payment-method-option input[type="radio"]:checked+label .icon-container {
            background-color: #e7f1ff !important;
        }

        /* Estilos para la tabla */
        .table th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            color: #6c757d;
        }

        .table td {
            vertical-align: middle;
            overflow: hidden;
        }

        /* Estilos para formularios y botones */
        .form-control,
        .form-select,
        .input-group-text {
            border-radius: 0.5rem;
            padding: 0.65rem 1rem;
            font-size: 0.95rem;
            border: 1px solid #e9ecef;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #4361ee;
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.15);
        }

        .btn {
            border-radius: 0.5rem;
            font-weight: 500;
            padding: 0.65rem 1.5rem;
            transition: all 0.2s ease;
        }

        .alert {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
        }

        /* Estilos para el campo de monto en divisa */
        #campo-monto-divisa .card {
            border-left: 3px solid #4361ee !important;
            background-color: #f8fafc;
        }

        #monto_divisa {
            border-left: none;
            font-weight: 500;
            background-color: #fff;
        }

        .input-group-text {
            background-color: #f8fafc;
            border-right: none;
            color: #64748b;
        }

        /* Estilos para los botones de método de pago */
        .payment-method-btn {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            text-align: left;
            padding: 1.25rem 1.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            background: white;
            transition: all 0.2s ease;
            cursor: pointer;
            margin-bottom: 0.75rem;
        }

        .payment-method-btn:hover {
            border-color: #94a3b8;
            transform: translateY(-2px);
        }

        .payment-method-btn.active {
            border-color: #4361ee;
            background-color: #f0f4ff;
            box-shadow: 0 0 0 1px #4361ee;
        }

        .payment-method-btn .icon-container {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 12px;
            margin-right: 1rem;
            flex-shrink: 0;
        }

        .payment-method-btn .icon-container i {
            font-size: 1.5rem;
            transition: transform 0.2s ease;
        }

        .payment-method-btn:hover .icon-container i {
            transform: scale(1.1);
        }

        .payment-method-btn .content {
            flex-grow: 1;
        }

        .payment-method-btn .title {
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: #1e293b;
        }

        .payment-method-btn .description {
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 0;
        }

        /* Estilos para la tabla de pedidos */
        .card-body {
            display: flex;
            flex-direction: column;
        }

        .table-responsive {
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        table {
            margin-bottom: 0 !important;
        }

        tfoot {
            position: sticky;
            bottom: 0;
            background: white;
        }

        /* Mejoras de accesibilidad */
        .form-label {
            font-weight: 500;
            color: #334155;
        }

        .text-muted {
            font-size: 0.8rem;
            color: #64748b !important;
        }
    </style>
@endsection


@section('content')
    <div class="container-fluid py-4">

        <!-- Fila para el contenido principal -->
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="row">
                    <!-- Tabla de Resumen de Pedidos -->
                    <div class="col-lg-6 mb-4">
                        <div class="row justify-content-center mb-4">
                            <div class="col-lg-12">
                                <!-- Tarjeta de Tasa de Cambio -->
                                <div class="card shadow-sm border-0"
                                    style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <h6 class="text-white-50 mb-0">Tasa BCV</h6>
                                                <h2 class="text-white mb-0">
                                                    {{ number_format(request('tasa_cambio', 0), 2, ',', '.') }} Bs.
                                                </h2>
                                            </div>
                                            <div class="bg-white bg-opacity-25 p-3 rounded-circle">
                                                <i class="fas fa-exchange-alt fa-2x text-primary"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Resumen de Pago Parcial (Divisa + Bolívares) -->
                        <div id="resumen-pago-parcial" class="card mt-4 d-none">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-percentage me-2"></i>Resumen de Pago Parcial</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card bg-light mb-3">
                                            <div class="card-header bg-primary text-white">
                                                <h6 class="mb-0">Pago en Divisa (USD)</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span>Monto en Divisa:</span>
                                                    <strong>$ <span id="monto_divisa_total">0.00</span></strong>
                                                </div>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span>Descuento Aplicado:</span>
                                                    <strong><span id="descuento_divisa">0.00%</span></strong>
                                                </div>
                                                <div class="d-flex justify-content-between fw-bold">
                                                    <span>Total a Pagar:</span>
                                                    <strong>$ <span id="total_divisa">0.00</span></strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card bg-light">
                                            <div class="card-header bg-success text-white">
                                                <h6 class="mb-0">Pago en Bolívares (VES)</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span>Monto Restante:</span>
                                                    <strong><span id="monto_restante_bs">0.00</span> Bs.</strong>
                                                </div>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span>Tasa BCV:</span>
                                                    <strong><span id="tasa_bcv_parcial">0.00</span> Bs./$</strong>
                                                </div>
                                                <div class="d-flex justify-content-between fw-bold">
                                                    <span>Total a Pagar:</span>
                                                    <strong><span id="total_bs">0.00</span> Bs.</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card shadow-sm border h-100" style="background-color: #f8f9fa;">
                                    <div class="card-header bg-light py-2 border-bottom">
                                        <h6 class="mb-0 text-muted"><i class="fas fa-shopping-cart me-2"></i>Pedidos
                                            Seleccionados</h6>
                                    </div>
                                    <div class="card-body p-0 d-flex flex-column" style="height: 100%;">
                                        <div class="table-responsive" style="flex: 1 1 auto; overflow-y: auto;">
                                            <table class="table table-sm align-middle mb-0">
                                                <thead class="small position-sticky top-0 bg-white" style="z-index: 1;">
                                                    <tr class="bg-light">
                                                        <th class="text-muted">Pedido</th>
                                                        <th class="text-end pe-3 text-muted">Total ($)</th>
                                                        <th class="text-end pe-3 text-muted">Abonado ($)</th>
                                                        <th class="text-end pe-3 text-muted">Saldo ($)</th>
                                                        <th class="text-end pe-3 text-muted">IVA (Bs.)</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="small">
                                                    @php $detalle_pedidos = []; @endphp
                                                    @php $totalPagarDivisa = 0; @endphp
                                                    @php $totalIvaBs = 0; @endphp
                                                    @php $totalOriginal = 0; @endphp
                                                    @php $totalDescuento = 0; @endphp
                                                    @php $totalRetencion = 0; @endphp
                                                    @php $totalIva = 0; @endphp
                                                    @php $porc_retencion = 0; @endphp
                                                    @foreach ($pedidosSeleccionados as $index => $pedido)
                                                        @php
                                                            // Calcular descuentos y totales por pedido (alineado con pagos_new)
                                                            $descuento = isset($pedido->descuento_aplicado) && $pedido->descuento_aplicado ? $pedido->monto_descuento : 0;
                                                            $totalDescuento += $descuento;
                                                            $saldoConDescuento = $pedido->saldo_pendiente - $descuento;
                                                            $descuento_div = abs($pedido->descuento_pedido) > 0 ? $saldoConDescuento * (abs($pedido->descuento_pedido) / 100) : 0;
                                                            $totalDescuento += $descuento_div;
                                                            $totalPagarDivisa += $saldoConDescuento - $descuento_div;
                                                            $descuentoPedido = $pedido->descuento_pedido ?? 0;
                                                            $monto_con_descuento = $saldoConDescuento;
                                                            $nuevo_saldo = $monto_con_descuento - ($monto_con_descuento * abs($descuentoPedido)) / 100;
                                                            $totalIva += $pedido->iva > 0 && $pedido->factura != 'NO' ? ($nuevo_saldo * 16) / 100 : 0;
                                                            $totalRetencion += $pedido->retencion && $pedido->factura != 'NO' ? $pedido->retencion : 0;
                                                            $porc_retencion = $pedido->porc_retencion && $pedido->factura != 'NO' ? ($totalIva * $pedido->porc_retencion) / 100 : 0;

                                                            $totalIvaBs += $pedido->saldo_iva_bs;
                                                            $totalOriginal += $pedido->total;

                                                            $detalle_pedidos[] = [
                                                                'pedido_id' => $pedido->id,
                                                                'saldo_base' => $pedido->saldo_base,
                                                                'saldo_iva_bs' => $pedido->saldo_iva_bs,
                                                                'total' => $pedido->total,
                                                                'monto_pagado' => $pedido->total - $pedido->saldo_base,
                                                                'iva' => $pedido->iva > 0 && $pedido->factura != 'NO' ? ($nuevo_saldo * 16) / 100 : 0,
                                                                'retencion' => $pedido->retencion && $pedido->factura != 'NO' ? $pedido->retencion : 0,
                                                            ];
                                                        @endphp
                                                        <tr class="border-bottom pedido-fila-new"
                                                            data-pedido-id="{{ $pedido->id }}"
                                                            data-saldo-base="{{ $pedido->saldo_base }}"
                                                            data-saldo-iva-bs="{{ $pedido->saldo_iva_bs }}"
                                                            data-total="{{ $pedido->total }}">
                                                            <td class="py-2">
                                                                <div class="text-dark">#{{ $pedido->id }}</div>
                                                                <small class="text-muted"><i
                                                                        class="far fa-calendar-alt me-1"></i>{{ \Carbon\Carbon::parse($pedido->fecha)->format('d/m/Y') }}</small>
                                                            </td>
                                                            <td class="text-end pe-3">
                                                                {{ number_format($pedido->total, 2, ',', '.') }} $
                                                            </td>
                                                            <td class="text-end pe-3">
                                                                {{ number_format($pedido->total - $pedido->saldo_base, 2, ',', '.') }} $
                                                            </td>
                                                            <td class="text-end pe-3 fw-bold text-danger">
                                                                {{ number_format($pedido->saldo_base, 2, ',', '.') }} $
                                                            </td>
                                                            <td class="text-end pe-3 fw-bold text-primary">
                                                                {{ number_format($pedido->saldo_iva_bs, 2, ',', '.') }} Bs.
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot class="position-sticky bottom-0 bg-white"
                                                    style="box-shadow: 0 -2px 10px rgba(0,0,0,0.05);">
                                                    <input type="hidden" id="total_porc_retencion"
                                                        name="total_porc_retencion" value="{{ $porc_retencion }}">
                                                    <input type="hidden" id="total_retencion" name="total_retencion"
                                                        value="{{ $totalRetencion }}">
                                                    <input type="hidden" id="total_iva" name="total_iva"
                                                        value="{{ $totalIva }}">
                                                    <input type="hidden" id="total_descuento_pago"
                                                        name="total_descuento_pago" value="{{ $totalDescuento }}">
                                                    <tr>
                                                        <td colspan="6" class="p-0">
                                                            <div class="p-3"
                                                                style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 0 0 0.5rem 0.5rem;">
                                                                <div class="text-white w-100">
                                                                    <div class="row align-items-center">
                                                                        <div class="col-12 col-md-4 mb-3 mb-md-0">
                                                                            <h6 class="mb-0 fw-light">TOTAL USD</h6>
                                                                            <div class="d-flex align-items-baseline">
                                                                                <span class="h3 mb-0 fw-bold me-2">{{ number_format($totalPagarDivisa, 2, ',', '.') }}</span>
                                                                                <span class="h5 mb-0">$</span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-12 col-md-4 mb-3 mb-md-0">
                                                                            <h6 class="mb-0 fw-light">TOTAL IVA (Bs.)</h6>
                                                                            <div class="d-flex align-items-baseline">
                                                                                <span class="h3 mb-0 fw-bold me-2 text-info">{{ number_format($totalIvaBs, 2, ',', '.') }}</span>
                                                                                <span class="h5 mb-0 text-info">Bs.</span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-12 col-md-4">
                                                                            <small class="d-block text-white-50">Base Original:</small>
                                                                            <div class="d-flex align-items-center justify-content-start">
                                                                                <span class="text-white-50 fw-bold me-1">{{ number_format($totalOriginal, 2, ',', '.') }}</span>
                                                                                <span class="text-white-50">$</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulario de Pago -->
                    <div class="col-lg-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-primary text-white py-3">
                                <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i> Moneda de Pago</h5>
                            </div>
                            <div class="card-body">
                                @if (session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        {{ session('error') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                    </div>
                                @endif

                                <form action="{{ route('vendedores.pagos.index') }}" method="POST" id="form-pago">
                                    @csrf
                                    <input type="hidden" name="rif" value="{{ $cliente->RIF }}">
                                    <input type="hidden" name="pedidos" value="{{ implode(',', $pedidosIds) }}">
                                    <input type="hidden" name="detalle_pedidos"
                                        value="{{ json_encode($detalle_pedidos) }}">
                                    <input type="hidden" name="total_descuento" value="{{ $totalDescuento ?? 0 }}">
                                    <input type="hidden" id="tasa_cambio_request"
                                        value="{{ request('tasa_cambio', 0) }}">
                                    <input type="hidden" name="cliente_nombre" value="{{ $cliente->NOMBRE ?? '' }}">
                                    <input type="hidden" name="forma_pago_id" id="forma_pago_id"
                                        value="{{ old('forma_pago_id') }}">
                                    <input type="hidden" name="total_pagar"
                                        value="{{ $totalPagar - $totalDescuento }}">
                                     <input type="hidden" name="monto_total_bs" id="monto-total-bs" value="">
                            <div class="mb-1">
                                <div class="row align-items-center">
                                    <div class="col-md-6 bordered-right">
                                        <small class="d-block text-white-50">SALDO BASE ($):</small>
                                        <div class="d-flex align-items-end">
                                            <span class="display-4 fw-bold text-white me-2 lh-1" id="total-dolares2">
                                                {{ number_format($total_pagar, 2, ',', '.') }}</span>
                                            <span class="h4 text-white-50 mb-1"> USD</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="d-block text-white-50">SALDO IVA (BS):</small>
                                        <div class="d-flex align-items-end">
                                            <span class="display-4 fw-bold text-info me-2 lh-1" id="total-bolivares-iva">
                                                {{ number_format($total_iva, 2, ',', '.') }}</span>
                                            <span class="h4 text-info-50 mb-1"> Bs.</span>
                                        </div>
                                    </div>
                                </div>

                                        <div class="mt-3">
                                    <div class="d-flex align-items-center text-white-50 small">
                                        <span class="me-1">Tasa sugerida: Bs. {{ number_format($tasa_bcv, 2, ',', '.') }}</span>
                                        <i class="ml-1 mr-1 fas fa-exchange-alt me-1"></i>
                                        @php
                                        $total_bs_equiv = (float)($total_pagar ?? 0) * (float)($tasa_bcv ?? 1);
                                        @endphp
                                        <span>Equiv. Base: Bs. {{ number_format($total_bs_equiv, 2, ',', '.') }}</span>
                                        <span class="mx-2">|</span>
                                        <span class="fw-bold text-white">TOTAL ESTIMADO: Bs. {{ number_format($total_bs_equiv + $total_iva, 2, ',', '.') }}</span>
                                    </div>
                                </div>
                                        </div>
                                    <!-- Moneda de Pago -->
                                    <div class="mb-4">
                                        <div class="d-flex flex-column gap-2">
                                            <!-- Opción Divisa Total -->
                                            <div class="payment-method-option">
                                                <input type="radio" name="tipo_pago" id="pago-total"
                                                    value="divisa_total" class="d-none" checked>
                                                <label for="pago-total" class="payment-method-btn">
                                                    <div class="icon-container bg-soft-primary">
                                                        <i class="fas fa-dollar-sign text-primary"></i>
                                                    </div>
                                                    <div class="content">
                                                        <div class="title">PAGO EN DIVISA TOTAL</div>
                                                        <div class="description text-muted">Pago completo en dólares con
                                                            descuentos aplicables</div>
                                                    </div>
                                                    <div class="form-check-circle">
                                                        <div class="checkmark"></div>
                                                    </div>
                                                </label>
                                            </div>

                                            <!-- Opción Divisa Parcial -->
                                            <div class="payment-method-option">
                                                <input type="radio" name="tipo_pago" id="pago-parcial"
                                                    value="divisa_parcial" class="d-none">
                                                <label for="pago-parcial" class="payment-method-btn">
                                                    <div class="icon-container" style="background-color: #f0f9ff;">
                                                        <i class="fas fa-percentage"
                                                            style="color: #0ea5e9; font-size: 1.25rem;"></i>
                                                    </div>
                                                    <div class="content">
                                                        <div class="title">DIVISA PARCIAL</div>
                                                        <p class="description text-muted">Pago parcial en dólares</p>
                                                    </div>
                                                    <div class="form-check-circle">
                                                        <div class="checkmark"></div>
                                                    </div>
                                                </label>
                                            </div>

                                            <!-- Opción Bolívares -->
                                            <div class="payment-method-option">
                                                <input type="radio" name="tipo_pago" id="pago-bs" value="bs"
                                                    class="d-none">
                                                <label for="pago-bs" class="payment-method-btn">
                                                    <div class="icon-container" style="background-color: #fef9c3;">
                                                        <i class="fas fa-money-bill-wave"
                                                            style="color: #eab308; font-size: 1.25rem;"></i>
                                                    </div>
                                                    <div class="content">
                                                        <div class="title">Bolívares (Bs.)</div>
                                                        <p class="description text-muted">Pago total en moneda local</p>
                                                    </div>
                                                    <div class="form-check-circle">
                                                        <div class="checkmark"></div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Campo para monto en divisa (se muestra con Divisa Parcial) -->
                                    <div id="campo-monto-divisa" class="mt-3 d-none">
                                        <div class="card border-0 bg-light">
                                            <div class="card-body p-3">
                                                <label for="monto_divisa" class="form-label fw-medium">Monto en
                                                    Divisa</label>
                                                <div class="input-group input-group-lg">
                                                    <span class="input-group-text bg-white">$</span>
                                                    <input type="number" class="form-control form-control-lg"
                                                        id="monto_divisa" name="monto_divisa" min="1"
                                                        step="1" max="{{ floor($totalPagar) }}"
                                                        value="{{ floor($totalPagar) }}" style="font-weight: 500;">
                                                </div>
                                                <small class="text-muted">Monto máximo: {{ floor($totalPagar) }} $ (solo
                                                    números enteros)</small>
                                            </div>
                                        </div>

                                        <!-- Resumen de Pago en Bolívares (se muestra con Divisa Parcial) -->
                                        <div id="resumen-divisa-parcial" class="card border-0 shadow-sm mt-3 d-none">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0">Resumen de Pago en Bolívares</h6>
                                            </div>
                                            <div class="card-body p-0">
                                                <table class="table table-borderless mb-0">
                                                    <tbody>
                                                        <tr>
                                                            <td class="text-muted border-0 ps-3">Monto en Bolívares:</td>
                                                            <td class="text-end border-0 pe-3 fw-medium"
                                                                id="monto-bolivares-parcial">0,00 Bs.</td>
                                                            <input type="hidden" name="monto_bolivares_parcial"
                                                                id="monto-bolivares-parcial-input" value="0">
                                                        </tr>
                                                        <tr>
                                                            <td class="text-muted border-0 ps-3">IVA (16%):</td>
                                                            <td class="text-end border-0 pe-3 fw-medium"
                                                                id="iva-bolivares-parcial">0,00 Bs.</td>
                                                            <input type="hidden" name="iva_bolivares_parcial"
                                                                id="iva-bolivares-parcial-input" value="0">
                                                        </tr>
                                                        <tr>
                                                            <td class="text-muted border-0 ps-3">Retención (75% IVA):</td>
                                                            <td class="text-end border-0 pe-3 fw-medium text-danger"
                                                                id="retencion-bolivares-parcial">-0,00 Bs.</td>
                                                            <input type="hidden" name="retencion_bolivares_parcial"
                                                                id="retencion-bolivares-parcial-input" value="0">
                                                        </tr>
                                                        <tr>
                                                            <td class="text-muted border-0 ps-3">Descuento:</td>
                                                            <td class="text-end border-0 pe-3 fw-medium text-success"
                                                                id="descuento-bolivares-parcial">-0,00 Bs.</td>
                                                            <input type="hidden" name="descuento_bolivares_parcial"
                                                                id="descuento-bolivares-parcial-input" value="0">
                                                        </tr>
                                                        <tr class="border-top">
                                                            <td class="text-dark fw-bold border-0 ps-3">TOTAL A PAGAR:</td>
                                                            <td class="text-end border-0 pe-3 fw-bold text-primary"
                                                                id="total-bolivares-parcial">0,00 Bs.</td>
                                                            <input type="hidden" name="total_bolivares_parcial"
                                                                id="total-bolivares-parcial-input" value="0">
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Campo para tasa BCV (se muestra con Bolívares) -->
                                    <div id="campo-tasa-bcv" class="mt-3 d-none">
                                        <div class="card border-0 bg-light">
                                            <div class="card-body p-3">
                                                <div class="mb-3">
                                                    <label for="tasa_bcv" class="form-label fw-medium">Tasa BCV (Bs. por
                                                        $)</label>
                                                    <input type="number" class="form-control form-control-lg" readonly
                                                        id="tasa_bcv" name="tasa_bcv" min="0.0001" step="0.0001"
                                                        value="{{ request('tasa_cambio', 0) }}"
                                                        style="font-weight: 500;">
                                                </div>
                                                <!-- Sección Informativa de Totales -->
                                                <div class="row mb-3">
                                                    <div class="col-12">
                                                        <div id="summary-container" class="card shadow-sm border-0 mb-3 "
                                                            style="background: #1e293b;">
                                                            <div class="card-body py-3 px-4">
                                                                <!-- Totales: 2 columnas por fila en móvil, horizontal en desktop -->
                                                                <div class="row text-center align-items-end text-white">
                                                                    <div class="col-6 col-md-3 mb-2 mb-md-0">
                                                                        <div class="fw-semibold small">Subtotal</div>
                                                                        <div class="fw-bold small">
                                                                            <span
                                                                                id="subtotal_dolares">{{ number_format($totalPagar - ($totalIva ?? 0), 2, ',', '.') }}
                                                                                $</span>
                                                                            <span id="subtotal_bolivares2"
                                                                                class="d-none">{{ number_format(($totalPagar - ($totalIva ?? 0)) * 1, 2, ',', '.') }}
                                                                                Bs.</span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-6 col-md-3 mb-2 mb-md-0">
                                                                        <div class="fw-semibold small">Exento</div>
                                                                        <div class="fw-bold small" id="exento_bolivares">
                                                                            {{ number_format($totalesBs['exento'] ?? 0, 2, ',', '.') }}
                                                                            Bs.
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-6 col-md-3 mb-2 mb-md-0">
                                                                        <div class="fw-semibold small">Base</div>
                                                                        <div class="fw-bold small">
                                                                            <span
                                                                                id="base_dolares">{{ number_format($totalPagar - ($totalIva ?? 0), 2, ',', '.') }}
                                                                                $</span>
                                                                            <span id="base_bolivares"
                                                                                class="d-none">{{ number_format(($totalPagar - ($totalIva ?? 0)) * 1, 2, ',', '.') }}
                                                                                Bs.</span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-6 col-md-3 mb-2 mb-md-0">
                                                                        <div class="fw-semibold small">Impuesto (16%)</div>
                                                                        <div class="fw-bold small">
                                                                            <span
                                                                                id="impuesto_dolares">{{ number_format($totalIva ?? 0, 2, ',', '.') }}
                                                                                $</span>
                                                                            <span id="impuesto_bolivares"
                                                                                class="d-none">0,00 Bs.</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row mt-2">
                                                                    <div class="col-12 text-right text-white">
                                                                        <div class="fw-semibold small">- Retención (75%)
                                                                        </div>
                                                                        <div class="fw-bold small"
                                                                            id="retencion_bolivares">
                                                                            {{ number_format($totalesBs['retencion'] ?? 0, 2, ',', '.') }}
                                                                            Bs.
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row mt-2">
                                                                    <!-- Segunda fila: TOTAL (más llamativo, monto en Bs. alineado a la derecha y resaltado) -->
                                                                    <div class="col-12">
                                                                        <div
                                                                            class="d-flex justify-content-end align-items-center">
                                                                            <div class="text-end w-100">
                                                                                <div
                                                                                    class="fw-semibold small text-white-50 text-right mr-3">
                                                                                    TOTAL</div>
                                                                                <div class="display-6 fw-bold text-right"
                                                                                    style="color: #38bdf8; letter-spacing: 1px; background: rgba(56,189,248,0.12); border-radius: 0.5rem; padding: 0.25rem 1rem;"
                                                                                    id="total_bolivares2">
                                                                                    {{ number_format($totalesBs['total'] ?? 0, 2, ',', '.') }}
                                                                                    Bs.
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="orders-table" class="table-responsive d-none">
                                                    <table class="table table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th>Pedido</th>
                                                                <th class="text-end">Monto ($)</th>
                                                                <th class="text-end">Monto (Bs.)</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($pedidosSeleccionados as $pedido)
                                                                @php
                                                                    // Usar saldo_pendiente en lugar de saldo
                                                                    $saldoOriginal = $pedido->saldo_pendiente;
                                                                    $descuento = $pedido->descuento_aplicado
                                                                        ? $pedido->porcentaje_descuento
                                                                        : 0;
                                                                    $saldoConDescuento =
                                                                        $descuento > 0
                                                                            ? $pedido->saldo_con_descuento
                                                                            : $saldoOriginal;
                                                                    $ivacond =
                                                                        $pedido->factura == 'NO'
                                                                            ? 0
                                                                            : $pedido->porcentaje_iva;
                                                                @endphp
                                                                <tr class="pedido-fila"
                                                                    data-monto-original="{{ $saldoOriginal }}"
                                                                    data-descuento="{{ $descuento }}"
                                                                    data-porcentaje-iva="{{ $ivacond ?? 16 }}"
                                                                    data-retencion="{{ $pedido->retencion ?? 0 }}">
                                                                    <td>
                                                                        <div class="form-check">
                                                                            <input class="form-check-input pedido-checkbox"
                                                                                type="checkbox"
                                                                                value="{{ $pedido->id }}"
                                                                                id="pedido_{{ $pedido->id }}"
                                                                                data-monto-original="{{ $saldoOriginal }}"
                                                                                data-descuento="{{ $descuento }}"
                                                                                data-monto="{{ $saldoConDescuento }}"
                                                                                data-monto-descuento="{{ $saldoOriginal - $saldoConDescuento }}"
                                                                                checked>
                                                                            <label class="form-check-label"
                                                                                for="pedido_{{ $pedido->id }}">
                                                                                #{{ $pedido->id }}
                                                                            </label>
                                                                        </div>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <div class="text-end pe-3">
                                                                            $<span
                                                                                class="monto-pedido">{{ number_format($saldoConDescuento, 2, ',', '.') }}</span>
                                                                        </div>
                                                                    </td>
                                                                    <td class="text-end monto-bolivares"
                                                                        data-monto-dolares="{{ $saldoConDescuento }}">0,00
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                            <tr class="table-active fw-bold">
                                                                <td>Total</td>
                                                                <td class="text-end">
                                                                    $<span id="total-dolares">
                                                                        {{ number_format(
                                                                            $pedidosSeleccionados->sum(function ($pedido) {
                                                                                $descuento = isset($pedido->descuento_aplicado) && $pedido->descuento_aplicado ? $pedido->monto_descuento : 0;
                                                                                return $pedido->saldo_pendiente - $descuento;
                                                                            }),
                                                                            2,
                                                                            ',',
                                                                            '.',
                                                                        ) }}
                                                                    </span>
                                                                </td>
                                                                <td class="text-end">Bs. <span
                                                                        id="total-bolivares">0,00</span></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                            </div>

                            <!-- Campos para Transferencia Bancaria -->
                            <div id="campos-transferencia" class="d-none">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="banco_id" class="form-label">Banco</label>
                                        <select class="form-select" id="banco_id" name="banco_id">
                                            <option value="">Seleccione un banco</option>
                                            @foreach ($bancos as $banco)
                                                <option value="{{ $banco->id }}">{{ $banco->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="referencia" class="form-label">Número de Referencia</label>
                                        <input type="text" class="form-control" id="referencia" name="referencia"
                                            placeholder="Ej: 1234567890">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="fecha" class="form-label">Fecha de la Transacción</label>
                                        <input type="text" class="form-control datepicker" id="fecha"
                                            name="fecha" placeholder="dd/mm/aaaa" autocomplete="off">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="comprobante" class="form-label">Comprobante de Pago</label>
                                        <input type="file" class="form-control" id="comprobante" name="comprobante"
                                            accept="image/*,.pdf">
                                        <small class="text-muted">Formatos aceptados: JPG, PNG, PDF (Máx. 5MB)</small>
                                    </div>
                                </div>
                                <input type="hidden" name="monto"
                                    value="{{ number_format($totalPagar, 2, '.', '') }}">

                                <!-- Tabla de Resumen para Divisa Parcial -->
                            </div>

                            <!-- Botones de acción -->
                            <div class="d-flex justify-content-between align-items-center mt-5 mb-4 ml-3">
                                <a href="{{ route('vendedores.pagos.clientes') }}"
                                    class="btn btn-outline-secondary px-4 py-2"
                                    style="min-width: 150px; margin-right: 1.5rem;">
                                    <i class="fas fa-arrow-left me-2"></i> Volver
                                </a>
                                <button type="submit" class="btn btn-primary px-4 py-2 fw-bold mr-3" id="btn-pagar"
                                    style="min-width: 200px;">
                                    <i class="fas fa-credit-card me-2"></i> Registrar Pago
                                </button>
                            </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection

    @section('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            // Función para formatear números como moneda
            function formatoMoneda(valor) {
                return valor.toLocaleString('es-VE', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            // Función para calcular el pago en Divisa Parcial
            function calcularDivisaParcial() {
                if ($('input[name="tipo_pago"]:checked').val() !== 'divisa_parcial') return;

                const montoDivisa = parseFloat($('#monto_divisa').val()) || 0;
                const tasaBcv = parseFloat($('#tasa_bcv').val()) || 0;
                const totalPagarUSD = parseFloat('{{ $totalPagarDivisa }}') || 0;
                const totalIvaBs = parseFloat('{{ $totalIvaBs }}') || 0;

                // Validar que el monto en divisa no sea mayor al total a pagar en USD
                if (montoDivisa > totalPagarUSD) {
                    $('#monto_divisa').val(totalPagarUSD.toFixed(2));
                    return;
                }

                // Calcular monto restante en USD que se pagará en Bolívares
                const montoRestanteUSD = totalPagarUSD - montoDivisa;
                const montoBolivaresBase = montoRestanteUSD * tasaBcv;

                // El Total en Bolívares es el monto base convertido + el IVA ya en BS
                const totalBolivares = montoBolivaresBase + totalIvaBs;

                // Actualizar la interfaz
                $('#monto-bolivares-parcial').text(formatoMoneda(montoBolivaresBase) + ' Bs.');
                $('#iva-bolivares-parcial').text(formatoMoneda(totalIvaBs) + ' Bs.');
                $('#total-bolivares-parcial').text(formatoMoneda(totalBolivares) + ' Bs.');

                // Actualizar campos ocultos
                $('#monto-bolivares-parcial-input').val(montoBolivaresBase.toFixed(2));
                $('#total-bolivares-parcial-input').val(totalBolivares.toFixed(2));

                // Mostrar el resumen
                $('#resumen-divisa-parcial').removeClass('d-none');
            }

            $(document).ready(function() {
                // Mostrar/ocultar campos según el método de pago
                $('input[name="tipo_pago"]').change(function() {
                    const tipoPago = $(this).val();

                    // Ocultar todos los campos primero
                    $('#campo-tasa-bcv, #campo-monto-divisa, #resumen-divisa-parcial').addClass('d-none');

                    if (tipoPago === 'divisa_parcial') {
                        $('#campo-monto-divisa, #resumen-divisa-parcial').removeClass('d-none');
                        calcularDivisaParcial();
                    } else if (tipoPago === 'bs') {
                        $('#campo-tasa-bcv').removeClass('d-none');
                    }
                });

                // Calcular pago parcial cuando cambia el monto en divisa o la tasa
                $(document).on('input', '#monto_divisa, #tasa_bcv', function() {
                    if ($('input[name="tipo_pago"]:checked').val() === 'divisa_parcial') {
                        calcularDivisaParcial();
                    }
                });

                // Inicializar el método de pago seleccionado
                const tipoPagoInicial = $('input[name="tipo_pago"]:checked').val();
                if (tipoPagoInicial === 'divisa_parcial') {
                    $('#campo-monto-divisa, #resumen-divisa-parcial').removeClass('d-none');
                    calcularDivisaParcial();
                } else if (tipoPagoInicial === 'bs') {
                    $('#campo-tasa-bcv').removeClass('d-none');
                }

                // Inicializar el datepicker y otros elementos
                initDatepicker();
                initPaymentCards();

                // Inicializar montos y descuentos
                inicializarMontosYDescuentos();

                // Configurar eventos
                //setupEventListeners();
                initPaymentCards();

                // Ocultar campos que no se usan por defecto
                $('#campos-transferencia').addClass('d-none');
                $('#campo-tasa-bcv').addClass('d-none');
                $('.campo-monto-divisa').addClass('d-none');

                // Mostrar campos según el método de pago seleccionado inicialmente
                const tipoPago = $('input[name="tipo_pago"]:checked').val();
                if (tipoPago === 'bs') {
                    $('#campo-tasa-bcv').removeClass('d-none');
                    calcularConversionBolivares();
                } else if (tipoPago === 'divisa_parcial') {
                    $('.campo-monto-divisa').removeClass('d-none');
                    actualizarTotales();
                }
            });

            // Definir todas las funciones en el ámbito global
            function inicializarMontosYDescuentos() {
                $('.pedido-fila-new').each(function() {
                    const $fila = $(this);
                    const descuento = parseFloat($fila.data('descuento-original') || $fila.data('descuento') || 0) || 0;
                    const montoOriginal = parseFloat($fila.data('monto-original') || 0) || 0;
                    const montoConDescuento = descuento > 0 ? montoOriginal * (1 - descuento / 100) : montoOriginal;
                    // Guardar el monto calculado como dato para uso posterior
                    $fila.data('monto', montoConDescuento);

                    // Si existe el elemento visual de descuento, actualizarlo de forma segura
                    const $badgeDescuento = $fila.find('.descuento-pedido');
                    if ($badgeDescuento && $badgeDescuento.length) {
                        $badgeDescuento.text(descuento.toFixed(2) + '%');
                    }
                });
            }

            // Función para mostrar/ocultar campos según el método de pago
            function togglePaymentFields() {
                const tipoPago = $('input[name="tipo_pago"]:checked').val();
                const esDivisaTotal = tipoPago === 'divisa_total';
                const esBolivares = tipoPago === 'bs';
                const esDivisaParcial = tipoPago === 'divisa_parcial';

                // If Bolivares is selected, set the exchange rate from the request and show calculations
                if (esBolivares) {
                    const tasaCambio = parseFloat($('#tasa_cambio_request').val().replace(',', '.')) || 0;
                    if (tasaCambio > 0) {
                        $('#tasa_bcv').val(tasaCambio);
                        // Cedano Bs
                        // Trigger calculation
                        calcularConversionBolivares();
                        // Show the summary and table
                        $('#summary-container').removeClass('d-none');
                        //$('#orders-table').removeClass('d-none');
                    }
                }

                // Ocultar todos los campos primero
                $('#campo-tasa-bcv').addClass('d-none');
                $('#campos-transferencia').addClass('d-none');
                $('.campo-monto-divisa').addClass('d-none');

                // Mostrar resumen y tabla por defecto (se ocultarán si es necesario según el tipo de pago)
                $('#summary-container').removeClass('d-none');
                //$('#summary-container, #orders-table').removeClass('d-none');

                // Mostrar campos según el tipo de pago
                if (esDivisaTotal) {
                    // Restaurar valores originales de descuentos
                    $('.pedido-fila').each(function() {
                        const $fila = $(this);
                        const montoOriginal = parseFloat($fila.data('monto-original'));
                        const descuento = parseFloat($fila.data('descuento-original')) || 0;
                        const montoConDescuento = montoOriginal * (1 - (descuento / 100));

                        // Actualizar visualmente el descuento
                        $fila.find('.descuento-pedido').text(descuento.toFixed(2) + '%');
                        $fila.find('.monto-pedido').text(montoConDescuento.toFixed(2).replace(/\./g, ','));

                        // Actualizar datos
                        $fila.data('monto', montoConDescuento);
                        $fila.data('descuento', descuento);
                    });

                    // Actualizar totales
                    actualizarTotales();

                } else if (esBolivares) {
                    // Mostrar campo de tasa BCV
                    $('#campo-tasa-bcv').removeClass('d-none');

                    // Establecer descuentos en cero
                    $('.pedido-fila').each(function() {
                        const $fila = $(this);
                        const montoOriginal = parseFloat($fila.data('monto-original'));

                        // Actualizar visualmente el descuento a cero
                        $fila.find('.descuento-pedido').text('0.00%');
                        $fila.data('descuento', 0);
                        $fila.data('monto', montoOriginal);
                    });

                    calcularConversionBolivares();

                } else if (esDivisaParcial) {
                    // Mostrar campo de monto en divisa y tasa BCV
                    $('.campo-monto-divisa, #campo-tasa-bcv').removeClass('d-none');

                    // Mostrar resumen de pago parcial
                    //$('#resumen-pago-parcial').removeClass('d-none');

                    // Aplicar descuentos como en Divisa Total
                    $('.pedido-fila').each(function() {
                        const $fila = $(this);
                        const montoOriginal = parseFloat($fila.data('monto-original'));
                        const descuento = parseFloat($fila.data('descuento-original')) || 0;
                        const montoConDescuento = montoOriginal * (1 - (descuento / 100));

                        // Actualizar visualmente el descuento
                        $fila.find('.descuento-pedido').text(descuento.toFixed(2) + '%');
                        $fila.find('.monto-pedido').text(montoConDescuento.toFixed(2).replace(/\./g, ','));

                        // Actualizar datos
                        $fila.data('monto', montoConDescuento);
                        $fila.data('descuento', descuento);
                    });

                    // Calcular montos para divisa parcial
                    calcularDivisaParcial();

                    // Calcular monto restante para divisa parcial
                    let totalSeleccionado = 0;
                    $('.pedido-fila input[type="checkbox"]:checked').each(function() {
                        const monto = parseFloat($(this).data('monto-original') || 0);
                        totalSeleccionado += monto;
                    });

                    const montoDivisa = parseFloat($('#monto_divisa').val()) || 0;
                    const montoRestante = totalSeleccionado - montoDivisa;

                    if (montoRestante > 0) {
                        $('#monto_restante').text(montoRestante.toFixed(2).replace(/\./g, ','));
                        $('#monto_restante_group').removeClass('d-none');
                    } else {
                        $('#monto_restante_group').addClass('d-none');
                    }
                }

                // Ocultar todos los campos primero
                $('#campo-monto-divisa, #campo-tasa-bcv').addClass('d-none');

                // Mostrar el campo correspondiente
                if (tipoPago === 'divisa_parcial') {
                    $('#campo-monto-divisa').removeClass('d-none').addClass('animate__animated animate__fadeIn');
                    $('#monto_divisa').prop('required', true);
                    restaurarMontosOriginales();
                } else if (tipoPago === 'bs') {
                    $('#campo-tasa-bcv').removeClass('d-none').addClass('animate__animated animate__fadeIn');
                    if ($('#tasa_bcv').length === 0) {
                        console.warn('El campo tasa_bcv no existe en el DOM.');
                        return;
                    }
                    // Ocultar resumen y tabla al seleccionar BS
                    $('#summary-container, #orders-table').addClass('d-none');
                    $('#tasa_bcv').trigger('input'); // Actualizar conversión
                    restaurarMontosOriginales();
                } else if (tipoPago === 'divisa_total') {
                    // Aplicar descuento para Divisa Total
                    console.log('Aplicando descuento Divisa Total');
                    aplicarDescuentoDivisaTotal();
                }
            }



            // Obtener el descuento de un pedido
            function obtenerDescuentoPedido($elemento) {
                // Buscar el descuento en este orden: data-porcentaje-descuento > data-descuento > 0
                const $fila = $elemento.closest('.pedido-fila');
                const porcentajeDescuento = parseFloat($fila.data('porcentaje-descuento') || $fila.data('descuento') || 0);
                return Math.max(0, Math.min(100, porcentajeDescuento)); // Asegurar que esté entre 0 y 100
            }

            // Obtener el monto original de un pedido
            function obtenerMontoOriginal($elemento) {
                const $fila = $elemento.closest('.pedido-fila');
                return parseFloat($fila.data('monto-original') || $fila.find('.monto-original').data('monto') || 0);
            }

            // Formatear moneda
            function formatearMoneda(monto) {
                return '$' + parseFloat(monto).toFixed(2).replace(/\./g, ',');
            }

            // Aplicar descuento para pago en Divisa Total
            function aplicarDescuentoDivisaTotal() {
                console.log('=== INICIANDO APLICACIÓN DE DESCUENTO DIVISA TOTAL ===');
                let totalDescuento = 0;
                let totalMontoOriginal = 0;
                let totalConDescuento = 0;
                const pedidos = [];

                // Recorrer cada fila de pedido para aplicar el descuento individual
                $('.pedido-fila-new').each(function() {
                    const $fila = $(this);
                    const $checkbox = $fila.find('input.pedido-checkbox');
                    const $montoElement = $fila.find('.monto-pedido');

                    // Obtener el monto original del pedido
                    const montoOriginal = parseFloat($fila.data('monto-original')) || 0;

                    // Obtener el descuento existente (badge)
                    const descuentoExistente = parseFloat($fila.data('porcentaje-descuento') || 0);

                    // Obtener el descuento adicional de pedidos.descuento
                    // El valor viene en negativo desde la base de datos, lo convertimos a positivo
                    let descuentoAdicional = 0;
                    const descuentoPedido = parseFloat($fila.data('descuento-pedido') || 0);
                    const monto_con_descuento = parseFloat($fila.data('monto-con-descuento') || 0);
                    //const nuevo_saldo = monto_con_descuento - (monto_con_descuento*descuentoPedido / 100);
                    const nuevo_saldo = monto_con_descuento - (monto_con_descuento * Math.abs(descuentoPedido) / 100);

                    // REVISAR SI SE SIGUE UTILIZANDO ESTO
                    if (descuentoPedido < 0) {
                        descuentoAdicional = Math.abs(descuentoPedido);
                    }

                    // Calcular el monto con el descuento existente
                    const montoConDescuentoExistente = montoOriginal * (1 - (descuentoExistente / 100));

                    // Aplicar el descuento adicional de pedidos.descuento solo si existe
                    let montoConDescuento = montoConDescuentoExistente;

                    // CEDANO 2
                    //let montoDescuento = montoOriginal - montoConDescuentoExistente;
                    let montoDescuento = montoOriginal - montoConDescuentoExistente;

                    if (descuentoAdicional > 0) {
                        const montoDescuentoAdicional = montoConDescuentoExistente * (descuentoAdicional / 100);
                        montoConDescuento = montoConDescuentoExistente - montoDescuentoAdicional;
                        montoDescuento = montoOriginal - montoConDescuento;
                    }

                    // FIN REVISAR SI SE SIGUE UTILIZANDO ESTO

                    console.log('Pedido ID:', $fila.data('pedido-id'),
                        'Monto original:', montoOriginal,
                        'Descuento existente:', descuentoExistente + '%',
                        'Descuento adicional:', descuentoAdicional + '%',
                        'Monto con descuento existente:', montoConDescuentoExistente,
                        'Monto con descuento adicional:', montoConDescuento,
                        'Monto descuento total:', montoDescuento);

                    // Actualizar la fila en la tabla
                    // CEDANO 3
                    /*
                    $montoElement
                        .text(formatearMoneda(montoDescuento))
                        .data('monto', montoConDescuento);
                    */

                    $montoElement
                        .text(formatearMoneda(nuevo_saldo))
                        .data('monto', nuevo_saldo);

                    // Actualizar los datos en la fila
                    $fila.data({
                        'monto': montoConDescuento,
                        'monto-original': montoOriginal,
                        'descuento-aplicado': descuentoExistente,
                        'descuento-extra': descuentoAdicional
                    });

                    // CEDANO

                    // Actualizar los badges de descuento
                    // Badge para el descuento existente
                    const badgeHtml = descuentoExistente > 0 ?
                        `<span class="badge bg-success">${descuentoExistente}%</span><br><small>-$${(montoOriginal * (descuentoExistente / 100)).toFixed(2)}</small>` :
                        '<span class="badge bg-secondary">0%</span>';

                    // Badge para el descuento adicional
                    const badgeAdicionalHtml = descuentoAdicional > 0 ?
                        `<span class="badge bg-warning">${descuentoAdicional}%</span><br><small>-$${(montoOriginal * (descuentoAdicional / 100)).toFixed(2)}</small>` :
                        '<span class="badge bg-secondary">0%</span>';

                    // Actualizar los badges en la interfaz
                    $fila.find('.descuento-aplicado').html(badgeHtml);
                    $fila.find('.descuento-adicional').html(badgeAdicionalHtml);

                    // Actualizar datos en el DOM para cálculos posteriores
                    $fila.data({
                        'monto': montoConDescuento,
                        'monto-descuento': montoDescuento,
                        'monto-original': montoOriginal,
                        'descuento-aplicado': descuentoPedido
                    });

                    $checkbox.data({
                        'monto': montoConDescuento,
                        'monto-descuento': montoDescuento,
                        'monto-original': montoOriginal,
                        'descuento-aplicado': descuentoPedido
                    });

                    // Acumular totales
                    totalDescuento += montoDescuento;
                    totalMontoOriginal += montoOriginal;
                    totalConDescuento += montoConDescuento;

                    // Agregar a la lista de pedidos para el resumen
                    pedidos.push({
                        id: $fila.data('pedido-id'),
                        montoOriginal: montoOriginal,
                        montoDescuento: montoDescuento,
                        montoConDescuento: montoConDescuento,
                        descuentoAplicado: descuentoPedido
                    });
                });

                // Actualizar totales en la interfaz
                const totalFormateado = formatearMoneda(totalConDescuento);

                console.log('Total original:', totalMontoOriginal,
                    'Total descuento:', totalDescuento,
                    'Total con descuento:', totalConDescuento);

                $('#total-pagar').text('$' + totalFormateado);
                $('#monto-total').val(totalConDescuento.toFixed(2));
                $('#total-dolares').text(totalConDescuento.toFixed(2).replace(/\./g, ','));

                // Calcular el porcentaje de descuento total
                const porcentajeDescuentoTotal = totalMontoOriginal > 0 ?
                    (totalDescuento / totalMontoOriginal * 100).toFixed(2) :
                    '0.00';

                $('#total-descuento').text(porcentajeDescuentoTotal + '%');
                $('input[name="total_descuento"]').val(porcentajeDescuentoTotal);

                // Actualizar el máximo en el campo de monto en divisa
                $('#monto_divisa').attr('max', Math.floor(totalConDescuento));

                // Si está en modo bolívares, actualizar la conversión
                if ($('#pago-bs').is(':checked')) {
                    calcularConversionBolivares();
                }

                console.log('=== FIN DE APLICACIÓN DE DESCUENTO ===');
            }

            // Restaurar montos originales
            function restaurarMontosOriginales() {
                console.log('Restaurando montos originales...');
                $('.pedido-fila').each(function() {
                    const $fila = $(this);
                    const montoOriginal = parseFloat($fila.data('monto-original'));

                    // Restaurar monto original en la interfaz
                    $fila.find('.monto-pedido')
                        .text(formatearMoneda(montoOriginal))
                        .data('monto', montoOriginal);

                    // Restablecer el badge de descuento
                    $fila.find('.descuento-aplicado').html('<span class="badge bg-secondary">0%</span>');

                    // Actualizar datos en el DOM
                    $fila.data({
                        'monto': montoOriginal,
                        'monto-descuento': 0
                    });

                    // Actualizar el checkbox también
                    const $checkbox = $fila.find('input.pedido-checkbox');
                    const descuento = parseFloat($fila.data('porcentaje-descuento') || 0);

                    // Restaurar monto original
                    $checkbox.data('monto', montoOriginal);
                    $checkbox.data('monto-descuento', 0);

                    // Actualizar UI
                    $fila.find('.monto-pedido').text(formatearMoneda(montoOriginal));
                    $fila.find('.descuento-aplicado').html(descuento > 0 ?
                        `<span class="badge bg-secondary">${descuento.toFixed(2)}%</span>` :
                        '<span class="badge bg-secondary">0%</span>');
                });

                // Restaurar total de descuento
                $('#total-descuento').text('0,00%');

                actualizarTotal();

                // Si está en modo bolívares, actualizar la conversión
                if ($('#pago-bs').is(':checked')) {
                    calcularConversionBolivares();
                }
            }

            // Actualizar totales
            function actualizarTotal() {
                const tipoPago = $('input[name="tipo_pago"]:checked').val();
                const esDivisaTotal = tipoPago === 'divisa_total';
                let totalDolares = 0;
                let totalDescuento = 0;
                let totalOriginal = 0;

                $('.pedido-fila').each(function() {
                    const $fila = $(this);
                    const $checkbox = $fila.find('input.pedido-checkbox');
                    const montoOriginal = parseFloat($fila.data('monto-original')) || 0;
                    let montoConDescuento = montoOriginal;
                    let descuentoAplicado = 0;
                    let ivaOriginal = parseFloat($fila.attr('data-iva')) || 0;
                    let ivaNuevo = ivaOriginal;

                    if ($checkbox.is(':checked')) {
                        // Aplicar descuento solo si es Divisa Total
                        if (esDivisaTotal) {
                            const porcentajeDescuento = parseFloat($fila.find('.descuento-aplicado .badge').first()
                                .text()) || 0;
                            if (porcentajeDescuento > 0) {
                                descuentoAplicado = (montoOriginal * porcentajeDescuento) / 100;
                                montoConDescuento = montoOriginal - descuentoAplicado;
                            }
                            // Aplicar descuento adicional si existe
                            const descuentoAdicional = parseFloat($fila.data('descuento-pedido') || 0);
                            if (descuentoAdicional < 0) {
                                const montoDescuentoAdicional = (montoConDescuento * Math.abs(descuentoAdicional)) /
                                    100;
                                descuentoAplicado += montoDescuentoAdicional;
                                montoConDescuento -= montoDescuentoAdicional;
                            }
                        }

                        // Si el IVA depende del monto con descuento, recalcularlo aquí:
                        // Por ejemplo, si hay un porcentaje fijo:
                        const porcentajeIva = ivaOriginal && montoOriginal ? (ivaOriginal / montoOriginal) : 0;
                        ivaNuevo = montoConDescuento * porcentajeIva;
                        $fila.attr('data-iva', ivaNuevo.toFixed(2));

                        totalDolares += montoConDescuento;
                        totalDescuento += descuentoAplicado;
                        totalOriginal += montoOriginal;

                        // Actualizar el monto mostrado en la fila
                        $fila.find('.monto-pedido').text(montoConDescuento.toFixed(2).replace(/\./g, ','));

                        // Actualizar datos en el checkbox solo si es necesario
                        if (esDivisaTotal) {
                            $checkbox.data({
                                'monto': montoConDescuento,
                                'monto-descuento': descuentoAplicado
                            });
                        }
                    } else {
                        // Para pedidos no seleccionados, usar el monto original
                        $fila.attr('data-iva', ivaOriginal.toFixed(2));
                        totalDolares += montoOriginal;
                        totalOriginal += montoOriginal;
                    }
                });

                // Calcular porcentaje de descuento
                const porcentajeDescuento = totalOriginal > 0 ?
                    (totalDescuento / totalOriginal * 100).toFixed(2) :
                    '0.00';

                // Actualizar totales en la interfaz
                const totalFormateado = formatearMoneda(totalDolares);
                $('#total-pagar').text('$' + totalFormateado);
                $('#monto-total').val(totalDolares.toFixed(2));
                $('#total-dolares').text(totalDolares.toFixed(2).replace(/\./g, ','));

                // Actualizar descuento
                if (esDivisaTotal) {
                    $('#total-descuento').text(porcentajeDescuento + '%');
                    $('input[name="total_descuento"]').val(porcentajeDescuento);
                } else {
                    $('#total-descuento').text('0,00%');
                    $('input[name="total_descuento"]').val('0');
                }

                // Actualizar el máximo en el campo de monto en divisa
                const maxMonto = Math.floor(totalDolares);
                $('#monto_divisa').attr('max', maxMonto);

                const montoActual = parseFloat($('#monto_divisa').val()) || 0;
                if (montoActual > maxMonto) {
                    $('#monto_divisa').val(maxMonto);
                }

                // Actualizar total en la tabla de resumen
                $('#total-dolares').text(totalDolares.toFixed(2).replace(/\./g, ','));

                // Actualizar totales en la tabla de bolívares si está visible
                if ($('#campo-tasa-bcv').is(':visible')) {
                    calcularConversionBolivares();
                }
            }

            // Calcular divisa parcial (parte en $ y parte en Bs.)
            function calcularDivisaParcial() {
                // Obtener valores
                const montoDivisa = parseFloat($('#monto_divisa').val()) || 0;
                const tasaBcv = parseFloat($('#tasa_bcv').val()) || 0;
                const totalPagar = parseFloat('{{ $totalPagar }}') || 0;

                // Validar monto
                if (montoDivisa > totalPagar) {
                    $('#monto_divisa').val(totalPagar.toFixed(2));
                    return;
                }

                // Validar valores numéricos
                if (isNaN(montoDivisa) || isNaN(tasaBcv) || isNaN(totalPagar)) {
                    $('#monto-bolivares-parcial').text('0,00 Bs.');
                    $('#iva-bolivares-parcial').text('0,00 Bs.');
                    $('#retencion-bolivares-parcial').text('-0,00 Bs.');
                    $('#descuento-bolivares-parcial').text('-0,00 Bs.');
                    $('#total-bolivares-parcial').text('0,00 Bs.');
                    $('#monto-bolivares-parcial-input').val('0');
                    $('#iva-bolivares-parcial-input').val('0');
                    $('#retencion-bolivares-parcial-input').val('0');
                    $('#descuento-bolivares-parcial-input').val('0');
                    $('#total-bolivares-parcial-input').val('0');
                    return;
                }

                // Calcular monto restante en bolívares
                const montoRestante = totalPagar - montoDivisa;
                const montoBolivares = montoRestante * tasaBcv;

                // Calcular IVA (16% del monto en bolívares)
                const detallePedidos = JSON.parse($('input[name="detalle_pedidos"]').val() || '[]');
                const porcentajeIva = detallePedidos.some(p => parseFloat(p.iva) > 0) ? 16 : 0;
                const iva = montoBolivares * (porcentajeIva / 100);

                // Calcular retención (75% del IVA)
                const retencion = iva * 0.75;

                // Obtener el porcentaje de descuento (si existe)
                const descuentoPorcentaje = parseFloat('{{ $descuentoProntoPago ?? 0 }}') || 0;
                const descuento = montoBolivares * (descuentoPorcentaje / 100);

                // Calcular total
                const total = montoBolivares + iva - retencion - descuento;

                // Actualizar la tabla "Resumen de Pago en Bolívares"
                $('#monto-bolivares-parcial').text(montoBolivares.toLocaleString('es-VE', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + ' Bs.');
                $('#iva-bolivares-parcial').text(iva.toLocaleString('es-VE', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + ' Bs.');
                $('#retencion-bolivares-parcial').text('-' + retencion.toLocaleString('es-VE', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + ' Bs.');
                $('#descuento-bolivares-parcial').text('-' + descuento.toLocaleString('es-VE', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + ' Bs.');
                $('#total-bolivares-parcial').text(total.toLocaleString('es-VE', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + ' Bs.');

                // Actualizar campos ocultos
                $('#monto-bolivares-parcial-input').val(montoBolivares.toFixed(2));
                $('#iva-bolivares-parcial-input').val(iva.toFixed(2));
                $('#retencion-bolivares-parcial-input').val(retencion.toFixed(2));
                $('#descuento-bolivares-parcial-input').val(descuento.toFixed(2));
                $('#total-bolivares-parcial-input').val(total.toFixed(2));

                // Mostrar el resumen
                $('#resumen-divisa-parcial').removeClass('d-none');
            }

            // Calcular conversión a bolívares
            function calcularConversionBolivares() {
                const tasa = parseFloat($('#tasa_bcv').val()) || 0;
                const tipoPago = $('input[name="tipo_pago"]:checked').val();

                if (tipoPago === 'bs') {
                    if (tasa > 0) {
                        // Mostrar resumen y tabla solo si hay una tasa válida
                        //$('#summary-container, #orders-table').removeClass('d-none');
                    } else {
                        // Ocultar si no hay tasa o es cero
                        $('#summary-container, #orders-table').addClass('d-none');
                        return;
                    }
                }

                let totalDolares = 0;
                let ivaDolares = 0;
                let retencionDolares = 0;
                let totalBolivares = 0;

                // Resetear totales
                totalDolares = 0;
                ivaDolares = 0;
                retencionDolares = 0;

                // Calcular total en dólares y bolívares
                $('.pedido-fila').each(function() {
                    const $fila = $(this);
                    if ($fila.find('input[type="checkbox"]').is(':checked')) {
                        // Obtener montos en dólares desde los atributos data
                        const montoDolares = parseFloat($fila.data('monto-original')) || 0;
                        let montoIvaDolares = parseFloat($fila.attr('data-iva')) || 0;

                        // Si el IVA es 0, verificar si hay un porcentaje de IVA en los datos
                        if (montoIvaDolares === 0) {
                            const porcentajeIva = parseFloat($fila.data('porcentaje-iva')) || 0;
                            if (porcentajeIva > 0) {
                                montoIvaDolares = (montoDolares * porcentajeIva) / 100;
                                $fila.attr('data-iva', montoIvaDolares.toFixed(2));
                            }
                        }

                        const montoRetencionDolares = parseFloat($fila.data('retencion')) || 0;

                        console.log('Pedido:', $fila.data('pedido-id'), 'Monto:', montoDolares, 'IVA:', montoIvaDolares,
                            'Retención:', montoRetencionDolares);

                        // Sumar al total en dólares (incluyendo IVA)
                        totalDolares += montoDolares + montoIvaDolares;

                        // Acumular IVA y retención
                        ivaDolares += montoIvaDolares;
                        if (montoRetencionDolares > 0) {
                            retencionDolares += montoRetencionDolares;
                        }
                    }
                });
                /*
                console.log('--- Totales ---');
                console.log('Total $:', totalDolares);
                console.log('IVA $:', ivaDolares);
                console.log('Retención $:', retencionDolares);
                */
                // Calcular totales en dólares primero
                const baseSinIvaDolares = totalDolares - ivaDolares; // Restar IVA para obtener la base

                console.log('--- Cálculos en Dólares ---');
                console.log('Base sin IVA $:', baseSinIvaDolares);
                console.log('IVA $:', ivaDolares);
                console.log('Retención $:', retencionDolares);

                // Calcular el total en dólares (restando la retención del IVA)
                const totalDolaresNeto = baseSinIvaDolares + (ivaDolares - retencionDolares);

                // Convertir a bolívares aplicando la tasa una sola vez
                const baseBolivares = baseSinIvaDolares * tasa;
                //const ivaBolivares = ivaDolares * tasa;
                let ivaBolivares = 0;
                let retencionBolivares = 0;
                $('.pedido-fila-new').each(function() {
                    const $fila = $(this);
                    const montoOriginal = parseFloat($fila.attr('data-monto-original')) || 0;
                    const montoBsConDescuento = parseFloat($fila.attr('data-monto-con-descuento')) || 0;
                    const iva = parseFloat($fila.attr('data-iva')) || 0;
                    if (iva > 0) {
                        porcIva = iva * 100 / montoOriginal;
                        ivaBolivares += montoBsConDescuento * porcIva / 100 * tasa; //(iva*tasa);
                        console.warn('ESTABLECIO....' + ivaBolivares)
                    }
                    // si tiene retencion acumula el valor para retencionBolivares
                    const retencion = parseFloat($fila.attr('data-retencion')) || 0;
                    if (retencion > 0) {
                        //retencionBolivares += ( retencion*tasa );
                        let porc_retenc2 = (retencion * 100) / iva;
                        retencionBolivares = (ivaBolivares * porc_retenc2 / 100);
                    }
                });
                //let retencionBolivares = retencionDolares * tasa;
                // Calcular el total restando la retención del IVA
                //totalBolivares = (baseSinIvaDolares + (ivaDolares - retencionDolares)) * tasa;
                totalBolivares = (baseBolivares + (ivaBolivares - retencionBolivares));

                console.log('--- Cálculos en Bolívares (Tasa:', tasa, ') ---');
                console.log('Base sin IVA Bs:', baseBolivares);
                console.log('IVA Bs.:', ivaBolivares);
                console.log('Retención Bs:', retencionBolivares);
                console.log('Total Bs:', totalBolivares);

                // Mostrar cálculos finales
                console.log('--- Cálculos Finales ---');
                console.log('Base sin IVA $:', baseSinIvaDolares, 'x', tasa, '=', baseBolivares.toFixed(2), 'Bs.');
                console.log('IVA $:', ivaDolares, 'x', tasa, '=', ivaBolivares.toFixed(2), 'Bs.');
                console.log('Retención $:', retencionDolares, 'x', tasa, '=', retencionBolivares.toFixed(2), 'Bs.');
                console.log('Total Bs:', baseBolivares.toFixed(2), '+', ivaBolivares.toFixed(2), '-', retencionBolivares
                    .toFixed(2), '=', totalBolivares.toFixed(2), 'Bs.');

                // Actualizar la visualización de montos en bolívares
                const formatOptions = {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                    useGrouping: true,
                    locale: 'es-ES'
                };

                // Asegurarse de que los valores no sean NaN
                if (isNaN(ivaBolivares)) ivaBolivares = 0;
                if (isNaN(baseBolivares)) baseBolivares = 0;
                if (isNaN(totalBolivares)) totalBolivares = 0;

                // Formatear valores
                const ivaFormatted = ivaBolivares.toLocaleString('es-ES', formatOptions);
                const baseFormatted = baseBolivares.toLocaleString('es-ES', formatOptions);
                const totalFormatted = totalBolivares.toLocaleString('es-ES', formatOptions);

                // Debug: Mostrar valores finales
                console.log('--- Valores finales para UI ---');
                console.log('Base Bs:', baseFormatted);
                console.log('IVA Bs.:', ivaFormatted);
                console.log('Total Bs:', totalFormatted);

                // Mostrar/ocultar montos en dólares/bolívares
                if (tasa > 0) {
                    // Mostrar montos en bolívares
                    $('#base_dolares').addClass('d-none');
                    $('#subtotal_dolares').addClass('d-none');
                    $('#base_bolivares')
                        .removeClass('d-none')
                        .text(baseFormatted + ' Bs.');

                    $('#subtotal_bolivares2')
                        .removeClass('d-none')
                        .text(baseFormatted + ' Bs.');

                    $('#impuesto_dolares').addClass('d-none');
                    $('#impuesto_bolivares')
                        .removeClass('d-none')
                        .text(ivaFormatted + ' Bs.');

                    // Actualizar total en la interfaz
                    $('#total_bolivares2').html(totalFormatted + ' Bs.');
                } else {
                    // Mostrar montos en dólares
                    $('#base_dolares').removeClass('d-none');
                    $('#subtotal_dolares').removeClass('d-none');
                    $('#base_bolivares').addClass('d-none');
                    $('#subtotal_bolivares2').addClass('d-none');

                    $('#impuesto_dolares').removeClass('d-none');
                    $('#impuesto_bolivares').addClass('d-none');

                    // Restaurar total en dólares
                    const totalDolaresFormatted = (totalDolares + ivaDolares - retencionDolares).toLocaleString('es-ES',
                        formatOptions);
                    $('#total_bolivares2').html(totalDolaresFormatted + ' $');
                }

                // Guardar el total sin IVA para referencia
                const totalSinIvaBolivares = totalDolares * tasa;

                // Debug: Mostrar valores en consola
                console.log('Tasa BCV:', tasa);
                console.log('Total $:', totalDolares);
                console.log('IVA $:', ivaDolares);
                console.log('Total + IVA $:', totalDolares + ivaDolares);
                console.log('Total Bs:', totalBolivares);
                $('#total-iva').html(ivaDolares.toFixed(2));

                // Formatear valores para mostrar
                const totalBolivaresFormateado = totalBolivares.toLocaleString('es-ES', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

                const ivaBolivaresFormateado = ivaBolivares.toLocaleString('es-ES', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

                // Actualizar la interfaz con los valores formateados
                console.log('Actualizando UI con valores:');
                console.log('Total sin IVA Bs--:', (totalDolares * tasa).toLocaleString('es-ES'));
                console.log('IVA Bs...:', ivaBolivaresFormateado);
                console.log('Retención Bs:', (retencionBolivares).toLocaleString('es-ES'));
                console.log('Total con IVA Bs:', totalBolivaresFormateado);

                // Actualizar total en la tabla
                $('#total-bolivares').text(totalBolivaresFormateado);

                // Actualizar subtotal en la sección de resumen
                const subtotalBolivares = (totalDolares * tasa).toLocaleString('es-ES', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                $('#subtotal_bolivares').text(subtotalBolivares + ' Bs.');

                // Actualizar retención en la sección de resumen
                const retencionBolivaresFormateada = retencionBolivares.toLocaleString('es-ES', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                $('#retencion_bolivares').text(retencionBolivaresFormateada + ' Bs.');

                // Actualizar IVA en la sección de resumen (solo si hay IVA)
                if (ivaBolivares > 0) {
                    $('#impuesto_bolivares').html(ivaBolivaresFormateado + ' Bs.');
                } else {
                    $('#impuesto_bolivares').html('0,00 Bs.');
                }

                // Actualizar total en la tarjeta resaltada
                $('#total_bolivares2').html(totalBolivaresFormateado + ' Bs.');

                // Actualizar el campo oculto con el monto total en bolívares
                $('#monto-total-bs').val(totalBolivares.toFixed(2));


                // Actualizar el campo oculto con el monto total
                $('#monto-total').val(totalDolares.toFixed(2));

                // Forzar actualización de la UI
                $('body').trigger('conversionCalculada', {
                    total: totalBolivaresFormateado,
                    iva: ivaBolivaresFormateado
                });


                $('#monto-total-con-descuento').html((totalBolivares / tasa).toFixed(2));
                $('#ahorro-total').html('0,00');
                $('.descuento-adicional').html('0,00');
                $('.pedido-fila-new').each(function() {
                    const montoOriginal = parseFloat($(this).data('monto-original')) || 0;
                    //$(this).find('.monto-pedido').html(montoConDescuento.toFixed(2).replace(/\./g, ','));
                });
            }

            // Inicializar botones de método de pago
            function initPaymentCards() {
                // Manejar clic en los botones de método de pago
                $('.payment-method-btn').on('click', function() {
                    // Remover clase active de todos los botones
                    $('.payment-method-btn').removeClass('active');
                    // Agregar clase active al botón clickeado
                    $(this).addClass('active');
                    // Actualizar el check visual
                    $('.payment-method-btn i.fa-check-circle').css('opacity', '0');
                    $(this).find('i.fa-check-circle').css('opacity', '1');

                    // Efecto de pulso
                    $(this).addClass('animate__animated animate__pulse');
                    setTimeout(() => {
                        $(this).removeClass('animate__animated animate__pulse');
                    }, 300);

                    // Actualizar campos según el método seleccionado
                    togglePaymentFields();
                });

                // Marcar el botón seleccionado inicialmente
                $('input[name="tipo_pago"]:checked').siblings('label').addClass('active')
                    .find('i.fa-check-circle').css('opacity', '1');

                // Inicializar montos originales
                $('.pedido-fila').each(function() {
                    const montoTexto = $(this).find('.monto-pedido').text().replace('$', '').replace(',', '.');
                    $(this).data('monto-original', parseFloat(montoTexto) || 0);
                    $(this).data('descuento', parseFloat($(this).find('.descuento-aplicado').text()) || 0);
                });
            }

            // Validar formulario antes de enviar
            function validateForm() {
                const tipoPago = $('input[name="tipo_pago"]:checked').val();
                let montoDivisa = parseFloat($('#monto_divisa').val()) || 0;
                const totalPagar = parseFloat('{{ $totalPagar }}');

                // Validar monto en divisa si es pago parcial
                if (tipoPago === 'divisa_parcial') {
                    if (isNaN(montoDivisa) || montoDivisa <= 0) {
                        showError('Por favor ingrese un monto válido en divisa');
                        return false;
                    }

                    if (montoDivisa > totalPagar) {
                        showError('El monto en divisa no puede ser mayor al total a pagar');
                        return false;
                    }
                }
                return true;
            }

            // Mostrar mensaje de error
            function showError(message) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message,
                    confirmButtonColor: '#4361ee',
                    confirmButtonText: 'Entendido',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                });
            }

            // Inicializar datepicker
            function initDatepicker() {
                $('.datepicker').datepicker({
                    format: 'dd/mm/yyyy',
                    autoclose: true,
                    todayHighlight: true,
                    language: 'es',
                    orientation: 'bottom auto'
                });
            }

            // Actualizar el monto mostrado para un pedido específico
            function actualizarMontoPedido($fila) {
                const tipoPago = $('input[name="tipo_pago"]:checked').val();
                const montoOriginal = parseFloat($fila.data('monto-original')) || 0;
                let montoMostrar = montoOriginal;

                // Si es Divisa Total, aplicar descuentos si existen
                if (tipoPago === 'divisa_total') {
                    const porcentajeDescuento = parseFloat($fila.find('.descuento-aplicado .badge').first().text()) || 0;
                    const descuentoAdicional = parseFloat($fila.data('descuento-pedido') || 0);

                    // Aplicar descuento principal
                    if (porcentajeDescuento > 0) {
                        montoMostrar = montoOriginal * (1 - (porcentajeDescuento / 100));
                    }

                    // Aplicar descuento adicional si existe
                    if (descuentoAdicional < 0) {
                        montoMostrar = montoMostrar * (1 - (Math.abs(descuentoAdicional) / 100));
                    }
                }

                // Actualizar el monto mostrado en la fila
                $fila.find('.monto-pedido').text(montoMostrar.toFixed(2).replace(/\./g, ','));

                return montoMostrar;
            }

            // Función para manejar el cambio de método de pago
            function handlePaymentMethodChange() {
                const tipoPago = $('input[name="tipo_pago"]:checked').val();
                console.log('Método de pago seleccionado:', tipoPago);

                // Ocultar todos los campos primero
                $('#campo-tasa-bcv, #campos-transferencia, .campo-monto-divisa, #campo-monto-divisa').addClass('d-none');

                // Resetear montos y descuentos
                $('.pedido-fila').each(function() {
                    const $fila = $(this);
                    const montoOriginal = parseFloat($fila.data('monto-original')) || 0;

                    // Actualizar visualmente el descuento
                    $fila.find('.descuento-pedido').text('0.00%');
                    $fila.data('descuento', 0);
                    $fila.data('monto', montoOriginal);
                    $fila.find('.monto-pedido').text(montoOriginal.toFixed(2).replace(/\./g, ','));
                });

                // Mostrar campos según el tipo de pago
                if (tipoPago === 'bs') {
                    console.log('Mostrando campo tasa BCV desde handlePaymentMethodChange');
                    $('#campo-tasa-bcv').removeClass('d-none');
                    // Forzar el cálculo de conversión
                    if ($('#tasa_bcv').val()) {
                        console.log('Calculando conversión desde handlePaymentMethodChange');
                        calcularConversionBolivares();
                    }
                } else if (tipoPago === 'divisa_parcial') {
                    $('#campo-monto-divisa').removeClass('d-none');
                    actualizarTotales();
                } else if (tipoPago === 'divisa_total') {
                    console.log('Aplicando descuentos para Divisa Total');
                    aplicarDescuentoDivisaTotal();
                } else {
                    restaurarMontosOriginales();
                }

                // Actualizar la interfaz
                actualizarInterfazMetodoPago(tipoPago);
                actualizarTotal();
            }

            // Función para actualizar la interfaz según el método de pago seleccionado
            function actualizarInterfazMetodoPago(tipoPago) {
                // Actualizar clases activas
                $('.payment-method-option').removeClass('active');
                $(`input[value="${tipoPago}"]`).closest('.payment-method-option').addClass('active');

                // Actualizar iconos de check
                $('.payment-method-btn i.fa-check-circle').css('opacity', '0');
                $(`input[value="${tipoPago}"]`).siblings('label').find('i.fa-check-circle').css('opacity', '1');
            }

            // Función para aplicar descuentos
            function aplicarDescuentos() {
                const esDivisaTotal = $('input[name="tipo_pago"]:checked').val() === 'divisa_total';
                console.log('Aplicando descuentos. Es Divisa Total:', esDivisaTotal);
                let totalDescuento = 0;
                let totalConDescuento = 0;

                $('.pedido-fila').each(function() {
                    const $fila = $(this);
                    const $checkbox = $fila.find('input.pedido-checkbox');
                    const montoOriginal = parseFloat($checkbox.data('monto-original'));
                    let montoConDescuento = montoOriginal;
                    let montoDescuento = 0;

                    if (esDivisaTotal) {
                        const descuento = parseFloat($checkbox.data('descuento')) || 0;
                        const porcentajeDescuento = Math.abs(descuento); // Convertir a positivo
                        montoDescuento = montoOriginal * (porcentajeDescuento / 100);
                        montoConDescuento = montoOriginal - montoDescuento;
                        totalDescuento += montoDescuento;

                        // Actualizar los datos en el checkbox
                        $checkbox.data('monto', montoConDescuento);
                        $checkbox.data('monto-descuento', montoDescuento);

                        // Mostrar el descuento aplicado
                        $fila.find('.descuento-aplicado').html(
                            `<span class="badge bg-success">-${porcentajeDescuento}%</span>`);
                    } else {
                        // Restaurar monto original si no es Divisa Total
                        $checkbox.data('monto', montoOriginal);
                        $checkbox.data('monto-descuento', 0);
                        $fila.find('.descuento-aplicado').html('<span class="badge bg-secondary">0%</span>');
                    }

                    totalConDescuento += montoConDescuento;

                    // Actualizar el monto mostrado en la tabla
                    $fila.find('.monto-pedido').text(`$${montoConDescuento.toFixed(2).replace(/\./g, ',')}`);

                    // Actualizar el monto en la columna de bolívares si está visible
                    if ($('#campo-tasa-bcv').is(':visible')) {
                        const tasa = parseFloat($('#tasa_bcv').val()) || 0;
                        const montoBolivares = montoConDescuento * tasa;
                        $fila.find('.monto-bolivares').text(montoBolivares.toLocaleString('es-ES', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }));
                    }
                });

                // Actualizar totales
                actualizarTotales();

                // Actualizar el campo oculto con el total de descuento
                $('input[name="total_descuento"]').val(totalDescuento.toFixed(2));

                // Si hay una tasa BCV, recalcular la conversión
                if ($('#tasa_bcv').val()) {
                    calcularConversionBolivares();
                }
            }

            // Inicializar el formulario
            console.log('Inicializando formulario...');

            // Función para inicializar el método de pago
            function inicializarMetodoPago() {
                // Asegurarse de que los botones de radio estén en el estado correcto
                const tipoPago = $('input[name="tipo_pago"]:checked').val();
                console.log('Método de pago seleccionado al inicializar:', tipoPago);

                // Actualizar la interfaz según el método de pago
                actualizarInterfazMetodoPago(tipoPago);

                // Mostrar/ocultar campos según el método de pago
                actualizarCamposPorMetodoPago(tipoPago);

                // Aplicar descuentos si es necesario
                if (tipoPago === 'divisa_total') {
                    console.log('Aplicando descuento inicial para Divisa Total');
                    // Pequeño retraso para asegurar que la interfaz se haya actualizado
                    setTimeout(function() {
                        aplicarDescuentoDivisaTotal();
                        actualizarTotal();
                    }, 50);
                } else {
                    actualizarTotal();
                }
            }

            // Mover la función al ámbito global
            window.actualizarCamposPorMetodoPago = function(tipoPago) {
                console.log('Actualizando campos para:', tipoPago);

                // Ocultar todos los campos primero
                $('#campo-tasa-bcv, #campos-transferencia, .campo-monto-divisa, #campo-monto-divisa').addClass('d-none');

                switch (tipoPago) {
                    case 'bs':
                        console.log('Mostrando campo tasa BCV');
                        $('#campo-tasa-bcv').removeClass('d-none');
                        // Forzar el cálculo de conversión si hay tasa
                        if ($('#tasa_bcv').val()) {
                            console.log('Calculando conversión a bolívares');
                            if (typeof calcularConversionBolivares === 'function') {
                                calcularConversionBolivares();
                            }
                        } else {
                            console.log('No hay tasa BCV definida');
                        }
                        break;
                    case 'divisa_parcial':
                        $('#campo-monto-divisa').removeClass('d-none');
                        if (typeof actualizarTotales === 'function') {
                            actualizarTotales();
                        }
                        break;
                    case 'divisa_total':
                        if (typeof aplicarDescuentoDivisaTotal === 'function') {
                            aplicarDescuentoDivisaTotal();
                        }
                        break;
                }
            }

            // Función para manejar el cambio de método de pago
            function manejarCambioMetodoPago() {
                const tipoPago = $('input[name="tipo_pago"]:checked').val();
                console.log('Método de pago seleccionado:', tipoPago);

                // Actualizar la interfaz
                actualizarInterfazMetodoPago(tipoPago);
                actualizarCamposPorMetodoPago(tipoPago);
                actualizarTotal();

                // Actualizar el estado activo de las opciones
                $('.payment-method-option').removeClass('active');
                $('input[name="tipo_pago"]:checked').closest('.payment-method-option')
                    .addClass('active')
                    .find('.fa-check-circle').css('opacity', '1');
            }

            // Inicializar cuando el documento esté listo
            $(document).ready(function() {
                //console.log('Documento listo, inicializando...');

                // Validación de monto en divisa
                $('#monto_divisa').on('input', function() {
                    let value = $(this).val();
                    const max = parseFloat($(this).attr('max')) || 0;

                    if (value.includes('.')) {
                        value = value.split('.')[0];
                        $(this).val(value);
                    }

                    if (parseFloat(value) > max) {
                        $(this).val(max);
                    }

                    if (parseFloat(value) < 1) {
                        $(this).val(1);
                    }
                });

                // Calcular conversión cuando cambia la tasa BCV
                $(document).on('input', '#tasa_bcv', function() {
                    if ($(this).val() && !isNaN($(this).val())) {
                        calcularConversionBolivares();
                    }
                });

                // Inicializar montos y descuentos
                inicializarMontosYDescuentos();

                // Manejar cambios en el método de pago
                $('input[name="tipo_pago"]').on('change', manejarCambioMetodoPago);

                // Inicializar la interfaz
                manejarCambioMetodoPago();

                // Manejar clics en los botones de método de pago
                $(document).on('click', '.payment-method-option', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const $radio = $(this).find('input[type="radio"]');
                    if (!$radio.prop('checked')) {
                        $radio.prop('checked', true).trigger('change');
                    }
                });

                // Manejar envío del formulario
                $('#form-pago').on('submit', function(e) {
                    e.preventDefault();

                    const tipoPago = $('input[name="tipo_pago"]:checked').val();
                    const montoTotal = parseFloat($('#monto-total').val()) || 0;
                    const form = this;

                    if (tipoPago === 'divisa_total') {
                        montoTotal2 = $('#total_pagar_divisa').val();
                        let addFields = ['total_pagar_divisa', 'total_retencion', 'total_descuento_pago',
                            'total_iva'
                        ];
                        addFields.forEach(function(field) {
                            $('<input>').attr({
                                type: 'hidden',
                                name: field,
                                value: $('#' + field).val()
                            }).appendTo(form);
                        });
                    }
                    // Remove required attribute from tasa_bcv unless payment type is 'bs'
                    if (tipoPago === 'bs') {
                        $('#tasa_bcv').attr('required', true);
                    } else {
                        $('#tasa_bcv').removeAttr('required');
                    }

                    if (montoTotal <= 0) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Debe seleccionar al menos un pedido para realizar el pago.',
                            confirmButtonColor: '#4361ee'
                        });
                        return false;
                    }

                    // Validar campos según el tipo de pago
                    if (tipoPago === 'bs') {
                        const tasaBcv = parseFloat($('#tasa_bcv').val()) || 0;
                        if (tasaBcv <= 0) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Debe ingresar una tasa BCV válida para continuar con el pago en bolívares.',
                                confirmButtonColor: '#4361ee'
                            });
                            return false;
                        }

                        // Asegurarse de que los montos en bolívares estén actualizados
                        calcularConversionBolivares();

                    } else if (tipoPago === 'divisa_parcial') {
                        const montoDivisa = parseFloat($('#monto_divisa').val()) || 0;
                        if (montoDivisa <= 0 || montoDivisa > montoTotal) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Debe ingresar un monto en divisa válido para continuar con el pago parcial.',
                                confirmButtonColor: '#4361ee'
                            });
                            return false;
                        }

                        // Actualizar montos para pago parcial
                        const montoPendiente = montoTotal - montoDivisa;
                        $('#monto-pendiente').text(montoPendiente.toFixed(2).replace(/\./g, ','));
                    }

                    // Mostrar confirmación
                    Swal.fire({
                        title: '¿Está seguro de procesar el pago?',
                        text: 'Esta acción no se puede deshacer.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#4361ee',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Sí, procesar pago',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Deshabilitar el botón de envío
                            const $submitButton = $('#btn-procesar-pago');
                            $submitButton.prop('disabled', true);
                            $submitButton.html(
                                '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...'
                            );

                            // Si el pago es en bolívares, actualizar el campo oculto con el total en bolívares (que ya incluye IVA)
                            if (tipoPago === 'bs') {
                                // Obtener el total en bolívares del elemento que lo muestra
                                const totalBolivaresText = $('#total_bolivares2').text().trim();
                                // Eliminar el símbolo de moneda y espacios, luego reemplazar comas por puntos
                                const cleanText = totalBolivaresText.replace(/[^0-9,]/g, '').replace(
                                    ',', '.');
                                const totalBolivares = parseFloat(cleanText) || 0;
                                //console.log('Texto original:', totalBolivaresText, 'Texto limpio:', cleanText, 'Valor numérico:', totalBolivares);

                                //console.log('Total en bolívares a enviar:', totalBolivares);

                                // Agregar campo oculto con el total en bolívares
                                $('<input>').attr({
                                    type: 'hidden',
                                    name: 'total_bolivares',
                                    value: totalBolivares.toFixed(2)
                                }).appendTo(form);

                                // Actualizar el campo de total_pagar_divisa con el monto en bolívares solo si el tipo de pago es 'bs'
                                $('#total_pagar_divisa').val(totalBolivares.toFixed(2));

                                // Agregar campo oculto con el total a pagar que será usado por paymentData.totalAPagar
                                $('<input>').attr({
                                    type: 'hidden',
                                    name: 'total_a_pagar',
                                    value: totalBolivares.toFixed(2)
                                }).appendTo(form);

                                //console.log('Total a pagar establecido en:', totalBolivares.toFixed(2));
                            }

                            // Enviar el formulario
                            form.submit();
                        }
                    });
                });

                // Función para inicializar el datepicker
                function initDatepicker() {
                    $('.datepicker').datepicker({
                        format: 'dd/mm/yyyy',
                        autoclose: true,
                        todayHighlight: true,
                        language: 'es',
                        orientation: 'bottom auto'
                    });
                }

                // Función para configurar los event listeners
                function setupEventListeners() {
                    initPaymentCards();

                    // Ocultar campos que no se usan por defecto
                    $('#campos-transferencia').addClass('d-none');
                    $('#campo-tasa-bcv').addClass('d-none');
                    $('.campo-monto-divisa').addClass('d-none');

                    // Mostrar campos según el método de pago seleccionado inicialmente
                    const tipoPago = $('input[name="tipo_pago"]:checked').val();
                    if (tipoPago === 'bs') {
                        $('#campo-tasa-bcv').removeClass('d-none');
                        calcularConversionBolivares();
                    } else if (tipoPago === 'divisa_parcial') {
                        $('.campo-monto-divisa').removeClass('d-none');
                        actualizarTotales();
                    }
                }

                // Mover la función al ámbito global
                window.actualizarTotales = function() {
                    let totalDolares = 0;
                    let totalDescuento = 0;
                    let totalPedidos = 0;

                    // Calcular totales de los pedidos seleccionados
                    $('.pedido-fila').each(function() {
                        const $fila = $(this);
                        const monto = parseFloat($fila.data('monto')) || 0;
                        const montoOriginal = parseFloat($fila.data('monto-original')) || 0;

                        if (monto > 0) {
                            totalDolares += monto;
                            totalPedidos++;

                            // Calcular descuento si existe
                            if (monto < montoOriginal) {
                                const descuento = ((montoOriginal - monto) / montoOriginal) * 100;
                                totalDescuento += descuento;
                            }
                        }
                    });

                    // Actualizar la interfaz
                    $('#total-pagar').text('$' + totalDolares.toFixed(2).replace(/\./g, ','));
                    $('#monto-total').val(totalDolares.toFixed(2));

                    // Actualizar el campo oculto con el total de descuento
                    if (totalPedidos > 0) {
                        const descuentoPromedio = totalDescuento / totalPedidos;
                        $('input[name="total_descuento"]').val(descuentoPromedio.toFixed(2));
                    }

                    // Si hay una tasa BCV, recalcular la conversión
                    if ($('#tasa_bcv').val()) {
                        if (typeof calcularConversionBolivares === 'function') {
                            calcularConversionBolivares();
                        }
                    }
                }

                // Inicializar eventos
                function inicializarEventos() {
                    // Evento para cambios en el método de pago
                    $(document).on('change', 'input[name="tipo_pago"]', function() {
                        // Ocultar todos los campos primero
                        $('#campo-tasa-bcv').addClass('d-none');
                        $('#campos-transferencia').addClass('d-none');
                        $('.campo-monto-divisa').addClass('d-none');

                        const tipoPago = $(this).val();
                        console.log('Cambiando a método de pago:', tipoPago);

                        if (tipoPago === 'divisa_total') {
                            location.reload();
                            restaurarMontosOriginales();
                        } else if (tipoPago === 'bs') {
                            console.log('Mostrando campo de tasa BCV');
                            $('#campo-tasa-bcv').removeClass('d-none');
                            if ($('#tasa_bcv').val()) {
                                calcularConversionBolivares();
                            }
                        } else if (tipoPago === 'divisa_parcial') {
                            console.log('Mostrando campo de monto en divisa');
                            $('.campo-monto-divisa').removeClass('d-none');
                            actualizarTotales();
                        }
                    });

                    // Evento para cambios en la tasa BCV
                    $(document).on('input', '#tasa_bcv', function() {
                        if ($('input[name="tipo_pago"]:checked').val() === 'bs') {
                            calcularConversionBolivares();
                        }
                    });

                    // Inicializar la conversión si ya hay una tasa
                    const tipoPagoInicial = $('input[name="tipo_pago"]:checked').val();
                    console.log('Tipo de pago inicial en inicializarEventos:', tipoPagoInicial);

                    if (tipoPagoInicial === 'bs') {
                        $('#campo-tasa-bcv').removeClass('d-none');
                        if ($('#tasa_bcv').val()) {
                            console.log('Ejecutando cálculo inicial de conversión...');
                            setTimeout(function() {
                                calcularConversionBolivares();
                            }, 100);
                        }
                    }
                }

                // Inicializar eventos
                inicializarEventos();

                // Forzar la actualización del método de pago seleccionado inicialmente
                const tipoPagoSeleccionado = $('input[name="tipo_pago"]:checked').val();
                console.log('Método de pago inicial:', tipoPagoSeleccionado);

                if (tipoPagoSeleccionado === 'bs') {
                    console.log('Método de pago inicial es bolivares, mostrando campo de tasa...');
                    $('#campo-tasa-bcv').removeClass('d-none');

                    // Si ya hay una tasa, forzar el cálculo
                    if ($('#tasa_bcv').val()) {
                        console.log('Tasa BCV encontrada, calculando conversión...');
                        setTimeout(function() {
                            calcularConversionBolivares();
                        }, 100);
                    } else {
                        console.log('No hay tasa BCV definida aún');
                    }
                }
            });
        </script>
    @endsection
