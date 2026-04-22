@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel') . ' - Pagos del Pedido')
@section('titulo_header', 'Pagos del Pedido')
@section('subtitulo_header', 'Traza completa de comprobantes del pedido #' . $pedido->id)

@section('styles')
    <style>
        .receipt-container {
            max-width: 1100px;
            margin: 0 auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .receipt-header {
            background: linear-gradient(135deg, #667eea 0%, #2b2d86 100%);
            color: #fff;
            padding: 24px;
        }

        .summary-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 16px;
        }
    </style>
@endsection

@section('content')
    <div class="receipt-container">
        <div class="receipt-header d-flex justify-content-between align-items-center">
            <div>
                <h3 class="mb-1">TRAZA COMPLETA DE PAGOS</h3>
                <div>Pedido #{{ $pedido->id }}</div>
            </div>
            <a href="{{ url('vendedores/pagos/clientes') }}" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>

        <div class="p-3 p-md-4">
            <div class="summary-card mb-3">
                <div class="row">
                    <div class="col-md-4 mb-2"><strong>Cliente:</strong> {{ $cliente->NOMBRE ?? 'N/A' }}</div>
                    <div class="col-md-4 mb-2"><strong>RIF:</strong> {{ $cliente->RIF ?? $pedido->rif }}</div>
                    <div class="col-md-4 mb-2"><strong>Fecha Pedido:</strong>
                        {{ $pedido->fecha ? \Carbon\Carbon::parse($pedido->fecha)->format('d/m/Y') : 'N/A' }}
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-3 mb-2"><strong>Total base:</strong> $ {{ number_format($pedido->base ?? 0, 2, ',', '.') }}</div>
                    <div class="col-md-3 mb-2"><strong>Pagado acumulado:</strong> $ {{ number_format($totalPagadoUsd, 2, ',', '.') }}</div>
                    <div class="col-md-3 mb-2"><strong>Descuento aplicado:</strong> $ {{ number_format($totalDescuentoUsd, 2, ',', '.') }}</div>
                    <div class="col-md-3 mb-2"><strong>IVA aplicado (Bs):</strong> Bs. {{ number_format($totalIvaBsAplicado, 2, ',', '.') }}</div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6 mb-2"><strong>Saldo base actual:</strong> $ {{ number_format($pedido->saldo_base ?? 0, 2, ',', '.') }}</div>
                    <div class="col-md-6 mb-2"><strong>Saldo IVA actual:</strong> Bs. {{ number_format($pedido->saldo_iva_bs ?? 0, 2, ',', '.') }}</div>
                </div>
            </div>

            @if((float)($pedido->porc_retencion ?? 0) > 0)
            <div class="summary-card mb-3" style="border-left: 4px solid #f97316; background: linear-gradient(to right, rgba(249,115,22,0.06) 0%, #f8fafc 100%);">
                <h6 class="font-weight-bold mb-3" style="color: #ea580c;">
                    <i class="fas fa-percentage mr-1"></i> Retención de IVA
                </h6>
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <strong>Porcentaje:</strong><br>
                        <span style="font-size:1.1em; color:#ea580c; font-weight:700;">{{ $pedido->porc_retencion }}%</span>
                    </div>
                    <div class="col-md-3 mb-2">
                        <strong>Monto retención:</strong><br>
                        <span style="font-size:1.1em; font-weight:700;">Bs. {{ number_format($pedido->retencion ?? 0, 2, ',', '.') }}</span>
                    </div>
                    <div class="col-md-3 mb-2">
                        <strong>Saldo IVA pendiente:</strong><br>
                        @if((float)($pedido->saldo_iva_bs ?? 0) > 0.01)
                            <span style="color:#dc2626; font-weight:700; font-size:1.05em;">Bs. {{ number_format($pedido->saldo_iva_bs, 2, ',', '.') }}</span>
                            <span class="badge badge-warning ml-1">Pendiente</span>
                        @else
                            <span style="color:#16a34a; font-weight:700; font-size:1.05em;">Bs. 0,00</span>
                            <span class="badge badge-success ml-1">Validado</span>
                        @endif
                    </div>
                    <div class="col-md-3 mb-2">
                        <strong>Comprobante:</strong><br>
                        @if($pedido->comprobante_retencion)
                            <a href="{{ asset('imgs/' . $pedido->comprobante_retencion) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">
                                <i class="fas fa-eye mr-1"></i>Ver comprobante
                            </a>
                        @else
                            <span class="badge badge-danger">Sin comprobante</span>
                            <div class="small text-muted mt-1">Pendiente de carga</div>
                        @endif
                    </div>
                </div>
                @if((float)($pedido->saldo_iva_bs ?? 0) > 0.01 && !$pedido->comprobante_retencion)
                <div class="alert alert-warning py-2 px-3 mb-0 mt-2" style="border-radius:8px; font-size:0.88em;">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    El saldo de IVA permanecerá pendiente hasta que suba el comprobante de retención y el administrador lo valide.
                </div>
                @elseif((float)($pedido->saldo_iva_bs ?? 0) > 0.01 && $pedido->comprobante_retencion)
                <div class="alert alert-info py-2 px-3 mb-0 mt-2" style="border-radius:8px; font-size:0.88em;">
                    <i class="fas fa-clock mr-1"></i>
                    Comprobante cargado. En espera de validación por el administrador.
                </div>
                @endif
            </div>
            @endif

        <div class="card shadow-sm border-0">
            <div class="card-header bg-light border-0">
                <h6 class="mb-0">Traza de Pagos del Pedido</h6>
            </div>
            <div class="card-body p-0">
                @if ($pagosPedido->isEmpty())
                    <div class="p-4 text-center text-muted">
                        No hay pagos registrados para este pedido.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Fecha</th>
                                    <th>Estatus Pago</th>
                                    <th>Tipo</th>
                                    <th>Referencia</th>
                                    <th>Banco</th>
                                    <th class="text-end">Monto USD</th>
                                    <th class="text-end">Descuento USD</th>
                                    <th class="text-end">IVA Bs aplicado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pagosPedido as $index => $pp)
                                    @php $pago = $pp->pago; @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $pago->fecha ? \Carbon\Carbon::parse($pago->fecha)->format('d/m/Y') : ($pago->created_at ? \Carbon\Carbon::parse($pago->created_at)->format('d/m/Y') : 'N/A') }}</td>
                                        <td>
                                            <span class="badge badge-{{ ($pago->estatus ?? '') === 'APROBADO' ? 'success' : (($pago->estatus ?? '') === 'RECHAZADO' ? 'danger' : 'warning') }}">
                                                {{ $pago->estatus ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>{{ $pago->tipo_pago->DPAGO ?? ($pago->tipo_pago ?? 'N/A') }}</td>
                                        <td>{{ $pago->referencia ?? 'N/A' }}</td>
                                        <td>{{ $pago->banco->nombre ?? 'N/A' }}</td>
                                        <td class="text-end">$ {{ number_format($pp->monto ?? 0, 2, ',', '.') }}</td>
                                        <td class="text-end">$ {{ number_format($pp->descuento ?? 0, 2, ',', '.') }}</td>
                                        <td class="text-end">Bs. {{ number_format($pp->iva ?? 0, 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
