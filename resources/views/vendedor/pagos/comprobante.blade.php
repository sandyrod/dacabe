@extends('layouts.app')

@php
    $rif = @$pago->pagos[0]->pago_pedidos[0]->pedido->rif ?? session('pago_cliente.rif');
    $cliente = \App\Models\OrderClient::select('RIF', 'NOMBRE')->where('RIF', $rif)->first();
@endphp

@section('titulo', config('app.name', 'Laravel') . ' - Comprobante de Pago')
@section('titulo_header', 'Comprobante de Pago')
@section('subtitulo_header', 'Comprobante de Pago ')

@section('styles')
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            padding: 20px;
        }

        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #190876ff 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.2em;
            margin-bottom: 10px;
            font-weight: 300;
            letter-spacing: 2px;
        }

        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        .content {
            padding: 30px;
        }

        .client-info {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-left: 4px solid #667eea;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-weight: 600;
            color: #4a5568;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 1.1em;
            color: #2d3748;
            font-weight: 500;
        }

        .section-title {
            font-size: 1.4em;
            color: #2d3748;
            margin: 30px 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
            font-weight: 600;
        }

        .table-container {
            overflow-x: auto;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        thead {
            background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%);
            color: white;
        }

        th {
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 0.9em;
            letter-spacing: 0.5px;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            color: #4a5568;
        }

        tbody tr:hover {
            background-color: #f7fafc;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .totals-section {
            background: linear-gradient(135deg, #1a202c 0%, #2d3748 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin-top: 30px;
        }

        .totals-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .total-item {
            text-align: center;
            padding: 15px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            backdrop-filter: blur(10px);
        }

        .total-label {
            font-size: 0.9em;
            opacity: 0.8;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .total-value {
            font-size: 1.3em;
            font-weight: 700;
        }

        .currency-symbol {
            font-size: 0.9em;
            opacity: 0.8;
        }

        .footer {
            background: #f8fafc;
            padding: 20px;
            text-align: center;
            color: #718096;
            font-size: 0.9em;
            border-top: 1px solid #e2e8f0;
        }

        @media (max-width: 768px) {
            .receipt-container {
                margin: 10px;
            }

            .content {
                padding: 20px;
            }

            .header {
                padding: 20px;
            }

            .header h1 {
                font-size: 1.8em;
            }

            th,
            td {
                padding: 8px 6px;
                font-size: 0.9em;
            }

            .totals-grid {
                grid-template-columns: 1fr;
            }
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            background: linear-gradient(135deg, #e6eebbff 0%, #eec80aff 100%);
            color: #2d3748;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    </style>
    </style>
@endsection

@section('content')
    <div class="receipt-container">
        <div class="header">
            <h1>RECIBO DE PAGO</h1>
            <p>Comprobante de Transacción</p>
            <span class="status-badge">
                {{ optional($pago->pagos->first())->estatus ?? 'Sin estatus' }}
            </span><br>
            <a href="{{ url('vendedores/pagos/clientes') }}"
                style="
                display: inline-block;
                margin-top: 18px;
                padding: 10px 28px;
                background: linear-gradient(135deg, #d7dde6 0%, #bfc9d1 50%, #8e9ba7 100%);
                color: #222b3a;
                border: none;
                border-radius: 25px;
                font-size: 1em;
                font-weight: 600;
                text-decoration: none;
                box-shadow: 0 2px 8px rgba(142,155,167,0.10);
                transition: background 0.2s, box-shadow 0.2s;
               "
                onmouseover="this.style.background='linear-gradient(135deg, #e5e9f2 0%, #cfd8e3 100%)';this.style.boxShadow='0 4px 16px rgba(191,201,209,0.18)';"
                onmouseout="this.style.background='linear-gradient(135deg, #d7dde6 0%, #bfc9d1 50%, #8e9ba7 100%)';this.style.boxShadow='0 2px 8px rgba(142,155,167,0.10)';">
                ← Volver a Pagos
            </a>
        </div>

        <div class="content">

            @if(session('aviso_retencion'))
            <div style="background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); border: 2px solid #f97316; border-radius: 12px; padding: 20px 24px; margin-bottom: 24px; display: flex; gap: 16px; align-items: flex-start;">
                <div style="flex-shrink: 0; width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 22px; box-shadow: 0 4px 16px rgba(249,115,22,0.35);">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div>
                    <strong style="display: block; font-size: 16px; font-weight: 700; color: #9a3412; margin-bottom: 6px;">
                        Retención de IVA pendiente
                    </strong>
                    <span style="font-size: 14px; color: #c2410c; line-height: 1.5;">
                        Se aplicó retención en el/los pedido(s) <strong>#{{ implode(', #', session('aviso_retencion.pedidos')) }}</strong>.
                        El saldo de IVA pendiente de <strong>Bs. {{ number_format(session('aviso_retencion.total_retencion_pendiente'), 2, ',', '.') }}</strong>
                        quedará registrado hasta que el administrador reciba y registre el comprobante de retención.
                    </span>
                    <div style="margin-top: 10px; padding: 10px 14px; background: rgba(249,115,22,0.1); border-radius: 8px; font-size: 13px; color: #7c2d12; font-weight: 500;">
                        <i class="fas fa-info-circle me-1"></i>
                        El administrador ha sido notificado. El pedido solo quedará completamente saldado una vez que se registre el comprobante de retención.
                    </div>
                </div>
            </div>
            @endif

            <div class="client-info">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Cliente</span>
                        <span class="info-value">{{ $cliente->NOMBRE ?? 'No disponible' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">RIF</span>
                        <span class="info-value">{{ $cliente->RIF ?? 'No disponible' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Moneda de Pago</span>
                        <span class="info-value">{{ $pago->moneda_pago ?? 'No Especificado' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Fecha</span>
                        <span
                            class="info-value">{{ \Carbon\Carbon::parse($pago->fecha)->translatedFormat('d \d\e F \d\e Y') }}</span>
                    </div>
                </div>
            </div>

            <h2 class="section-title">Pagos Realizados</h2>
            @php $ajustesYaMostrados = []; @endphp
            @foreach ($pago->pagos as $pagoItem)
                <div class="table-container" style="margin-bottom: 15px;">
                    <table>
                        <thead>
                            <tr>
                                <th colspan="5" style="background: #e2e8f0; color: #2d3748;">
                                    Pago #{{ $loop->iteration }} -
                                    {{ \Carbon\Carbon::parse($pagoItem->fecha_pago ?? $pago->fecha)->format('d/m/Y') }}
                                    <span class="status-badge"
                                        style="margin-left: 10px; background: linear-gradient(135deg, #a7d96e 0%, #7cb342 100%); color: white;">{{ $pagoItem->estatus }}</span>
                                </th>
                            </tr>
                            <tr>
                                <th>Tipo de Pago</th>
                                <th>Monto USD</th>
                                <th>Referencia</th>
                                <th>Banco</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $pagoItem->tipo_pago->DPAGO ?? 'N/A' }}</td>
                                <td>
                                    {{ number_format($pagoItem->monto ?? 0, 2, ',', '.') }}
                                    @if (($pago->moneda_pago ?? '') == 'Bolívares' && ($pagoItem->rate ?? 0) > 0)
                                        <div style="font-size: 0.75em; color: #718096; margin-top: 4px; line-height: 1.2;">
                                            Bs.
                                            {{ number_format(($pagoItem->monto ?? 0) * ($pagoItem->rate ?? 0), 2, ',', '.') }}
                                            <br>
                                            <span style="font-size: 0.9em;">Tasa:
                                                {{ number_format($pagoItem->rate ?? 0, 2, ',', '.') }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $pagoItem->referencia ?? 'N/A' }}</td>
                                <td>{{ $pagoItem->banco->nombre ?? 'N/A' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                @php
                    $pedidosDelPago = $pagoItem->pago_pedidos ?? collect();
                @endphp

                @if ($pedidosDelPago->count() > 0)
                    <div class="table-container"
                        style="margin-bottom: 30px; margin-left: 30px; border-left: 5px solid #667eea; padding-left: 15px; background: linear-gradient(to right, rgba(102, 126, 234, 0.05) 0%, transparent 100%); border-radius: 8px; padding: 15px;">
                        <h4
                            style="color: #667eea; margin: 0 0 10px 0; font-size: 0.95em; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                            📋 Pedidos afectados por este pago
                        </h4>
                        <table style="box-shadow: 0 2px 8px rgba(102, 126, 234, 0.1);">
                            <thead style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                                <tr>
                                    <th>Nro. Pedido</th>
                                    <th>Fecha Pedido</th>
                                    <th>Monto Asignado</th>
                                    @if(($pagoItem->moneda_pago ?? '') != 'Bolívares')
                                    <th>% Descuento</th>
                                    @endif
                                    <th>Saldo Pedido (USD)</th>
                                    <th>Saldo IVA (Bs)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pedidosDelPago as $pagoPedido)
                                    <tr style="background: white;">
                                        <td><strong>#{{ $pagoPedido->pedido->id ?? '-' }}</strong></td>
                                        <td>
                                            {{ isset($pagoPedido->pedido->fecha) ? \Carbon\Carbon::parse($pagoPedido->pedido->fecha)->format('d/m/Y') : '-' }}
                                        </td>
                                        <td>
                                            <strong
                                                style="color: #667eea;">${{ number_format($pagoPedido->monto ?? 0, 2, ',', '.') }}</strong>
                                            @if (($pago->moneda_pago ?? '') == 'Bolívares' && ($pagoItem->rate ?? 0) > 0)
                                                <div style="font-size: 0.75em; color: #718096; margin-top: 2px;">
                                                    Bs.
                                                    {{ number_format(($pagoPedido->monto ?? 0) * ($pagoItem->rate ?? 0), 2, ',', '.') }}
                                                </div>
                                            @endif
                                        </td>
                                        @php
                                            $esDivisaItem = ($pagoItem->moneda_pago ?? '') != 'Bolívares';
                                            $dcto         = (float) ($pagoPedido->descuento ?? 0);
                                            $montoAsig    = (float) ($pagoPedido->monto ?? 0);
                                            $porcDcto     = ($esDivisaItem && ($montoAsig + $dcto) > 0.001)
                                                ? round($dcto / ($montoAsig + $dcto) * 100, 2)
                                                : 0;
                                        @endphp
                                        @if($esDivisaItem)
                                        <td style="text-align:center;">
                                            @if($porcDcto > 0)
                                                <span style="display:inline-block;padding:3px 10px;border-radius:12px;background:#c6f6d5;color:#276749;font-weight:700;font-size:0.9em;">
                                                    {{ number_format($porcDcto, 2, ',', '.') }}%
                                                </span>
                                                <div style="font-size:0.75em;color:#718096;margin-top:2px;">
                                                    -${{ number_format($dcto, 2, ',', '.') }}
                                                </div>
                                            @else
                                                <span style="color:#a0aec0;font-size:0.85em;">—</span>
                                            @endif
                                        </td>
                                        @endif
                                        <td>
                                            <strong style="color: #2d3748;">
                                                ${{ number_format($pagoPedido->pedido->saldo_base ?? 0, 2, ',', '.') }}
                                            </strong>
                                        </td>
                                        <td>
                                            <strong style="color: #2d3748;">
                                                Bs. {{ number_format($pagoPedido->pedido->saldo_iva_bs ?? 0, 2, ',', '.') }}
                                            </strong>
                                        </td>
                                    </tr>
                                @endforeach
                                <tr
                                    style="background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); font-weight: bold; border-top: 2px solid #667eea;">
                                    <td colspan="2" style="text-align: right; color: #4a5568;">Subtotal de este pago:
                                    </td>
                                    <td><strong
                                            style="color: #667eea; font-size: 1.1em;">${{ number_format($pedidosDelPago->sum('monto'), 2, ',', '.') }}</strong>
                                        @if (($pago->moneda_pago ?? '') == 'Bolívares' && ($pagoItem->rate ?? 0) > 0)
                                            <div style="font-size: 0.75em; color: #718096; margin-top: 2px;">
                                                Bs.
                                                {{ number_format(($pedidosDelPago->sum('monto') ?? 0) * ($pagoItem->rate ?? 0), 2, ',', '.') }}
                                            </div>
                                        @endif
                                    </td>
                                    @if(($pagoItem->moneda_pago ?? '') != 'Bolívares')
                                    <td style="text-align:center;">
                                        @php $totalDcto = $pedidosDelPago->sum('descuento'); @endphp
                                        @if($totalDcto > 0.001)
                                            <span style="display:inline-block;padding:3px 10px;border-radius:12px;background:#c6f6d5;color:#276749;font-weight:700;font-size:0.9em;">
                                                -${{ number_format($totalDcto, 2, ',', '.') }}
                                            </span>
                                        @else
                                            <span style="color:#a0aec0;font-size:0.85em;">—</span>
                                        @endif
                                    </td>
                                    @endif
                                    <td>
                                        <strong style="color: #2d3748;">
                                            ${{ number_format($pedidosDelPago->sum(function ($item) { return (float) ($item->pedido->saldo_base ?? 0); }), 2, ',', '.') }}
                                        </strong>
                                    </td>
                                    <td>
                                        <strong style="color: #2d3748;">
                                            Bs. {{ number_format($pedidosDelPago->sum(function ($item) {return (float) ($item->pedido->saldo_iva_bs ?? 0);} ), 2, ',', '.') }}
                                        </strong>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    {{-- Sección de Ajustes adicionales --}}
                    @php
                        $ajustesPorPedidoComprobante = [];
                        $totalAjustesComprobante = 0;
                        foreach ($pedidosDelPago as $pp) {
                            if (!$pp->pedido) continue;
                            if (in_array($pp->pedido->id, $ajustesYaMostrados)) continue;
                            $ajustesList = \App\Models\PedidoAjuste::where('pedido_id', $pp->pedido->id)->get();
                            if ($ajustesList->count() > 0) {
                                $ajustesPorPedidoComprobante[$pp->pedido->id] = [
                                    'ajustes' => $ajustesList,
                                    'ajustes_monto' => (float) ($pp->ajustes_monto ?? 0),
                                ];
                                $totalAjustesComprobante += (float) ($pp->ajustes_monto ?? 0);
                                $ajustesYaMostrados[] = $pp->pedido->id;
                            }
                        }
                    @endphp

                    @if(!empty($ajustesPorPedidoComprobante))
                    <div style="margin-top: 20px; margin-bottom: 10px; margin-left: 30px; border-left: 5px solid #9f7aea; padding: 15px; background: linear-gradient(to right, rgba(159, 122, 234, 0.07) 0%, transparent 100%); border-radius: 8px;">
                        <h4 style="color: #6b46c1; margin: 0 0 12px 0; font-size: 0.95em; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                            ⚖️ Ajustes adicionales incluidos en este pago
                        </h4>
                        @foreach($ajustesPorPedidoComprobante as $pedidoIdAj => $infoAj)
                            <div style="margin-bottom: 12px;">
                                <div style="font-size: 0.85em; font-weight: 700; color: #553c9a; margin-bottom: 6px;">
                                    Pedido #{{ $pedidoIdAj }}
                                </div>
                                <table style="box-shadow: 0 2px 6px rgba(159, 122, 234, 0.12); width: 100%;">
                                    <thead style="background: linear-gradient(135deg, #9f7aea 0%, #6b46c1 100%); color: white;">
                                        <tr>
                                            <th style="padding: 8px 12px; text-align: left;">Concepto</th>
                                            <th style="padding: 8px 12px; text-align: center;">Tipo</th>
                                            <th style="padding: 8px 12px; text-align: right;">Monto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($infoAj['ajustes'] as $ajuste)
                                            @php
                                                $esCargo = $ajuste->tipo === 'cargo';
                                            @endphp
                                            <tr style="background: white;">
                                                <td style="padding: 8px 12px; color: #4a5568;">{{ $ajuste->concepto ?? '-' }}</td>
                                                <td style="padding: 8px 12px; text-align: center;">
                                                    <span style="display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 0.8em; font-weight: 600; background: {{ $esCargo ? '#fed7d7' : '#c6f6d5' }}; color: {{ $esCargo ? '#9b2c2c' : '#276749' }};">
                                                        {{ $esCargo ? 'Cargo' : 'Abono' }}
                                                    </span>
                                                </td>
                                                <td style="padding: 8px 12px; text-align: right; font-weight: 600; color: {{ $esCargo ? '#c53030' : '#276749' }};">
                                                    {{ $esCargo ? '+' : '-' }}{{ number_format($ajuste->monto ?? 0, 2, ',', '.') }} $
                                                </td>
                                            </tr>
                                        @endforeach
                                        @if($infoAj['ajustes_monto'] > 0.001)
                                        <tr style="background: #faf5ff; border-top: 2px solid #9f7aea;">
                                            <td colspan="2" style="padding: 8px 12px; text-align: right; font-weight: 700; color: #553c9a;">
                                                Total ajustes pagados:
                                            </td>
                                            <td style="padding: 8px 12px; text-align: right; font-weight: 700; color: #553c9a;">
                                                {{ number_format($infoAj['ajustes_monto'], 2, ',', '.') }} $
                                                @if(($pago->moneda_pago ?? '') == 'Bolívares' && ($pagoItem->rate ?? 0) > 0)
                                                    <div style="font-size: 0.75em; color: #718096; margin-top: 2px;">
                                                        Bs. {{ number_format($infoAj['ajustes_monto'] * ($pagoItem->rate ?? 0), 2, ',', '.') }}
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        @endforeach
                    </div>
                    @endif

                @else
                    <div
                        style="margin-bottom: 30px; margin-left: 30px; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107; color: #856404; border-radius: 8px;">
                        <em>⚠️ No hay pedidos asociados a este pago</em>
                    </div>
                @endif
            @endforeach



            <div class="footer">
                <p>Este recibo ha sido generado automáticamente • Fecha de emisión:
                    {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    @endsection
