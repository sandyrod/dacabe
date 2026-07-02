<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contenido de Caja {{ $caja->codigo }}</title>
    <link rel="stylesheet" href="{{ asset('theme/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/fontawesome-free/css/all.min.css') }}">
    <style>
        body { background: linear-gradient(135deg, #f2f6fa 0%, #e2eaf2 100%); }
        .ticket-card { max-width: 1060px; margin: 28px auto; border-radius: 16px; overflow: hidden; box-shadow: 0 14px 34px rgba(0,0,0,.13); }
        .ticket-head { background: linear-gradient(130deg,#0f2d46,#1d4e89); color: #fff; padding: 18px 22px; }
        .ticket-brand { display: flex; align-items: center; gap: 12px; }
        .ticket-brand img { width: 44px; height: 44px; object-fit: contain; background: #fff; border-radius: 8px; padding: 4px; }
        .badge-state { font-size: .85rem; padding: .4rem .7rem; border-radius: 20px; }
    </style>
</head>
<body>
    <div class="ticket-card card">
        <div class="ticket-head d-flex justify-content-between align-items-center">
            <div class="ticket-brand">
                <img src="{{ asset('imgs/logos/dacabe.png') }}" alt="Logo DACABE">
                <div>
                    <h3 class="mb-0">Inversiones DACABE - Contenido de Caja</h3>
                    <small>Código: {{ $caja->codigo }}</small>
                </div>
            </div>
            <span class="badge badge-light badge-state">{{ str_replace('_',' ', $caja->estatus) }}</span>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Información del cliente</h5>
                    <p class="mb-1"><strong>Cliente:</strong> {{ $caja->cliente_nombre }}</p>
                    <p class="mb-1"><strong>RIF:</strong> {{ $caja->cliente_rif }}</p>
                    <p class="mb-1"><strong>Teléfono:</strong> {{ $caja->telefono ?: '-' }}</p>
                    <p class="mb-1"><strong>Dirección de entrega:</strong> {{ $caja->direccion_entrega }}</p>
                    <p class="mb-1"><strong>Dirección fiscal:</strong> {{ $caja->direccion_fiscal ?: '-' }}</p>
                    <p class="mb-1"><strong>Ciudad / Estado:</strong> {{ $caja->ciudad ?: '-' }} / {{ $caja->estado ?: '-' }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Datos operativos</h5>
                    <p class="mb-1"><strong>Vendedor:</strong> {{ $caja->vendedor_nombre ?: '-' }}</p>
                    <p class="mb-1"><strong>Chofer:</strong> {{ $caja->chofer_nombre ?: '-' }}</p>
                    <p class="mb-1"><strong>Fecha de armado:</strong> {{ optional($caja->fecha_armado)->format('d/m/Y H:i') }}</p>
                    <p class="mb-1"><strong>Fecha de entrega:</strong> {{ optional($caja->fecha_entrega)->format('d/m/Y H:i') ?: '-' }}</p>
                    <p class="mb-1"><strong>Números de pedido:</strong> {{ $pedidoIds->implode(', ') ?: '-' }}</p>
                    <p class="mb-1"><strong>Números de factura:</strong> {{ $facturas->implode(', ') ?: '-' }}</p>
                </div>
            </div>

            <hr>

            <h5>Productos y cantidades</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm">
                    <thead>
                        <tr>
                            <th>Pedido</th>
                            <th>Factura</th>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Unidad</th>
                            <th>Cantidad</th>
                            <th>Vendedor</th>
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
                                <td>{{ $item->vendedor_nombre ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
