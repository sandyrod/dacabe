@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel') . ' - Editor de Pedidos')
@section('titulo_header', 'Editor de Pedidos')
@section('subtitulo_header', 'Administrador')

@section('content')
<div class="container-fluid">
    @include('layouts.partials.info')

    <div class="card card-primary card-outline">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0"><i class="fas fa-edit mr-1"></i>Pedidos editables</h3>
            <a href="{{ route('admin.pedidos_editor.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i>Nuevo Pedido
            </a>
        </div>
        <div class="card-body">
            <form class="form-inline mb-3" method="GET" action="{{ route('admin.pedidos_editor.index') }}">
                <input type="text" class="form-control form-control-sm mr-2" name="search" value="{{ request('search') }}" placeholder="Pedido, RIF, cliente o vendedor">
                <button class="btn btn-sm btn-primary mr-2" type="submit"><i class="fas fa-search mr-1"></i>Buscar</button>
                <a href="{{ route('admin.pedidos_editor.index') }}" class="btn btn-sm btn-outline-secondary">Limpiar</a>
            </form>

            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>Pedido</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>RIF</th>
                            <th>Vendedor</th>
                            <th>Depósito</th>
                            <th>Estatus</th>
                            <th class="text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pedidos as $pedido)
                            <tr>
                                <td>#{{ $pedido->id }}</td>
                                <td>{{ optional($pedido->created_at)->format('d/m/Y H:i') }}</td>
                                <td>{{ $pedido->descripcion }}</td>
                                <td>{{ $pedido->rif }}</td>
                                <td>{{ $pedido->seller_code ?: 'S/COD' }}</td>
                                <td>{{ $pedido->cdepos ?: '-' }}</td>
                                <td><span class="badge badge-light border">{{ $pedido->estatus }}</span></td>
                                <td class="text-right">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.pedidos_editor.edit', $pedido->id) }}">
                                        <i class="fas fa-pen mr-1"></i>Editar
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No hay pedidos para mostrar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($pedidos->hasPages())
                <div class="mt-3">
                    {{ $pedidos->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
