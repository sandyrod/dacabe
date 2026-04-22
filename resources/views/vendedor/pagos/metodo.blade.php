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

    /* Estilos para opciones de retención elegantes */
    .retention-option {
        position: relative;
        margin-bottom: 8px;
    }

    .retention-radio {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }

    .retention-label {
        display: block;
        padding: 12px 16px;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background: #fff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .retention-label:hover {
        border-color: #4361ee;
        box-shadow: 0 4px 16px rgba(67, 97, 238, 0.15);
        transform: translateY(-1px);
    }

    .retention-radio:checked + .retention-label {
        border-color: #4361ee;
        background: linear-gradient(135deg, #f8f9ff 0%, #e7f1ff 100%);
        box-shadow: 0 4px 20px rgba(67, 97, 238, 0.2);
    }

    .retention-content {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .retention-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #4361ee 0%, #3f37c9 100%);
        color: white;
        font-size: 16px;
        transition: all 0.3s ease;
        flex-shrink: 0;
    }

    .retention-radio:checked + .retention-label .retention-icon {
        background: linear-gradient(135deg, #4361ee 0%, #7209b7 100%);
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
    }

    .retention-text {
        flex: 1;
    }

    .retention-text strong {
        color: #2d3436;
        font-size: 14px;
        font-weight: 600;
        display: block;
        margin-bottom: 2px;
    }

    .retention-text small {
        color: #6c757d;
        font-size: 12px;
        line-height: 1.4;
    }

    .retention-radio:checked + .retention-label .retention-text strong {
        color: #4361ee;
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

    /* Card de ajustes entre resumen de pedidos y totales */
    .ajustes-resumen-card {
        border-radius: 14px;
        overflow: hidden;
        border: 1px solid rgba(99, 102, 241, 0.25) !important;
        background: linear-gradient(135deg, #eef2ff 0%, #f8fafc 100%);
    }

    .ajustes-resumen-head {
        background: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%);
        color: #fff;
        padding: 12px 14px;
    }

    .ajustes-resumen-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        padding: 8px 0;
        border-bottom: 1px dashed rgba(100, 116, 139, 0.25);
    }

    .ajustes-resumen-item:last-child {
        border-bottom: none;
    }

    .ajustes-resumen-totales {
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid rgba(99, 102, 241, 0.2);
    }

    @media (max-width: 575.98px) {
        .ajustes-resumen-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
        }
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
                                                    <th class="text-end pe-3 text-muted">Saldo</th>
                                                    <th class="text-center pe-3 text-muted">Descuento</th>
                                                    <th class="text-center pe-3 text-muted">Desc. Adicional</th>
                                                    <th class="text-end pe-3 text-muted">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody class="small">
                                                @php $detalle_pedidos = []; @endphp
                                                @php $totalDescuento = 0; @endphp
                                                @php $pedidosIds = collect($pedidosSeleccionados)->pluck('id')->toArray(); @endphp

                                                @php $totalPagarDivisa = 0; @endphp
                                                @php $totalPagar = 0; @endphp
                                                @php $totalPagarBs = 0; @endphp
                                                @php $iva_bs = 0; @endphp
                                                @php $totalIva = 0; @endphp
                                                @php $totalRetencion = 0; @endphp
                                                @php $porc_retencion = 0; @endphp
                                                @php $totalAjustesNetos = 0; @endphp
                                                @php $totalAjustes = 0; @endphp
                                                @foreach ($pedidosSeleccionados as $index => $pedido)
                                                @php
                                                $basePedido = (float) ($pedido->base ?? $pedido->total ?? 0);
                                                $saldoBasePedido = (float) ($pedido->saldo_base ?? 0);
                                                $aplicaDescuentoDivisa = abs($basePedido - $saldoBasePedido) <= 0.01;

                                                // Obtener ajustes netos del pedido
                                                $ajustesNetoPedido = isset($pedido->ajustes_neto) ? (float) $pedido->ajustes_neto : 0;
                                                $totalAjustesNetos += $ajustesNetoPedido;

                                                // Sumar total_ajustes del pedido
                                                $totalAjustesPedido = isset($pedido->total_ajustes) ? (float) $pedido->total_ajustes : 0;
                                                $totalAjustes += $totalAjustesPedido;

                                                $descuento =
                                                $aplicaDescuentoDivisa &&
                                                isset($pedido->descuento_aplicado) &&
                                                $pedido->descuento_aplicado
                                                ? $pedido->monto_descuento
                                                : 0;
                                                $totalDescuento += $descuento;
                                                $saldoConDescuento = $pedido->saldo_con_ajustes - $descuento;
                                                $descuento_div =
                                                $aplicaDescuentoDivisa && abs($pedido->descuento_pedido) > 0
                                                ? $saldoConDescuento *
                                                (abs($pedido->descuento_pedido) / 100)
                                                : 0;
                                                $totalDescuento += $descuento_div;
                                                
                                                // Incluir ajustes en el total a pagar en divisa
                                                $totalConAjustes = ($saldoConDescuento - $descuento_div);
                                                $totalPagarDivisa += $totalConAjustes;
                                                $totalPagar = ($pedido->saldo_con_ajustes) - $descuento;
                                                $totalPagarBs += $pedido->saldo_con_ajustes;
                                                //$totalIva += $pedido->iva ?? 0;
                                                $iva_bs += $pedido->saldo_iva_bs;
                                                $descuentoPedido = $pedido->descuento_pedido ?? 0;
                                                $monto_con_descuento = $saldoConDescuento;
                                                $nuevo_saldo =
                                                $monto_con_descuento -
                                                ($monto_con_descuento * abs($descuentoPedido)) / 100;
                                                $totalIva +=
                                                $pedido->iva > 0
                                                ? ($nuevo_saldo * 16) / 100
                                                : 0;
                                                $totalRetencion +=
                                                ($pedido->retencion && $pedido->saldo_iva_bs > 0)
                                                ? $pedido->retencion
                                                : 0;
                                                $porc_retencion =
                                                $pedido->porc_retencion
                                                ? ($totalIva * $pedido->porc_retencion) / 100
                                                : 0;
                                                $porc_descuento =
                                                $aplicaDescuentoDivisa ? (abs($descuentoPedido) + $pedido->porcentaje_descuento) : 0;
                                                $detalle_pedidos[] = [
                                                'pedido_id' => $pedido->id,
                                                'descuento' => $descuento + $descuento_div,
                                                'porc_descuento' => $porc_descuento,
                                                'iva' =>
                                                $pedido->iva > 0
                                                ? ($nuevo_saldo * 16) / 100
                                                : 0,
                                                'retencion' =>
                                                $pedido->retencion
                                                ? $pedido->retencion
                                                : 0,
                                                'saldo' => $saldoConDescuento,
                                                'ajustes_neto' => $ajustesNetoPedido,
                                                ];
                                                @endphp
                                                <tr class="border-bottom pedido-fila-new"
                                                    data-pedido-id="{{ $pedido->id }}"
                                                    data-monto-original="{{ $pedido->saldo_pendiente }}"
                                                    data-porcentaje-descuento="{{ $aplicaDescuentoDivisa ? ($pedido->porcentaje_descuento ?? 0) : 0 }}"
                                                    data-monto-descuento="{{ $descuento }}"
                                                    data-monto-con-descuento="{{ $saldoConDescuento }}"
                                                    data-descuento-pedido="{{ $aplicaDescuentoDivisa ? ($pedido->descuento_pedido ?? 0) : 0 }}"
                                                    data-iva="{{ $pedido->factura != 'NO' ? $pedido->iva : 0 }}"
                                                    data-retencion="{{ $pedido->retencion ? $pedido->retencion : 0 }}"
                                                    data-porc_retencion="{{ $pedido->porc_retencion ? $pedido->porc_retencion : 0 }}">
                                                    <td class="py-2">
                                                        <div class="text-dark">#{{ $pedido->id }}</div>
                                                        <small class="text-muted"><i
                                                                class="far fa-calendar-alt me-1"></i>{{ $pedido->fecha_formateada }}</small>
                                                        @if(!empty($pedido->factura_numero))
                                                            <div class="small text-success mt-1">
                                                                <i class="fas fa-file-invoice me-1"></i>Factura: {{ $pedido->factura_numero }}
                                                            </div>
                                                        @endif
                                                        
                                                        <!-- Mostrar detalles de ajustes si existen -->
                                                        @if(isset($pedido->ajustes_detalle) && count($pedido->ajustes_detalle) > 0)
                                                            <div class="mt-2">
                                                                @foreach($pedido->ajustes_detalle as $ajuste)
                                                                    <div class="small text-muted d-flex align-items-center mb-1">
                                                                        <i class="fas fa-{{ $ajuste['tipo'] === 'cargo' ? 'plus-circle text-danger' : 'minus-circle text-success' }} me-1" style="font-size: 0.6rem;"></i>
                                                                        <span>{{ $ajuste['concepto'] }}: 
                                                                            <span class="fw-bold {{ $ajuste['tipo'] === 'cargo' ? 'text-danger' : 'text-success' }}">
                                                                                {{ $ajuste['tipo'] === 'cargo' ? '+' : '-' }}{{ number_format($ajuste['monto'], 2, ',', '.') }}$
                                                                            </span>
                                                                        </span>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td class="text-end pe-3 monto-original"
                                                        data-monto="{{ $pedido->saldo_con_ajustes }}">
                                                        {{ number_format($pedido->saldo_con_ajustes, 2, ',', '.') }}
                                                        $
                                                        <input type="hidden" class="monto-original-hidden"
                                                            value="{{ $pedido->saldo_con_ajustes }}">
                                                    </td>
                                                    <td
                                                        class="text-center pe-3 {{ $descuento > 0 ? 'text-success' : 'text-muted' }}">
                                                        <div class="descuento-aplicado">
                                                            @if ($descuento > 0)
                                                            <span
                                                                class="badge bg-success">{{ number_format($aplicaDescuentoDivisa ? ($pedido->porcentaje_descuento ?? 0) : 0, 2) }}%</span>
                                                            @else
                                                            <span class="badge bg-secondary">0%</span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td class="text-center pe-3">
                                                        <div class="descuento-adicional">
                                                            @if ($aplicaDescuentoDivisa && isset($pedido->descuento_pedido) && $pedido->descuento_pedido < 0)
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
                                                <input type="hidden" name="saldo_iva_total" value="{{ $iva_bs }}">
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
                                                                        <h6 class="mb-0 fw-light">BASE</h6>
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
                                                                        <!-- Total Ajustes: usa saldo_ajustes (DB) y ajustes_neto (netoPendiente) como fuentes -->
                                                                        @php
                                                                            $totalAjustesFooter = collect($pedidosSeleccionados)->sum(function($p) {
                                                                                $fromNeto  = (float) ($p->ajustes_neto ?? 0);
                                                                                $fromSaldo = (float) ($p->saldo_ajustes ?? 0);
                                                                                // Preferir ajustes_neto si es distinto de cero, sino saldo_ajustes del pedido
                                                                                return abs($fromNeto) > 0.001 ? $fromNeto : $fromSaldo;
                                                                            });
                                                                            // DEBUG TEMPORAL — eliminar después de confirmar que funciona
                                                                            $debugAjustes = collect($pedidosSeleccionados)->map(fn($p) => [
                                                                                'id' => $p->id,
                                                                                'ajustes_neto' => $p->ajustes_neto ?? 'N/A',
                                                                                'saldo_ajustes' => $p->saldo_ajustes ?? 'N/A',
                                                                            ]);
                                                                        @endphp
                                                                        @if(abs($totalAjustesFooter) > 0.001)
                                                                        <div class="mt-1" style="border-top: 1px solid rgba(255,255,255,0.2); padding-top: 4px;">
                                                                            <small class="d-block text-white-50">Ajuste adicional:</small>
                                                                            <small class="fw-bold" style="font-size: 0.82rem; color: {{ $totalAjustesFooter >= 0 ? '#7ee8a2' : '#f9a8d4' }};">
                                                                                {{ $totalAjustesFooter >= 0 ? '+' : '' }}{{ number_format($totalAjustesFooter, 2, ',', '.') }} $
                                                                            </small>
                                                                        </div>
                                                                        @endif
                                                                    </div>
                                                                    <!-- Total sin Dcto. -->
                                                                    <div class="col-6 col-md-3 mb-3 mb-md-0">
                                                                        <small class="d-block text-white-50">Total sin Dcto.:</small>
                                                                        <div
                                                                            class="d-flex align-items-center justify-content-start">
                                                                            <s class="text-white-50 me-1"
                                                                                id="monto-total">{{ number_format($pedido->saldo_pendiente, 2, ',', '.') }}</s>
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
                                                                            class="d-block text-white-50">IVA Bs.:</small>
                                                                        <div
                                                                            class="d-flex align-items-center justify-content-start">
                                                                            <span class="text-info fw-bold me-1"
                                                                                id="total-iva">{{ number_format($iva_bs, 2, ',', '.') }}</span>
                                                                            <span class="text-info">Bs.</span>
                                                                        </div>
                                                                    </div>
                                                                    <!-- Retención -->
                                                                    <div class="col-6 col-md-2">
                                                                        <small
                                                                            class="d-block text-white-50">Retención:</small>
                                                                        <div
                                                                            class="d-flex align-items-center justify-content-start">
                                                                            <span class="text-warning fw-bold me-1"
                                                                                id="total-retencion">{{ number_format($totalRetencion, 2, ',', '.') }}</span>
                                                                            <span class="text-warning">Bs.</span>
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

                            <form action="{{ route('vendedores.pagos.index') }}" method="POST" id="form-pago" novalidate>
                                @csrf
                                @php
                                    $basePagarDivisa = (float) ($totalPagarDivisa - $totalDescuento);
                                    $totalIvaBsVista = (float) ($iva_bs ?? 0);
                                    $soloIvaEnBs = $basePagarDivisa <= 0.01 && $totalIvaBsVista > 0.01;
                                @endphp
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
                                    value="{{ $totalPagarDivisa - $totalDescuento }}">
                                <input type="hidden" name="total_ajustes_netos" value="{{ $totalAjustesNetos }}">
                                <input type="hidden" name="base_real" value="{{ $totalPagarDivisa - $totalDescuento }}">
                                <input type="hidden" name="monto_total_bs" id="monto-total-bs" value="">

                                <!-- Moneda de Pago -->
                                <div class="mb-4">
                                    @if($soloIvaEnBs)
                                    <div class="alert alert-info mb-3" role="alert">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Este pago corresponde solo a IVA en bolívares. La moneda de pago se limita a <strong>Bolívares (Bs.)</strong>.
                                    </div>
                                    @endif
                                    <div class="d-flex flex-column gap-2">
                                        <!-- Opción Divisa Total -->
                                        <div class="payment-method-option">
                                            <input type="radio" name="tipo_pago" id="pago-total"
                                                value="divisa_total" class="d-none" {{ !$soloIvaEnBs ? 'checked' : '' }} {{ $soloIvaEnBs ? 'disabled' : '' }}>
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
                                        <div class="payment-method-option d-none">
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
                                                class="d-none" {{ $soloIvaEnBs ? 'checked' : '' }}>
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
                                                    step="1" max="{{ floor($totalPagarBs) }}"
                                                    value="{{ floor($totalPagarBs) }}" style="font-weight: 500;">
                                            </div>
                                            <small class="text-muted">Monto máximo: {{ floor($totalPagarBs) }} $ (solo
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
                                                <label for="tasa_bcv" class="form-label fw-medium">Tasa BCV (Bs. por $)</label>
                                                <input type="number" class="form-control form-control-lg" readonly
                                                    id="tasa_bcv" name="tasa_bcv" min="0.0001" step="0.0001"
                                                    value="{{ request('tasa_cambio', 0) }}"
                                                    style="font-weight: 500;">
                                            </div>
                                            <!-- Card cliente + pedidos seleccionados (solo Bolívares) -->
                                            <div class="card border-0 shadow-sm mb-3" style="border-radius: 14px; overflow: hidden;">
                                                <div style="background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%); padding: 14px 18px; display: flex; align-items: center; gap: 12px;">
                                                    <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                        <i class="fas fa-user-circle" style="color: white; font-size: 18px;"></i>
                                                    </div>
                                                    <div>
                                                        <div style="font-size: 13px; font-weight: 700; color: white; line-height: 1.2;">{{ $cliente->NOMBRE }}</div>
                                                        <div style="font-size: 11px; color: rgba(255,255,255,0.7); margin-top: 2px;">RIF: {{ $cliente->RIF }}</div>
                                                    </div>
                                                    <div class="ms-auto">
                                                        <span style="background: rgba(255,255,255,0.15); color: white; font-size: 10px; font-weight: 600; padding: 3px 10px; border-radius: 20px; letter-spacing: 0.5px;">
                                                            {{ $pedidosSeleccionados->count() }} {{ $pedidosSeleccionados->count() == 1 ? 'PEDIDO' : 'PEDIDOS' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div style="background: white; padding: 0;">
                                                    @foreach ($pedidosSeleccionados as $pedSel)
                                                    @php
                                                        $baseSel   = (float) ($pedSel->saldo_base ?? 0);
                                                        $ivaSel    = (float) ($pedSel->saldo_iva_bs ?? 0);
                                                        $ajustesSel = (float) ($pedSel->ajustes_neto ?? 0);
                                                    @endphp
                                                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 18px; border-bottom: 1px solid #f1f5f9; gap: 12px;">
                                                        <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                                                            <div style="width: 30px; height: 30px; border-radius: 8px; background: #eff6ff; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                                <i class="fas fa-file-invoice" style="color: #3b82f6; font-size: 12px;"></i>
                                                            </div>
                                                            <div style="min-width: 0;">
                                                                <div style="font-size: 12px; font-weight: 600; color: #1e293b;">Pedido #{{ $pedSel->id }}</div>
                                                                <div style="font-size: 10px; color: #94a3b8;">{{ $pedSel->fecha_formateada ?? \Carbon\Carbon::parse($pedSel->fecha)->format('d/m/Y') }}</div>
                                                                @if(!empty($pedSel->factura_numero))
                                                                <div style="font-size: 10px; color: #16a34a; font-weight: 600;">Factura: {{ $pedSel->factura_numero }}</div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div style="display: flex; gap: 16px; flex-shrink: 0; text-align: right;">
                                                            <div>
                                                                <div style="font-size: 9px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">Base</div>
                                                                <div style="font-size: 12px; font-weight: 600; color: #1e293b;">{{ number_format($baseSel, 2, ',', '.') }} $</div>
                                                            </div>
                                                            @if ($ivaSel > 0)
                                                            <div>
                                                                <div style="font-size: 9px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">IVA Bs.</div>
                                                                <div style="font-size: 12px; font-weight: 600; color: #f59e0b;">{{ number_format($ivaSel, 2, ',', '.') }}</div>
                                                            </div>
                                                            @endif
                                                            @if ($ajustesSel != 0)
                                                            <div>
                                                                <div style="font-size: 9px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">Ajuste</div>
                                                                <div style="font-size: 12px; font-weight: 600; color: {{ $ajustesSel > 0 ? '#ef4444' : '#10b981' }};">{{ number_format($ajustesSel, 2, ',', '.') }} $</div>
                                                            </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>

                                            @php
                                                $ajustesListado = [];
                                                $totalCargosAjustes = 0.0;
                                                $totalDescuentosAjustes = 0.0;
                                                $tasaAjustes = (float) request('tasa_cambio', $tasaSugerida ?? 0);

                                                foreach ($pedidosSeleccionados as $pedAju) {
                                                    $detallesAjuste = isset($pedAju->ajustes_detalle) && is_array($pedAju->ajustes_detalle)
                                                        ? $pedAju->ajustes_detalle
                                                        : [];

                                                    if (!empty($detallesAjuste)) {
                                                        foreach ($detallesAjuste as $ajuste) {
                                                            $montoAjuste = (float) ($ajuste['monto'] ?? 0);
                                                            $tipoAjuste = strtolower((string) ($ajuste['tipo'] ?? ''));
                                                            $esCargo = in_array($tipoAjuste, ['cargo', 'debito', 'débito']);

                                                            if ($esCargo) {
                                                                $totalCargosAjustes += $montoAjuste;
                                                            } else {
                                                                $totalDescuentosAjustes += $montoAjuste;
                                                            }

                                                            $ajustesListado[] = [
                                                                'pedido_id' => $pedAju->id,
                                                                'concepto' => $ajuste['concepto'] ?? 'Ajuste aplicado',
                                                                'monto' => $montoAjuste,
                                                                'es_cargo' => $esCargo,
                                                            ];
                                                        }
                                                    } else {
                                                        $ajusteNeto = (float) ($pedAju->ajustes_neto ?? 0);
                                                        if (abs($ajusteNeto) > 0.001) {
                                                            if ($ajusteNeto >= 0) {
                                                                $totalCargosAjustes += $ajusteNeto;
                                                            } else {
                                                                $totalDescuentosAjustes += abs($ajusteNeto);
                                                            }

                                                            $ajustesListado[] = [
                                                                'pedido_id' => $pedAju->id,
                                                                'concepto' => 'Ajuste neto del pedido',
                                                                'monto' => abs($ajusteNeto),
                                                                'es_cargo' => $ajusteNeto >= 0,
                                                            ];
                                                        }
                                                    }
                                                }

                                                $netoAjustes = $totalCargosAjustes - $totalDescuentosAjustes;
                                                $totalCargosAjustesBs = $totalCargosAjustes * $tasaAjustes;
                                                $totalDescuentosAjustesBs = $totalDescuentosAjustes * $tasaAjustes;
                                                $netoAjustesBs = $netoAjustes * $tasaAjustes;
                                            @endphp

                                            <div class="card border-0 shadow-sm mb-3 ajustes-resumen-card">
                                                <div class="ajustes-resumen-head d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-sliders-h me-2"></i>
                                                        <strong style="font-size: 13px; letter-spacing: 0.4px;">AJUSTES APLICADOS</strong>
                                                    </div>
                                                    <span class="badge bg-light text-dark" style="font-size: 10px;">
                                                        {{ count($ajustesListado) }} {{ count($ajustesListado) === 1 ? 'registro' : 'registros' }}
                                                    </span>
                                                </div>
                                                <div class="card-body py-2 px-3">
                                                    @if(count($ajustesListado) > 0)
                                                        <div style="max-height: 185px; overflow-y: auto;">
                                                            @foreach($ajustesListado as $ajItem)
                                                                @php
                                                                    $montoAjusteBs = $ajItem['monto'] * $tasaAjustes;
                                                                @endphp
                                                                <div class="ajustes-resumen-item">
                                                                    <div style="min-width: 0;">
                                                                        <div style="font-size: 12px; font-weight: 700; color: #1e293b; line-height: 1.2;">
                                                                            Pedido #{{ $ajItem['pedido_id'] }}
                                                                        </div>
                                                                        <div style="font-size: 11px; color: #64748b; line-height: 1.25;">
                                                                            {{ $ajItem['concepto'] }}
                                                                        </div>
                                                                    </div>
                                                                    <span class="badge {{ $ajItem['es_cargo'] ? 'bg-danger-subtle text-danger-emphasis' : 'bg-success-subtle text-success-emphasis' }}" style="font-size: 11px;">
                                                                        {{ $ajItem['es_cargo'] ? '+' : '-' }}{{ number_format($ajItem['monto'], 2, ',', '.') }} $ 
                                                                        | {{ $ajItem['es_cargo'] ? '+' : '-' }}{{ number_format($montoAjusteBs, 2, ',', '.') }} Bs.
                                                                    </span>
                                                                </div>
                                                            @endforeach
                                                        </div>

                                                        <div class="ajustes-resumen-totales">
                                                            <div class="d-flex justify-content-between align-items-center small mb-1">
                                                                <span class="text-muted">Cargos</span>
                                                                <span class="fw-semibold text-danger">+{{ number_format($totalCargosAjustes, 2, ',', '.') }} $ | +{{ number_format($totalCargosAjustesBs, 2, ',', '.') }} Bs.</span>
                                                            </div>
                                                            <div class="d-flex justify-content-between align-items-center small mb-1">
                                                                <span class="text-muted">Descuentos</span>
                                                                <span class="fw-semibold text-success">-{{ number_format($totalDescuentosAjustes, 2, ',', '.') }} $ | -{{ number_format($totalDescuentosAjustesBs, 2, ',', '.') }} Bs.</span>
                                                            </div>
                                                            <div class="d-flex justify-content-between align-items-center" style="font-size: 13px;">
                                                                <span class="fw-bold" style="color: #334155;">Neto ajustes</span>
                                                                <span class="fw-bold" style="color: {{ $netoAjustes >= 0 ? '#dc2626' : '#059669' }};">
                                                                    {{ $netoAjustes >= 0 ? '+' : '' }}{{ number_format($netoAjustes, 2, ',', '.') }} $ 
                                                                    | {{ $netoAjustes >= 0 ? '+' : '' }}{{ number_format($netoAjustesBs, 2, ',', '.') }} Bs.
                                                                </span>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="text-center py-3">
                                                            <div style="font-size: 12px; color: #64748b;">
                                                                <i class="fas fa-check-circle text-success me-1"></i>
                                                                No hay cargos o descuentos adicionales en los pedidos seleccionados.
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
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
                                                                            id="subtotal_dolares">{{ number_format($totalPagarBs, 2, ',', '.') }}
                                                                            $</span>
                                                                        <span id="subtotal_bolivares2"
                                                                            class="d-none">{{ number_format(($totalPagarBs) * 1, 2, ',', '.') }}
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
                                                                            id="base_dolares">{{ number_format($totalPagarBs, 2, ',', '.') }}
                                                                            $</span>
                                                                        <span id="base_bolivares"
                                                                            class="d-none">{{ number_format(($totalPagarBs) * 1, 2, ',', '.') }}
                                                                            Bs.</span>
                                                                    </div>
                                                                </div>
                                                                <div class="col-6 col-md-3 mb-2 mb-md-0">
                                                                    <div class="fw-semibold small">Impuesto (16%)</div>
                                                                    <div class="fw-bold small">
                                                                        <span
                                                                            id="impuesto_dolares">{{ number_format($iva_bs ?? 0, 2, ',', '.') }}
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
                                                                        {{ number_format($totalRetencion ?? 0, 2, ',', '.') }}
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

                        {{-- ── IVA en Divisa (solo para divisa_total y cuando hay saldo_iva_bs) ── --}}
                        @php
                            $tasaParaIva = (float) request('tasa_cambio', $tasaSugerida ?? 1);
                            $ivaEnDivisaUsd = ($tasaParaIva > 0 && $iva_bs > 0)
                                ? round($iva_bs / $tasaParaIva, 2)
                                : 0;
                            $retencionEnDivisaUsd = ($tasaParaIva > 0 && $totalRetencion > 0)
                                ? round($totalRetencion / $tasaParaIva, 2)
                                : 0;
                            $ivaNetoEnDivisaUsd = max($ivaEnDivisaUsd - $retencionEnDivisaUsd, 0);
                        @endphp
                        @if($iva_bs > 0)
                        <div id="bloque-iva-divisa" class="mt-3" style="display:none;">
                            {{-- Tarjeta seleccionable: clic en cualquier parte la activa --}}
                            <div id="iva-divisa-card"
                                 style="background:linear-gradient(135deg,#e0f2fe 0%,#bae6fd 100%);border-radius:14px;padding:20px 22px;border:2px solid #0ea5e9;cursor:pointer;transition:border-color .2s,box-shadow .2s;"
                                 title="Haz clic para incluir/excluir el IVA en este pago">
                                {{-- Fila principal: ícono, título, badge, toggle --}}
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-percentage text-info" style="font-size:20px;"></i>
                                    <div class="flex-grow-1 ml-3">
                                        <h6 class="mb-0 fw-bold" style="color:#075985;">¿Pagar IVA en divisa?</h6>
                                        <small class="text-muted">
                                            Equivalente: ${{ number_format($ivaEnDivisaUsd,2,',','.') }}
                                            (Bs.{{ number_format($iva_bs,2,',','.') }} ÷ {{ number_format($tasaParaIva,2,',','.') }})
                                        </small>
                                    </div>
                                    <span class="badge bg-info me-2">IVA Bs. {{ number_format($iva_bs,2,',','.') }}</span>
                                    {{-- Toggle visual --}}
                                    <div id="iva-divisa-toggle-icon" style="width:36px;height:20px;border-radius:10px;background:#94a3b8;transition:background .2s;position:relative;flex-shrink:0;">
                                        <div id="iva-divisa-toggle-dot" style="width:16px;height:16px;border-radius:50%;background:#fff;position:absolute;top:2px;left:2px;transition:left .2s;"></div>
                                    </div>
                                </div>
                                {{-- checkbox real oculto --}}
                                <input class="d-none" type="checkbox" id="iva_en_divisa_check" name="iva_en_divisa" value="1">
                                {{-- Opciones de retención (solo visibles cuando está activo) --}}
                                @if($totalRetencion > 0)
                                <div id="iva-divisa-opciones" class="d-none ps-2 pt-3" onclick="event.stopPropagation()">
                                    <label class="d-block mb-2 fw-semibold text-secondary" style="font-size:13px;">Opción de retención:</label>
                                    <div class="d-flex gap-3 flex-wrap">
                                        <div class="retention-option">
                                            <input type="radio" name="opcion_iva_divisa" id="retencion-iva" value="retencion" checked class="retention-radio">
                                            <label for="retencion-iva" class="retention-label">
                                                <div class="retention-content">
                                                    <div class="retention-icon">
                                                        <i class="fas fa-percentage"></i>
                                                    </div>
                                                    <div class="retention-text">
                                                        <strong>Aplicar retención</strong>
                                                        <small class="d-block text-muted">Pagar ${{ number_format($ivaNetoEnDivisaUsd,2,',','.') }} (IVA neto)</small>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="retention-option ml-3">
                                            <input type="radio" name="opcion_iva_divisa" id="completo-iva" value="completo" class="retention-radio">
                                            <label for="completo-iva" class="retention-label">
                                                <div class="retention-content">
                                                    <div class="retention-icon">
                                                        <i class="fas fa-coins"></i>
                                                    </div>
                                                    <div class="retention-text">
                                                        <strong>Pagar IVA completo</strong>
                                                        <small class="d-block text-muted">Pagar ${{ number_format($ivaEnDivisaUsd,2,',','.') }}</small>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                @else
                                <input type="hidden" name="opcion_iva_divisa" value="completo">
                                @endif
                                {{-- Campos ocultos --}}
                                <input type="hidden" id="iva_en_divisa_usd" value="{{ $ivaEnDivisaUsd }}">
                                <input type="hidden" id="iva_bs_total" value="{{ $iva_bs }}">
                                <input type="hidden" id="iva_neto_divisa_usd" value="{{ $ivaNetoEnDivisaUsd }}">
                            </div>
                        </div>
                        @endif

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
            /*
            if ($('input[name="tipo_pago"]:checked').val() !== 'divisa_parcial') return;

            const montoDivisa = parseFloat($('#monto_divisa').val()) || 0;
            const tasaBcv = parseFloat($('#tasa_bcv').val()) || 0;
            const totalPagar = parseFloat('{{ $totalPagar }}') || 0;

            // Validar que el monto en divisa no sea mayor al total a pagar
            if (montoDivisa > totalPagar) {
                //$('#monto_divisa').val(totalPagar.toFixed(2));
                return;
            }

            // Validar que los valores sean números válidos
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
            const iva = montoBolivares * 0.16;

            // Calcular retención (75% del IVA)
            const retencion = iva * 0.75;

            // Obtener el porcentaje de descuento (si existe)
            const descuentoPorcentaje = parseFloat('{{ $descuentoProntoPago ?? 0 }}') || 0;
            const descuento = montoBolivares * (descuentoPorcentaje / 100);

            // Calcular total
            const total = montoBolivares + iva - retencion - descuento;

            // Actualizar la interfaz
            $('#monto-bolivares-parcial').text(formatoMoneda(montoBolivares) + ' Bs.');
            $('#iva-bolivares-parcial').text(formatoMoneda(iva) + ' Bs.');
            $('#retencion-bolivares-parcial').text('-' + formatoMoneda(retencion) + ' Bs.');
            $('#descuento-bolivares-parcial').text('-' + formatoMoneda(descuento) + ' Bs.');
            $('#total-bolivares-parcial').text(formatoMoneda(total) + ' Bs.');

            // Actualizar campos ocultos
            $('#monto-bolivares-parcial-input').val(montoBolivares.toFixed(2));
            $('#iva-bolivares-parcial-input').val(iva.toFixed(2));
            $('#retencion-bolivares-parcial-input').val(retencion.toFixed(2));
            $('#descuento-bolivares-parcial-input').val(descuento.toFixed(2));
            $('#total-bolivares-parcial-input').val(total.toFixed(2));

            // Mostrar el resumen
            $('#resumen-divisa-parcial').removeClass('d-none');
            */
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
            //$(document).on('input', '#monto_divisa, #tasa_bcv', function() {
            $(document).on('input', '#tasa_bcv', function() {
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
            $('.pedido-fila').each(function() {
                const $fila = $(this);
                const montoTexto = $fila.find('.monto-pedido').text().replace(/\./g, '').replace(',', '.');
                const monto = parseFloat(montoTexto) || 0;

                // Guardar monto original sin descuentos
                $fila.data('monto-original', monto);
                $fila.data('monto', monto);

                // Inicializar descuento
                let descuento = 0;
                const $descuentoBadge = $fila.find('.badge.bg-success');
                if ($descuentoBadge.length) {
                    const descuentoTexto = $descuentoBadge.text().replace('%', '').trim();
                    descuento = parseFloat(descuentoTexto) || 0;
                }

                // Guardar descuento original
                $fila.data('descuento-original', descuento);
                $fila.data('descuento', descuento);

                // Aplicar descuento inicial si corresponde
                if (descuento > 0) {
                    const montoConDescuento = monto * (1 - (descuento / 100));
                    $fila.find('.monto-pedido').text(montoConDescuento.toFixed(2).replace(/\./g, ','));
                    $fila.data('monto', montoConDescuento);
                }

                // Asegurar que la columna de descuento muestre el valor correcto
                $fila.find('.descuento-pedido').text(descuento.toFixed(2) + '%');
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
                /*
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
                */
            }

            // Ocultar todos los campos primero
            $('#campo-monto-divisa, #campo-tasa-bcv').addClass('d-none');

            // Mostrar el campo correspondiente
            if (tipoPago === 'divisa_parcial') {
                /*
                $('#campo-monto-divisa').removeClass('d-none').addClass('animate__animated animate__fadeIn');
                $('#monto_divisa').prop('required', true);
                restaurarMontosOriginales();
                */
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
            //$('#monto_divisa').attr('max', Math.floor(totalConDescuento));

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
            /*
            const maxMonto = Math.floor(totalPagarBs);
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
            */
        }

        // Calcular divisa parcial (parte en $ y parte en Bs.)
        function calcularDivisaParcial() {
            /*
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
            */
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

            // Convertir a bolívares aplicando la tasa
            // Usar variables de PHP para Base y iva desde el backend
            const totalPagarBs = parseFloat("{{ $totalPagarBs ?? 0 }}") || 0;
            const iva_bs = parseFloat("{{ $iva_bs ?? 0 }}") || 0;

            const baseBolivares = totalPagarBs * tasa;
            let ivaBolivares = iva_bs;

            let retencionBolivares = 0;
            $('.pedido-fila-new').each(function() {
                const $fila = $(this);
                const iva = parseFloat($fila.attr('data-iva')) || 0;

                // si tiene retencion acumula el valor para retencionBolivares
                const retencion = parseFloat($fila.attr('data-retencion')) || 0;
                if (retencion > 0 && iva > 0) {
                    let porc_retenc2 = (retencion * 100) / iva;
                    // Aquí asumiendo que el IVA bolívares de la fila proporcional se puede calcular
                    // o usamos retencionBolivares total
                    //retencionBolivares += (ivaBolivares * porc_retenc2 / 100);
                }
            });
            retencionBolivares = parseFloat("{{ $totalRetencion ?? 0 }}") || 0;


            // Verificar o calcular el total
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

                $('#total_iva').val(ivaBolivares);

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
            //$('#total-iva').html(ivaDolares.toFixed(2));

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
            //$('#retencion_bolivares').text(retencionBolivaresFormateada + ' Bs.');

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


            $('#monto-total-con-descuento').html((totalPagarBs).toFixed(2));
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

        // ── IVA en Divisa: mostrar/ocultar bloque y actualizar total ────────────
        const SOLO_IVA_BS = {{ $soloIvaEnBs ? 'true' : 'false' }};
        const ivaEnDivisaUsdBase = parseFloat($('#iva_en_divisa_usd').val()) || 0;
        const ivaNetoEnDivisaUsdBase = parseFloat($('#iva_neto_divisa_usd').val()) || 0;
        const baseTotalPagarDivisa = parseFloat('{{ $totalPagarDivisa }}') || 0;

        function toggleBloqueIvaDivisa() {
            const tipo = $('input[name="tipo_pago"]:checked').val();
            if (SOLO_IVA_BS) {
                $('#bloque-iva-divisa').hide();
                $('#iva_en_divisa_check').prop('checked', false);
                return;
            }
            if (tipo === 'divisa_total') {
                //$('#bloque-iva-divisa').show();
            } else {
                $('#bloque-iva-divisa').hide();
                $('#iva_en_divisa_check').prop('checked', false);
            }
            actualizarTotalConIvaDivisa();
        }

        function actualizarTotalConIvaDivisa() {
            const checked = $('#iva_en_divisa_check').is(':checked');
            // Actualizar toggle visual
            if (checked) {
                $('#iva-divisa-toggle-icon').css('background', '#0ea5e9');
                $('#iva-divisa-toggle-dot').css('left', '18px');
                $('#iva-divisa-card').css({'border-color':'#0284c7','box-shadow':'0 0 0 3px rgba(14,165,233,.25)'});
            } else {
                $('#iva-divisa-toggle-icon').css('background', '#94a3b8');
                $('#iva-divisa-toggle-dot').css('left', '2px');
                $('#iva-divisa-card').css({'border-color':'#0ea5e9','box-shadow':'none'});
            }
            if (!checked) {
                $('#total_pagar_divisa').val(baseTotalPagarDivisa.toFixed(2));
                $('#monto-total-con-descuento').text(baseTotalPagarDivisa.toLocaleString('es-ES',{minimumFractionDigits:2,maximumFractionDigits:2}));
                $('#iva-divisa-opciones').addClass('d-none');
                return;
            }
            $('#iva-divisa-opciones').removeClass('d-none');
            const opcion = $('input[name="opcion_iva_divisa"]:checked').val() || 'retencion';
            const ivaAdd = opcion === 'retencion' ? ivaNetoEnDivisaUsdBase : ivaEnDivisaUsdBase;
            const nuevoTotal = baseTotalPagarDivisa + ivaAdd;
            $('#total_pagar_divisa').val(nuevoTotal.toFixed(2));
            $('#monto-total-con-descuento').text(nuevoTotal.toLocaleString('es-ES',{minimumFractionDigits:2,maximumFractionDigits:2}));
        }

        // Clic en cualquier parte de la tarjeta = toggle checkbox
        $(document).on('click', '#iva-divisa-card', function(e) {
            // Ignorar clics directos en radios (opciones de retención)
            if ($(e.target).is('input[type="radio"]') || $(e.target).closest('label').find('input[type="radio"]').length) return;
            $('#iva_en_divisa_check').prop('checked', !$('#iva_en_divisa_check').is(':checked'));
            actualizarTotalConIvaDivisa();
        });

        $(document).on('change', 'input[name="tipo_pago"]', toggleBloqueIvaDivisa);
        $(document).on('change', 'input[name="opcion_iva_divisa"]', actualizarTotalConIvaDivisa);

        // Inicializar al cargar
        toggleBloqueIvaDivisa();

        // Validar formulario antes de enviar
        function validateForm() {
            /*
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
            */
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
            /*
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
            */

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
                if ($radio.is(':disabled')) {
                    if (SOLO_IVA_BS) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Solo pago en bolívares',
                            text: 'Cuando el pedido tiene solo saldo IVA en Bs., el pago se procesa únicamente en bolívares.',
                            confirmButtonColor: '#4361ee'
                        });
                    }
                    return;
                }
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

                if (SOLO_IVA_BS && tipoPago !== 'bs') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Moneda no permitida',
                        text: 'Este pago solo permite Bolívares porque el pedido tiene saldo pendiente únicamente en IVA Bs.',
                        confirmButtonColor: '#4361ee'
                    });
                    return false;
                }

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
                    // IVA en divisa
                    const ivaChecked = $('#iva_en_divisa_check').is(':checked') ? '1' : '0';
                    $('<input>').attr({type:'hidden',name:'iva_en_divisa',value:ivaChecked}).appendTo(form);
                    const opcionIvaDiv = $('input[name="opcion_iva_divisa"]:checked').val() || 'completo';
                    $('<input>').attr({type:'hidden',name:'opcion_iva_divisa',value:opcionIvaDiv}).appendTo(form);
                } else if (tipoPago === 'bs') {
                    // Para pago en bolívares, asegurarse de incluir total_iva y otros campos necesarios
                    let addFields = ['total_retencion', 'total_descuento_pago', 'total_iva'];
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

                // Para pagos en bolívares, permitir si hay IVA pendiente aunque el total base sea 0
                const ivaBsPendiente = parseFloat('{{ $iva_bs ?? 0 }}') || 0;
                if (montoTotal <= 0 && !(tipoPago === 'bs' && ivaBsPendiente > 0)) {
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
                    /*
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
                    */
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
