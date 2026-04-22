@extends('layouts.print')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h3>Pedidos Pendientes de Pago</h3>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nro.</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Saldo Pendiente</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pedidosPendientes as $pedido)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $pedido->fecha->format('d/m/Y H:i') }}</td>
                            <td>{{ $pedido->descripcion }}</td>
                            <td>{{ $pedido->saldo_pendiente }} Bs</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-right">Total Pendiente:</th>
                            <th>{{ $pedidosPendientes->sum('saldo_pendiente') }} Bs</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
