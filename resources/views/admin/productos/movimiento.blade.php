@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-gradient-navy text-white">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-boxes mr-2"></i>Movimiento de Productos
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body bg-light-silver p-4">
                        <!-- Filtros -->
                        <form method="GET" action="{{ route('admin.productos.movimiento') }}" class="mb-0">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label for="search" class="font-weight-bold text-primary mb-1">
                                            <i class="fas fa-search mr-1"></i>Buscar Producto / Cliente
                                        </label>
                                        <input type="text" name="search" id="search" class="form-control"
                                            value="{{ request('search') }}" placeholder="Código, nombre, cliente...">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label for="vendedor" class="font-weight-bold text-primary mb-1">
                                            <i class="fas fa-user-tie mr-1"></i>Vendedor
                                        </label>
                                        <select name="vendedor" id="vendedor" class="form-control">
                                            <option value="">Todos</option>
                                            @foreach ($vendedores as $v)
                                                <option value="{{ $v->codigo }}"
                                                    {{ request('vendedor') == $v->codigo ? 'selected' : '' }}>
                                                    {{ $v->nombre_completo }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold text-primary mb-1">
                                            <i class="far fa-calendar-alt mr-1"></i>Desde
                                        </label>
                                        <input type="date" name="fecha_inicio" class="form-control"
                                            value="{{ request('fecha_inicio') }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold text-primary mb-1">
                                            <i class="far fa-calendar-check mr-1"></i>Hasta
                                        </label>
                                        <input type="date" name="fecha_fin" class="form-control"
                                            value="{{ request('fecha_fin') }}">
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex flex-column align-items-end">
                                    <div class="d-flex w-100 mt-2">
                                        <button type="submit" class="btn btn-primary flex-grow-1 mr-1" title="Filtrar">
                                            <i class="fas fa-filter"></i>
                                        </button>
                                        <a href="{{ route('admin.productos.movimiento') }}" class="btn btn-secondary flex-grow-1"
                                            title="Limpiar Filtros">
                                            <i class="fas fa-undo"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <!-- Estadísticas -->
                        <div class="row mb-4 mt-4">
                            <div class="col-md-4 mb-4">
                                <div class="stat-card h-100 shadow-sm"
                                    style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
                                    <div class="card-body text-white position-relative">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="pr-3">
                                                <h6 class="text-uppercase text-white-50 mb-1 small font-weight-bold">
                                                    <i class="fas fa-cubes mr-2"></i>Total Productos
                                                </h6>
                                                <h3 class="mb-0 font-weight-bold">{{ number_format($totalProductos, 0) }}</h3>
                                            </div>
                                            <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                                                <i class="fas fa-box"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 mb-4">
                                <div class="stat-card h-100 shadow-sm"
                                    style="background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);">
                                    <div class="card-body text-white position-relative">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="pr-3">
                                                <h6 class="text-uppercase text-white-50 mb-1 small font-weight-bold">
                                                    <i class="fas fa-dollar-sign mr-2"></i>Valor Estimado
                                                </h6>
                                                <h3 class="mb-0 font-weight-bold">${{ number_format($totalMonto, 2) }}</h3>
                                            </div>
                                            <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                                                <i class="fas fa-hand-holding-usd"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 mb-4">
                                <div class="stat-card h-100 shadow-sm"
                                    style="background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);">
                                    <div class="card-body text-white position-relative">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="pr-3">
                                                <h6 class="text-uppercase text-white-50 mb-1 small font-weight-bold">
                                                    <i class="fas fa-shopping-cart mr-2"></i>Pedidos Afectados
                                                </h6>
                                                <h3 class="mb-0 font-weight-bold">{{ number_format($totalPedidos, 0) }}</h3>
                                            </div>
                                            <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                                                <i class="fas fa-file-invoice"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Listado de Movimientos -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped mb-0">
                                        <thead class="bg-light text-navy">
                                            <tr>
                                                <th class="text-center">Fecha</th>
                                                <th class="text-center">Pedido #</th>
                                                <th>Cliente</th>
                                                <th>Vendedor</th>
                                                <th>Producto</th>
                                                <th class="text-center">Can</th>
                                                <th class="text-right">Precio $</th>
                                                <th class="text-right">Total $</th>
                                                <th class="text-center">Estatus</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($movimientos as $mov)
                                                <tr>
                                                    <td class="text-center small">
                                                        {{ \Carbon\Carbon::parse($mov->fecha_pedido)->format('d/m/Y') }}
                                                    </td>
                                                    <td class="text-center font-weight-bold text-primary">
                                                        #{{ $mov->pedido_id }}
                                                    </td>
                                                    <td class="small">
                                                        {{ $mov->cliente }}
                                                    </td>
                                                    <td class="text-center small">
                                                        {{ $mov->seller_code }}
                                                    </td>
                                                    <td>
                                                        <div class="d-flex flex-column">
                                                            <span class="font-weight-bold">{{ $mov->codigo_inven }}</span>
                                                            <small class="text-muted text-truncate" style="max-width: 200px;">{{ $mov->inven_descr }}</small>
                                                        </div>
                                                    </td>
                                                    <td class="text-center font-weight-bold">
                                                        {{ number_format($mov->cantidad, 0) }}
                                                    </td>
                                                    <td class="text-right small">
                                                        ${{ number_format($mov->precio_dolar, 2) }}
                                                    </td>
                                                    <td class="text-right font-weight-bold text-navy">
                                                        ${{ number_format($mov->cantidad * $mov->precio_dolar, 2) }}
                                                    </td>
                                                    <td class="text-center">
                                                        @php
                                                            $badgeClass = 'secondary';
                                                            if($mov->pedido_estatus == 'APROBADO' || $mov->pedido_estatus == 'ENTREGADO') $badgeClass = 'success';
                                                            if($mov->pedido_estatus == 'PENDIENTE') $badgeClass = 'warning';
                                                            if($mov->pedido_estatus == 'RECHAZADO' || $mov->pedido_estatus == 'ANULADO') $badgeClass = 'danger';
                                                        @endphp
                                                        <span class="badge badge-{{ $badgeClass }} small">
                                                            {{ $mov->pedido_estatus }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="9" class="text-center py-5">
                                                        <i class="fas fa-box-open fa-4x text-muted mb-3" style="opacity: 0.3;"></i>
                                                        <p class="text-muted font-weight-bold">No se encontraron movimientos con los filtros seleccionados.</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="p-3">
                                    {{ $movimientos->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .bg-gradient-navy {
            background: linear-gradient(45deg, #001f3f, #003366);
        }

        .text-navy {
            color: #001f3f;
        }

        .bg-light-silver {
            background-color: #f4f6f9;
        }

        .stat-card {
            border-radius: 12px;
            transition: transform 0.3s;
            border: none;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .table thead th {
            border-top: none;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge {
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 4px;
        }
    </style>
@endsection
