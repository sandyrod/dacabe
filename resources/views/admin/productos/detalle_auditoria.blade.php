@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-gradient-info text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-search-plus mr-2"></i>Detalle de Auditoría - {{ $producto->CODIGO }}
                            </h3>
                            <div class="card-tools">
                                <a href="{{ route('admin.productos.auditoria') }}" class="btn btn-sm btn-light">
                                    <i class="fas fa-arrow-left mr-1"></i>Volver
                                </a>
                                <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body bg-light p-4">
                        <!-- Información del Producto -->
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <div class="card border-primary shadow-lg">
                                    <div class="card-header bg-gradient-primary text-white">
                                        <h5 class="mb-0">
                                            <i class="fas fa-box mr-2"></i>Información del Producto
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-2">
                                                    <span class="text-muted small">Código:</span>
                                                    <p class="font-weight-bold mb-1">{{ $producto->CODIGO }}</p>
                                                </div>
                                                <div class="mb-2">
                                                    <span class="text-muted small">Descripción:</span>
                                                    <p class="font-weight-bold mb-1">{{ $producto->DESCR }}</p>
                                                </div>
                                                <div class="mb-2">
                                                    <span class="text-muted small">Tipo:</span>
                                                    <p class="font-weight-bold mb-1">{{ $producto->TIPO ?? 'N/A' }}</p>
                                                </div>
                                                <div class="mb-2">
                                                    <span class="text-muted small">Unidad Medida:</span>
                                                    <p class="font-weight-bold mb-1">{{ $producto->DUNIMEDD ?? 'N/A' }}</p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-2">
                                                    <span class="text-muted small">Precio Actual:</span>
                                                    <p class="font-weight-bold text-success mb-1">${{ number_format($producto->PRECIO1 ?? 0, 2, ',', '.') }}</p>
                                                </div>
                                                <div class="mb-2">
                                                    <span class="text-muted small">Stock Actual:</span>
                                                    <p class="font-weight-bold text-info mb-1">{{ number_format($producto->CANTIDAD ?? 0, 0, ',', '.') }} unidades</p>
                                                </div>
                                                <div class="mb-2">
                                                    <span class="text-muted small">Activo:</span>
                                                    <p class="font-weight-bold mb-1">
                                                        @if(isset($producto->ACTIVO) && $producto->ACTIVO)
                                                            <span class="badge badge-success">Sí</span>
                                                        @else
                                                            <span class="badge badge-danger">No</span>
                                                        @endif
                                                    </p>
                                                </div>
                                                <div class="mb-2">
                                                    <span class="text-muted small">Última Venta:</span>
                                                    <p class="font-weight-bold mb-1">
                                                        {{ $producto->ULTVEN ? \Carbon\Carbon::parse($producto->ULTVEN)->format('d/m/Y') : 'N/A' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="mb-0">
                                            <i class="fas fa-chart-bar mr-2"></i>Estadísticas
                                        </h5>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="stat-item mb-3">
                                            <small class="text-muted">Total Ventas</small>
                                            <h4 class="text-primary mb-0">{{ number_format($stats['total_ventas'], 0) }} unid.</h4>
                                        </div>
                                        <div class="stat-item mb-3">
                                            <small class="text-muted">Ingresos Totales</small>
                                            <h4 class="text-success mb-0">${{ number_format($stats['total_ingresos'], 2) }}</h4>
                                        </div>
                                        <div class="stat-item mb-3">
                                            <small class="text-muted">Precio Promedio</small>
                                            <h4 class="text-info mb-0">${{ number_format($stats['precio_promedio'], 2) }}</h4>
                                        </div>
                                        <div class="stat-item mb-3">
                                            <small class="text-muted">Clientes Únicos</small>
                                            <h4 class="text-warning mb-0">{{ $stats['clientes_unicos'] }}</h4>
                                        </div>
                                        <div class="stat-item">
                                            <small class="text-muted">Pedidos Afectados</small>
                                            <h4 class="text-dark mb-0">{{ $stats['pedidos_afectados'] }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Stock Actual en Depósitos -->
                        @if($stockActual->count() > 0)
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card border-primary">
                                    <div class="card-header bg-gradient-navy text-white">
                                        <h5 class="mb-0">
                                            <i class="fas fa-warehouse mr-2"></i>Stock Actual en Depósitos
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Depósito</th>
                                                        <th>Nombre Depósito</th>
                                                        <th class="text-center">Unidades</th>
                                                        <th class="text-center">Cajas</th>
                                                        <th class="text-center">Reserva</th>
                                                        <th class="text-right">Disponible</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($stockActual as $stock)
                                                    <tr>
                                                        <td class="font-weight-bold">{{ $stock->CDEPOS }}</td>
                                                        <td>{{ $stock->NOMBRE ?? 'N/A' }}</td>
                                                        <td class="text-center font-weight-bold">{{ number_format($stock->EUNIDAD, 0, ',', '.') }}</td>
                                                        <td class="text-center">{{ number_format($stock->ECAJA, 0, ',', '.') }}</td>
                                                        <td class="text-center text-warning">{{ number_format($stock->RESERVA, 0, ',', '.') }}</td>
                                                        <td class="text-right font-weight-bold text-success">
                                                            {{ number_format($stock->EUNIDAD - $stock->RESERVA, 0, ',', '.') }}
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
                        @endif

                        <!-- Filtros de Fecha -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <form method="GET" action="{{ route('admin.productos.auditoria.detalle', $producto->CODIGO) }}" class="mb-0">
                                    <div class="row g-3 align-items-end">
                                        <div class="col-md-3">
                                            <label class="font-weight-bold text-primary mb-1">
                                                <i class="far fa-calendar-alt mr-1"></i>Desde
                                            </label>
                                            <input type="date" name="fecha_inicio" class="form-control"
                                                value="{{ request('fecha_inicio') }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="font-weight-bold text-primary mb-1">
                                                <i class="far fa-calendar-check mr-1"></i>Hasta
                                            </label>
                                            <input type="date" name="fecha_fin" class="form-control"
                                                value="{{ request('fecha_fin') }}">
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex">
                                                <button type="submit" class="btn btn-primary mr-2">
                                                    <i class="fas fa-filter mr-1"></i>Filtrar
                                                </button>
                                                <a href="{{ route('admin.productos.auditoria.detalle', $producto->CODIGO) }}" class="btn btn-secondary">
                                                    <i class="fas fa-undo mr-1"></i>Limpiar
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Historial de Movimientos -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-dark text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-history mr-2"></i>Historial de Movimientos
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped mb-0">
                                        <thead class="bg-light text-dark">
                                            <tr>
                                                <th class="text-center">Fecha</th>
                                                <th class="text-center">Pedido #</th>
                                                <th>Cliente</th>
                                                <th class="text-center">Vendedor</th>
                                                <th class="text-center">Cantidad</th>
                                                <th class="text-right">Precio $</th>
                                                <th class="text-right">Total $</th>
                                                <th class="text-center">Estatus</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($movimientos as $mov)
                                                <tr>
                                                    <td class="text-center small">
                                                        {{ \Carbon\Carbon::parse($mov->fecha_pedido)->format('d/m/Y H:i') }}
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
                                                    <td class="text-center font-weight-bold">
                                                        {{ number_format($mov->cantidad, 0) }}
                                                    </td>
                                                    <td class="text-right small">
                                                        ${{ number_format($mov->precio_dolar, 2) }}
                                                    </td>
                                                    <td class="text-right font-weight-bold text-success">
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
                                                    <td colspan="8" class="text-center py-5">
                                                        <i class="fas fa-inbox fa-4x text-muted mb-3" style="opacity: 0.3;"></i>
                                                        <p class="text-muted font-weight-bold">No se encontraron movimientos para este producto.</p>
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
        .bg-gradient-info {
            background: linear-gradient(45deg, #17a2b8, #138496);
        }

        .bg-gradient-navy {
            background: linear-gradient(45deg, #001f3f, #003366);
        }

        .stat-item {
            border-bottom: 1px solid #eee;
            padding-bottom: 0.5rem;
        }

        .stat-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .table thead th {
            border-top: none;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .badge {
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(23, 162, 184, 0.05);
        }

        .card-header {
            border-bottom: 2px solid rgba(0,0,0,0.125);
        }
    </style>
@endsection
