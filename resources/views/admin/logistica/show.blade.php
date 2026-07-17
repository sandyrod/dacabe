@extends('layouts.app')

@section('titulo', 'Logística - Detalle Caja')
@section('titulo_header', 'Detalle de Caja')
@section('subtitulo_header', $caja->codigo)

@section('content')
<section class="content">
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @php
            $loteCerrado = !empty($caja->lote_cerrado_at);
        @endphp
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h3 class="card-title mb-0">{{ $caja->codigo }} - {{ $caja->cliente_nombre }}</h3>
                <div>
                    @if($caja->bulto_codigo)
                        @if(!$loteCerrado)
                            <form action="{{ route('admin.logistica.lote.cerrar') }}" method="POST" target="_blank" class="d-inline mr-1">
                                @csrf
                                <input type="hidden" name="cliente_rif" value="{{ $caja->cliente_rif }}">
                                <input type="hidden" name="bulto_codigo" value="{{ $caja->bulto_codigo }}">
                                <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Esto marcará el lote como finalizado e irá a imprimir todas las etiquetas. ¿Continuar?');">
                                    <i class="fas fa-check-circle mr-1"></i>Cerrar lote e imprimir
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('admin.logistica.lote.etiquetas', ['clienteRif' => $caja->cliente_rif, 'bultoCodigo' => $caja->bulto_codigo]) }}" target="_blank" class="btn btn-info btn-sm mr-1"><i class="fas fa-print mr-1"></i>Imprimir lote</a>
                    @endif
                    <a href="{{ route('admin.logistica.label', $caja->id) }}" target="_blank" class="btn btn-success btn-sm"><i class="fas fa-print mr-1"></i>Etiqueta</a>
                    @if(!$loteCerrado)
                        <a href="{{ route('admin.logistica.edit', $caja->id) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit mr-1"></i>Editar</a>
                        <form action="{{ route('admin.logistica.destroy', $caja->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Deseas eliminar esta caja? Esta acción no se puede deshacer.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash mr-1"></i>Eliminar</button>
                        </form>
                    @else
                        <button type="button" class="btn btn-secondary btn-sm" disabled><i class="fas fa-lock mr-1"></i>Lote cerrado</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4"><strong>Cliente:</strong> {{ $caja->cliente_nombre }} ({{ $caja->cliente_rif }})</div>
                    <div class="col-md-4"><strong>Teléfono:</strong> {{ $caja->telefono ?: '-' }}</div>
                    <div class="col-md-4"><strong>Estatus:</strong> {{ str_replace('_', ' ', $caja->estatus) }}</div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-4"><strong>Bulto:</strong> {{ $caja->bulto_codigo ?: '-' }}</div>
                    <div class="col-md-4"><strong>Orden dentro del bulto:</strong> {{ $caja->bulto_posicion && $caja->bulto_total ? $caja->bulto_posicion . '/' . $caja->bulto_total : '-' }}</div>
                    <div class="col-md-4"><strong>Fecha armado:</strong> {{ optional($caja->fecha_armado)->format('d/m/Y H:i') }}</div>
                </div>
                @if($caja->bulto_codigo)
                    <div class="row mt-2">
                        <div class="col-md-6"><strong>Lote cerrado:</strong> {{ $caja->lote_cerrado_at ? optional($caja->lote_cerrado_at)->format('d/m/Y H:i') : 'No' }}</div>
                        <div class="col-md-6"><strong>Relacionados en lote:</strong> {{ isset($loteCajas) ? $loteCajas->count() : 0 }} caja(s)</div>
                    </div>
                @endif
                <div class="row mt-2">
                    <div class="col-md-6"><strong>Dirección entrega:</strong> {{ $caja->direccion_entrega }}</div>
                    <div class="col-md-3"><strong>Ciudad:</strong> {{ $caja->ciudad ?: '-' }}</div>
                    <div class="col-md-3"><strong>Estado:</strong> {{ $caja->estado ?: '-' }}</div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-4"><strong>Vendedor:</strong> {{ $caja->vendedor_nombre ?: '-' }}</div>
                    <div class="col-md-4"><strong>Chofer:</strong> {{ $caja->chofer_nombre ?: '-' }}</div>
                    <div class="col-md-4"><strong>Fecha entrega:</strong> {{ optional($caja->fecha_entrega)->format('d/m/Y H:i') ?: '-' }}</div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-4"><strong>Peso caja:</strong> {{ $caja->peso_kg !== null ? number_format((float) $caja->peso_kg, 3, ',', '.') . ' kg' : '-' }}</div>
                </div>
                @if($caja->observaciones)
                    <div class="row mt-2">
                        <div class="col-12"><strong>Observaciones:</strong> {{ $caja->observaciones }}</div>
                    </div>
                @endif

                <hr>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                        <tr>
                            <th>Pedido</th>
                            <th>Factura</th>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Unidad</th>
                            <th>Cantidad</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($caja->items as $item)
                            <tr>
                                <td>#{{ $item->pedido_id }}</td>
                                <td>{{ $item->factura_numero ?: '-' }}</td>
                                <td>{{ $item->producto_codigo }}</td>
                                <td>{{ $item->producto_descripcion }}</td>
                                <td>{{ $item->unidad ?: '-' }}</td>
                                <td>{{ number_format($item->cantidad, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                @if(isset($loteCajas) && $loteCajas->count() > 1)
                    <hr>
                    <h5 class="mb-3">Cajas relacionadas en este lote</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Bulto</th>
                                    <th>Estatus</th>
                                    <th>Items</th>
                                    <th>Etiqueta</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($loteCajas as $loteCaja)
                                    <tr>
                                        <td>{{ $loteCaja->codigo }}</td>
                                        <td>{{ $loteCaja->bulto_posicion && $loteCaja->bulto_total ? $loteCaja->bulto_posicion . '/' . $loteCaja->bulto_total : '-' }}</td>
                                        <td>{{ str_replace('_', ' ', $loteCaja->estatus) }}</td>
                                        <td>{{ $loteCaja->items_count ?? $loteCaja->items->count() }}</td>
                                        <td><a target="_blank" href="{{ route('admin.logistica.label', $loteCaja->id) }}">Imprimir</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
