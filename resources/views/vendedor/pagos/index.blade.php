@extends('layouts.app')

@section('title', 'Procesar Pago')

@section('content')

@php
    $cliente = \App\Models\OrderClient::select('RIF', 'NOMBRE')->where('RIF', $clienteRif)->first();
@endphp

@section('titulo', config('app.name', 'Laravel') . ' - Pagos de Pedidos')
@section('titulo_header', 'Gestión de Pagos')
@section('subtitulo_header', 'Pendientes de Pago - ' . ($cliente->NOMBRE ?? 'Cliente'))

@section('styles')


    <style>
        .total-container,
        #resumen-pago .card-body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important;
            border: none !important;
            border-radius: 10px;
            padding: 25px 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            color: #ffffff !important;
        }

        .total-container h4 {
            color: #e0e9ff;
            font-size: 1.2rem;
            margin-bottom: 10px;
            font-weight: 500;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .card {
            display: flex;
            flex-direction: column;
            min-height: 100%;
            border-radius: 12px !important;
            overflow: hidden;
            transition: all 0.3s ease-in-out;
            transform: translateY(0);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2) !important;
        }

        .card-body {
            flex: 1;
        }

        .total-value {
            font-size: 28px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 20px;
        }

        .btn-procesar {
            background-color: #ffffff;
            color: #1e3c72;
            border: 2px solid #ffffff;
            font-weight: 600;
            padding: 10px 25px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .btn-procesar:hover {
            background-color: transparent;
            color: #ffffff;
        }

        .total-label {
            font-size: 18px;
            color: #e0e9ff;
            opacity: 0.9;
        }

        .table-active {
            background-color: rgba(0, 123, 255, 0.1) !important;
        }

        .payment-status {
            min-width: 60px;
            text-align: center;
        }
    </style>
@endsection

@section('content')
    <div class="container">
        <div class="row mb-4 g-4">
            <!-- Card 1: Información de Moneda -->
            <div class="col-md-6 d-flex">
                <div class="card border-0 shadow-lg w-100 hover-effect"
                    style="background: linear-gradient(145deg, #1a237e 0%, #283593 100%); border-radius: 12px !important;">
                    <div class="card-header bg-transparent border-0 py-3">
                        <h3 class="text-white mb-0 d-flex align-items-center"
                            style="font-weight: 600; letter-spacing: 0.5px;">
                            <i class="fas fa-money-bill-wave me-2 pr-2"></i>
                            INFORMACIÓN DE PAGO
                        </h3>
                    </div>
                    <div class="card-body p-4 d-flex flex-column" style="padding: 1.5rem !important;">
                        <div class="d-flex align-items-center flex-grow-1">
                            <div class="flex-grow-1">
                                <div class="d-flex flex-column">
                                    <div class="mb-2 d-flex align-items-center">
                                        <i class="fas fa-user text-white-50" style="width: 24px; text-align: center;"></i>
                                        <span
                                            class="text-white ms-2">{{ $cliente->NOMBRE ?? 'Cliente no seleccionado' }}</span>
                                    </div>
                                    <div class="mb-2 d-flex align-items-center">
                                        <i class="fas fa-shopping-cart text-white-50"
                                            style="width: 24px; text-align: center;"></i>
                                        <span class="text-white">
                                            <span id="pedidos-seleccionados" class="fw-bold"> Pedidos seleccionados:
                                                {{ $pedidos_seleccionados }}</span>
                                        </span>
                                    </div>
                                    @if(isset($detallePedidos) && !empty($detallePedidos))
                                        @php 
                                            $detallesArray = json_decode($detallePedidos, true);
                                            $tieneAjustes = false;
                                            $totalAjustesMostrar = 0;
                                            $ajustesPorPedido = [];
                                            
                                            if(is_array($detallesArray)) {
                                                foreach($detallesArray as $detalle) {
                                                    if(isset($detalle['ajustes_neto']) && $detalle['ajustes_neto'] != 0) {
                                                        $tieneAjustes = true;
                                                        $totalAjustesMostrar += $detalle['ajustes_neto'];
                                                        $ajustesPorPedido[] = [
                                                            'pedido_id' => $detalle['pedido_id'],
                                                            'ajustes_neto' => $detalle['ajustes_neto']
                                                        ];
                                                    }
                                                }
                                            }
                                        @endphp
                                        @if($tieneAjustes)
                                        <div class="mb-2 d-flex align-items-center">
                                            <i class="fas fa-balance-scale text-white-50"
                                                style="width: 24px; text-align: center;"></i>
                                            <span class="text-white">
                                                <span class="fw-bold">Ajustes aplicados:
                                                    {{ $totalAjustesMostrar >= 0 ? '+' : '' }}{{ number_format($totalAjustesMostrar, 2, ',', '.') }}$</span>
                                            </span>
                                        </div>
                                        @endif
                                    @endif
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-money-bill-wave text-white-50"
                                            style="width: 24px; text-align: center;"></i>
                                        <span class="text-white">
                                            Moneda:
                                            <span id="tipo-moneda" class="badge bg-white text-dark ms-1">
                                                {{ session('pago_cliente.tipo_pago') == 'divisa_total'
                                                    ? 'Divisa Total'
                                                    : (session('pago_cliente.tipo_pago') == 'divisa_parcial'
                                                        ? 'Divisa Parcial'
                                                        : 'Bolívares') }}
                                            </span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Total a Pagar -->
            <div class="col-md-6 d-flex">
                <div class="card border-0 shadow-lg w-100 hover-effect"
                    style="background: linear-gradient(145deg, #0d47a1 0%, #1565c0 100%); border-radius: 12px !important;">
                    <div class="card-header bg-transparent border-0 py-3">
                        <h3 class="text-white mb-0 d-flex align-items-center"
                            style="font-weight: 600; letter-spacing: 0.5px;">
                            <i class="fas fa-calculator me-2 pr-2"></i>
                            TOTAL A PAGAR
                        </h3>
                    </div>
                    <div class="card-body p-4 d-flex flex-column" style="padding: 1.5rem !important;">
                        @php
                            $calcTotalAjustes = 0;
                            if (isset($detallesAjustes) && !empty($detallesAjustes)) {
                                foreach ($detallesAjustes as $ajustes) {
                                    if ($ajustes && $ajustes->count() > 0) {
                                        foreach ($ajustes as $ajuste) {
                                            $tipoAjuste = isset($ajuste->tipo) ? strtolower($ajuste->tipo) : '';
                                            $monto = $ajuste->monto ?? 0;
                                            if ($tipoAjuste == 'cargo' || $tipoAjuste == 'debito') {
                                                $calcTotalAjustes += $monto;
                                            } else {
                                                $calcTotalAjustes -= $monto;
                                            }
                                        }
                                    }
                                }
                            }

                            $tipoPagoSesion = session('pago_cliente.tipo_pago');
                            $esPagoBs = $tipoPagoSesion === 'bs';
                            $tasaBcvActual = (float) ($tasa_bcv ?? 0);
                            $calcTotalAjustesBs = $tasaBcvActual > 0
                                ? ((float) $calcTotalAjustes * $tasaBcvActual)
                                : 0;

                            // En Bs, la base viene separada de IVA/retención y el total base en Bs viene en total_bolivares.
                            $totalBolivaresBase = (float) ($total_bolivares ?? 0);
                            $totalIvaBs = (float) ($total_iva ?? 0);
                            $totalRetencionBs = (float) ($total_retencion ?? 0);
                            $baseBsSeparada = $totalBolivaresBase - $totalIvaBs + $totalRetencionBs;

                            $totalConAjustes = $esPagoBs
                                ? ($totalBolivaresBase + $calcTotalAjustesBs)
                                : (float) ($total_pagar ?? 0) + (float) $calcTotalAjustes;

                            // La base sin ajustes viene de metodo.blade.php (base_real).
                            // En pago Bs se convierte por tasa para mostrarla en bolívares.
                            $baseSinAjustesFuente = (float) ($base_sin_ajustes ?? 0);
                            $baseSinAjustesDisplay = $esPagoBs
                                ? $baseBsSeparada
                                : $baseSinAjustesFuente;

                            if ($baseSinAjustesDisplay <= 0) {
                                $baseSinAjustesDisplay = $esPagoBs
                                    ? ((float) ($totalConAjustes ?? 0) - $calcTotalAjustesBs)
                                    : ((float) ($totalConAjustes ?? 0) - (float) ($calcTotalAjustes ?? 0));
                            }
                        @endphp
                        <div class="d-flex align-items-center flex-grow-1">
                            <div class="flex-grow-1">
                                @if (session('pago_cliente.tipo_pago') == 'bs')
                                    <!-- Mostrar en Bolívares con conversión a USD -->
                                    <div class="mb-1">
                                        <div class="d-flex align-items-end">
                                            <span class="display-4 fw-bold text-white me-2 lh-1"
                                                id="total-bolivares">{{ number_format($totalConAjustes, 2, ',', '.') }}</span>
                                            <span class="h4 text-white-50 mb-1">Bs.</span>
                                        </div>

                                        <div class="mt-2">
                                            <div class="d-flex align-items-center text-white-50 small">
                                                <span class="me-1">Tasa: Bs.
                                                    {{ number_format($tasa_bcv, 2, ',', '.') }}</span>
                                                <i class="ml-1 mr-1 fas fa-exchange-alt me-1"></i>
                                                <span>$ {{ number_format(($tasa_bcv > 0 ? ($totalConAjustes / $tasa_bcv) : 0), 2, ',', '.') }} </span>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <!-- Mostrar en Dólares con conversión a Bs. -->
                                    <div class="mb-1">
                                        @if (isset($total_pagar_divisa_parcial) && $total_pagar_divisa_parcial > 0)
                                            <div class="d-flex align-items-end">
                                                <span class="display-4 fw-bold me-1" style="color:#1cc88a;"
                                                    id="total-bolivares2">
                                                    {{ number_format($total_pagar_divisa_parcial, 2, ',', '.') }}</span>
                                                <span class="h4 text-white-50 mb-1" style="color:#1cc88a !important;">
                                                    Bs.</span>
                                            </div>
                                        @endif
                                        <div class="d-flex align-items-end">
                                            <span class="display-4 fw-bold text-white me-2 lh-1" id="total-dolares2">
                                                {{ number_format($total_pagar, 2, ',', '.') }}</span>
                                            <span class="h4 text-white-50 mb-1"> USD</span>
                                        </div>

                                        <div class="mt-2">
                                            <div class="d-flex align-items-center text-white-50 small">
                                                <span class="me-1">Tasa: Bs. {{ number_format($tasa_bcv, 2, ',', '.') }}
                                                </span>
                                                <i class="ml-1 mr-1 fas fa-exchange-alt me-1"></i>
                                                @php
                                                    $total_pagar_tasa_bcv =
                                                        (float) ($total_pagar ?? 0) * (float) ($tasa_bcv ?? 1);
                                                @endphp
                                                <span>Bs. {{ number_format($total_pagar_tasa_bcv, 2, ',', '.') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Card de Información de Ajustes (Modal Trigger) -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-0 shadow ajustes-info-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px !important;">
                    <div class="card-header bg-transparent border-0 py-2 ajustes-info-header" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#ajustesModal">
                        <div class="d-flex flex-column flex-xl-row justify-content-between align-items-start align-items-xl-center ajustes-info-row">
                            <h4 class="text-white mb-0 d-flex align-items-center" style="font-weight: 600; font-size: 1.1rem;">
                                <i class="fas fa-balance-scale me-2"></i>
                                INFORMACIÓN DE AJUSTES
                            </h4>
                            <div class="text-white d-flex align-items-center w-100 w-xl-auto mt-2 mt-xl-0 ajustes-info-metrics">
                                <div class="me-0 me-xl-3 text-start text-xl-end w-100 ajustes-info-metrics-wrap">
                                    <div class="fw-bold d-flex flex-column flex-sm-row flex-wrap align-items-start align-items-sm-center w-100 ajustes-info-badges">
                                        <span class="badge bg-white bg-opacity-25 me-0 me-sm-2 mb-2 mb-sm-0 px-2 py-1" style="font-size: 0.85rem; backdrop-filter: blur(10px);">
                                            <i class="fas fa-balance-scale me-1"></i>
                                            Ajustes:
                                            {{ number_format($calcTotalAjustes, 2, ',', '.') }}$
                                            @if($esPagoBs)
                                                | {{ number_format($calcTotalAjustesBs, 2, ',', '.') }} Bs.
                                            @endif
                                        </span>
                                        <span class="badge bg-white bg-opacity-25 me-0 me-sm-2 mb-2 mb-sm-0 px-2 py-1 ml-0 ml-sm-2" style="font-size: 0.85rem; backdrop-filter: blur(10px);">
                                            <i class="fas fa-chart-line me-1"></i>
                                            Base:
                                            {{ number_format(max(0, $baseSinAjustesDisplay), 2, ',', '.') }}
                                            {{ $esPagoBs ? 'Bs.' : '$' }}
                                        </span>
                                        <span class="badge bg-warning bg-opacity-50 px-3 py-2 ml-0 ml-sm-2" style="font-size: 0.9rem; backdrop-filter: blur(10px); border: 2px solid rgba(255, 193, 7, 0.8); box-shadow: 0 0 15px rgba(255, 193, 7, 0.4); animation: pulse 2s infinite;">
                                            <i class="fas fa-star me-1"></i>
                                            Total: {{ number_format($totalConAjustes, 2, ',', '.') }} {{ $esPagoBs ? 'Bs.' : '$' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-start text-xl-center mt-2 ajustes-info-hint">
                                <div class="small text-white-50" style="font-size: 0.75rem;">
                                    <i class="fas fa-hand-pointer me-1"></i>
                                    Click para ver detalles
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Detalles de Ajustes -->
        <div class="modal fade" id="ajustesModal" tabindex="-1" aria-labelledby="ajustesModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <h5 class="modal-title" id="ajustesModalLabel">
                            <i class="fas fa-calculator me-2"></i>
                            Detalles de Ajustes
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="mb-3 text-primary">Resumen de Ajustes</h6>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="mb-2">
                                            <strong>Total de Ajustes:</strong> 
                                            <span class="float-end text-success">
                                                {{ number_format($calcTotalAjustes, 2, ',', '.') }} $
                                                @if($esPagoBs)
                                                    | {{ number_format($calcTotalAjustesBs, 2, ',', '.') }} Bs.
                                                @endif
                                            </span>
                                        </div>
                                        <div class="mb-2">
                                            <strong>Base sin Ajustes:</strong>
                                            <span class="float-end text-info">
                                                {{ number_format(max(0, $baseSinAjustesDisplay), 2, ',', '.') }} {{ $esPagoBs ? 'Bs.' : '$' }}
                                            </span>
                                        </div>
                                        <div class="mb-2">
                                            <strong>Total con Ajustes:</strong> 
                                            <span class="float-end text-primary fw-bold">{{ number_format($totalConAjustes, 2, ',', '.') }} {{ $esPagoBs ? 'Bs.' : '$' }}</span>
                                        </div>
                                        <hr>
                                        <div class="text-center">
                                            <small class="text-muted">
                                                Base ({{ number_format(max(0, $baseSinAjustesDisplay), 2, ',', '.') }} {{ $esPagoBs ? 'Bs.' : '$' }})
                                                + Ajustes ({{ number_format($calcTotalAjustes, 2, ',', '.') }}$
                                                @if($esPagoBs)
                                                    | {{ number_format($calcTotalAjustesBs, 2, ',', '.') }} Bs.
                                                @endif
                                                )
                                                = Total ({{ number_format($totalConAjustes, 2, ',', '.') }} {{ $esPagoBs ? 'Bs.' : '$' }})
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-3 text-primary">Detalles de Ajustes por Pedido</h6>
                                <div class="card bg-light" style="max-height: 400px; overflow-y: auto;">
                                    <div class="card-body">
                                        @if(isset($detallesAjustes) && !empty($detallesAjustes))
                                            @foreach($detallesAjustes as $pedidoId => $ajustes)
                                                @if($ajustes->count() > 0)
                                                    <div class="mb-3 p-3 border rounded" style="background: rgba(102, 126, 234, 0.1);">
                                                        <h6 class="text-primary mb-2">
                                                            <i class="fas fa-file-invoice me-1"></i>
                                                            Pedido #{{ $pedidoId }}
                                                        </h6>
                                                        @foreach($ajustes as $ajuste)
                                                            <div class="mb-2 p-2 bg-white rounded">
                                                                <div class="d-flex justify-content-between align-items-start">
                                                                    <div class="flex-grow-1">
                                                                        <strong class="text-dark">{{ $ajuste->concepto ?? 'Sin concepto' }}</strong>
                                                                        <div class="small text-muted">
                                            @php
                                                $tipoAjuste = isset($ajuste->tipo) ? strtolower($ajuste->tipo) : '';
                                                $icono = ($tipoAjuste == 'cargo' || $tipoAjuste == 'debito') ? 'fa-plus-circle text-danger' : 'fa-minus-circle text-success';
                                                $textoTipo = ($tipoAjuste == 'cargo' || $tipoAjuste == 'debito') ? 'Cargo' : 'Abono';
                                            @endphp
                                            <i class="fas {{ $icono }} me-1"></i>{{ $textoTipo }}
                                            @if(isset($ajuste->created_at))
                                                - {{ \Carbon\Carbon::parse($ajuste->created_at)->format('d/m/Y') }}
                                            @endif
                                                                        </div>
                                                                    </div>
                                                                    <div class="text-end">
                                                                        <span class="badge {{ ($tipoAjuste == 'cargo' || $tipoAjuste == 'debito') ? 'bg-danger' : 'bg-success' }} bg-opacity-25 text-dark">
                                                                            {{ ($tipoAjuste == 'cargo' || $tipoAjuste == 'debito') ? '+' : '-' }}{{ number_format($ajuste->monto ?? 0, 2, ',', '.') }}$
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            @endforeach
                                        @else
                                            <div class="text-muted text-center">
                                                <i class="fas fa-info-circle me-2"></i>
                                                No hay detalles de ajustes disponibles
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <h5 class="mb-0 text-dark">Pedidos Pendientes de Pago</h5>
                            <button type="button" onclick="window.history.back()" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Volver
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif
                        @if (session('danger'))
                            <div class="alert alert-danger">
                                {{ session('danger') }}
                            </div>
                        @endif

                        <div class="alert alert-info mb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-credit-card me-2 mr-2"></i>
                                    <span class="me-3 fw-bold">
                                        {{ session('pago_cliente.tipo_pago') == 'divisa_total'
                                            ? 'Divisa Total'
                                            : (session('pago_cliente.tipo_pago') == 'divisa_parcial'
                                                ? 'Divisa Parcial'
                                                : 'Bolívares') }}</span>
                                    @php
                                        $isDollar = str_contains(session('pago_cliente.forma_pago_desc', ''), '$');
                                        $currency = $isDollar ? 'USD' : '$';
                                    @endphp
                                    <span class="d-none ml-2 badge bg-{{ $isDollar ? 'success' : 'danger' }} px-3 py-2">
                                        <i class="fas fa-{{ $isDollar ? 'dollar-sign' : 'money-bill-wave' }} me-1"></i>
                                        {{ $currency }}
                                    </span>
                                </div>
                                <div class="fw-bold fs-5">
                                    <i class="fas fa-money-bill-wave me-3"></i>
                                    @php
                                        $tipoPago = session('pago_cliente.tipo_pago');
                                        $monto = session('pago_cliente.monto', 0);
                                        $tasaBcv = session('tasa_bcv', 1);
                                        $montoMostrar = $tipoPago === 'bolivares' ? $monto * $tasaBcv : $monto;
                                    @endphp
                                    Monto a pagar: <b>{{ number_format($totalConAjustes, 2, ',', '.') }}</b>
                                    {{ $tipoPago === 'bolivares' ? 'Bs.' : '$' }}
                                </div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('vendedores.pagos.storeMultiple') }}"
                            id="multiplePaymentsForm" enctype="multipart/form-data">
                            @if (session('pago_cliente.metodo_pago') === 'transferencia')
                                <input type="hidden" name="banco_id" value="{{ session('pago_cliente.banco_id') }}">
                                <input type="hidden" name="referencia" value="{{ session('pago_cliente.referencia') }}">
                                <input type="hidden" name="fecha_pago" value="{{ session('pago_cliente.fecha') }}">
                                <input type="hidden" name="comprobante"
                                    value="{{ session('pago_cliente.comprobante') ?? '' }}">
                            @endif
                            <input type="hidden" name="metodo_pago" value="{{ session('pago_cliente.metodo_pago') }}">
                            <input type="hidden" name="moneda_pago"
                                value="{{ session('pago_cliente.tipo_pago') == 'divisa_total'
                                    ? 'Divisa Total'
                                    : (session('pago_cliente.tipo_pago') == 'divisa_parcial'
                                        ? 'Divisa Parcial'
                                        : 'Bolívares') }}">
                            <input type="hidden" name="monto" value="{{ session('pago_cliente.monto') }}">
                            <input type="hidden" name="total_iva" value="{{ $total_iva ?? 0 }}">
                            <input type="hidden" name="total_retencion" value="{{ $total_retencion ?? 0 }}">
                            <input type="hidden" name="total_descuento_pago" value="{{ $total_descuento_pago ?? 0 }}">
                            <input type="hidden" name="detallePedidos" value="{{ $detallePedidos ?? 0 }}">
                            <input type="hidden" name="total_ajustes_netos" value="{{ $total_ajustes_netos ?? 0 }}">
                            {{-- IVA en divisa: propagados desde metodo.blade.php --}}
                            <input type="hidden" name="iva_en_divisa" value="{{ request('iva_en_divisa', 0) }}">
                            <input type="hidden" name="opcion_iva_divisa" value="{{ request('opcion_iva_divisa', 'completo') }}">
                            @csrf

                            {{-- Info IVA en divisa para el vendedor --}}
                            @if(session('pago_cliente.tipo_pago') == 'divisa_total' && request('iva_en_divisa') == '1')
                            <div class="alert alert-info mb-3 py-2">
                                <i class="fas fa-info-circle me-1"></i>
                                <strong>IVA incluido en este pago en divisa.</strong>
                                Opción: {{ request('opcion_iva_divisa') == 'retencion' ? 'Retención aplicada (IVA neto)' : 'IVA completo' }}.
                                El administrador aplicará el descuento del IVA al aprobar.
                            </div>
                            @endif

                            @if (1==2 && session('pago_cliente.tipo_pago') == 'bs' && (($total_iva ?? 0) > 0 || ($saldo_iva_total ?? 0) > 0))
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <div class="alert alert-warning mb-0">
                                            <div class="form-check mb-1">
                                                <input class="form-check-input" type="checkbox" id="pagar_monto_iva"
                                                    name="pagar_monto_iva" value="1"
                                                    {{ old('pagar_monto_iva') ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold" for="pagar_monto_iva">
                                                    Pagar monto IVA
                                                </label>
                                            </div>
                                            @php $ivaDisplay = ($total_iva ?? 0) > 0 ? $total_iva : ($saldo_iva_total ?? 0); @endphp
                                            <small class="d-block text-dark">
                                                Monto IVA pendiente: Bs. {{ number_format($ivaDisplay, 2, ',', '.') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Abono parcial: solo para pago BS con 1 pedido, con IVA y sin retención --}}
                            @php
                                $pedidosCount = $pedidos_seleccionados ? count(array_filter(explode(',', $pedidos_seleccionados))) : 0;
                            @endphp
                            @if (session('pago_cliente.tipo_pago') == 'bs' && $pedidosCount === 1 && ($total_iva ?? 0) > 0 && ($total_retencion ?? 0) == 0)
                            @php
                                $montoAbonoAmbos = (float) ($totalConAjustes ?? $total_pagar ?? 0);
                                $montoAbonoSoloIva = (float) (($total_iva ?? 0) - ($total_retencion ?? 0));
                                $montoAbonoSoloBase = max((float) $montoAbonoAmbos - (float) $montoAbonoSoloIva, 0);
                            @endphp
                            <div class="row mb-3">
                                <div class="col-12">
                                    <div style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 2px solid #16a34a; border-radius: 12px; padding: 20px 24px;">
                                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                                            <div style="width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-sliders-h" style="color: white; font-size: 16px;"></i>
                                            </div>
                                            <h6 style="margin: 0; font-size: 16px; font-weight: 700; color: #14532d;">¿A qué aplicar el abono?</h6>
                                        </div>
                                        <div style="display: flex; flex-wrap: wrap; gap: 12px;">
                                            <label style="flex: 1; min-width: 160px; cursor: pointer;">
                                                <input type="radio" name="abono_tipo" value="ambos" data-monto="{{ number_format($montoAbonoAmbos, 2, '.', '') }}" checked style="position: absolute; opacity: 0; z-index: -1;">
                                                <div class="abono-card" data-value="ambos" style="border: 2px solid #16a34a; border-radius: 10px; padding: 14px 18px; background: white; transition: all 0.2s ease; display: flex; align-items: center; gap: 10px;">
                                                    <div style="width: 18px; height: 18px; border-radius: 50%; border: 2px solid #16a34a; background: #16a34a; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                        <div style="width: 8px; height: 8px; border-radius: 50%; background: white;"></div>
                                                    </div>
                                                    <div>
                                                        <div style="font-weight: 700; color: #14532d; font-size: 14px;">Base e IVA</div>
                                                        <div style="font-size: 12px; color: #4ade80;">Distribuir entre ambos</div>
                                                        <div style="font-size: 11px; color: #6b7280; font-weight: 600; margin-top: 4px;">
                                                            Monto: Bs. {{ number_format($montoAbonoAmbos, 2, ',', '.') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </label>
                                            <label style="flex: 1; min-width: 160px; cursor: pointer;">
                                                <input type="radio" name="abono_tipo" value="solo_base" data-monto="{{ number_format($montoAbonoSoloBase, 2, '.', '') }}" style="position: absolute; opacity: 0; z-index: -1;">
                                                <div class="abono-card" data-value="solo_base" style="border: 2px solid #d1d5db; border-radius: 10px; padding: 14px 18px; background: white; transition: all 0.2s ease; display: flex; align-items: center; gap: 10px;">
                                                    <div style="width: 18px; height: 18px; border-radius: 50%; border: 2px solid #d1d5db; background: white; flex-shrink: 0;"></div>
                                                    <div>
                                                        <div style="font-weight: 700; color: #374151; font-size: 14px;">Solo base</div>
                                                        <div style="font-size: 12px; color: #9ca3af;">No aplica al IVA</div>
                                                        <div style="font-size: 11px; color: #6b7280; font-weight: 600; margin-top: 4px;">
                                                            Monto: Bs. {{ number_format($montoAbonoSoloBase, 2, ',', '.') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </label>
                                            <label style="flex: 1; min-width: 160px; cursor: pointer;">
                                                <input type="radio" name="abono_tipo" value="solo_iva" data-monto="{{ number_format($montoAbonoSoloIva, 2, '.', '') }}" style="position: absolute; opacity: 0; z-index: -1;">
                                                <div class="abono-card" data-value="solo_iva" style="border: 2px solid #d1d5db; border-radius: 10px; padding: 14px 18px; background: white; transition: all 0.2s ease; display: flex; align-items: center; gap: 10px;">
                                                    <div style="width: 18px; height: 18px; border-radius: 50%; border: 2px solid #d1d5db; background: white; flex-shrink: 0;"></div>
                                                    <div>
                                                        <div style="font-weight: 700; color: #374151; font-size: 14px;">Solo IVA</div>
                                                        <div style="font-size: 12px; color: #9ca3af;">No aplica a la base</div>
                                                        <div style="font-size: 11px; color: #6b7280; font-weight: 600; margin-top: 4px;">
                                                            Monto: Bs. {{ number_format($montoAbonoSoloIva, 2, ',', '.') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Sección de IVA con retención -->
                            @if (session('pago_cliente.tipo_pago') == 'bs' && ($total_retencion ?? 0) > 0)
                            <div class="iva-responsive" style="background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); border-radius: 20px; padding: 32px; margin-bottom: 24px; border: 3px solid #0ea5e9; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15); position: relative;">
                                <!-- Header con resumen -->
                                <div class="iva-header-responsive" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; flex-wrap: wrap; gap: 16px;">
                                    
                                    <div style="display: flex; align-items: center; gap: 16px;">
                                        <div style="width: 56px; height: 56px; border-radius: 16px; background: rgba(255, 255, 255, 0.9); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 20px rgba(2, 132, 199, 0.3);">
                                            <i class="fas fa-calculator" style="font-size: 28px; color: #0284c7;"></i>
                                        </div>
                                        <h4 style="margin: 0; font-size: 24px; font-weight: 700; color: #075985;">Opciones de IVA con Retención</h4>
                                    </div>
                                    
                                    <div class="iva-summary-responsive d-flex flex-column flex-lg-row" style="display: flex; gap: 20px; background: rgba(255, 255, 255, 0.7); padding: 16px 24px; border-radius: 16px; backdrop-filter: blur(10px); border: 2px solid rgba(255, 255, 255, 0.9);">
                                        <div class="iva-summary-item" style="text-align: center; flex: 1 1 auto;">
                                            <div style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">IVA Total</div>
                                            <div style="font-size: 16px; font-weight: 700; color: #1e293b;">Bs. {{ number_format($total_iva ?? 0, 2, ',', '.') }}</div>
                                        </div>
                                        <div class="iva-summary-item" style="text-align: center; flex: 1 1 auto;">
                                            <div style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Retención 75%</div>
                                            <div style="font-size: 16px; font-weight: 700; color: #1e293b;">Bs. {{ number_format($total_retencion, 2, ',', '.') }}</div>
                                        </div>
                                        <div class="iva-summary-item" style="text-align: center; flex: 1 1 auto; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 12px 20px; border-radius: 12px; box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4);">
                                            <div style="font-size: 11px; font-weight: 600; opacity: 0.9; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Neto</div>
                                            <div style="font-size: 18px; font-weight: 700;">Bs. {{ number_format(($total_iva ?? 0) - $total_retencion, 2, ',', '.') }}</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Opciones de pago -->
                                <div class="iva-grid-responsive" style="display: flex; flex-wrap: wrap; gap: 24px; align-items: stretch;">
                                    <!-- Opción 1: Aplicar Retención -->
                                    <div class="iva-hover iva-selected" style="flex: 1 1 320px; min-width: min(320px, 100%); position: relative;">
                                        <input type="radio" name="pago_iva_opcion" id="iva_retencion" value="retencion" checked style="position: absolute; opacity: 0; z-index: -1;">
                                        <label for="iva_retencion" style="display: block; width: 100%; cursor: pointer;">
                                            <div id="card-retencion" class="iva-card-responsive" style="background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%); border-radius: 20px; padding: 28px; border: 3px solid #ffffff; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12); transition: all 0.3s ease; position: relative; overflow: hidden; min-height: 160px; display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                                <div style="flex-shrink: 0;">
                                                    <div class="icon-wrapper" style="width: 64px; height: 64px; border-radius: 18px; background: linear-gradient(135deg, #fb923c 0%, #ea580c 100%); display: flex; align-items: center; justify-content: center; font-size: 24px; color: white; box-shadow: 0 8px 30px rgba(251, 146, 60, 0.4); transition: all 0.3s ease;">
                                                        <i class="fas fa-percentage"></i>
                                                    </div>
                                                </div>
                                                <div style="flex: 1; text-align: left;">
                                                    <h5 class="elegant-title" style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0 0 8px 0; line-height: 1.2;">Aplicar Retención</h5>
                                                    <div class="elegant-amount" style="font-size: 22px; font-weight: 800; color: #ea580c; margin: 0 0 12px 0; line-height: 1;">Bs. {{ number_format(($total_iva ?? 0) - $total_retencion, 2, ',', '.') }}</div>
                                                    <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #64748b; font-weight: 500;">
                                                        <i class="fas fa-file-invoice"></i>
                                                        <span>Comprobante de retención requerido</span>
                                                    </div>
                                                </div>
                                                <div style="flex-shrink: 0;">
                                                    <div id="selector-retencion" style="width: 32px; height: 32px; border-radius: 50%; background: #f1f5f9; border: 3px solid #ffffff; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);">
                                                        <div id="dot-retencion" style="width: 10px; height: 10px; border-radius: 50%; background: #94a3b8; transition: all 0.3s ease;"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                    
                                    <!-- Opción 2: Pagar IVA Completo -->
                                    <div class="iva-hover" style="flex: 1 1 320px; min-width: min(320px, 100%); position: relative;">
                                        <input type="radio" name="pago_iva_opcion" id="iva_completo" value="completo" style="position: absolute; opacity: 0; z-index: -1;">
                                        <label for="iva_completo" style="display: block; width: 100%; cursor: pointer;">
                                            <div id="card-completo" class="iva-card-responsive" style="background: linear-gradient(145deg, #f0f9ff 0%, #e0f2fe 100%); border-radius: 20px; padding: 28px; border: 3px solid #ffffff; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12); transition: all 0.3s ease; position: relative; overflow: hidden; min-height: 160px; display: flex; align-items: center; gap: 20px; cursor: pointer;">
                                                <div style="flex-shrink: 0;">
                                                    <div class="icon-wrapper" style="width: 64px; height: 64px; border-radius: 18px; background: linear-gradient(135deg, #60a5fa 0%, #2563eb 100%); display: flex; align-items: center; justify-content: center; font-size: 24px; color: white; box-shadow: 0 8px 30px rgba(96, 165, 250, 0.4); transition: all 0.3s ease;">
                                                        <i class="fas fa-check-circle"></i>
                                                    </div>
                                                </div>
                                                <div style="flex: 1; text-align: left;">
                                                    <h5 class="elegant-title" style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0 0 8px 0; line-height: 1.2;">Pagar IVA Completo</h5>
                                                    <div class="elegant-amount" style="font-size: 22px; font-weight: 800; color: #2563eb; margin: 0 0 12px 0; line-height: 1;">Bs. {{ number_format($total_iva ?? 0, 2, ',', '.') }}</div>
                                                    <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #64748b; font-weight: 500;">
                                                        <i class="fas fa-info-circle"></i>
                                                        <span>Sin aplicar retención</span>
                                                    </div>
                                                </div>
                                                <div style="flex-shrink: 0;">
                                                    <div id="selector-completo" style="width: 32px; height: 32px; border-radius: 50%; background: #f1f5f9; border: 3px solid #ffffff; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);">
                                                        <div id="dot-completo" style="width: 10px; height: 10px; border-radius: 50%; background: #94a3b8; transition: all 0.3s ease;"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Alerta de comprobante requerido -->
                                <div id="alerta-retencion" style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); border: 2px solid #fca5a5; border-radius: 16px; padding: 20px 24px; margin-top: 24px; display: flex; align-items: center; gap: 16px; box-shadow: 0 4px 20px rgba(251, 146, 60, 0.2);">
                                    <div style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; box-shadow: 0 4px 20px rgba(245, 158, 11, 0.3);">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                    <div>
                                        <strong style="display: block; font-size: 16px; font-weight: 700; color: #92400e; margin-bottom: 4px;">Comprobante de retención requerido</strong>
                                        <span style="font-size: 14px; color: #b45309; line-height: 1.4;">Debe adjuntar el comprobante cuando aplica la retención del 75%</span>
                                    </div>
                                </div>
                                
                                <!-- Campo oculto -->
                                <input type="hidden" id="monto_iva_adicional" name="monto_iva_adicional" value="{{ ($total_iva ?? 0) - $total_retencion }}">
                            </div>
                            @endif

                            @if (session('pago_cliente.metodo_pago') === 'transferencia')
                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6 class="card-subtitle mb-2 text-muted">Datos de la Transferencia</h6>
                                                <p class="mb-1"><strong>Banco:</strong>
                                                    {{ $bancos->firstWhere('id', session('pago_cliente.banco_id'))->nombre ?? 'N/A' }}
                                                </p>
                                                <p class="mb-1"><strong>Referencia:</strong>
                                                    {{ session('pago_cliente.referencia') }}</p>
                                                <p class="mb-0"><strong>Fecha:</strong>
                                                    {{ \Carbon\Carbon::parse(session('pago_cliente.fecha'))->format('d/m/Y') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="row">
                                @if (session('pago_cliente.metodo_pago') === 'efectivo')
                                    <input type="hidden" name="tipo_pago"
                                        value="{{ session('pago_cliente.tipo_pago') }}">
                                @endif

                                <!-- Hidden field to store the final amount -->
                                <input type="hidden" id="monto" name="monto" value="{{ $totalConAjustes ?? 0 }}">

                                <div class="col-md-3">
                                    <label for="monto_bs">Monto </label>
                                    <input type="number" class="form-control @error('monto_bs') is-invalid @enderror"
                                        id="monto_bs" name="monto_bs" step="0.01"
                                        value="{{ old('monto_bs', number_format($totalConAjustes ?? 0, 2, '.', '')) }}">
                                    @error('monto_bs')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="col-md-3">
                                    <label for="pago_destino_id">Banco Receptor *</label>
                                    <select class="form-control @error('pago_destino_id') is-invalid @enderror"
                                        id="pago_destino_id" name="pago_destino_id" required>
                                        <option value="">Seleccione un destino</option>
                                        @foreach ($pago_destinos as $destino)
                                            <option value="{{ $destino->id }}">{{ $destino->nombre }}</option>
                                        @endforeach
                                    </select>
                                    @error('pago_destino_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label for="fecha_pago">Fecha de Pago</label>
                                        <input type="date"
                                            class="form-control @error('fecha_pago') is-invalid @enderror" id="fecha_pago"
                                            name="fecha_pago" value="{{ old('fecha_pago', date('Y-m-d')) }}">
                                        @error('fecha_pago')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label for="fecha_pago">Tipo de Pago</label>
                                        <select class="form-control @error('tipo_pago') is-invalid @enderror"
                                            id="tpago_id" name="tpago_id">
                                            <option value="" disabled>Seleccione el tipo de pago</option>
                                            @foreach ($tipos_pago as $i => $tipo)
                                                <option value="{{ $tipo->CPAGO }}"
                                                    {{ old('tipo_pago', $tipos_pago[0]->CPAGO ?? '') == $tipo->CPAGO ? 'selected' : '' }}>
                                                    {{ $tipo->DPAGO }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('tipo_pago')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                @if (isset($bancos) && count($bancos) > 0)
                                    <div class="col-md-3">
                                        <label for="banco_codigo">Banco Origen</label>
                                        <select class="form-control @error('banco_codigo') is-invalid @enderror"
                                            id="banco_codigo" name="banco_codigo">
                                            <option value="" disabled>Seleccione el banco</option>
                                            @foreach ($bancos as $i => $banco)
                                                <option value="{{ $banco->codigo }}"
                                                    {{ old('banco_codigo', $bancos[0]->codigo ?? '') == $banco->codigo ? 'selected' : '' }}>
                                                    {{ $banco->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('banco_codigo')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror

                                        @error('banco_codigo')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                @endif

                                <div class="col-md-3">
                                    <label for="referencia">Referencia</label>
                                    <input type="text" class="form-control @error('referencia') is-invalid @enderror"
                                        id="referencia" name="referencia">
                                    @error('referencia')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="descripcion">Detalles del pago</label>
                                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                                    </div>

                                </div>
                                <div class="col-md-6">
                                    <label for="comprobantes">Comprobantes de Pago</label>

                                    {{ Form::file('comprobantes[]', [
                                        'class' => 'form-control',
                                        'id' => 'comprobantes',
                                        'multiple' => true,
                                        'accept' => 'image/jpeg,image/png,application/pdf',
                                    ]) }}
                                    <small class="form-text text-muted">Formatos permitidos: PDF, JPG, JPEG, PNG. Puede
                                        seleccionar varios archivos.</small>
                                    <div id="file-preview" class="mt-2"></div>
                                </div>

                            </div>

                            <div class="row justify-content-center mt-4 mb-4">
                                <div class="col-auto mx-2">
                                    <button type="button" id="btn-agregar-pago"
                                        class="btn btn-primary btn-action py-2 px-4 btn-elegant"
                                        style="min-width: 220px;">
                                        <i class="fas fa-plus-circle me-2"></i>Agregar Pago
                                    </button>
                                </div>
                                <div class="col-auto">
                                    <button type="submit" id="btn-aplicar-cambios"
                                        class="btn btn-success btn-action py-2 px-4 btn-elegant" style="min-width: 220px;"
                                        disabled>
                                        <i class="fas fa-check-circle me-2"></i>Aplicar Cambios
                                    </button>
                                </div>
                            </div>

                            <div class="d-none row mt-4 mb-5" id="resumen-pago">
                                <div class="col-12">
                                    <div class="card border-0 shadow" style="border-radius: 12px; overflow: hidden;">
                                        <div class="card-body py-4 px-4 total-container"
                                            style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h5 class="mb-1 text-white">Resumen de Pago</h5>
                                                    <small class="text-white-50">Pedidos seleccionados para pago</small>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <div class="text-end me-4">
                                                        <div class="text-white-50 small">Total a Pagar</div>
                                                        <h3 class="mb-0 text-white fw-bold" id="total-pagar">{{ number_format($total_pagar, 2, ',', '.') }} $</h3>
                                                        @if(isset($total_ajustes_netos) && $total_ajustes_netos != 0)
                                                            <div class="text-white-50" style="font-size: 0.7rem;">
                                                                Total Ajustes: {{ $total_ajustes_netos >= 0 ? '+' : '' }}{{ number_format($total_ajustes_netos, 2, ',', '.') }}$
                                                            </div>
                                                            <div class="text-white-50" style="font-size: 0.6rem;">
                                                                (Base: {{ number_format($base_sin_ajustes, 2, ',', '.') }}$)
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <button id="btn-procesar-pago" class="btn btn-light btn-lg px-4 py-2"
                                                        style="border-radius: 8px;">
                                                        <i class="fas fa-calculator me-3"></i>Procesar Pago
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Lista de Pagos Agregados -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Pagos Registrados</h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0" id="pagos-lista">
                                            <thead>
                                                <tr>
                                                    <th>Referencia</th>
                                                    <th>Banco Origen</th>
                                                    <th>Banco Destino</th>
                                                    <th>Monto</th>
                                                    <th>Fecha</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody id="pagos-body">
                                                <!-- Los pagos se agregarán aquí dinámicamente -->
                                            </tbody>
                                            <tfoot>
                                                <tr class="table-active">
                                                    <td colspan="5" class="text-end fw-bold">Total Pagado:</td>
                                                    <td id="total-pagado" class="text-nowrap fw-bold" colspan="3">0.00
                                                        $</td>
                                                </tr>
                                                <tr class="table-active">
                                                    <td colspan="5" class="text-end fw-bold">Saldo Pendiente:</td>
                                                    <td id="saldo-pendiente" colspan="3" class="text-nowrap fw-bold">
                                                        @php
                                                            $montoFormateado = number_format(
                                                                $montoMostrar ?? 0,
                                                                2,
                                                                ',',
                                                                '.',
                                                            );
                                                            $simboloMoneda = $tipoPago === 'bolivares' ? 'Bs.' : '$';
                                                            echo e($montoFormateado . ' ' . $simboloMoneda);
                                                        @endphp
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                        <div class="text-center py-3" id="sin-pagos">
                                            <p class="text-muted mb-0">No hay pagos registrados</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Campos ocultos para el envío del formulario -->
                            <input type="hidden" name="pagos_json" id="pagos-json">
                            <input type="hidden" name="rate_json" id="rate-json">

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        .btn-action {
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .btn-primary {
            background: linear-gradient(45deg, #4e73df, #224abe);
            border: none;
        }

        .btn-success {
            background: linear-gradient(45deg, #1cc88a, #13855c);
            border: none;
        }

        .btn-danger {
            background: linear-gradient(45deg, #e74a3b, #be2617);
            border: none;
        }

        /* Estilos adicionales para la sección de IVA - Garantizados */
        .iva-hover:hover #card-retencion {
            transform: translateY(-8px) !important;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2) !important;
            border: 3px solid #10b981 !important;
        }

        .iva-hover:hover #card-completo {
            transform: translateY(-8px) !important;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2) !important;
            border: 3px solid #10b981 !important;
        }

        .iva-hover:hover #selector-retencion,
        .iva-hover:hover #selector-completo {
            transform: scale(1.2) !important;
            border: 3px solid #10b981 !important;
            background: #d1fae5 !important;
        }

        .iva-hover:hover #dot-retencion,
        .iva-hover:hover #dot-completo {
            background: #10b981 !important;
            transform: scale(1.5) !important;
        }

        /* ESTILOS SELECCIONADOS - MÁS EVIDENTES Y FORZADOS */
        .iva-selected #card-retencion,
        .iva-selected #card-completo {
            background: linear-gradient(145deg, #f0fdf4 0%, #dcfce7 100%) !important;
            border: 4px solid #10b981 !important;
            box-shadow: 0 20px 60px rgba(16, 185, 129, 0.4) !important;
            transform: translateY(-6px) scale(1.02) !important;
            position: relative !important;
        }

        .iva-selected #card-retencion::before,
        .iva-selected #card-completo::before {
            content: '✓ SELECCIONADO' !important;
            position: absolute !important;
            top: 5px !important;
            left: 20px !important;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
            color: white !important;
            padding: 6px 16px !important;
            border-radius: 20px !important;
            font-size: 12px !important;
            font-weight: 700 !important;
            box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4) !important;
            z-index: 10 !important;
            animation: selectedBadge 0.6s ease-out !important;
        }

        .iva-selected #selector-retencion,
        .iva-selected #selector-completo {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
            border: 4px solid #10b981 !important;
            transform: scale(1.4) !important;
            animation: selectorPulse 0.8s ease-out !important;
            box-shadow: 0 8px 30px rgba(16, 185, 129, 0.5) !important;
        }

        .iva-selected #dot-retencion,
        .iva-selected #dot-completo {
            background: white !important;
            transform: scale(2.5) !important;
            box-shadow: 0 0 25px rgba(255, 255, 255, 0.9) !important;
        }

        /* Iconos seleccionados */
        .iva-selected .icon-wrapper {
            transform: scale(1.2) rotate(5deg) !important;
            box-shadow: 0 16px 50px rgba(0, 0, 0, 0.4) !important;
        }

        /* Textos seleccionados */
        .iva-selected .elegant-title {
            color: #065f46 !important;
            font-weight: 800 !important;
        }

        .iva-selected .elegant-amount {
            color: #059669 !important;
            font-weight: 900 !important;
            text-shadow: 0 2px 10px rgba(16, 185, 129, 0.3) !important;
        }

        /* ESTILOS NO SELECCIONADOS - BORDE BLANCO FORZADO */
        .iva-hover:not(.iva-selected) #card-retencion,
        .iva-hover:not(.iva-selected) #card-completo {
            border: 3px solid #ffffff !important;
        }

        .ajustes-info-row,
        .ajustes-info-metrics,
        .ajustes-info-metrics-wrap,
        .ajustes-info-badges {
            min-width: 0;
        }

        .ajustes-info-badges {
            flex-wrap: wrap;
            row-gap: 8px;
        }

        .ajustes-info-badges .badge {
            white-space: normal;
            text-align: left;
            line-height: 1.25;
            max-width: 100%;
        }

        @media (max-width: 1199.98px) {
            .ajustes-info-row {
                flex-direction: column !important;
                align-items: stretch !important;
            }

            .ajustes-info-metrics,
            .ajustes-info-metrics-wrap,
            .ajustes-info-badges {
                width: 100% !important;
            }

            .ajustes-info-badges {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 8px !important;
            }

            .ajustes-info-badges .badge {
                width: 100% !important;
                margin: 0 !important;
            }
        }

        @keyframes selectorPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.5); }
            100% { transform: scale(1.4); }
        }

        @keyframes selectedBadge {
            0% { 
                opacity: 0;
                transform: translateY(-10px) scale(0.8);
            }
            50% { 
                transform: translateY(0px) scale(1.1);
            }
            100% { 
                opacity: 1;
                transform: translateY(0px) scale(1);
            }
        }

        /* Responsive */
        @media (max-width: 991.98px), (hover: none) and (pointer: coarse) {
            .iva-responsive {
                padding: 20px !important;
                overflow: hidden !important;
                width: 100% !important;
                max-width: 100% !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
                box-sizing: border-box !important;
            }

            .iva-responsive * {
                box-sizing: border-box !important;
                max-width: 100% !important;
            }
            
            .iva-header-responsive {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 20px !important;
                width: 100% !important;
                min-width: 0 !important;
            }

            .iva-responsive .iva-header-responsive > div:nth-child(2) {
                display: grid !important;
                grid-template-columns: 1fr !important;
                gap: 10px !important;
                width: 100% !important;
                padding: 12px !important;
            }

            .iva-summary-responsive {
                display: grid !important;
                grid-template-columns: 1fr !important;
                gap: 10px !important;
                width: 100% !important;
                min-width: 0 !important;
                padding: 12px !important;
            }

            .iva-summary-item {
                width: 100% !important;
                max-width: 100% !important;
                min-width: 0 !important;
                text-align: left !important;
                padding: 10px 12px !important;
                border-radius: 10px !important;
                box-sizing: border-box !important;
            }

            .iva-header-responsive > div:first-child {
                width: 100% !important;
                justify-content: flex-start !important;
                min-width: 0 !important;
            }

            .iva-header-responsive > div:first-child h4 {
                font-size: 19px !important;
                line-height: 1.25 !important;
                word-break: break-word !important;
                overflow-wrap: anywhere !important;
            }

            .iva-header-responsive > div:last-child {
                width: 100% !important;
                max-width: 100% !important;
                display: flex !important;
                flex-direction: column !important;
                gap: 10px !important;
                padding: 12px !important;
                box-sizing: border-box !important;
                min-width: 0 !important;
            }

            .iva-header-responsive > div:last-child > div {
                width: 100% !important;
                max-width: 100% !important;
                text-align: left !important;
                padding: 10px 12px !important;
                border-radius: 10px !important;
                box-sizing: border-box !important;
                min-width: 0 !important;
            }
            
            .iva-grid-responsive {
                grid-template-columns: 1fr !important;
                gap: 16px !important;
                width: 100% !important;
                min-width: 0 !important;
                display: grid !important;
            }

            .iva-hover {
                width: 100% !important;
                min-width: 0 !important;
            }

            .iva-hover > label {
                width: 100% !important;
                display: block !important;
            }
            
            .iva-card-responsive {
                flex-direction: column !important;
                text-align: center !important;
                gap: 16px !important;
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
                min-width: 0 !important;
                overflow: hidden !important;
            }

            .iva-responsive #card-retencion,
            .iva-responsive #card-completo {
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 14px !important;
                padding: 18px !important;
                min-height: unset !important;
            }

            .iva-responsive #card-retencion > div,
            .iva-responsive #card-completo > div {
                width: 100% !important;
            }

            .iva-responsive #selector-retencion,
            .iva-responsive #selector-completo {
                margin: 0 auto !important;
            }

            .iva-card-responsive > div {
                min-width: 0 !important;
                width: 100% !important;
            }

            .iva-card-responsive .elegant-title,
            .iva-card-responsive .elegant-amount,
            .iva-card-responsive span {
                overflow-wrap: anywhere !important;
                word-break: break-word !important;
            }

            .iva-selected #card-retencion,
            .iva-selected #card-completo {
                transform: none !important;
                box-shadow: 0 12px 30px rgba(16, 185, 129, 0.25) !important;
            }

            .iva-selected #card-retencion::before,
            .iva-selected #card-completo::before {
                top: -12px !important;
                left: 50% !important;
                transform: translateX(-50%) !important;
                font-size: 11px !important;
                padding: 4px 12px !important;
            }

            #alerta-retencion {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 12px !important;
                padding: 16px !important;
            }

            #alerta-retencion > div:first-child {
                width: 42px !important;
                height: 42px !important;
                flex-shrink: 0 !important;
            }

            .ajustes-info-header {
                padding: 12px !important;
            }

            .ajustes-info-row {
                display: flex !important;
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 12px !important;
            }

            .ajustes-info-row > h4 {
                font-size: 1rem !important;
                line-height: 1.3 !important;
                margin-right: 0 !important;
            }

            .ajustes-info-metrics {
                width: 100% !important;
                display: block !important;
            }

            .ajustes-info-metrics-wrap {
                margin-right: 0 !important;
                text-align: left !important;
                width: 100% !important;
            }

            .ajustes-info-badges {
                display: flex !important;
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 8px !important;
            }

            .ajustes-info-badges .badge {
                margin-left: 0 !important;
                margin-right: 0 !important;
                width: 100% !important;
            }

            .ajustes-info-hint {
                margin-top: 0 !important;
                text-align: left !important;
            }
        }

        @media (max-width: 575.98px) {
            .iva-responsive {
                padding: 14px !important;
                border-radius: 14px !important;
            }

            .iva-header-responsive > div:first-child {
                gap: 10px !important;
            }

            .iva-header-responsive > div:first-child h4 {
                font-size: 17px !important;
            }

            .iva-card-responsive {
                padding: 18px !important;
                min-height: unset !important;
            }

            .elegant-amount {
                font-size: 20px !important;
            }

            .ajustes-info-row > h4 {
                font-size: 0.95rem !important;
            }

            .ajustes-info-badges .badge {
                font-size: 0.78rem !important;
            }
        }
    </style>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
    <script type="text/javascript">
        // Initialize URL_TASA with proper escaping
        // @ts-ignore
        const URL_TASA = '{{ addslashes(url('get-tasa-bcv')) }}';

        // Set the currency symbol based on payment type
        
        const tipoMoneda = ($('#tipo-moneda').html() || '').trim() == 'Bolívares' ? 'Bs.' : '$';

        // Initialize payment data from server-side using JSON.parse for better type safety
        const paymentSession = {
            metodoPago: "{{ addslashes(session('pago_cliente.metodo_pago', '')) }}",
            tipoPago: "{{ addslashes(session('pago_cliente.tipo_pago', '')) }}"
        };

        let selPedidos = {!! json_encode($pedidos_seleccionados) !!};
        var numeroPedidos = 0;
        if (typeof selPedidos === 'string') {
            // Si es una cadena separada por comas
            numeroPedidos = selPedidos.trim() === '' ? 0 : selPedidos.split(',').length;
        } else if (Array.isArray(selPedidos)) {
            numeroPedidos = selPedidos.length;
        } else if (typeof selPedidos === 'object' && selPedidos !== null) {
            numeroPedidos = Object.keys(selPedidos).length;
        }

        // Initialize payment data
        const paymentData = (function() {
            try {
                // @ts-ignore
                const initialAmount = JSON.parse('{{ json_encode($total_bolivares ?? ($montoMostrar ?? 0)) }}');
                const amount = typeof initialAmount === 'number' ? initialAmount : Number(initialAmount) || 0;
                const amountFloat = parseFloat(amount.toString()) || 0;

                return {
                    montoMostrar: amount,
                    saldoPendiente: amountFloat,
                    totalAPagar: amountFloat
                };
            } catch (error) {
                console.error('Error initializing payment data:', error);
                return {
                    montoMostrar: 0,
                    saldoPendiente: 0,
                    totalAPagar: 0
                };
            }
        })();

        // Función para actualizar la tabla de pagos
        function actualizarTablaPagos() {
            const tbody = $('#pagos-body');
            tbody.empty();

            if (pagos.length === 0) {
                tbody.html('<tr><td colspan="8" class="text-center">No hay pagos registrados</td></tr>');
                return;
            }

            let totalPagos = 0;

            pagos.forEach((pago, index) => {
                const tr = $('<tr>');

                // Formatear el monto
                const monto = parseFloat(pago.monto) || 0;
                totalPagos += monto;

                // Determinar la moneda y el monto correcto
                const esBolivares = pago.moneda_pago === 'bs' || (pago.monto_bs && parseFloat(pago.monto_bs) > 0);
                const montoFinal = esBolivares ? (parseFloat(pago.monto_bs) || monto) : monto;
                const simboloMoneda = esBolivares ? 'Bs. ' : '$';

                const montoFormateado = simboloMoneda + new Intl.NumberFormat('es-VE', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(montoFinal);

                // Formatear la fecha si existe
                let fechaFormateada = 'N/A';
                if (pago.fecha_pago) {
                    const fecha = new Date(pago.fecha_pago);
                    // Mostrar la fecha en formato dd/mm/YYYY
                    const dia = String(fecha.getDate()).padStart(2, '0');
                    const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                    const anio = fecha.getFullYear();
                    fechaFormateada = `${dia}/${mes}/${anio}`;
                }

                tr.html(`
                <td>${pago.referencia || 'N/A'}</td>
                <td>${pago.banco_origen || 'N/A'}</td>
                <td>${pago.banco_destino || 'N/A'}</td>
                <td class="text-right">${montoFormateado}</td>
                <td>${fechaFormateada}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm btn-eliminar-pago" data-index="${index}" title="Eliminar pago">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `);

                tbody.append(tr);
            });
        }

        // Actualizar el monto total pagado
        function actualizarTotalPagado() {
            totalPagado = pagos.reduce((total, pago) => total + parseFloat(pago.monto || 0), 0);
            const simboloMoneda = ($('#tipo-moneda').html() || '').trim() == 'Bolívares' ? 'Bs.' : '$';
            $('#total-pagado').text(totalPagado.toFixed(2) + ' ' + simboloMoneda);
            actualizarMontoPendiente();
        }

        // Actualizar el monto pendiente por pagar
        function actualizarMontoPendiente() {
            // Usar paymentData.totalAPagar si está disponible, de lo contrario usar 0
            //const totalAPagar = paymentData?.totalAPagar || 0;
            const totalAPagar = {{ json_encode((float)($totalConAjustes ?? 0)) }};
            console.log('totalAPagar');
            console.log(totalAPagar);
            const montoPendiente = parseFloat((totalAPagar - totalPagado).toFixed(2));
            const simboloMoneda = ($('#tipo-moneda').html() || '').trim() == 'Bolívares' ? 'Bs.' : '$';

            // Actualizar el monto pendiente en la interfaz
            $('#monto-pendiente').text(montoPendiente.toFixed(2) + ' ' + simboloMoneda);
            $('#saldo-pendiente').text(montoPendiente.toFixed(2) + ' ' + simboloMoneda);
            $('#monto_bs').val(montoPendiente.toFixed(2));

            // Actualizar clase CSS según el estado del pago
            const pendienteElement = $('#monto-pendiente').closest('tr');
            pendienteElement.removeClass('table-success table-danger');
            console.warn('montoPendiente:', montoPendiente);


            // Al habilitar el botón, agregamos el evento para enviar el formulario con los datos necesarios
            $('#btn-aplicar-cambios').off('click').on('click', function(e) {
                e.preventDefault();

                // Guardar los pagos en el campo oculto como JSON
                $('#pagos-json').val(JSON.stringify(pagos));

                // Guardar los pedidos seleccionados en un campo oculto
                if ($('#pedidos-seleccionados-json').length === 0) {
                    $('<input>').attr({
                        type: 'hidden',
                        id: 'pedidos-seleccionados-json',
                        name: 'pedidos_seleccionados'
                    }).appendTo('#multiplePaymentsForm');
                }
                $('#pedidos-seleccionados-json').val('{{ json_encode($pedidos_seleccionados) }}');
                $('#rate-json').val(JSON.stringify({{ $tasa_bcv }}));

                // Enviar el formulario
                $('#multiplePaymentsForm').submit();
            });

            // Habilitar o deshabilitar el botón de aplicar cambios
            if (montoPendiente <= 0) {
                console.log('Habilitando botón de aplicar cambios');
                pendienteElement.addClass('table-success');
                $('#btn-aplicar-cambios').prop('disabled', false);
                $('#btn-agregar-pago').prop('disabled', true);

                
            } else {
                pendienteElement.addClass('table-danger');
                $('#btn-aplicar-cambios').prop('disabled', true);
                $('#btn-agregar-pago').prop('disabled', false);
            }
            if (numeroPedidos == 1) {
                $('#btn-aplicar-cambios').prop('disabled', false);
                // Guardar los pagos en el campo oculto como JSON
                $('#pagos-json').val(JSON.stringify(pagos));
                if ($('#pedidos-seleccionados-json').length === 0) {
                    $('<input>').attr({
                        type: 'hidden',
                        id: 'pedidos-seleccionados-json',
                        name: 'pedidos_seleccionados'
                    }).appendTo('#multiplePaymentsForm');
                }
                $('#pedidos-seleccionados-json').val('{{ json_encode($pedidos_seleccionados) }}');
                $('#rate-json').val(JSON.stringify({{ $tasa_bcv }}));
            }
        }

        // Actualizar la lista de pagos en la interfaz
        function actualizarListaPagos() {
            const tbody = $('#pagos-lista tbody');
            tbody.empty();

            // Mostrar/ocultar mensaje de "No hay pagos"
            if (pagos.length > 0) {
                $('#sin-pagos').hide();
                $('#pagos-lista').show();
            } else {
                $('#sin-pagos').show();
                $('#pagos-lista').hide();
            }

            // Clear previous content
            tbody.empty();

            if (pagos.length === 0) {
                $('#sin-pagos').show();
                $('#pagos-lista').hide();
                return;
            }

            // Show the table and hide the 'no payments' message
            $('#sin-pagos').hide();
            $('#pagos-lista').show();

            // Add each payment to the table
            pagos.forEach((pago, index) => {
                const fecha = new Date(pago.fecha_pago);
                // Formatear la fecha en formato d/m/Y
                const dia = String(fecha.getDate()).padStart(2, '0');
                const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                const anio = fecha.getFullYear();
                const fechaFormateada = `${dia}/${mes}/${anio}`;

                // Format amount with thousands separator and 2 decimal places
                const montoFormateado = new Intl.NumberFormat('es-ES', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(parseFloat(pago.monto));

                const tr = $(`
                <tr data-index="${index}">
                    <td class="align-middle">${pago.referencia || 'N/A'}</td>
                    <td class="align-middle">
                        ${(pago.tipo_pago === 'bolivares') ? (pago.banco_origen || 'N/A') : ''}
                    </td>
                    <td class="align-middle">${pago.banco_destino || 'N/A'}</td>
                    <td class="align-middle text-end">${montoFormateado} ${tipoMoneda}</td>
                    <td class="align-middle">${fechaFormateada}</td>
                    <td class="text-center align-middle">
                        <button type="button" class="btn btn-danger btn-sm btn-eliminar-pago" data-index="${index}" title="Eliminar pago">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `);

                tbody.append(tr);
            });
        }

        // Función para eliminar un pago de la lista
        function eliminarPago(index) {
            if (index >= 0 && index < pagos.length) {
                // Mostrar confirmación antes de eliminar
                Swal.fire({
                    title: '¿Está seguro?',
                    text: '¿Desea eliminar este pago de la lista?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Guardar el monto del pago que se va a eliminar
                        const montoEliminado = parseFloat(pagos[index].monto) || 0;

                        // Eliminar el pago del array
                        pagos.splice(index, 1);

                        // Actualizar el total pagado
                        if (paymentData.totalPagado >= montoEliminado) {
                            paymentData.totalPagado -= montoEliminado;
                        }

                        // Actualizar la interfaz
                        actualizarListaPagos();
                        actualizarResumenPago();

                        // Actualizar el campo oculto del formulario
                        actualizarPagosJson();

                        // Mostrar mensaje de éxito
                        Swal.fire(
                            '¡Eliminado!',
                            'El pago ha sido eliminado de la lista.',
                            'success'
                        );

                        // Si no hay más pagos, ocultar la tabla
                        if (pagos.length === 0) {
                            $('#pagos-lista').hide();
                            $('#sin-pagos').show();
                        }
                    }
                });
            } else {
                console.error('Índice de pago inválido:', index);
            }
        }

        // Handle delete payment button click
        $(document).on('click', '.btn-eliminar-pago', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const index = $(this).data('index');
            eliminarPago(index);
        });

        // Función para agregar un pago a la lista
        function agregarPago() {
            // Obtener los datos del formulario
            const monto = parseFloat($('#monto_bs').val()) || 0;
            const tipoPago = $('#tpago_id option:selected').text();
            const referencia = $('#referencia').val() || '';
            const bancoOrigen = $('#banco_codigo option:selected').text();
            const bancoDestino = $('#pago_destino_id option:selected').text();
            const fechaPago = $('#fecha_pago').val();

            // Validar el monto
            if (monto <= 0) {
                Swal.fire('Error', 'El monto debe ser mayor a cero', 'error');
                return;
            }

            // Verificar que se haya seleccionado un tipo de pago
            if (!tipoPago) {
                Swal.fire('Error', 'Debe seleccionar un tipo de pago', 'error');
                return;
            }

            // Verificar que se haya seleccionado un banco de destino (obligatorio para todos los pagos)
            if (!bancoDestino || bancoDestino === 'Seleccione un destino') {
                Swal.fire('Error', 'Debe seleccionar un Banco Receptor', 'error');
                $('#pago_destino_id').focus();
                return;
            }

            // Verificar referencia para transferencias
            if ((tipoPago === 'Transferencia' || tipoPago === 'Pago Móvil') && !referencia) {
                Swal.fire('Error', 'Debe ingresar un número de referencia', 'error');
                return;
            }

            // Crear objeto de pago con los valores de la sesión
            const nuevoPago = {
                monto: parseFloat(monto),
                metodo: paymentSession.metodoPago,
                tipo_pago: paymentSession.tipoPago,
                referencia: referencia || '',
                banco_origen: bancoOrigen || '',
                banco_destino: bancoDestino || '',
                fecha: new Date().toISOString(),
                estado: 'Pendiente',
                pago_destino_id: $('#pago_destino_id').val() || null,
                fecha_pago: fechaPago || null,
                tpago_id: $('#tpago_id').val() || null,
                banco_codigo: $('#banco_codigo').val() || null,
                descripcion: $('#descripcion').val() || '',
                photo: $('#photo')[0]?.files?.length ? $('#photo')[0].files[0] : null
            };
            //
            pagos.push(nuevoPago);

            // Actualizar el total pagado
            totalPagado = pagos.reduce((total, pago) => total + parseFloat(pago.monto || 0), 0);
            console.warn('pagos:')
            console.warn(pagos)
            console.warn('totalPagado:')
            console.warn(totalPagado)
            // Actualizar la interfaz
            actualizarListaPagos();
            actualizarTotalPagado();
            actualizarMontoPendiente();

            // Limpiar el formulario
            $('#monto').val('');
            //$('#monto_bs').val('');
            $('#referencia').val('');

            // Mostrar mensaje de éxito
            Swal.fire({
                title: '¡Éxito!',
                text: 'Pago agregado correctamente',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        }

        // Variables globales
        let totalAPagar = 0;
        let totalPagado = 0;
        const pagos = [];

        $(document).ready(function() {
            // Inicializar tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();

            // Establecer fecha actual por defecto
            const today = new Date().toISOString().split('T')[0];
            const fechaPagoInput = document.getElementById('fecha_pago');

            if (fechaPagoInput) {
                fechaPagoInput.value = today;
            }

            // Inicializar la interfaz
            actualizarListaPagos();
            actualizarTotalPagado();
            actualizarMontoPendiente();

            // Ocultar la tabla de pagos inicialmente si no hay pagos
            if (pagos.length === 0) {
                $('#pagos-lista').hide();
                $('#sin-pagos').show();
            }

            // Handle Add Payment button click
            const addPaymentBtn = document.getElementById('btn-agregar-pago');
            if (addPaymentBtn) {
                addPaymentBtn.addEventListener('click', function() {
                    agregarPago();
                });
            }

            // Handle payment deletion
            document.addEventListener('click', function(e) {
                const deleteBtn = e.target.closest('.btn-eliminar-pago');
                if (!deleteBtn) return;

                e.preventDefault();
                const index = deleteBtn.getAttribute('data-index');
                const tbody = document.getElementById('pagos-body');
                if (!tbody) return;

                // Clear existing rows
                tbody.innerHTML = '';

                // Rebuild the table with remaining payments
                pagos.forEach(function(pago, idx) {
                    if (idx.toString() === index) return; // Skip the deleted payment

                    const pedidoNumero = pago.pedido_numero || 'N/A';
                    const descripcion = pago.descripcion || '';
                    
                    // Determinar la moneda y el monto correcto
                    const esBolivares = pago.moneda_pago === 'bs' || (pago.monto_bs && parseFloat(pago.monto_bs) > 0);
                    const montoFinal = esBolivares ? (parseFloat(pago.monto_bs) || pago.monto || 0) : (pago.monto || 0);
                    const simboloMoneda = esBolivares ? 'Bs. ' : '$';
                    const monto = simboloMoneda + formatNumber(montoFinal);

                    // Create table row
                    const tr = document.createElement('tr');
                    tr.setAttribute('data-index', idx);

                    // Create cells
                    [
                        pedidoNumero,
                        descripcion,
                        'Ref ' + monto,
                        '' // For delete button
                    ].forEach((text, i) => {
                        const td = document.createElement('td');
                        if (i === 3) {
                            // Add delete button to last cell
                            const deleteBtn = document.createElement('button');
                            deleteBtn.type = 'button';
                            deleteBtn.className =
                                'btn btn-sm btn-outline-danger btn-eliminar-pago';
                            deleteBtn.setAttribute('data-index', idx);

                            const icon = document.createElement('i');
                            icon.className = 'fas fa-trash';

                            deleteBtn.appendChild(icon);
                            td.appendChild(deleteBtn);
                        } else {
                            td.textContent = text;
                        }
                        tr.appendChild(td);
                    });

                    tbody.appendChild(tr);
                });

                // Remove the payment from the array
                pagos.splice(parseInt(index, 10), 1);

                // Update the UI
                actualizarTotales();
            });

            // Evento para actualizar el monto máximo cuando se selecciona un pedido
            $('#pedido_id').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                // @ts-ignore
                const saldo = parseFloat(selectedOption.data('saldo') || 0);

                if (saldo > 0) {
                    $('#monto_pago').attr('max', saldo);
                    $('#saldo-disponible').text(formatNumber(saldo));
                    $('#agregar-pago').prop('disabled', false);
                } else {
                    $('#monto_pago').attr('max', '');
                    $('#saldo-disponible').text('0,00');
                    $('#agregar-pago').prop('disabled', true);
                }

                $('#monto_pago').val('');
            });

            // Evento para agregar un pago
            $('#agregar-pago').on('click', function(e) {
                e.preventDefault();
                agregarPago();
            });

            // Permitir agregar con Enter
            $('#monto_pago').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    if (!$('#agregar-pago').prop('disabled')) {
                        agregarPago();
                    }
                }
            });

            // Evento para eliminar un pago
            $(document).on('click', '.btn-eliminar-pago', function() {
                const index = $(this).data('index');
                if (index >= 0 && index < pagos.length) {
                    Swal.fire({
                        title: '¿Está seguro?',
                        text: '¿Desea eliminar este pago?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            pagos.splice(index, 1);
                            actualizarListaPagos();
                            actualizarTotalPagado();
                            actualizarPagosJSON();
                        }
                    });
                }
            });

            // Validar el formulario antes de enviar
            $('#multiplePaymentsForm').on('submit', function(e) {
                if (pagos.length === 0) {
                    e.preventDefault();
                    Swal.fire('Error', 'Debe agregar al menos un pago', 'error');
                    return false;
                }

                // Validar que el total de pagos no exceda el total a pagar
                /*
                if (totalPagado > totalAPagar) {
                    e.preventDefault();
                    const excedente = totalPagado - totalAPagar;
                    Swal.fire({
                        icon: 'error',
                        title: 'Monto Excedido',
                        html: `El monto total de los pagos (${formatNumber(totalPagado)} $) excede el total a pagar (${formatNumber(totalAPagar)} $).<br>Excede por ${formatNumber(excedente)} $.`,
                        confirmButtonText: 'Corregir'
                    });
                    return false;
                }
                */

                // Verificar que la suma de los pagos cubra el total
                if (totalPagado < totalAPagar) {
                    e.preventDefault();
                    const faltante = totalAPagar - totalPagado;
                    Swal.fire({
                        title: 'Atención',
                        html: `El monto total de los pagos (${formatNumber(totalPagado)} $) no cubre el monto a pagar (${formatNumber(totalAPagar)} $).<br>Faltan ${formatNumber(faltante)} $ por cubrir.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Continuar de todos modos',
                        cancelButtonText: 'Seguir editando'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#multiplePaymentsForm').off('submit').submit();
                        }
                    });
                    return false;
                }

                // Mostrar confirmación antes de enviar
                e.preventDefault();
                Swal.fire({
                    title: '¿Está seguro?',
                    text: '¿Desea procesar los pagos registrados?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, procesar pagos',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#multiplePaymentsForm').off('submit').submit();
                    }
                });
            });

            // Inicializar la interfaz
            actualizarListaPagos();
            actualizarTotalPagado();

            // Función para formatear números con separador de miles y 2 decimales
            function formatNumber(number) {
                // Asegurarse de que number sea un número
                number = parseFloat(number) || 0;
                return number.toLocaleString('es-ES', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            // Función para parsear un valor formateado a número
            function parseFormattedNumber(formattedValue) {
                if (!formattedValue) return 0;
                // Para inputs de tipo number, parseFloat funciona directamente
                // ya que el navegador maneja el formato de número según la configuración regional
                return parseFloat(formattedValue) || 0;
            }

            // Función para calcular y actualizar monto_bs
            function updateMontoBs() {
                // Con input type="number", el valor ya es numérico
                const monto = parseFloat($('#monto').val()) || 0;
                const rate = parseFloat($('#rate').val()) || 0;
                const monto_bs = monto * rate;

                console.log('Calculando monto_bs:', monto, '*', rate, '=', monto_bs);

                // Actualizar el campo monto_bs con el valor calculado
                //$('#monto_bs').val(monto_bs.toFixed(2));

                // Guardar el valor sin formato en un campo oculto para el envío del formulario
                if (!$('#monto_bs_hidden').length) {
                    $('<input>').attr({
                        type: 'hidden',
                        id: 'monto_bs_hidden',
                        name: 'monto_bs',
                        value: monto_bs.toFixed(2)
                    }).appendTo('#multiplePaymentsForm');
                    // Cambiar el name del campo visible
                    $('#monto_bs').attr('name', 'monto_bs_formatted');
                } else {
                    $('#monto_bs_hidden').val(monto_bs.toFixed(2));
                }
            }

            // Función para calcular y actualizar monto cuando cambia monto_bs
            function updateMonto() {
                // Con input type="number", el valor ya es numérico
                const monto_bs = parseFloat($('#monto_bs').val()) || 0;
                const rate = parseFloat($('#rate').val()) || 0;

                console.log('Calculando monto:', monto_bs, '/', rate);

                // Evitar división por cero
                if (rate > 0) {
                    const monto = monto_bs / rate;

                    console.log('Resultado monto:', monto);

                    // Actualizar el campo monto con el valor calculado
                    // Forzar la actualización del valor
                    document.getElementById('monto').value = monto.toFixed(2);
                    console.log('Valor asignado a monto:', monto.toFixed(2));
                    console.log('Valor actual en el DOM:', document.getElementById('monto').value);

                    // Guardar el valor sin formato en un campo oculto para el envío del formulario
                    if (!$('#monto_hidden').length) {
                        $('<input>').attr({
                            type: 'hidden',
                            id: 'monto_hidden',
                            name: 'monto',
                            value: monto.toFixed(2)
                        }).appendTo('#multiplePaymentsForm');
                        // Cambiar el name del campo visible
                        $('#monto').attr('name', 'monto_formatted');
                    } else {
                        $('#monto_hidden').val(monto.toFixed(2));
                    }
                }
            }

            // Manejar cambios en los campos monto y rate
            $('#monto').on('input', function() {
                // Con input type="number", el valor ya es numérico
                const monto = parseFloat($(this).val()) || 0;
                console.log('Valor monto ingresado:', $(this).val(), 'parseado como:', monto);

                // Actualizar el campo oculto
                if ($('#monto_hidden').length) {
                    $('#monto_hidden').val(monto.toFixed(2));
                } else {
                    $('<input>').attr({
                        type: 'hidden',
                        id: 'monto_hidden',
                        name: 'monto',
                        value: monto.toFixed(2)
                    }).appendTo('#multiplePaymentsForm');
                    // Cambiar el name del campo visible para evitar duplicados
                    $(this).attr('name', 'monto_formatted');
                }

                // Actualizar monto_bs
                updateMontoBs();
            });

            $('#rate').on('input', function() {
                updateMontoBs();
            });

            // Manejar cambios en el campo monto_bs
            $('#monto_bs').on('input', function() {
                // Con input type="number", el valor ya es numérico
                const monto_bs = parseFloat($(this).val()) || 0;
                console.log('Valor monto_bs ingresado:', $(this).val(), 'parseado como:', monto_bs);

                // Actualizar el campo oculto
                if ($('#monto_bs_hidden').length) {
                    $('#monto_bs_hidden').val(monto_bs.toFixed(2));
                } else {
                    $('<input>').attr({
                        type: 'hidden',
                        id: 'monto_bs_hidden',
                        name: 'monto_bs',
                        value: monto_bs.toFixed(2)
                    }).appendTo('#multiplePaymentsForm');
                    // Cambiar el name del campo visible para evitar duplicados
                    $(this).attr('name', 'monto_bs_formatted');
                }

                // Actualizar monto inmediatamente
                updateMonto();

                // Verificar que el valor de monto se haya actualizado
                console.log('Valor de monto después de updateMonto:', $('#monto').val());
            });

            // Manejar selección/deselección de todos
            $('#selectAll').on('change', function() {
                $('.pedidoCheckbox').prop('checked', this.checked);

                // Habilitar/deshabilitar los campos de monto
                $('.pedidoCheckbox').each(function() {
                    const pedidoId = $(this).data('pedido-id');
                    const isChecked = $(this).prop('checked');
                    $(`input.payment-amount[data-pedido-id="${pedidoId}"]`).prop('disabled', !
                        isChecked);

                    // Actualizar la apariencia visual
                    if (isChecked) {
                        $(`tr[data-pedido-id="${pedidoId}"]`).addClass('table-active');
                        $(`span.payment-status[data-pedido-id="${pedidoId}"]`).addClass(
                            'bg-primary text-white').text('Total');
                    } else {
                        $(`tr[data-pedido-id="${pedidoId}"]`).removeClass('table-active');
                        $(`span.payment-status[data-pedido-id="${pedidoId}"]`).removeClass(
                            'bg-primary text-white bg-warning text-dark').text('-');
                    }
                });

                updateTotal();
            });

            // Manejar selección individual
            $('.pedidoCheckbox').on('change', function() {
                const pedidoId = $(this).data('pedido-id');
                const isChecked = $(this).prop('checked');

                // Habilitar/deshabilitar el campo de monto
                $(`input.payment-amount[data-pedido-id="${pedidoId}"]`).prop('disabled', !isChecked);

                // Actualizar la apariencia visual
                if (isChecked) {
                    $(`tr[data-pedido-id="${pedidoId}"]`).addClass('table-active');
                    $(`span.payment-status[data-pedido-id="${pedidoId}"]`).addClass('bg-primary text-white')
                        .text('Total');

                    // Si es el primer pedido seleccionado, filtrar por cliente
                    if (!filtroActivo) {
                        // Obtener el cliente del pedido seleccionado
                        const clienteTexto = $(`tr[data-pedido-id="${pedidoId}"]`).find('td:eq(3)').text()
                            .trim();
                        clienteSeleccionado = clienteTexto;
                        filtroActivo = true;

                        // Mostrar mensaje de filtro activo
                        if ($('#filtroClienteAlert').length === 0) {
                            $('#totalAlert').before(
                                `<div id="filtroClienteAlert" class="alert alert-info">
                            <button type="button" class="close" id="resetFiltroCliente">&times;</button>
                            <strong>Filtro activo:</strong> Mostrando solo pedidos del cliente: <span id="clienteSeleccionadoNombre">${clienteSeleccionado}</span>
                        </div>`
                            );
                        } else {
                            $('#filtroClienteAlert').show();
                            $('#clienteSeleccionadoNombre').text(clienteSeleccionado);
                        }

                        // Filtrar la tabla para mostrar solo pedidos del mismo cliente
                        filtrarPorCliente(clienteSeleccionado);
                    }
                } else {
                    $(`tr[data-pedido-id="${pedidoId}"]`).removeClass('table-active');
                    $(`span.payment-status[data-pedido-id="${pedidoId}"]`).removeClass(
                        'bg-primary text-white bg-warning text-dark').text('-');


                }

                updateTotal();
            });

            // Función para actualizar el total
            function formatCurrency(amount) {
                return '$' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            }

            function updateTotal() {
                let total = 0;
                let selectedCount = 0;
                paymentData.totalAPagar = 0; // Reset totalAPagar before recalculation

                // Recalculate totalAPagar based on selected orders
                $('.pedidoCheckbox:checked').each(function() {
                    const monto = parseFloat($(this).closest('tr').find('.payment-amount').val()) || 0;
                    paymentData.totalAPagar += monto;
                    selectedCount++;
                });

                // Update the total display
                $('#total-a-pagar').text(paymentData.totalAPagar.toFixed(2));

                // Update selected orders count in the card
                $('#pedidos-seleccionados-cantidad').text(selectedCount);

                // Get the current exchange rate from the hidden field or session
                var tasaCambio = parseFloat($('#rate').val()) || parseFloat('{{ session('tasa_cambio', 1) }}');

                $('.pedidoCheckbox:checked').each(function() {
                    const pedidoId = $(this).data('pedido-id');
                    const paymentAmount = parseFloat($(`input.payment-amount[data-pedido-id="${pedidoId}"]`)
                        .val()) || 0;
                    total += paymentAmount;
                });

                // Update the total in the cards
                const formattedTotal = total.toLocaleString('es-VE', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

                // Update the total in the form
                $('#total-pagar').text(formattedTotal);
                $('#monto_total').val(total.toFixed(2));

                // Update the cards based on currency type
                const tipoPago = '{{ session('pago_cliente.tipo_pago') }}';
                var tasaCambio = parseFloat($('#rate').val()) || parseFloat('{{ session('tasa_cambio', 1) }}');

                if (tipoPago === 'bolivares') {
                    const totalBs = (total * tasaCambio).toLocaleString('es-VE', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });

                    // Ensure the exchange rate is displayed with proper formatting
                    $('#tasa-cambio-texto').html(`
                <i class="fas fa-exchange-alt me-1"></i>
                Bs. ${tasaCambio.toLocaleString('es-VE', { 
                    minimumFractionDigits: 2, 
                    maximumFractionDigits: 2 
                })}
            `);

                    // Update both USD and Bs. amounts
                    $('#total-bolivares').text(totalBs);
                    $('#total-dolares').text(formattedTotal);
                } else {
                    // For USD payments
                    $('#total-dolares').text(formattedTotal);
                }

                // Actualizar el display del total
                $('#totalSelected').text('Ref. ' + total.toFixed(2));
                $('#totalAlert').toggle(total > 0);

                // Actualizar monto_bs cuando cambia el monto
                updateMontoBs();
            }

            // Botón de enviar
            $('#multiplePaymentsFormBORRAR').on('submit', function(e) {

                // Verificar que todos los campos requeridos tengan valores
                const monto = parseFloat($('#monto').val()) || 0;
                const rate = parseFloat($('#rate').val()) || 0;
                const monto_bs = parseFloat($('#monto_bs').val()) || 0;

                if (monto <= 0 || rate <= 0 || monto_bs <= 0) {
                    e.preventDefault();
                    alert('Por favor, complete todos los campos de monto, tasa y monto en $');
                    return false;
                }

                // Verificar que al menos un pedido tenga un monto mayor a cero
                let hayMontosValidos = false;
                $('.pedidoCheckbox:checked').each(function() {
                    const pedidoId = $(this).data('pedido-id');
                    const montoIngresado = parseFloat($(
                        `input.payment-amount[data-pedido-id="${pedidoId}"]`).val()) || 0;
                    if (montoIngresado > 0) {
                        hayMontosValidos = true;
                    }
                });

                if (!hayMontosValidos) {
                    e.preventDefault();
                    alert(
                        'Debe ingresar al menos un monto válido mayor a cero para los pedidos seleccionados.'
                    );
                    return false;
                }

                console.log('Formulario válido, enviando datos...');
                console.log('Valores para enviar al servidor:', {
                    monto: monto,
                    rate: rate,
                    monto_bs: monto_bs
                });

                // No es necesario crear campos ocultos, los campos originales ya tienen los valores correctos
                return true;
            });

            // Después de enviar el formulario, resetear el filtro (esto se ejecutará cuando el usuario vuelva a la página)
            $(document).ready(function() {
                // Obtener tasa de cambio con manejo de errores
                $.ajax({
                    url: URL_TASA,
                    type: 'GET',
                    success: function(response) {
                        if (response && response.data && response.data.rate) {
                            $('#rate').val(response.data.rate);
                            // Actualizar monto_bs cuando se obtiene la tasa
                            if (typeof updateMontoBs === 'function') {
                                updateMontoBs();
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al obtener la tasa de cambio:', error);
                    }
                });

                // Manejar cambio en el monto de pago
                $(document).on('input', '.payment-amount', function() {
                    const $this = $(this);
                    const pedidoId = $this.data('pedido-id');
                    const saldoPendiente = parseFloat($this.data('saldo'));
                    const currentValue = parseFloat($this.val()) || 0;

                    // Validar que el monto no exceda el saldo pendiente
                    if (currentValue > saldoPendiente) {
                        $this.val(saldoPendiente.toFixed(2));
                    }

                    // Actualizar el estado de pago (total o parcial)
                    const $status = $(`span.payment-status[data-pedido-id="${pedidoId}"]`);
                    if (currentValue === saldoPendiente) {
                        $status.text('Total').removeClass('bg-warning text-dark').addClass(
                            'bg-primary text-white');
                    } else if (currentValue > 0 && currentValue < saldoPendiente) {
                        $status.text('Parcial').removeClass('bg-primary text-white').addClass(
                            'bg-warning text-dark');
                    } else {
                        $status.text('Inválido').removeClass('bg-primary text-white bg-warning')
                            .addClass('bg-danger text-white');
                    }

                    if (typeof updateTotal === 'function') {
                        updateTotal();
                    }
                });

                // Inicializar el total
                if (typeof updateTotal === 'function') {
                    updateTotal();
                }

                // Funcionalidad de búsqueda
                $('#searchPedidos').on('keyup', function() {
                    const searchText = $(this).val().toLowerCase();
                    let visibleRows = 0;

                    // Si hay un filtro de cliente activo, aplicar ambos filtros
                    if (typeof filtroActivo !== 'undefined' && filtroActivo) {
                        $('#dynamic-table tbody tr').each(function() {
                            const $row = $(this);
                            const pedidoId = $row.find('td:eq(2)').text().toLowerCase();
                            const cliente = $row.find('td:eq(3)').text().toLowerCase()
                                .trim();
                            const fecha = $row.find('td:eq(1)').text().toLowerCase();

                            // Aplicar ambos filtros: cliente seleccionado y texto de búsqueda
                            if (typeof clienteSeleccionado !== 'undefined' &&
                                cliente === clienteSeleccionado.toLowerCase() &&
                                (pedidoId.includes(searchText) || cliente.includes(
                                    searchText) || fecha.includes(searchText))) {
                                $row.show();
                                visibleRows++;
                            } else {
                                $row.hide();
                            }
                        });
                    } else {
                        // Búsqueda normal sin filtro de cliente
                        $('#dynamic-table tbody tr').each(function() {
                            const $row = $(this);
                            const pedidoId = $row.find('td:eq(2)').text().toLowerCase();
                            const cliente = $row.find('td:eq(3)').text().toLowerCase();
                            const fecha = $row.find('td:eq(1)').text().toLowerCase();

                            if (pedidoId.includes(searchText) || cliente.includes(
                                    searchText) || fecha.includes(searchText)) {
                                $row.show();
                                visibleRows++;
                            } else {
                                $row.hide();
                            }
                        });
                    }

                    // Actualizar el total solo con los pedidos visibles y seleccionados
                    if (typeof updateTotal === 'function') {
                        updateTotal();
                    }

                    // Mostrar mensaje si no hay resultados
                    if (visibleRows === 0 && searchText !== '') {
                        if ($('#no-results-message').length === 0) {
                            $('#dynamic-table tbody').append(
                                '<tr id="no-results-message"><td colspan="8" class="text-center">No se encontraron pedidos que coincidan con la búsqueda</td></tr>'
                            );
                        }
                    } else {
                        $('#no-results-message').remove();
                    }
                });
            }); // Cierre del document.ready principal

            // Función para filtrar por cliente
            function filtrarPorCliente(cliente) {
                let visibleRows = 0;

                $('#dynamic-table tbody tr').each(function() {
                    const $row = $(this);
                    const clienteRow = $row.find('td:eq(3)').text().trim().toLowerCase();

                    if (clienteRow === cliente.toLowerCase()) {
                        $(this).show();
                        visibleRows++;
                    } else {
                        $(this).hide();
                        // Desmarcar los checkboxes de los pedidos ocultos
                        $(this).find('.pedidoCheckbox').prop('checked', false);
                        const pedidoId = $(this).data('pedido-id');
                        $(`input.payment-amount[data-pedido-id="${pedidoId}"]`).prop('disabled', true);
                        $(`span.payment-status[data-pedido-id="${pedidoId}"]`).removeClass(
                            'bg-primary text-white bg-warning text-dark').text('-');
                    }
                });

                // Actualizar el total
                if (typeof updateTotal === 'function') {
                    updateTotal();
                }

                // Desactivar el checkbox "Seleccionar todos" ya que ahora solo se muestran algunos pedidos
                $('#selectAll').prop('checked', false);
            }

            // Manejar el botón para resetear el filtro de cliente
            $(document).on('click', '#resetFiltroCliente', function(e) {
                e.preventDefault();
                // Mostrar todas las filas
                $('#dynamic-table tbody tr').show();
                // Actualizar el total
                if (typeof updateTotal === 'function') {
                    updateTotal();
                }
                // Desmarcar el checkbox de seleccionar todos
                $('#selectAll').prop('checked', false);
            });

            // Manejar la vista previa de múltiples archivos
            document.getElementById('comprobantes').addEventListener('change', function(e) {
                const filePreview = document.getElementById('file-preview');
                filePreview.innerHTML = ''; // Limpiar vista previa anterior

                const files = e.target.files;
                const maxSize = 5 * 1024 * 1024; // 5MB en bytes
                const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];

                for (let i = 0; i < files.length; i++) {
                    const file = files[i];

                    // Validar tipo de archivo
                    if (!allowedTypes.includes(file.type)) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Tipo de archivo no permitido',
                            text: `El archivo ${file.name} no es un tipo de archivo permitido. Por favor, suba solo imágenes JPG, PNG o PDF.`,
                            confirmButtonText: 'Entendido'
                        });
                        this.value = ''; // Limpiar el input
                        filePreview.innerHTML = '';
                        return;
                    }

                    // Validar tamaño del archivo
                    if (file.size > maxSize) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Archivo demasiado grande',
                            text: `El archivo ${file.name} supera el tamaño máximo permitido de 5MB.`,
                            confirmButtonText: 'Entendido'
                        });
                        this.value = ''; // Limpiar el input
                        filePreview.innerHTML = '';
                        return;
                    }

                    // Crear elemento de vista previa
                    const previewItem = document.createElement('div');
                    previewItem.className =
                        'd-flex align-items-center justify-content-between mb-2 p-2 border rounded';

                    // Mostrar ícono según el tipo de archivo
                    let iconClass = 'fa-file';
                    if (file.type.startsWith('image/')) {
                        iconClass = 'fa-file-image';
                    } else if (file.type === 'application/pdf') {
                        iconClass = 'fa-file-pdf';
                    }

                    previewItem.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas ${iconClass} me-2"></i>
                    <span class="text-truncate" style="max-width: 200px;" title="${file.name}">${file.name}</span>
                    <small class="text-muted ms-2">(${(file.size / 1024).toFixed(2)} KB)</small>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-file" data-index="${i}">
                    <i class="fas fa-times"></i>
                </button>
            `;

                    filePreview.appendChild(previewItem);
                }
            });

            // Manejar la eliminación de archivos de la vista previa
            document.addEventListener('click', function(e) {
                if (e.target.closest('.btn-remove-file')) {
                    const button = e.target.closest('.btn-remove-file');
                    const index = parseInt(button.getAttribute('data-index'));

                    // Obtener el input de archivos
                    const fileInput = document.getElementById('comprobantes');
                    const files = Array.from(fileInput.files);

                    // Eliminar el archivo del array
                    files.splice(index, 1);

                    // Crear un nuevo DataTransfer y actualizar los archivos
                    const dataTransfer = new DataTransfer();
                    files.forEach(file => dataTransfer.items.add(file));
                    fileInput.files = dataTransfer.files;

                    // Disparar el evento change para actualizar la vista previa
                    const event = new Event('change');
                    fileInput.dispatchEvent(event);
                }
            });

            // Validar el formulario antes de enviar
            document.getElementById('multiplePaymentsForm').addEventListener('submit', function(e) {
                const fileInput = document.getElementById('comprobantes');
                const metodoPago = '{{ session('pago_cliente.metodo_pago') }}';

                // Validar comprobante para transferencias
                if (metodoPago === 'transferencia' && fileInput.files.length === 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Comprobante requerido',
                        text: 'Debe adjuntar al menos un comprobante de pago para transferencias.',
                        confirmButtonText: 'Entendido'
                    });
                    return false;
                }

                // Validar comprobante de retención SOLO si se seleccionó "Aplicar Retención"
                const opcionRetencion = document.querySelector('input[name="pago_iva_opcion"]:checked');
                if (opcionRetencion && opcionRetencion.value === 'retencion' && fileInput.files.length === 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'info',
                        title: 'Comprobante de Retención',
                        text: 'Se recomienda adjuntar el comprobante de retención cuando aplica esta opción, pero puede continuar sin él.',
                        confirmButtonText: 'Continuar sin comprobante',
                        confirmButtonColor: '#3b82f6',
                        showCancelButton: true,
                        cancelButtonText: 'Adjuntar comprobante',
                        cancelButtonColor: '#f59e0b'
                    }).then((result) => {
                        if (result.isDismissed) {
                            // Usuario quiere adjuntar comprobante, no hacer nada
                            e.preventDefault();
                            return false;
                        }
                        // Usuario quiere continuar, permitir el envío
                        return true;
                    });
                    return false;
                }

                return true;
            });

            // Abono tipo: selección visual de cards
            function inicializarAbonoTipo() {
                function aplicarMontoDesdeAbonoTipo() {
                    const $seleccion = $('input[name="abono_tipo"]:checked');
                    if (!$seleccion.length) return;

                    const montoSeleccionado = parseFloat($seleccion.data('monto')) || 0;

                    // Actualiza el monto editable principal
                    $('#monto_bs').val(montoSeleccionado.toFixed(2));

                    // Mantiene sincronizados campos usados por el formulario
                    $('#monto').val(montoSeleccionado.toFixed(2));
                    if ($('#monto_bs_hidden').length) {
                        $('#monto_bs_hidden').val(montoSeleccionado.toFixed(2));
                    }
                    if ($('#monto_hidden').length) {
                        $('#monto_hidden').val(montoSeleccionado.toFixed(2));
                    }
                }

                $('input[name="abono_tipo"]').on('change', function () {
                    var val = $(this).val();
                    $('.abono-card').each(function () {
                        var isSelected = $(this).data('value') === val;
                        if (isSelected) {
                            $(this).css({ 'border-color': '#16a34a', 'box-shadow': '0 0 0 3px rgba(22,163,74,0.2)' });
                            $(this).find('div:first-child').css({ 'border-color': '#16a34a', 'background': '#16a34a' });
                        } else {
                            $(this).css({ 'border-color': '#d1d5db', 'box-shadow': 'none' });
                            $(this).find('div:first-child').css({ 'border-color': '#d1d5db', 'background': 'white' });
                        }
                    });

                    aplicarMontoDesdeAbonoTipo();
                });
                $('.abono-card').on('click', function () {
                    var val = $(this).data('value');
                    $('input[name="abono_tipo"][value="' + val + '"]').prop('checked', true).trigger('change');
                });

                // Inicializa el monto con la opción por defecto al cargar la página
                aplicarMontoDesdeAbonoTipo();
            }
            inicializarAbonoTipo();

            // Funcionalidad para opciones de IVA con retención - Diseño garantizado
            function inicializarOpcionesIVA() {
                // Usar jQuery para mayor compatibilidad
                $('#card-retencion').on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $('#iva_retencion').prop('checked', true);
                    manejarCambioOpcion('retencion');
                });
                
                $('#card-completo').on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $('#iva_completo').prop('checked', true);
                    manejarCambioOpcion('completo');
                });
                
                // Fallback: listeners para radio buttons
                $('#iva_retencion').on('change', function() {
                    if (this.checked) {
                        manejarCambioOpcion('retencion');
                    }
                });
                
                $('#iva_completo').on('change', function() {
                    if (this.checked) {
                        manejarCambioOpcion('completo');
                    }
                });
                
                // Inicializar con la opción por defecto solo si la sección de retención existe en el DOM
                setTimeout(() => {
                    if ($('#iva_retencion').length > 0) {
                        manejarCambioOpcion('retencion');
                    }
                }, 100);
                
                // Validación del formulario
                $('#multiplePaymentsForm').on('submit', function(e) {
                    const opcionIVA = $('input[name="pago_iva_opcion"]:checked').val();
                    const fileInput = $('#comprobantes')[0];
                    
                    //if (opcionIVA === 'retencion' && fileInput.files.length === 0) {
                        //e.preventDefault();
                       // mostrarAlertaRetencion();
                        //return false;
                    //}
                });
            }
            
            function manejarCambioOpcion(opcion) {
                console.log('Cambiando a opción:', opcion); // Debug
                
                // Calcular montos - Nueva lógica
                const totalIVA = parseFloat('{{ $total_iva ?? 0 }}');
                const totalRetencion = parseFloat('{{ $total_retencion ?? 0 }}');
                // montoActual incluye ajustes para no sobreescribir el total correcto al cambiar opción IVA
                const montoActual = parseFloat('{{ $totalConAjustes ?? 0 }}');
                
                let montoAdicional = 0;
                let nuevoTotal = 0;
                let esRetencion = opcion === 'retencion';
                
                console.log('Valores:', { totalIVA, totalRetencion, montoActual }); // Debug
                
                if (esRetencion) {
                    // Aplicar retención: Mantenemos el total que viene (ya incluye retención aplicada)
                    montoAdicional = 0;
                    nuevoTotal = montoActual; // Mantener el total actual
                    mostrarAlertaRetencionDiv(true);
                } else {
                    // IVA completo: Sumamos el porcentaje de retención que se había quitado antes
                    montoAdicional = totalRetencion; // Agregar la retención que se había restado
                    nuevoTotal = montoActual + totalRetencion;
                    mostrarAlertaRetencionDiv(false);
                }
                
                console.log('Resultados:', { montoAdicional, nuevoTotal }); // Debug
                
                // Actualizar campo oculto
                $('#monto_iva_adicional').val(montoAdicional);
                
                // Actualizar totales con animación
                actualizarTotales(nuevoTotal, esRetencion);
                
                // Mostrar notificación
                mostrarNotificacion(opcion, montoAdicional, nuevoTotal);
                
                // Actualizar estado visual de los cards
                actualizarEstadoVisual(opcion);
            }
            
            function actualizarTotales(nuevoTotal, esRetencion) {
                // Actualizar el total visible con animación
                const totalBolivaresElement = $('#total-bolivares');
                if (totalBolivaresElement.length) {
                    // Animación de cambio
                    totalBolivaresElement.css({
                        'transform': 'scale(1.1)',
                        'transition': 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)'
                    });
                    
                    // Color según la operación
                    const color = esRetencion ? '#dc3545' : '#28a745';
                    totalBolivaresElement.css({
                        'color': color,
                        'font-weight': 'bold',
                        'text-shadow': `0 0 20px ${color}40`
                    });
                    
                    // Actualizar valor
                    setTimeout(() => {
                        totalBolivaresElement.text(nuevoTotal.toLocaleString('es-ES', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }));
                    }, 200);
                    
                    // Restaurar estilos
                    setTimeout(() => {
                        totalBolivaresElement.css({
                            'transform': 'scale(1)',
                            'color': '',
                            'font-weight': '',
                            'text-shadow': ''
                        });
                    }, 800);
                }
                
                // Actualizar otros campos del formulario
                $('#monto_bs, #monto').val(nuevoTotal.toFixed(2));
                
                // Actualizar total a pagar si existe
                const totalPagarElement = $('#total-pagar');
                if (totalPagarElement.length) {
                    totalPagarElement.text(nuevoTotal.toLocaleString('es-ES', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }) + ' $');
                }
            }
            
            function mostrarNotificacion(opcion, montoAdicional, nuevoTotal) {
                // Crear notificación
                const notificacion = $('<div>');
                const esRetencion = opcion === 'retencion';
                const icono = esRetencion ? 'fa-percentage' : 'fa-check-circle';
                const textoAccion = esRetencion ? 'mantiene' : 'aumenta';
                const textoOpcion = esRetencion ? 'Aplicar Retención' : 'IVA Completo';
                const colorPrincipal = esRetencion ? '#f59e0b' : '#10b981';
                const colorFondo = esRetencion ? '#fef3c7' : '#d1fae5';
                
                notificacion.css({
                    'position': 'fixed',
                    'top': '20px',
                    'right': '20px',
                    'z-index': '10000',
                    'min-width': '350px',
                    'max-width': '400px',
                    'background': `linear-gradient(135deg, ${colorFondo} 0%, rgba(255,255,255,0.9) 100%)`,
                    'border': `2px solid ${colorPrincipal}`,
                    'border-radius': '16px',
                    'padding': '20px',
                    'box-shadow': '0 10px 40px rgba(0,0,0,0.15)',
                    'transform': 'translateX(100%)',
                    'transition': 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)'
                });
                
                notificacion.html(`
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                        <div style="
                            width: 48px;
                            height: 48px;
                            border-radius: 12px;
                            background: linear-gradient(135deg, ${colorPrincipal} 0%, ${esRetencion ? '#d97706' : '#059669'} 100%);
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            color: white;
                            font-size: 20px;
                            box-shadow: 0 4px 20px ${colorPrincipal}40;
                        ">
                            <i class="fas ${icono}"></i>
                        </div>
                        <div style="flex: 1;">
                            <h5 style="margin: 0; font-size: 16px; font-weight: 700; color: ${esRetencion ? '#92400e' : '#065f46'};">
                                ¡Opción Actualizada!
                            </h5>
                        </div>
                        <button onclick="$(this).parent().parent().remove()" style="
                            background: none;
                            border: none;
                            font-size: 20px;
                            color: ${esRetencion ? '#92400e' : '#065f46'};
                            cursor: pointer;
                            padding: 0;
                            width: 24px;
                            height: 24px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            border-radius: 50%;
                            transition: all 0.3s ease;
                        ">&times;</button>
                    </div>
                    <div style="color: ${esRetencion ? '#78350f' : '#047857'};">
                        <div style="font-weight: 600; margin-bottom: 8px;">${textoOpcion}</div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;" class="d-none">
                            <span>Adicional por retención:</span>
                            <strong>Bs. ${montoAdicional.toFixed(2)}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Total ${textoAccion}:</span>
                            <strong style="color: ${colorPrincipal};">Bs. ${nuevoTotal.toFixed(2)}</strong>
                        </div>
                    </div>
                `);
                
                $('body').append(notificacion);
                
                // Animación de entrada
                setTimeout(() => {
                    notificacion.css('transform', 'translateX(0)');
                }, 100);
                
                // Cerrar automáticamente después de 5 segundos
                setTimeout(() => {
                    notificacion.remove();
                }, 5000);
            }
            
            function mostrarAlertaRetencionDiv(mostrar) {
                const alerta = $('#alerta-retencion');
                if (alerta.length) {
                    if (mostrar) {
                        alerta.css('display', 'flex');
                        alerta.css('animation', 'alertSlideIn 0.4s ease-out');
                    } else {
                        alerta.css('animation', 'alertSlideOut 0.3s ease-in');
                        setTimeout(() => {
                            alerta.css('display', 'none');
                        }, 300);
                    }
                }
            }
            
            function actualizarEstadoVisual(opcionSeleccionada) {
                console.log('Actualizando estado visual para:', opcionSeleccionada); // Debug
                
                // Obtener todos los contenedores de opciones
                $('.iva-hover').each(function() {
                    const radio = $(this).find('input[type="radio"]');
                    const card = $(this).find('.iva-card-responsive');
                    const selector = $(this).find('[id^="selector-"]');
                    const dot = $(this).find('[id^="dot-"]');
                    
                    if (radio.val() === opcionSeleccionada) {
                        $(this).addClass('iva-selected');
                        console.log('Añadiendo clase iva-selected a:', radio.val()); // Debug
                        
                        // Aplicar estilos inline directamente para mayor efecto
                        card.css({
                            'background': 'linear-gradient(145deg, #f0fdf4 0%, #dcfce7 100%) !important',
                            'border': '4px solid #10b981 !important',
                            'box-shadow': '0 20px 60px rgba(16, 185, 129, 0.4) !important',
                            'transform': 'translateY(-6px) scale(1.02) !important',
                            'position': 'relative !important'
                        });
                        
                        selector.css({
                            'background': 'linear-gradient(135deg, #10b981 0%, #059669 100%) !important',
                            'border': '4px solid #10b981 !important',
                            'transform': 'scale(1.4) !important',
                            'box-shadow': '0 8px 30px rgba(16, 185, 129, 0.5) !important'
                        });
                        
                        dot.css({
                            'background': 'white !important',
                            'transform': 'scale(2.5) !important',
                            'box-shadow': '0 0 25px rgba(255, 255, 255, 0.9) !important'
                        });
                        
                        // Agregar badge de selección
                        if (card.find('.selection-badge').length === 0) {
                            const badge = $('<div class="selection-badge">✓ SELECCIONADO</div>');
                            badge.css({
                                'position': 'absolute',
                                'top': '5px',
                                'left': '20px',
                                'background': 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
                                'color': 'white',
                                'padding': '6px 16px',
                                'border-radius': '20px',
                                'font-size': '12px',
                                'font-weight': '700',
                                'box-shadow': '0 4px 20px rgba(16, 185, 129, 0.4)',
                                'z-index': '10',
                                'animation': 'selectedBadge 0.6s ease-out'
                            });
                            card.prepend(badge);
                        }
                        
                    } else {
                        $(this).removeClass('iva-selected');
                        console.log('Removiendo clase iva-selected de:', radio.val()); // Debug
                        
                        // Restaurar estilos inline - borde blanco para ambos
                        card.css({
                            'background': 'linear-gradient(145deg, #ffffff 0%, #f8fafc 100%)',
                            'border': '3px solid #ffffff',
                            'box-shadow': '0 8px 32px rgba(0, 0, 0, 0.12)',
                            'transform': 'translateY(0) scale(1)',
                            'position': 'relative'
                        });
                        
                        selector.css({
                            'background': '#f1f5f9',
                            'border': '3px solid #ffffff',
                            'transform': 'scale(1)',
                            'box-shadow': '0 4px 20px rgba(0, 0, 0, 0.15)'
                        });
                        
                        dot.css({
                            'background': '#94a3b8',
                            'transform': 'scale(1)',
                            'box-shadow': 'none'
                        });
                        
                        // Remover badge
                        card.find('.selection-badge').remove();
                    }
                });
            }
            
            function mostrarAlertaRetencion() {
                Swal.fire({
                    icon: 'info',
                    title: 'Comprobante de Retención',
                    text: 'Se recomienda adjuntar el comprobante de retención cuando aplica esta opción.',
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#3b82f6',
                    backdrop: 'rgba(0,0,0,0.4)',
                    showClass: {
                        popup: 'animate__animated animate__fadeInDown'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutUp'
                    }
                });
            }
            
            // Animaciones CSS adicionales
            const style = document.createElement('style');
            style.textContent = `
                @keyframes alertSlideIn {
                    0% { 
                        opacity: 0;
                        transform: translateY(-20px);
                    }
                    100% { 
                        opacity: 1;
                        transform: translateY(0);
                    }
                }

                @keyframes alertSlideOut {
                    0% { 
                        opacity: 1;
                        transform: translateY(0);
                    }
                    100% { 
                        opacity: 0;
                        transform: translateY(-20px);
                    }
                }
            @keyframes pulse {
                0% {
                    transform: scale(1);
                    box-shadow: 0 0 15px rgba(255, 193, 7, 0.4);
                }
                50% {
                    transform: scale(1.05);
                    box-shadow: 0 0 25px rgba(255, 193, 7, 0.6);
                }
                100% {
                    transform: scale(1);
                    box-shadow: 0 0 15px rgba(255, 193, 7, 0.4);
                }
            }
            `;
            document.head.appendChild(style);
            
            // Inicializar cuando el DOM esté listo
            $(document).ready(function() {
                console.log('DOM listo, inicializando opciones IVA'); // Debug
                inicializarOpcionesIVA();
                
                // Inicializar modal de ajustes
                const ajustesModal = new bootstrap.Modal(document.getElementById('ajustesModal'));
                
                // Debug: verificar que la modal se puede inicializar
                console.log('Modal de ajustes inicializada:', ajustesModal);
                
                // Evento click alternativo para abrir la modal
                $('.card-header[data-bs-target="#ajustesModal"]').on('click', function(e) {
                    e.preventDefault();
                    console.log('Click en card-header detectado');
                    try {
                        ajustesModal.show();
                    } catch (error) {
                        console.error('Error al mostrar modal:', error);
                        // Fallback: usar jQuery si bootstrap no está disponible
                        $('#ajustesModal').modal('show');
                    }
                });
                
                // Evento para cerrar la modal
                $('[data-bs-dismiss="modal"]').on('click', function(e) {
                    e.preventDefault();
                    console.log('Click en botón cerrar detectado');
                    try {
                        ajustesModal.hide();
                    } catch (error) {
                        console.error('Error al cerrar modal:', error);
                        // Fallback: usar jQuery si bootstrap no está disponible
                        $('#ajustesModal').modal('hide');
                    }
                });
                
                // También cerrar al hacer clic fuera de la modal
                $('#ajustesModal').on('click', function(e) {
                    if (e.target === this) {
                        console.log('Click fuera de la modal detectado');
                        try {
                            ajustesModal.hide();
                        } catch (error) {
                            console.error('Error al cerrar modal:', error);
                            $('#ajustesModal').modal('hide');
                        }
                    }
                });
            });
         });
    </script>
@endsection
