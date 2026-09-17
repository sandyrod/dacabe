<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Lista de Precios</title>
    <style>
        @page { margin:24px 28px 30px; }
        body { color:#17324d; font-family:DejaVu Sans,sans-serif; font-size:9px; }
        .header { border-bottom:3px solid #087e8b; padding-bottom:10px; margin-bottom:12px; }
        .header-table,.meta-table,.products { border-collapse:collapse; width:100%; }
        .logo { max-width:115px; max-height:62px; }
        h1 { font-size:19px; margin:0 0 4px; }
        .company { color:#536878; line-height:1.45; }
        .meta-table td { background:#eef6f6; border:1px solid #d6e5e5; padding:7px; }
        .filters { color:#536878; font-size:8px; margin:9px 0; }
        .products th { background:#17324d; color:#fff; font-size:8px; padding:6px 4px; text-align:left; }
        .products td { border-bottom:1px solid #dce4e8; padding:5px 4px; vertical-align:middle; }
        .products tr:nth-child(even) td { background:#f7f9fa; }
        .thumb { height:32px; width:38px; object-fit:contain; }
        .number { text-align:right; white-space:nowrap; }
        .stock { text-align:center; }
        .code { color:#087e8b; font-weight:bold; }
        .footer { color:#71818c; font-size:7px; margin-top:10px; text-align:center; }
    </style>
</head>
<body>
    @php
        $logoPath = $company && $company->logo ? storage_path('app/public/logos/' . $company->logo) : null;
    @endphp
    <div class="header">
        <table class="header-table"><tr>
            <td style="width:28%">@if($logoPath && file_exists($logoPath))<img class="logo" src="{{ $logoPath }}" alt="Logo">@endif</td>
            <td style="width:44%;text-align:center"><h1>LISTA DE PRECIOS</h1><div class="company"><strong>{{ optional($company)->name }}</strong><br>{{ optional($company)->location }}<br>{{ optional($company)->phone }} {{ optional($company)->email }}</div></td>
            <td style="width:28%;text-align:right"><strong>{{ now()->format('d/m/Y') }}</strong><br>{{ now()->format('h:i A') }}</td>
        </tr></table>
    </div>

    <table class="meta-table"><tr>
        <td><strong>Depósito:</strong><br>{{ optional($deposito)->DDEPOS ?: 'No indicado' }}</td>
        <td><strong>Moneda:</strong><br>Divisa</td>
        <td><strong>Productos:</strong><br>{{ number_format($productos->count()) }}</td>
    </tr></table>

    @if(array_filter($filtros, function ($value) { return $value !== null && $value !== ''; }))
        <div class="filters"><strong>Filtros aplicados:</strong>
            @if(!empty($filtros['buscar'])) Búsqueda: {{ $filtros['buscar'] }} · @endif
            @if(!empty($filtros['cgrupo'])) Grupos: {{ implode(', ', (array) $filtros['cgrupo']) }} · @endif
            @if(!empty($filtros['stock'])) Stock: {{ str_replace('_', ' ', $filtros['stock']) }} · @endif
            @if(!empty($filtros['precio_desde'])) Desde: ${{ number_format($filtros['precio_desde'], 2, ',', '.') }} · @endif
            @if(!empty($filtros['precio_hasta'])) Hasta: ${{ number_format($filtros['precio_hasta'], 2, ',', '.') }} @endif
        </div>
    @endif

    <table class="products">
        <thead><tr><th style="width:7%">Foto</th><th style="width:14%">Código</th><th>Descripción</th><th style="width:15%;text-align:right">REF1</th><th style="width:18%;text-align:right">REF2</th><th style="width:9%;text-align:center">Stock</th></tr></thead>
        <tbody>
            @forelse($productos as $producto)
                @php $fotoPath = $producto->FOTO ? storage_path('app/public/products/' . $producto->FOTO) : null; @endphp
                <tr>
                    <td>@if($fotoPath && file_exists($fotoPath))<img class="thumb" src="{{ $fotoPath }}" alt="">@endif</td>
                    <td class="code">{{ $producto->CODIGO }}</td>
                    <td>{{ $producto->DESCR }}</td>
                    <td class="number">$ {{ number_format($producto->precio_ref1, 2, ',', '.') }}</td>
                    <td class="number">$ {{ number_format($producto->precio_ref2, 2, ',', '.') }}</td>
                    <td class="stock">{{ number_format($producto->stock, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;padding:24px">No hay productos para los filtros seleccionados.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="footer">Precios REF1 y REF2 expresados en divisa. Documento generado por {{ optional($company)->name }}.</div>
</body>
</html>