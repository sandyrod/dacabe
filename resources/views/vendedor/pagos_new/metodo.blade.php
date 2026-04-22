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

    <div class="row">
        <!-- Tabla de Resumen de Pedidos -->
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
                                    <th class="text-end pe-3 text-muted">Saldo</th>
                                    <th class="text-center pe-3 text-muted">Descuento</th>
                                    <th class="text-center pe-3 text-muted">Desc. Adicional</th>
                                    <th class="text-end pe-3 text-muted">Total</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                                @php $detalle_pedidos = []; @endphp
                                @php $totalDescuento = 0; @endphp
                                @php $totalPagarDivisa = 0; @endphp
                                @php $totalIva = 0; @endphp
                                @php $totalRetencion = 0; @endphp
                                @php $porc_retencion = 0; @endphp
                                @foreach ($pedidosSeleccionados as $index => $pedido)
                                @php
                                $descuento =
                                isset($pedido->descuento_aplicado) &&
                                $pedido->descuento_aplicado
                                ? $pedido->monto_descuento
                                : 0;
                                $totalDescuento += $descuento;
                                $saldoConDescuento = $pedido->saldo_pendiente - $descuento;
                                $descuento_div =
                                abs($pedido->descuento_pedido) > 0
                                ? $saldoConDescuento *
                                (abs($pedido->descuento_pedido) / 100)
                                : 0;
                                $totalDescuento += $descuento_div;
                                $totalPagarDivisa += $saldoConDescuento - $descuento_div;
                                //$totalIva += $pedido->iva ?? 0;
                                $descuentoPedido = $pedido->descuento_pedido ?? 0;
                                $monto_con_descuento = $saldoConDescuento;
                                $nuevo_saldo =
                                $monto_con_descuento -
                                ($monto_con_descuento * abs($descuentoPedido)) / 100;
                                $totalIva +=
                                $pedido->iva > 0 && $pedido->factura != 'NO'
                                ? ($nuevo_saldo * 16) / 100
                                : 0;
                                $totalRetencion +=
                                $pedido->retencion && $pedido->factura != 'NO'
                                ? $pedido->retencion
                                : 0;
                                $porc_retencion =
                                $pedido->porc_retencion && $pedido->factura != 'NO'
                                ? ($totalIva * $pedido->porc_retencion) / 100
                                : 0;
                                $porc_descuento =
                                abs($descuentoPedido) + $pedido->porcentaje_descuento;
                                $detalle_pedidos[] = [
                                'pedido_id' => $pedido->id,
                                'descuento' => $descuento + $descuento_div,
                                'porc_descuento' => $porc_descuento,
                                'iva' =>
                                $pedido->iva > 0 && $pedido->factura != 'NO'
                                ? ($nuevo_saldo * 16) / 100
                                : 0,
                                'retencion' =>
                                $pedido->retencion && $pedido->factura != 'NO'
                                ? $pedido->retencion
                                : 0,
                                'saldo' => $saldoConDescuento,
                                ];
                                @endphp
                                <tr class="border-bottom pedido-fila-new"
                                    data-pedido-id="{{ $pedido->id }}"
                                    data-monto-original="{{ $pedido->saldo_pendiente }}"
                                    data-porcentaje-descuento="{{ $pedido->porcentaje_descuento ?? 0 }}"
                                    data-monto-descuento="{{ $descuento }}"
                                    data-monto-con-descuento="{{ $saldoConDescuento }}"
                                    data-descuento-pedido="{{ $pedido->descuento_pedido ?? 0 }}"
                                    data-iva="{{ $pedido->factura != 'NO' ? $pedido->iva : 0 }}"
                                    data-retencion="{{ $pedido->retencion && $pedido->factura != 'NO' ? $pedido->retencion : 0 }}"
                                    data-porc_retencion="{{ $pedido->porc_retencion && $pedido->factura != 'NO' ? $pedido->porc_retencion : 0 }}">
                                    <td class="py-2">
                                        <div class="text-dark">#{{ $pedido->id }}</div>
                                        <small class="text-muted"><i
                                                class="far fa-calendar-alt me-1"></i>{{ $pedido->fecha_formateada }}</small>
                                    </td>
                                    <td class="text-end pe-3 monto-original"
                                        data-monto="{{ $pedido->saldo_pendiente }}">
                                        {{ number_format($pedido->saldo_pendiente, 2, ',', '.') }}
                                        $
                                        <input type="hidden" class="monto-original-hidden"
                                            value="{{ $pedido->saldo_pendiente }}">
                                    </td>
                                    <td
                                        class="text-center pe-3 {{ $descuento > 0 ? 'text-success' : 'text-muted' }}">
                                        <div class="descuento-aplicado">
                                            @if ($descuento > 0)
                                            <span
                                                class="badge bg-success">{{ number_format($pedido->porcentaje_descuento ?? 0, 2) }}%</span>
                                            @else
                                            <span class="badge bg-secondary">0%</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center pe-3">
                                        <div class="descuento-adicional">
                                            @if (isset($pedido->descuento_pedido) && $pedido->descuento_pedido < 0)
                                                <span
                                                class="badge bg-warning">{{ number_format(abs($pedido->descuento_pedido), 2) }}%</span>
                                                @else
                                                <span class="badge bg-secondary">0%</span>
                                                @endif
                                        </div>
                                    </td>
                                    <td
                                        class="text-end pe-3 fw-bold text-{{ $saldoConDescuento > 0 ? 'danger' : 'success' }} monto-pedido">
                                        {{ number_format($saldoConDescuento, 2, ',', '.') }} $
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
                                                    <!-- TOTAL+IVA -->
                                                    <div class="col-12 col-md-3 mb-3 mb-md-0">
                                                        <h6 class="mb-0 fw-light">TOTAL+IVA</h6>
                                                        @php
                                                        $totalPagarDivisa +=
                                                        $totalIva - $porc_retencion;
                                                        @endphp
                                                        <div class="d-flex align-items-baseline">
                                                            <span class="h3 mb-0 fw-bold me-2"
                                                                id="monto-total-con-descuento">{{ number_format($totalPagarDivisa, 2, ',', '.') }}</span>
                                                            <input type="hidden"
                                                                name="total_pagar_divisa"
                                                                id="total_pagar_divisa"
                                                                value="{{ $totalPagarDivisa }}">
                                                            <span class="h5 mb-0">$</span>
                                                        </div>
                                                    </div>
                                                    <!-- Total sin Dcto. -->
                                                    <div class="col-6 col-md-3 mb-3 mb-md-0">
                                                        <small class="d-block text-white-50">Total sin
                                                            Dcto.:</small>
                                                        <div
                                                            class="d-flex align-items-center justify-content-start">
                                                            <s class="text-white-50 me-1"
                                                                id="monto-total">{{ number_format($totalPagar, 2, ',', '.') }}</s>
                                                            <span class="text-white-50">$</span>
                                                        </div>
                                                    </div>
                                                    <!-- Ahorro -->
                                                    <div class="col-6 col-md-2 mb-3 mb-md-0">
                                                        <small
                                                            class="d-block text-white-50">Ahorro:</small>
                                                        <div
                                                            class="d-flex align-items-center justify-content-start">
                                                            <span class="text-success fw-bold me-1"
                                                                id="ahorro-total">{{ number_format($totalDescuento, 2, ',', '.') }}</span>
                                                            <span class="text-success">$</span>
                                                        </div>
                                                    </div>
                                                    <!-- IVA -->
                                                    <div class="col-6 col-md-2 mb-3 mb-md-0">
                                                        <small
                                                            class="d-block text-white-50">IVA:</small>
                                                        <div
                                                            class="d-flex align-items-center justify-content-start">
                                                            <span class="text-info fw-bold me-1"
                                                                id="total-iva">{{ number_format($totalIva, 2, ',', '.') }}</span>
                                                            <span class="text-info">$</span>
                                                        </div>
                                                    </div>
                                                    <!-- Retención -->
                                                    <div class="col-6 col-md-2">
                                                        <small
                                                            class="d-block text-white-50">Retención:</small>
                                                        <div
                                                            class="d-flex align-items-center justify-content-start">
                                                            <span class="text-warning fw-bold me-1"
                                                                id="total-retencion">{{ number_format($porc_retencion, 2, ',', '.') }}</span>
                                                            <span class="text-warning">$</span>
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

            <form action="{{ route('vendedores.pagos_new.index') }}" method="POST" id="form-pago">
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
            <a href="{{ route('vendedores.pagos_new.clientes') }}"
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
    $(document).ready(function() {
        // Inicializar datepicker
        if ($.fn.datepicker) {
            $('.datepicker').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                language: 'es'
            });
        }

        // Variables globales
        const tasaBCV = parseFloat('{{ request('
            tasa_cambio ', 0) }}') || 1;
        const subtotalDolaresBase = parseFloat('{{ $totalPagar - ($totalIva ?? 0) }}') || 0;
        const totalRetencionDolares = parseFloat('{{ $porc_retencion }}') || 0;
        const totalDolares = parseFloat('{{ $totalPagarDivisa }}') || 0;

        // Función para formatear moneda
        function formatCurrency(amount, currency = '$') {
            return new Intl.NumberFormat('es-VE', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(amount) + ' ' + (currency === '$' ? '$' : 'Bs.');
        }

        // Manejar cambio de método de pago
        $('input[name="tipo_pago"]').on('change', function() {
            const method = $(this).val();

            // Actualizar clases activas en las labels
            $('.payment-method-btn').removeClass('active');
            $(this).closest('.payment-method-option').find('.payment-method-btn').addClass('active');

            // Mostrar/ocultar campos específicos
            if (method === 'divisa_parcial') {
                $('#campo-monto-divisa').removeClass('d-none');
                $('#resumen-divisa-parcial').removeClass('d-none');
                $('#campo-tasa-bcv').addClass('d-none');
                $('#orders-table').addClass('d-none');
                actualizarCalculosParciales();
            } else if (method === 'bs') {
                $('#campo-monto-divisa').addClass('d-none');
                $('#resumen-divisa-parcial').addClass('d-none');
                $('#campo-tasa-bcv').removeClass('d-none');
                $('#orders-table').removeClass('d-none');
                actualizarCalculosBolivares();
            } else {
                $('#campo-monto-divisa').addClass('d-none');
                $('#resumen-divisa-parcial').addClass('d-none');
                $('#campo-tasa-bcv').addClass('d-none');
                $('#orders-table').addClass('d-none');
            }
        });

        // Manejar cambio en monto divisa (pago parcial)
        $('#monto_divisa').on('input', function() {
            actualizarCalculosParciales();
        });

        function actualizarCalculosParciales() {
            const montoDivisa = parseFloat($('#monto_divisa').val()) || 0;
            const totalDolaresFactura = parseFloat('{{ $totalPagar }}') || 0;
            const tasa = tasaBCV;

            // El resto se paga en Bolívares
            const montoRestanteDolares = Math.max(0, totalDolaresFactura - montoDivisa);
            const montoBolivares = montoRestanteDolares * tasa;

            // Cálculos de impuestos para la parte en Bolívares
            // (Siguiendo la lógica del controlador original si fuera necesario)
            // Por ahora replicamos la visualización simple
            $('#monto-bolivares-parcial').text(formatCurrency(montoBolivares, 'Bs.'));
            $('#monto-bolivares-parcial-input').val(montoBolivares);

            // Calcular IVA y Retención sobre la parte en Bolívares si aplica
            // Esto es una simplificación, la lógica real está en el controlador que procesa el pago
            const ivaBs = montoBolivares * 0.16;
            const retencionBs = ivaBs * 0.75;
            const totalBs = montoBolivares + ivaBs - retencionBs;

            $('#iva-bolivares-parcial').text(formatCurrency(ivaBs, 'Bs.'));
            $('#iva-bolivares-parcial-input').val(ivaBs);
            $('#retencion-bolivares-parcial').text('- ' + formatCurrency(retencionBs, 'Bs.'));
            $('#retencion-bolivares-parcial-input').val(retencionBs);
            $('#total-bolivares-parcial').text(formatCurrency(totalBs, 'Bs.'));
            $('#total-bolivares-parcial-input').val(totalBs);
        }

        function actualizarCalculosBolivares() {
            $('.monto-bolivares').each(function() {
                const dolares = parseFloat($(this).data('monto-dolares')) || 0;
                $(this).text(formatCurrency(dolares * tasaBCV, 'Bs.'));
            });

            const totalBs = totalDolares * tasaBCV;
            $('#total-bolivares').text(formatCurrency(totalBs, 'Bs.'));
            $('#monto-total-bs').val(totalBs);

            // Actualizar resumen visual superior si existe
            $('#subtotal_bolivares2').text(formatCurrency(subtotalDolaresBase * tasaBCV, 'Bs.'));
            $('#impuesto_bolivares').text(formatCurrency(parseFloat('{{ $totalIva }}') * tasaBCV, 'Bs.'));
            $('#retencion_bolivares').text(formatCurrency(totalRetencionDolares * tasaBCV, 'Bs.'));
            $('#total_bolivares2').text(formatCurrency(totalBs, 'Bs.'));
        }

        // Inicializar cálculos si ya hay una opción seleccionada
        const initialMethod = $('input[name="tipo_pago"]:checked').val();
        if (initialMethod) {
            $('input[name="tipo_pago"]:checked').trigger('change');
        }

        // Validación antes de enviar
        $('#form-pago').on('submit', function(e) {
            const method = $('input[name="tipo_pago"]:checked').val();

            if (method === 'divisa_parcial') {
                const montoDivisa = parseFloat($('#monto_divisa').val()) || 0;
                if (montoDivisa <= 0) {
                    e.preventDefault();
                    Swal.fire('Error', 'Debe ingresar un monto en divisa válido para el pago parcial.', 'error');
                    return;
                }
            }

            // Mostrar loader
            Swal.fire({
                title: 'Procesando...',
                text: 'Por favor espere mientras registramos su pago.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        });
    });
</script>
@endsection