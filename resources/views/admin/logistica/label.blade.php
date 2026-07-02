<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etiqueta {{ $caja->codigo }}</title>
    <style>
        @page { size: 5in 4.2in; margin: 8mm; }
        body { font-family: Arial, Helvetica, sans-serif; margin: 0; color: #111; }
        .label { border: 1px solid #111; border-radius: 10px; padding: 8px; }
        .top { display: flex; flex-direction: column; align-items: center; justify-content: center; border-bottom: 2px solid #111; padding-bottom: 4px; margin-bottom: 4px; min-height: 76px; }
        .brand { display: flex; align-items: center; justify-content: center; }
        .brand img { width: 250px !important; object-fit: contain; display: block; }
        .code { font-size: 12px; font-weight: 800; line-height: 1; margin-top: 2px; }
        .middle { display: grid; grid-template-columns: 42% 58%; gap: 8px; align-items: start; }
        .qr { border: 1px solid #333; border-radius: 8px; padding: 6px; display: flex; align-items: center; justify-content: center; min-height: 150px; }
        .right h2 { margin: 0 0 4px; font-size: 18px; line-height: 1.1; }
        .right .big { font-size: 14px; font-weight: 700; margin-bottom: 4px; }
        .right .line { font-size: 12px; margin-bottom: 3px; }
        .small { margin-top: 5px; font-size: 10px; color: #222; }
        .small strong { font-size: 11px; }
    </style>
</head>
<body onload="window.print()">
    <div class="label">
        <div class="top">
            <div class="brand">
                <img src="{{ asset('imgs/logos/dacabe.png') }}?v=20260702" alt="Logo DACABE">
            </div>
            <div class="code">{{ $caja->codigo }}</div>
        </div>

        <div class="middle">
            <div class="qr">
                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(160)->generate($publicUrl) !!}
            </div>
            <div class="right">
                <h2>{{ $caja->cliente_nombre }}</h2>
                <div class="big">{{ $caja->telefono ?: 'Sin teléfono' }}</div>
                <div class="line"><strong>Entrega:</strong> {{ $caja->direccion_entrega }}</div>
                <div class="line"><strong>Ciudad:</strong> {{ $caja->ciudad ?: '-' }}</div>
                <div class="line"><strong>Estado:</strong> {{ $caja->estado ?: '-' }}</div>
                <div class="small">
                    <div><strong>Vendedor:</strong> {{ $caja->vendedor_nombre ?: '-' }}</div>
                    <div><strong>Chofer:</strong> {{ $caja->chofer_nombre ?: '-' }}</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
