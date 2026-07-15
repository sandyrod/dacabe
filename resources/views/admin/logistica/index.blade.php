@extends('layouts.app')

@section('titulo', 'Logística - Cajas')
@section('titulo_header', 'Gestión de Cajas')
@section('subtitulo_header', 'Control de Entregas')

@section('content')
<section class="content">
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card">
            <div class="card-header">
                <form method="GET" class="row">
                    <div class="col-md-4">
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Buscar por código, cliente, vendedor, chofer...">
                    </div>
                    <div class="col-md-3">
                        <select name="estatus" class="form-control">
                            <option value="">Todos los estatus</option>
                            @foreach(['ARMADA','EN_TRANSITO','ENTREGADA','CANCELADA'] as $estatus)
                                <option value="{{ $estatus }}" @selected(request('estatus') === $estatus)>{{ str_replace('_', ' ', $estatus) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary"><i class="fas fa-search mr-1"></i>Filtrar</button>
                        <a href="{{ route('admin.logistica.index') }}" class="btn btn-secondary">Limpiar</a>
                    </div>
                    <div class="col-md-2 text-right">
                        <a href="{{ route('admin.logistica.create') }}" class="btn btn-success"><i class="fas fa-plus mr-1"></i>Nueva caja</a>
                    </div>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                        <tr>
                            <th>Código</th>
                            <th>Caja</th>
                            <th>Bulto</th>
                            <th>Cliente</th>
                            <th>Teléfono</th>
                            <th>Destino</th>
                            <th>Estatus</th>
                            <th>Vendedor</th>
                            <th>Chofer</th>
                            <th>Items</th>
                            <th>Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($cajas as $caja)
                            @php
                                $loteCerrado = !empty($caja->lote_cerrado_at);
                                $estatusList = ['ARMADA','EN_TRANSITO','ENTREGADA','CANCELADA'];
                            @endphp
                            <tr>
                                <td>{{ $caja->codigo }}</td>
                                <td>{{ $caja->bulto_posicion && $caja->bulto_total ? $caja->bulto_posicion . '/' . $caja->bulto_total : '-' }}</td>
                                <td>{{ $caja->bulto_codigo ?: '-' }}</td>
                                <td>{{ $caja->cliente_nombre }}</td>
                                <td>{{ $caja->telefono ?: '-' }}</td>
                                <td>{{ $caja->direccion_entrega }}</td>
                                <td>
                                    <span class="badge badge-{{ $caja->estatus === 'ENTREGADA' ? 'success' : ($caja->estatus === 'EN_TRANSITO' ? 'warning' : ($caja->estatus === 'CANCELADA' ? 'danger' : 'info')) }}">
                                        {{ str_replace('_', ' ', $caja->estatus) }}
                                    </span>
                                    @if($loteCerrado)
                                        <span class="badge badge-dark ml-1" title="Lote cerrado"><i class="fas fa-lock"></i></span>
                                    @endif
                                    <form action="{{ route('admin.logistica.update_status', $caja->id) }}" method="POST" class="mt-1">
                                        @csrf
                                        <select name="estatus" class="form-control form-control-sm" onchange="this.form.submit()" style="min-width: 150px;">
                                            @foreach($estatusList as $estatus)
                                                <option value="{{ $estatus }}" @selected($caja->estatus === $estatus)>{{ str_replace('_', ' ', $estatus) }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                                <td>{{ $caja->vendedor_nombre ?: '-' }}</td>
                                <td>{{ $caja->chofer_nombre ?: '-' }}</td>
                                <td>{{ $caja->items_count }}</td>
                                <td>
                                    @if($caja->bulto_codigo)
                                        <form action="{{ route('admin.logistica.lote.update_status') }}" method="POST" class="d-inline-block mr-1" title="Cambiar estatus de todo el lote">
                                            @csrf
                                            <input type="hidden" name="cliente_rif" value="{{ $caja->cliente_rif }}">
                                            <input type="hidden" name="bulto_codigo" value="{{ $caja->bulto_codigo }}">
                                            <select name="estatus" class="form-control form-control-sm d-inline-block" style="width: 145px;" onchange="this.form.submit()">
                                                <option value="" selected disabled>Cambiar lote</option>
                                                @foreach($estatusList as $estatus)
                                                    <option value="{{ $estatus }}">{{ str_replace('_', ' ', $estatus) }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                        @if(!$loteCerrado)
                                            <form action="{{ route('admin.logistica.lote.cerrar') }}" method="POST" target="_blank" class="d-inline" onsubmit="return confirm('Esto marcará el lote como finalizado e irá a imprimir todas las etiquetas del lote. ¿Continuar?');">
                                                @csrf
                                                <input type="hidden" name="cliente_rif" value="{{ $caja->cliente_rif }}">
                                                <input type="hidden" name="bulto_codigo" value="{{ $caja->bulto_codigo }}">
                                                <button type="submit" class="btn btn-xs btn-outline-primary" title="Cerrar lote e imprimir todas las etiquetas"><i class="fas fa-check-circle"></i></button>
                                            </form>
                                        @endif
                                        <a href="{{ route('admin.logistica.lote.etiquetas', ['clienteRif' => $caja->cliente_rif, 'bultoCodigo' => $caja->bulto_codigo]) }}" target="_blank" class="btn btn-xs btn-outline-info" title="Imprimir lote"><i class="fas fa-layer-group"></i></a>
                                    @endif
                                    <a href="{{ route('admin.logistica.show', $caja->id) }}" class="btn btn-xs btn-outline-primary" title="Ver"><i class="fas fa-eye"></i></a>
                                    @if(!$loteCerrado)
                                        <a href="{{ route('admin.logistica.edit', $caja->id) }}" class="btn btn-xs btn-outline-warning" title="Editar"><i class="fas fa-edit"></i></a>
                                    @else
                                        <button type="button" class="btn btn-xs btn-outline-secondary" title="Lote cerrado" disabled><i class="fas fa-edit"></i></button>
                                    @endif
                                    <a href="{{ route('admin.logistica.label', $caja->id) }}" target="_blank" class="btn btn-xs btn-outline-success" title="Etiqueta"><i class="fas fa-print"></i></a>
                                    @if(!$loteCerrado)
                                        <form action="{{ route('admin.logistica.destroy', $caja->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Deseas eliminar esta caja? Esta acción no se puede deshacer.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-outline-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                        </form>
                                    @else
                                        <button type="button" class="btn btn-xs btn-outline-secondary" title="Lote cerrado" disabled><i class="fas fa-trash"></i></button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="11" class="text-center py-4">No hay cajas registradas.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">{{ $cajas->links() }}</div>
        </div>
    </div>
</section>
@endsection
