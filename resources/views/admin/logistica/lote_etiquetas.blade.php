<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lote {{ $bultoCodigo }}</title>
    <style>
        @page { size: 15cm 9.1cm; margin: 3mm; }
        body { font-family: Arial, Helvetica, sans-serif; margin: 0; color: #0f172a; }
        .page { page-break-after: always; }
        .page:last-child { page-break-after: auto; }
        .label { border: 1px solid #0f172a; border-radius: 12px; padding: 7px 9px; min-height: calc(9.1cm - 6mm); box-sizing: border-box; display: flex; flex-direction: column; }
        .top { display: flex; justify-content: space-between; align-items: center; gap: 12px; border-bottom: 1px solid #0f172a; padding-bottom: 5px; margin-bottom: 6px; flex: 0 0 auto; }
        .brand img { width: 220px !important; object-fit: contain; display: block; }
        .bulto { text-align: right; }
        .bulto .title { font-size: 10px; text-transform: uppercase; letter-spacing: .08em; color: #475569; }
        .bulto .value { font-size: 22px; font-weight: 900; line-height: 1; color: #0f172a; }
        .middle { display: grid; grid-template-columns: 31% 69%; gap: 10px; align-items: center; flex: 1 1 auto; min-height: 0; }
        .qr { display: flex; align-items: center; justify-content: center; min-height: 100%; }
        .qr svg { width: 100%; height: auto; max-width: 100%; }
        .right { display: flex; flex-direction: column; gap: 6px; min-height: 0; justify-content: center; }
        .client-name { margin: 0; font-size: 20px; line-height: 1.0; font-weight: 900; letter-spacing: -0.02em; }
        .detail { font-size: 12px; line-height: 1.14; }
        .small-list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 2px 10px; margin-top: 1px; }
        .small-item { font-size: 10px; line-height: 1.12; color: #334155; }
        .small-item strong { color: #0f172a; font-weight: 800; }
        .footer { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px 12px; margin-top: auto; padding-top: 5px; border-top: 1px solid #e2e8f0; }
        .doc-item { font-size: 11px; line-height: 1.12; color: #334155; word-break: break-word; }
        .head-note { font-size: 11px; color: #475569; margin-top: 2px; }
        .batch-note { text-align: center; font-size: 12px; font-weight: 800; color: #0f172a; margin: 6px 0 0; }
    </style>
</head>
<body onload="window.print()">
    @foreach($cajas as $caja)
        <div class="page">
            <div class="label">
                <div class="top">
                    <div class="brand">
                        <img src="{{ asset('imgs/logos/dacabe.png') }}?v=20260702" alt="Logo DACABE">
                        <div class="head-note">Lote {{ $bultoCodigo }} | Cliente {{ $clienteRif }}</div>
                    </div>
                    <div class="bulto">
                        <div class="title">Caja</div>
                        <div class="value">{{ $caja->bulto_posicion && $caja->bulto_total ? $caja->bulto_posicion . '/' . $caja->bulto_total : '1/1' }}</div>
                    </div>
                </div>

                <div class="middle">
                    <div class="qr">
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(204)->generate(route('logistica.public.show', ['token' => $caja->public_token])) !!}
                    </div>
                    <div class="right">
                        <h2 class="client-name">{{ $caja->cliente_nombre }}</h2>
                        <div class="detail"><strong>Dirección:</strong> {{ $caja->direccion_entrega }}</div>
                        <div class="small-list">
                            <div class="small-item"><strong>Ciudad:</strong> {{ $caja->ciudad ?: '-' }}</div>
                            <div class="small-item"><strong>Estado:</strong> {{ $caja->estado ?: '-' }}</div>
                            <div class="small-item"><strong>Teléfono:</strong> {{ $caja->telefono ?: 'Sin teléfono' }}</div>
                            <div class="small-item"><strong>Vendedor:</strong> {{ $caja->vendedor_nombre ?: '-' }}</div>
                            <div class="small-item"><strong>Peso:</strong> {{ $caja->peso_kg !== null ? number_format((float) $caja->peso_kg, 3, ',', '.') . ' kg' : '-' }}</div>
                        </div>
                    </div>
                </div>

                <div class="footer">
                    @php
                        $pedidoIds = collect($caja->items)->pluck('pedido_id')->filter()->unique()->values()->all();
                        $facturas = collect($caja->items)->pluck('factura_numero')->filter()->unique()->values()->all();
                    @endphp
                    <div class="doc-item"><strong>Pedidos:</strong> {{ implode(', ', $pedidoIds ?: []) ?: '-' }}</div>
                    <div class="doc-item"><strong>Facturas:</strong> {{ implode(', ', $facturas ?: []) ?: '-' }}</div>
                </div>
            </div>
        </div>
    @endforeach
</body>
</html>