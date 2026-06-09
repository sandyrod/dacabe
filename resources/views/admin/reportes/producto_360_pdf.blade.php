<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Vision 360 Producto</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; }
        .header { border-bottom: 2px solid #1f6f8b; margin-bottom: 12px; padding-bottom: 8px; }
        .title { font-size: 18px; font-weight: 700; color: #13293d; }
        .sub { font-size: 10px; color: #475569; }
        .kpi-grid { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .kpi-grid td { border: 1px solid #d5deea; padding: 6px; width: 25%; }
        .kpi-label { font-size: 9px; text-transform: uppercase; color: #64748b; }
        .kpi-val { font-size: 12px; font-weight: 700; color: #0f172a; margin-top: 2px; }
        .section-title { font-size: 12px; font-weight: 700; margin: 10px 0 6px; color: #13293d; }
        table.report { width: 100%; border-collapse: collapse; }
        table.report th, table.report td { border: 1px solid #d5deea; padding: 5px; }
        table.report th { background: #eef4fb; font-size: 9px; text-transform: uppercase; color: #1e3a5f; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Vision 360 Producto</div>
        <div class="sub">Producto: {{ $productoSeleccionado->codigo }} - {{ $productoSeleccionado->descripcion }}</div>
        <div class="sub">Rango: {{ $filtros['fecha_desde'] ?: 'Inicio' }} a {{ $filtros['fecha_hasta'] ?: 'Hoy' }}</div>
        <div class="sub">Generado: {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <table class="kpi-grid">
        <tr>
            <td><div class="kpi-label">Ventas USD</div><div class="kpi-val">${{ number_format($resumen['ventas_usd'], 2, ',', '.') }}</div></td>
            <td><div class="kpi-label">Unidades</div><div class="kpi-val">{{ number_format($resumen['unidades'], 0, ',', '.') }}</div></td>
            <td><div class="kpi-label">Pedidos</div><div class="kpi-val">{{ number_format($resumen['pedidos_total']) }}</div></td>
            <td><div class="kpi-label">Clientes Activos</div><div class="kpi-val">{{ number_format($resumen['clientes_activos']) }}</div></td>
        </tr>
        <tr>
            <td><div class="kpi-label">Stock Actual</div><div class="kpi-val">{{ number_format($resumen['stock_actual'], 0, ',', '.') }}</div></td>
            <td><div class="kpi-label">Stock Minimo</div><div class="kpi-val">{{ number_format($resumen['stock_minimo'], 0, ',', '.') }}</div></td>
            <td><div class="kpi-label">Stock Maximo</div><div class="kpi-val">{{ number_format($resumen['stock_maximo'], 0, ',', '.') }}</div></td>
            <td><div class="kpi-label">Precio Lista USD</div><div class="kpi-val">${{ number_format($resumen['precio_lista_usd'], 2, ',', '.') }}</div></td>
        </tr>
    </table>

    <div class="section-title">Top Clientes</div>
    <table class="report">
        <thead><tr><th>Codcli</th><th>Cliente</th><th class="text-right">Pedidos</th><th class="text-right">Unidades</th><th class="text-right">USD</th></tr></thead>
        <tbody>
            @forelse($topClientes as $c)
                <tr>
                    <td>{{ $c->codcli }}</td>
                    <td>{{ $c->cliente }}</td>
                    <td class="text-right">{{ number_format($c->pedidos) }}</td>
                    <td class="text-right">{{ number_format($c->unidades, 0, ',', '.') }}</td>
                    <td class="text-right">${{ number_format($c->ventas_usd, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Sin datos.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Movimientos del Producto</div>
    <table class="report">
        <thead><tr><th>Pedido #</th><th>Fecha</th><th>Referencia</th><th>Estatus</th><th>Cliente</th><th>Codcli</th><th class="text-right">Unid.</th><th class="text-right">Precio USD</th><th class="text-right">Total USD</th></tr></thead>
        <tbody>
            @forelse($movimientosExport as $m)
                <tr>
                    <td>{{ $m->id }}</td>
                    <td>{{ $m->fecha ? \Carbon\Carbon::parse($m->fecha)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $m->referencia ?: '-' }}</td>
                    <td>{{ $m->estatus ?: 'SIN ESTATUS' }}</td>
                    <td>{{ $m->cliente }}</td>
                    <td>{{ $m->codcli ?: '-' }}</td>
                    <td class="text-right">{{ number_format($m->unidades, 0, ',', '.') }}</td>
                    <td class="text-right">${{ number_format($m->precio_usd, 2, ',', '.') }}</td>
                    <td class="text-right">${{ number_format($m->total_usd, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="9">Sin movimientos para el rango seleccionado.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
