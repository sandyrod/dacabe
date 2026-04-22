@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Pagos Recibidos Pendientes</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="tabla-pagos">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Descripción</th>
                                    <th>Vendedor</th>
                                    <th>Monto</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pagos as $pago)
                                <tr>
                                    <td>{{ formatoFechaDMASimple($pago->created_at) }}</td>
                                    <td>{{ $pago->descripcion ?? '' }}</td>
                                    <td>{{ $pago->vendedor_nombre }}</td>
                                    <td>{{ number_format($pago->monto, 2, ',', '.') }}</td>
                                    <td>
                                        <div class="btn-group" role="group" aria-label="Acciones">
                                            <button type="button" class="btn btn-sm btn-info mr-2" onclick="verDetallePago({{ $pago->id }})">
                                                <i class="fas fa-eye"></i> Ver Detalle
                                            </button>
                                            <button type="button" class="btn btn-sm btn-success mr-2" onclick="cambiarEstatus({{ $pago->id }}, 'APROBADO')">
                                                <i class="fas fa-check"></i> Aprobar
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="cambiarEstatus({{ $pago->id }}, 'RECHAZADO')">
                                                <i class="fas fa-times"></i> Rechazar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Incluir modal de detalles del pago -->
@include('pagos._payment_details_modal')
@endsection

@section('js')
<script src="{{ asset('js/functions.js') }}"></script>
@endsection
