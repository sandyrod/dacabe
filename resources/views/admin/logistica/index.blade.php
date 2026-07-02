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
                            <tr>
                                <td>{{ $caja->codigo }}</td>
                                <td>{{ $caja->cliente_nombre }}</td>
                                <td>{{ $caja->telefono ?: '-' }}</td>
                                <td>{{ $caja->direccion_entrega }}</td>
                                <td>
                                    <span class="badge badge-{{ $caja->estatus === 'ENTREGADA' ? 'success' : ($caja->estatus === 'EN_TRANSITO' ? 'warning' : ($caja->estatus === 'CANCELADA' ? 'danger' : 'info')) }}">
                                        {{ str_replace('_', ' ', $caja->estatus) }}
                                    </span>
                                </td>
                                <td>{{ $caja->vendedor_nombre ?: '-' }}</td>
                                <td>{{ $caja->chofer_nombre ?: '-' }}</td>
                                <td>{{ $caja->items_count }}</td>
                                <td>
                                    <a href="{{ route('admin.logistica.show', $caja->id) }}" class="btn btn-xs btn-outline-primary" title="Ver"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('admin.logistica.edit', $caja->id) }}" class="btn btn-xs btn-outline-warning" title="Editar"><i class="fas fa-edit"></i></a>
                                    <a href="{{ route('admin.logistica.label', $caja->id) }}" target="_blank" class="btn btn-xs btn-outline-success" title="Etiqueta"><i class="fas fa-print"></i></a>
                                    <form action="{{ route('admin.logistica.destroy', $caja->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Deseas eliminar esta caja? Esta acción no se puede deshacer.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-outline-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center py-4">No hay cajas registradas.</td></tr>
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
