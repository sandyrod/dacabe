@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-gradient-navy text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-clipboard-audit mr-2"></i>Auditoría de Movimientos de Productos
                            </h3>
                            <div class="card-tools">
                                <a href="{{ route('admin.productos.auditoria.export', request()->query()) }}" 
                                   class="btn btn-sm btn-light" title="Exportar a CSV">
                                    <i class="fas fa-file-csv mr-1"></i>Exportar
                                </a>
                                <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body bg-light p-4">
                        <!-- Filtros Avanzados -->
                        <form method="GET" action="{{ route('admin.productos.auditoria') }}" class="mb-4">
                            <div class="row g-3">
                                <!-- Primera fila -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="producto" class="font-weight-bold text-primary mb-1">
                                            <i class="fas fa-box mr-1"></i>Producto
                                        </label>
                                        <input type="text" name="producto" id="producto" class="form-control"
                                            value="{{ request('producto') }}" placeholder="Código o nombre...">
                                        @if($productosPopulares->count() > 0)
                                            <small class="text-muted">
                                                Populares: 
                                                @foreach($productosPopulares->take(5) as $prod)
                                                    <a href="?producto={{ $prod->codigo_inven }}" class="badge badge-light text-primary">{{ $prod->codigo_inven }}</a>
                                                @endforeach
                                            </small>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="cliente" class="font-weight-bold text-primary mb-1">
                                            <i class="fas fa-users mr-1"></i>Cliente
                                        </label>
                                        <input type="text" name="cliente" id="cliente" class="form-control"
                                            value="{{ request('cliente') }}" placeholder="Nombre cliente...">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
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
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label for="estatus" class="font-weight-bold text-primary mb-1">
                                            <i class="fas fa-flag mr-1"></i>Estatus
                                        </label>
                                        <select name="estatus" id="estatus" class="form-control">
                                            <option value="">Todos</option>
                                            <option value="APROBADO" {{ request('estatus') == 'APROBADO' ? 'selected' : '' }}>Aprobado</option>
                                            <option value="PENDIENTE" {{ request('estatus') == 'PENDIENTE' ? 'selected' : '' }}>Pendiente</option>
                                            <option value="ENTREGADO" {{ request('estatus') == 'ENTREGADO' ? 'selected' : '' }}>Entregado</option>
                                            <option value="RECHAZADO" {{ request('estatus') == 'RECHAZADO' ? 'selected' : '' }}>Rechazado</option>
                                            <option value="ANULADO" {{ request('estatus') == 'ANULADO' ? 'selected' : '' }}>Anulado</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="incluir_sincronizacion" class="font-weight-bold text-primary mb-1">
                                            <i class="fas fa-sync mr-1"></i>Act. Dacabe Madre
                                        </label>
                                        <select name="incluir_sincronizacion" id="incluir_sincronizacion" class="form-control">
                                            <option value="">No</option>
                                            <option value="1" {{ request('incluir_sincronizacion') == '1' ? 'selected' : '' }}>Sí</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Segunda fila -->
                            <div class="row g-3">
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
                                    <div class="d-flex align-items-end h-100">
                                        <button type="submit" class="btn btn-primary mr-2">
                                            <i class="fas fa-filter mr-1"></i>Filtrar
                                        </button>
                                        <a href="{{ route('admin.productos.auditoria') }}" class="btn btn-secondary">
                                            <i class="fas fa-undo mr-1"></i>Limpiar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <!-- Estadísticas Gerenciales -->
                        <div class="row mb-4">
                            <div class="col-md-2 mb-3">
                                <div class="stat-card h-100 shadow-sm" style="background: linear-gradient(135deg, #001f3f 0%, #003366 100%);">
                                    <div class="card-body text-white p-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="text-uppercase text-white-50 mb-1 small font-weight-bold">
                                                    <i class="fas fa-boxes mr-1"></i>Productos
                                                </h6>
                                                <h4 class="mb-0 font-weight-bold">{{ number_format($stats['total_productos_distintos'], 0, ',', '.') }}</h4>
                                            </div>
                                            <div class="stat-icon" style="background: rgba(255,255,255,0.2); width: 40px; height: 40px; font-size: 1.2rem;">
                                                <i class="fas fa-cube"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-2 mb-3">
                                <div class="stat-card h-100 shadow-sm" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                                    <div class="card-body text-white p-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="text-uppercase text-white-50 mb-1 small font-weight-bold">
                                                    <i class="fas fa-layer-group mr-1"></i>Unidades
                                                </h6>
                                                <h4 class="mb-0 font-weight-bold">{{ number_format($stats['total_unidades_movidas'], 0, ',', '.') }}</h4>
                                            </div>
                                            <div class="stat-icon" style="background: rgba(255,255,255,0.2); width: 40px; height: 40px; font-size: 1.2rem;">
                                                <i class="fas fa-cubes"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-2 mb-3">
                                <div class="stat-card h-100 shadow-sm" style="background: linear-gradient(135deg, #2c5f8d 0%, #4a8bc2 100%);">
                                    <div class="card-body text-white p-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="text-uppercase text-white-50 mb-1 small font-weight-bold">
                                                    <i class="fas fa-dollar-sign mr-1"></i>Valor Total
                                                </h6>
                                                <h4 class="mb-0 font-weight-bold">${{ number_format($stats['total_valor_dolar'], 2, ',', '.') }}</h4>
                                            </div>
                                            <div class="stat-icon" style="background: rgba(255,255,255,0.2); width: 40px; height: 40px; font-size: 1.2rem;">
                                                <i class="fas fa-chart-line"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-2 mb-3">
                                <div class="stat-card h-100 shadow-sm" style="background: linear-gradient(135deg, #3d7eaa 0%, #5fa3d3 100%);">
                                    <div class="card-body text-white p-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="text-uppercase text-white-50 mb-1 small font-weight-bold">
                                                    <i class="fas fa-shopping-cart mr-1"></i>Pedidos
                                                </h6>
                                                <h4 class="mb-0 font-weight-bold">{{ number_format($stats['total_pedidos'], 0, ',', '.') }}</h4>
                                            </div>
                                            <div class="stat-icon" style="background: rgba(255,255,255,0.2); width: 40px; height: 40px; font-size: 1.2rem;">
                                                <i class="fas fa-receipt"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-2 mb-3">
                                <div class="stat-card h-100 shadow-sm" style="background: linear-gradient(135deg, #4a90a4 0%, #7ab8d4 100%);">
                                    <div class="card-body text-white p-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="text-uppercase text-white-50 mb-1 small font-weight-bold">
                                                    <i class="fas fa-sync-alt mr-1"></i>Sincroniz.
                                                </h6>
                                                <h4 class="mb-0 font-weight-bold">{{ number_format($stats['total_sincronizaciones'], 0, ',', '.') }}</h4>
                                            </div>
                                            <div class="stat-icon" style="background: rgba(255,255,255,0.2); width: 40px; height: 40px; font-size: 1.2rem;">
                                                <i class="fas fa-database"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($stats['producto_mas_movido'])
                            <div class="col-md-2 mb-3">
                                <div class="stat-card h-100 shadow-sm" style="background: linear-gradient(135deg, #5ca0d3 0%, #8fc4e8 100%);">
                                    <div class="card-body text-dark p-3">
                                        <div>
                                            <h6 class="text-uppercase text-dark-50 mb-1 small font-weight-bold">
                                                <i class="fas fa-trophy mr-1"></i>Más Movido
                                            </h6>
                                            <small class="d-block font-weight-bold">{{ $stats['producto_mas_movido']->codigo }}</small>
                                            <small class="text-muted">{{ Str::limit($stats['producto_mas_movido']->nombre, 20) }}</small>
                                            <small class="d-block font-weight-bold">{{ number_format($stats['producto_mas_movido']->cantidad, 0, ',', '.') }} unid.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Tabla de Movimientos -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped mb-0">
                                        <thead class="bg-gradient-navy text-white">
                                            <tr>
                                                <th class="text-center" style="width: 80px;">
                                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'fecha_pedido', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-white">
                                                        Fecha <i class="fas fa-sort"></i>
                                                    </a>
                                                </th>
                                                <th class="text-center" style="width: 100px;">Tipo</th>
                                                <th class="text-center" style="width: 120px;">Referencia</th>
                                                <th>Producto</th>
                                                <th class="text-center" style="width: 80px;">Cantidad</th>
                                                <th class="text-right" style="width: 100px;">Precio $</th>
                                                <th class="text-right" style="width: 120px;">Total $</th>
                                                <th>Cliente</th>
                                                <th class="text-center" style="width: 80px;">Vendedor</th>
                                                <th class="text-center" style="width: 100px;">Estatus</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($movimientos as $mov)
                                                <tr class="@if($mov->tipo_movimiento == 'SINCRONIZACION') table-info @endif">
                                                    <td class="text-center small">
                                                        {{ \Carbon\Carbon::parse($mov->fecha_pedido)->format('d/m/Y') }}
                                                    </td>
                                                    <td class="text-center">
                                                        @if($mov->tipo_movimiento == 'PEDIDO')
                                                            <span class="badge badge-primary">PEDIDO</span>
                                                        @else
                                                            <span class="badge badge-info">SYNC</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center small">
                                                        @if($mov->tipo_movimiento == 'PEDIDO')
                                                            <a href="#" class="text-primary font-weight-bold clickable-pedido pedido-detail-link" 
                                                               data-pedido-id="{{ $mov->pedido_id }}"
                                                               data-toggle="modal" data-target="#pedidoDetailModal">
                                                                #{{ $mov->pedido_id }}
                                                            </a>
                                                        @else
                                                            <small class="text-muted">{{ $mov->referencia }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="d-flex flex-column">
                                                            @if($mov->tipo_movimiento == 'PEDIDO')
                                                                <a href="{{ route('admin.productos.auditoria.detalle', $mov->codigo_inven) }}" 
                                                                   class="font-weight-bold text-primary clickable-product">
                                                                    {{ $mov->codigo_inven }}
                                                                </a>
                                                            @else
                                                                <span class="font-weight-bold">{{ $mov->codigo_inven }}</span>
                                                            @endif
                                                            <small class="text-muted text-truncate" style="max-width: 250px;">
                                                                {{ $mov->inven_descr }}
                                                            </small>
                                                        </div>
                                                    </td>
                                                    <td class="text-center font-weight-bold">
                                                        {{ number_format($mov->cantidad, 0, ',', '.') }}
                                                    </td>
                                                    <td class="text-right small">
                                                        @if($mov->precio_dolar > 0)
                                                            ${{ number_format($mov->precio_dolar, 2, ',', '.') }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td class="text-right font-weight-bold">
                                                        @if($mov->total_dolar > 0)
                                                            ${{ number_format($mov->total_dolar, 2, ',', '.') }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td class="small">
                                                        {{ $mov->cliente }}
                                                    </td>
                                                    <td class="text-center small">
                                                        {{ $mov->seller_code }}
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
                                                    <td colspan="10" class="text-center py-5">
                                                        <i class="fas fa-clipboard-list fa-4x text-muted mb-3" style="opacity: 0.3;"></i>
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

    <!-- Modal para Detalle del Pedido -->
    <div class="modal fade" id="pedidoDetailModal" tabindex="-1" role="dialog" aria-labelledby="pedidoDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-file-invoice-dollar mr-2"></i>
                        <span id="pedidoDetailTitle">Detalle del Pedido</span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div id="pedidoDetailContent">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Cargando...</span>
                            </div>
                            <p class="mt-3 text-muted">Cargando detalles del pedido...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <div class="row align-items-center">
                        <div class="col-md-6 text-left">
                            <small class="text-muted">
                                <i class="fas fa-info-circle mr-1"></i>
                                Última actualización: <span id="lastUpdate">-</span>
                            </small>
                        </div>
                        <div class="col-md-6 text-right">
                            <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                                <i class="fas fa-times mr-1"></i>Cerrar
                            </button>
                            <button type="button" class="btn btn-primary" id="btnPrintPedido">
                                <i class="fas fa-print mr-1"></i>Imprimir
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .bg-gradient-primary {
            background: linear-gradient(45deg, #001f3f, #003366);
        }

        .bg-gradient-navy {
            background: linear-gradient(45deg, #001f3f, #003366);
        }

        .text-navy {
            color: #001f3f;
        }

        .text-primary {
            color: #003366 !important;
        }

        .btn-primary {
            background: linear-gradient(45deg, #001f3f, #003366);
            border-color: #001f3f;
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, #003366, #004080);
            border-color: #003366;
        }

        .stat-card {
            border-radius: 12px;
            transition: all 0.3s ease;
            border: none;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,31,63,0.25);
        }

        .stat-icon {
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
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
            background-color: rgba(0,31,63,0.05);
        }

        .form-control:focus {
            border-color: #001f3f;
            box-shadow: 0 0 0 0.2rem rgba(0,31,63,0.25);
        }

        .text-primary:hover {
            text-decoration: none;
            transform: scale(1.05);
            transition: all 0.2s ease;
        }

        .clickable-product {
            position: relative;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .clickable-product:hover {
            color: #004080 !important;
            transform: translateX(3px);
        }

        .clickable-product::after {
            content: '↗';
            position: absolute;
            right: -15px;
            top: 0;
            font-size: 0.8em;
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .clickable-product:hover::after {
            opacity: 1;
        }

        .clickable-pedido {
            position: relative;
            cursor: pointer;
            transition: all 0.2s ease;
            padding-right: 20px;
        }

        .clickable-pedido:hover {
            color: #004080 !important;
            transform: scale(1.1);
        }

        .clickable-pedido::after {
            content: '👁';
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            font-size: 0.7em;
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .clickable-pedido:hover::after {
            opacity: 1;
        }

        .table-info {
            background-color: rgba(0,31,63,0.1);
        }

        .badge-info {
            background-color: #003366;
        }

        /* Estilos para la modal */
        .modal-xl {
            max-width: 90%;
        }

        .info-item {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 1rem;
            transition: all 0.3s ease;
        }

        .info-item:hover {
            background: #e9ecef;
            transform: translateY(-2px);
        }

        .badge-lg {
            font-size: 1.2rem;
            padding: 1rem;
        }

        .badge-pill {
            border-radius: 50px;
        }

        .modal-body .table th {
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            padding: 0.75rem;
        }

        .modal-body .table td {
            vertical-align: middle;
            padding: 0.75rem;
        }

        .spinner-border {
            display: inline-block;
            width: 2rem;
            height: 2rem;
            vertical-align: text-bottom;
            border: 0.25em solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            border-right-color: #001f3f;
            animation: spinner-border .75s linear infinite;
        }

        @keyframes spinner-border {
            to {
                transform: rotate(360deg);
            }
        }

        /* Estilos para impresión */
        @media print {
            .modal-header, .modal-footer {
                display: none !important;
            }
            .modal-body {
                padding: 0 !important;
            }
            .modal-content {
                border: none !important;
                box-shadow: none !important;
            }
        }
    </style>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Auto-ajustar fechas si están vacías
            if (!$('input[name="fecha_inicio"]').val()) {
                var today = new Date();
                var firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
                $('input[name="fecha_inicio"]').val(firstDay.toISOString().split('T')[0]);
            }
            
            if (!$('input[name="fecha_fin"]').val()) {
                var today = new Date();
                $('input[name="fecha_fin"]').val(today.toISOString().split('T')[0]);
            }

            // Manejar clic en los enlaces de detalle de pedido
            $('.pedido-detail-link').click(function(e) {
                e.preventDefault();
                var pedidoId = $(this).data('pedido-id');
                cargarDetallePedido(pedidoId);
            });
        });

        function cargarDetallePedido(pedidoId) {
            // Actualizar título del modal
            $('#pedidoDetailTitle').text('Cargando Pedido #' + pedidoId + '...');
            
            $.ajax({
                url: '{{ route("admin.pedidos.detalle.ajax", "") }}/' + pedidoId,
                method: 'GET',
                success: function(response) {
                    // Actualizar título con el número de pedido
                    $('#pedidoDetailTitle').text('Detalle del Pedido #' + response.pedido.id);
                    
                    // Actualizar fecha de última actualización
                    $('#lastUpdate').text(new Date().toLocaleString('es-ES'));
                    
                    var html = '<div class="container-fluid p-4">';
                    
                    // Sección de información principal
                    html += '<div class="row mb-4">';
                    html += '<div class="col-12">';
                    html += '<div class="card border-0 shadow-sm bg-light">';
                    html += '<div class="card-body p-4">';
                    html += '<div class="row">';
                    
                    // Columna izquierda - Información del pedido
                    html += '<div class="col-md-6">';
                    html += '<div class="d-flex align-items-center mb-3">';
                    html += '<div class="badge badge-primary badge-lg p-3 mr-3">';
                    html += '<i class="fas fa-file-invoice fa-2x"></i>';
                    html += '</div>';
                    html += '<div>';
                    html += '<h4 class="mb-1 text-primary">Pedido #' + response.pedido.id + '</h4>';
                    html += '<p class="text-muted mb-0"><i class="fas fa-calendar-alt mr-1"></i>' + response.pedido.fecha + '</p>';
                    html += '</div>';
                    html += '</div>';
                    
                    html += '<div class="row">';
                    html += '<div class="col-sm-6 mb-3">';
                    html += '<div class="info-item">';
                    html += '<small class="text-muted d-block mb-1">CLIENTE</small>';
                    html += '<span class="font-weight-bold text-dark">' + response.pedido.cliente + '</span>';
                    html += '</div>';
                    html += '</div>';
                    
                    html += '<div class="col-sm-6 mb-3">';
                    html += '<div class="info-item">';
                    html += '<small class="text-muted d-block mb-1">VENDEDOR</small>';
                    html += '<span class="font-weight-bold text-dark">' + response.pedido.vendedor + '</span>';
                    html += '</div>';
                    html += '</div>';
                    
                    html += '<div class="col-sm-6 mb-3">';
                    html += '<div class="info-item">';
                    html += '<small class="text-muted d-block mb-1">ESTATUS</small>';
                    html += '<span class="badge badge-' + response.pedido.estatus_class + ' badge-pill">' + response.pedido.estatus + '</span>';
                    html += '</div>';
                    html += '</div>';
                    
                    html += '<div class="col-sm-6 mb-3">';
                    html += '<div class="info-item">';
                    html += '<small class="text-muted d-block mb-1">TOTAL DEL PEDIDO</small>';
                    html += '<h3 class="mb-0 text-success">$' + response.pedido.total + '</h3>';
                    html += '</div>';
                    html += '</div>';
                    
                    html += '</div>'; // Fin row
                    html += '</div>'; // Fin card-body
                    html += '</div>'; // Fin card
                    html += '</div>'; // Fin col
                    html += '</div>'; // Fin row principal
                    
                    // Sección de productos
                    html += '<div class="row">';
                    html += '<div class="col-12">';
                    html += '<div class="card border-0 shadow-sm">';
                    html += '<div class="card-header bg-white border-bottom">';
                    html += '<h5 class="mb-0 text-primary"><i class="fas fa-boxes mr-2"></i>Productos del Pedido</h5>';
                    html += '</div>';
                    html += '<div class="card-body p-0">';
                    html += '<div class="table-responsive">';
                    html += '<table class="table table-hover mb-0">';
                    html += '<thead class="bg-light">';
                    html += '<tr>';
                    html += '<th class="border-0 text-left">Código</th>';
                    html += '<th class="border-0 text-left">Descripción</th>';
                    html += '<th class="border-0 text-center" style="width: 100px;">Cantidad</th>';
                    html += '<th class="border-0 text-right" style="width: 120px;">Precio Unit.</th>';
                    html += '<th class="border-0 text-right" style="width: 120px;">Total</th>';
                    html += '</tr>';
                    html += '</thead>';
                    html += '<tbody>';
                    
                    response.productos.forEach(function(producto, index) {
                        html += '<tr>';
                        html += '<td class="align-middle">';
                        html += '<span class="font-weight-bold text-primary">' + producto.codigo + '</span>';
                        html += '</td>';
                        html += '<td class="align-middle">';
                        html += '<span class="text-dark">' + producto.nombre + '</span>';
                        html += '</td>';
                        html += '<td class="align-middle text-center">';
                        html += '<span class="badge badge-info badge-pill">' + producto.cantidad + '</span>';
                        html += '</td>';
                        html += '<td class="align-middle text-right">';
                        html += '<span class="font-weight-bold">$' + producto.precio + '</span>';
                        html += '</td>';
                        html += '<td class="align-middle text-right">';
                        html += '<span class="font-weight-bold text-success">$' + producto.total + '</span>';
                        html += '</td>';
                        html += '</tr>';
                    });
                    
                    html += '</tbody>';
                    html += '</table>';
                    html += '</div>';
                    html += '</div>'; // Fin card-body
                    html += '</div>'; // Fin card
                    html += '</div>'; // Fin col
                    html += '</div>'; // Fin row
                    
                    html += '</div>'; // Fin container
                    
                    $('#pedidoDetailContent').html(html);
                },
                error: function() {
                    $('#pedidoDetailTitle').text('Error al Cargar');
                    $('#pedidoDetailContent').html(`
                        <div class="text-center py-5">
                            <div class="text-danger mb-3">
                                <i class="fas fa-exclamation-triangle fa-3x"></i>
                            </div>
                            <h5 class="text-danger">Error al cargar los detalles del pedido</h5>
                            <p class="text-muted">No se pudo obtener la información del pedido solicitado.</p>
                        </div>
                    `);
                }
            });
        }

        // Función para imprimir
        $('#btnPrintPedido').click(function() {
            window.print();
        });
    </script>
    </script>
@endsection
