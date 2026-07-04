<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contenido de Caja {{ $caja->codigo }}</title>
    <link rel="stylesheet" href="{{ asset('theme/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/fontawesome-free/css/all.min.css') }}">
    <style>
        body { background: radial-gradient(circle at top left, #f8fbff 0%, #edf3f9 38%, #e3ebf4 100%); }
        .ticket-card { max-width: 1120px; margin: 24px auto; border-radius: 20px; overflow: hidden; box-shadow: 0 18px 38px rgba(8, 15, 32, .15); border: 0; }
        .ticket-head { background: linear-gradient(130deg,#10263c,#1f5a92); color: #fff; padding: 20px 24px; }
        .ticket-brand { display: flex; align-items: center; gap: 14px; }
        .ticket-brand img { width: 56px; height: 56px; object-fit: contain; background: #fff; border-radius: 12px; padding: 5px; }
        .ticket-head h3 { font-size: 1.3rem; font-weight: 800; margin-bottom: 2px; white-space: nowrap; }
        .client-block { background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%); border: 1px solid #dbe6f2; border-radius: 18px; padding: 18px 20px; box-shadow: inset 0 1px 0 rgba(255,255,255,.65); }
        .client-name { font-size: 2rem; font-weight: 900; line-height: 1.02; color: #0f172a; margin-bottom: 6px; }
        .client-meta { font-size: 1rem; color: #1f2937; margin-bottom: 4px; }
        .client-address { font-size: 1.05rem; color: #334155; }
        .section-title { font-size: 1rem; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: .08em; margin-bottom: 10px; }
        .product-table th { background: #e9f1fa; color: #10263c; border-color: #c8d8ea !important; }
        .product-table td { vertical-align: middle; }
        @media (max-width: 767.98px) {
            .ticket-card { margin: 0; border-radius: 0; box-shadow: none; min-height: 100vh; }
            .ticket-head { padding: 16px 14px; }
            .ticket-brand { align-items: center; gap: 12px; }
              .ticket-brand img { width: 68px; height: 68px; border-radius: 12px; padding: 3px; }
              .ticket-head h3 { font-size: .92rem; line-height: 1.05; white-space: normal; }
              .ticket-head h3 span { display: block; }
            .card-body { padding: 14px; }
            .client-block { padding: 14px 14px 12px; border-radius: 14px; margin-bottom: 14px !important; }
            .client-name { font-size: 1.45rem; line-height: 1.06; }
            .client-meta { font-size: .95rem; line-height: 1.25; }
            .client-address { font-size: 1rem; line-height: 1.25; }
            .section-title { font-size: .86rem; margin-bottom: 8px; }
            .row.mb-4 { margin-bottom: 14px !important; }
            .row.mb-4 > .col-md-6 { margin-bottom: 10px; }
            .card.bg-light { border-radius: 12px; }
            .card-body.py-3 { padding: 12px !important; }
            .card-body.py-3 p { font-size: .95rem; margin-bottom: .35rem; word-break: break-word; }
              .section-title.mt-3 { margin-top: 18px !important; display: block; clear: both; }
              .table-responsive { margin-top: 12px; clear: both; }
            .product-table { font-size: .9rem; }
            .product-table th, .product-table td { padding: .45rem .5rem; }
            .product-table th:nth-child(1), .product-table td:nth-child(1) { min-width: 170px; }
        }
    </style>
</head>
<body>
    <div class="ticket-card card">
        <div class="ticket-head d-flex justify-content-between align-items-center">
            <div class="ticket-brand">
                <img src="{{ asset('imgs/logos/dacabe.png') }}" alt="Logo DACABE">
                <div>
                        <h3 class="mb-0">Inversiones DACABE <span>Contenido de Caja</span></h3>
                    <small> @if($bultoEtiqueta) Caja {{ $bultoEtiqueta }} @endif</small>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="client-block mb-4">
                <div class="client-name">{{ $caja->cliente_nombre }}</div>
                <div class="client-meta"><strong>RIF:</strong> {{ $caja->cliente_rif }} | <strong>Teléfono:</strong> {{ $caja->telefono ?: '-' }}</div>
                <div class="client-address"><strong>Dirección:</strong> {{ $caja->direccion_entrega }}</div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="card bg-light border-0 h-100 mb-0">
                        <div class="card-body py-3">
                            <p class="mb-1"><strong>Pedidos:</strong> {{ implode(', ', $pedidoIds ?: []) ?: '-' }}</p>
                            <p class="mb-0"><strong>Facturas:</strong> {{ implode(', ', $facturas ?: []) ?: '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-title mt-3">Productos</div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm product-table mb-0">
                    <thead>
                        <tr>
                            <th>Descripción</th>
                            <th class="text-center">Cantidad</th>
                            <th>Unidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($caja->items as $item)
                            <tr>
                                <td>{{ $item->producto_descripcion }}</td>
                                <td class="text-center">{{ number_format($item->cantidad, 2, ',', '.') }}</td>
                                <td>{{ $item->unidad ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
