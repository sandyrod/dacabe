<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Vision 360 Cliente</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; }
        .header { border-bottom: 2px solid #1f6e8c; margin-bottom: 12px; padding-bottom: 8px; }
        .title { font-size: 18px; font-weight: 700; color: #0f243a; }
        .sub { font-size: 10px; color: #475569; }
        .kpi-grid { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .kpi-grid td { border: 1px solid #cfd9e5; padding: 6px; width: 25%; }
        .kpi-label { font-size: 9px; text-transform: uppercase; color: #64748b; }
        .kpi-val { font-size: 12px; font-weight: 700; color: #0f172a; margin-top: 2px; }
        .section-title { font-size: 12px; font-weight: 700; margin: 10px 0 6px; color: #0f243a; }
        table.report { width: 100%; border-collapse: collapse; }
        table.report th, table.report td { border: 1px solid #d5deea; padding: 5px; }
        table.report th { background: #eef4fb; font-size: 9px; text-transform: uppercase; color: #1e3a5f; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Vision 360 Cliente</div>
        <div class="sub">
            Cliente: {{ $clienteSeleccionado->nombre }} (RIF: {{ $clienteSeleccionado->rif ?: 'N/D' }}, Codcli: {{ $clienteSeleccionado->codcli ?: 'N/D' }})
        </div>
        <div class="sub">
            Rango: {{ $filtros['fecha_desde'] ?: 'Inicio' }} a {{ $filtros['fecha_hasta'] ?: 'Hoy' }}
        </div>
        <div class="sub">Generado: {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <table class="kpi-grid">
        <tr>
            <td><div class="kpi-label">Ventas USD</div><div class="kpi-val">${{ number_format($resumen['ventas_usd'], 2, ',', '.') }}</div></td>
            <td><div class="kpi-label">Pedidos</div><div class="kpi-val">{{ number_format($resumen['pedidos_total']) }}</div></td>
            <td><div class="kpi-label">Cobranza</div><div class="kpi-val">${{ number_format($resumen['pagos_aprobados'], 2, ',', '.') }}</div></td>
            <td><div class="kpi-label">Saldo Cartera USD</div><div class="kpi-val">${{ number_format($resumen['saldo_cartera_usd'], 2, ',', '.') }}</div></td>
        </tr>
    </table>

    <div class="section-title">Top Productos</div>
    <table class="report">
        <thead>
            <tr>
                <th>Codigo</th>
                <th>Descripcion</th>
                <th class="text-right">Unidades</th>
                <th class="text-right">Ventas USD</th>
            </tr>
        </thead>
        <tbody>
            @forelse($topProductos as $item)
                <tr>
                    <td>{{ $item->codigo_inven }}</td>
                    <td>{{ $item->descripcion }}</td>
                    <td class="text-right">{{ number_format($item->unidades, 0, ',', '.') }}</td>
                    <td class="text-right">${{ number_format($item->total_usd, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="4">Sin datos.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Pedidos</div>
    <table class="report">
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Referencia</th>
                <th>Cliente</th>
                <th>Codcli</th>
                <th>Estatus</th>
                <th class="text-right">Unidades</th>
                <th class="text-right">USD</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pedidosExport as $pedido)
                <tr>
                    <td>{{ $pedido->id }}</td>
                    <td>{{ $pedido->fecha ? \Carbon\Carbon::parse($pedido->fecha)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $pedido->referencia ?: '-' }}</td>
                    <td>{{ $pedido->descripcion }}</td>
                    <td>{{ $pedido->codcli ?: '-' }}</td>
                    <td>{{ $pedido->estatus ?: 'SIN ESTATUS' }}</td>
                    <td class="text-right">{{ number_format($pedido->total_unidades, 0, ',', '.') }}</td>
                    <td class="text-right">${{ number_format($pedido->total_usd, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="8">Sin pedidos para el rango seleccionado.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Estado de Cuenta y Saldos (Cuentas por Cobrar)</div>
    <table class="report">
        <thead>
            <tr>
                <th>Pedido</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Vencimiento</th>
                <th class="text-right">Saldo USD</th>
                <th class="text-right">Saldo Bs</th>
            </tr>
        </thead>
        <tbody>
            @forelse($carteraDetalle as $item)
                <tr>
                    <td>#{{ $item->id }}</td>
                    <td>{{ $item->fecha ? \Carbon\Carbon::parse($item->fecha)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $item->descripcion }}</td>
                    <td>
                        @if(!empty($item->fecha_vencimiento))
                            {{ \Carbon\Carbon::parse($item->fecha_vencimiento)->format('d/m/Y') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right">${{ number_format($item->saldo_total_usd, 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->saldo_total_bs, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="6">Sin saldos en cuentas por cobrar.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
