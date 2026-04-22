@extends('layouts.vendedor')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">Confirmar Pago</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('vendedor.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('vendedores.pagos.clientes') }}">Pagos</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Confirmar Pago</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Detalles del Pago</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="font-weight-bold">Cliente:</h5>
                            <p class="mb-1"><strong>RIF:</strong> {{ $cliente->RIF }}</p>
                            <p><strong>Nombre:</strong> {{ $cliente->NOMBRE }}</p>
                        </div>
                        <div class="col-md-6 text-md-right">
                            <h5 class="font-weight-bold">Resumen del Pago</h5>
                            <p class="mb-1"><strong>Total a Pagar:</strong> {{ number_format($totalPagar, 2, ',', '.') }} Bs.</p>
                            @if($totalDescuento > 0)
                                <p class="mb-1 text-success"><strong>Descuento Aplicado:</strong> -{{ number_format($totalDescuento, 2, ',', '.') }} Bs.</p>
                                <p class="mb-1"><strong>Total con Descuento:</strong> {{ number_format($totalPagar - $totalDescuento, 2, ',', '.') }} Bs.</p>
                            @endif
                            <p class="mb-1"><strong>Método de Pago:</strong> 
                                @if($tipoPago === 'bs')
                                    Bolívares (Total)
                                @elseif($tipoPago === 'divisa_total')
                                    Divisa (Total)
                                @elseif($tipoPago === 'divisa_parcial')
                                    Divisa (Parcial) - Monto en Divisa: ${{ number_format($montoDivisa, 2) }}
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Pedido #</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                    <th>Total</th>
                                    <th>Saldo Pendiente</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pedidos as $pedido)
                                    <tr>
                                        <td>{{ $pedido->id }}</td>
                                        <td>{{ \Carbon\Carbon::parse($pedido->fecha)->format('d/m/Y') }}</td>
                                        <td>
                                            <span class="badge {{ $pedido->estatus === 'APROBADO' ? 'badge-success' : 'badge-warning' }}">
                                                {{ ucfirst(strtolower($pedido->estatus)) }}
                                            </span>
                                        </td>
                                        <td>{{ number_format($pedido->total, 2, ',', '.') }} Bs.</td>
                                        <td>
                                            @php
                                                $montoPagado = $pedido->pagos->where('estatus', 'APROBADO')->sum('monto');
                                                $saldoPendiente = $pedido->total - $montoPagado;
                                            @endphp
                                            {{ number_format($saldoPendiente, 2, ',', '.') }} Bs.
                                        </td>
                                    </tr>
                                    @if($pedido->detalles->count() > 0)
                                        <tr>
                                            <td colspan="5" class="p-0">
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered mb-0">
                                                        <thead class="bg-light">
                                                            <tr>
                                                                <th>Código</th>
                                                                <th>Producto</th>
                                                                <th class="text-center">Cantidad</th>
                                                                <th class="text-right">Precio Unit.</th>
                                                                <th class="text-right">IVA</th>
                                                                <th class="text-right">Subtotal</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($pedido->detalles as $detalle)
                                                                <tr>
                                                                    <td>{{ $detalle->producto->codigo ?? 'N/A' }}</td>
                                                                    <td>{{ $detalle->producto->nombre ?? 'Producto no encontrado' }}</td>
                                                                    <td class="text-center">{{ $detalle->cantidad }}</td>
                                                                    <td class="text-right">{{ number_format($detalle->precio_dolar, 2, ',', '.') }} $</td>
                                                                    <td class="text-right">{{ $detalle->iva ?? 0 }}%</td>
                                                                    <td class="text-right">
                                                                        {{ number_format($detalle->cantidad * $detalle->precio_dolar * (1 + ($detalle->iva ?? 0) / 100), 2, ',', '.') }} $
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <form action="{{ route('vendedores.pagos.procesar', $cliente->RIF) }}" method="POST" id="confirmarPagoForm">
                                @csrf
                                <input type="hidden" name="pedidos" value="{{ implode(',', $pedidosIds) }}">
                                <input type="hidden" name="tipo_pago" value="{{ $tipoPago }}">
                                <input type="hidden" name="total_pagar" value="{{ $totalPagar }}">
                                <input type="hidden" name="total_descuento" value="{{ $totalDescuento }}">
                                @if($tipoPago === 'divisa_parcial')
                                    <input type="hidden" name="monto_divisa" value="{{ $montoDivisa }}">
                                @endif
                                
                                <div class="form-group">
                                    <label for="observaciones">Observaciones (Opcional):</label>
                                    <textarea class="form-control" id="observaciones" name="observaciones" rows="3"></textarea>
                                </div>

                                <div class="d-flex justify-content-between mt-4">
                                    <a href="{{ route('vendedores.pagos.clientes') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left mr-2"></i> Volver Atrás
                                    </a>
                                    <div>
                                        <button type="button" class="btn btn-outline-secondary mr-2" onclick="window.print()">
                                            <i class="fas fa-print mr-2"></i> Imprimir
                                        </button>
                                        <button type="submit" class="btn btn-primary" id="confirmarPagoBtn">
                                            <i class="fas fa-check-circle mr-2"></i> Confirmar Pago
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Deshabilitar el botón de confirmar después de hacer clic para evitar doble envío
        $('#confirmarPagoForm').on('submit', function() {
            const btn = $('#confirmarPagoBtn');
            btn.prop('disabled', true);
            btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...');
        });
    });
</script>
@endpush
