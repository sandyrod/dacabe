<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte de Comisiones - {{ date('Y-m-d') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
        }
        .filters {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: 4px;
        }
        .filters p {
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
            color: #666;
        }
        .badge {
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Comisiones</h1>
        <p>Generado el: {{ date('d/m/Y H:i') }}</p>
    </div>

    @if(isset($filtros) && (isset($filtros['vendedor']) || isset($filtros['estado']) || isset($filtros['fecha_inicio'])))
    <div class="filters">
        <h3>Filtros aplicados:</h3>
        @if(isset($filtros['vendedor']) && $filtros['vendedor'])
            <p><strong>Vendedor:</strong> {{ $vendedores[$filtros['vendedor']] ?? $filtros['vendedor'] }}</p>
        @endif
        @if(isset($filtros['producto']) && $filtros['producto'])
            <p><strong>Producto:</strong> {{ $filtros['producto'] }}</p>
        @endif
        @if(isset($filtros['estado']) && $filtros['estado'])
            <p><strong>Estado:</strong> {{ ucfirst($filtros['estado']) }}</p>
        @endif
        @if(isset($filtros['fecha_inicio']) && $filtros['fecha_inicio'])
            <p><strong>Desde:</strong> {{ $filtros['fecha_inicio'] }}
            @if(isset($filtros['fecha_fin']) && $filtros['fecha_fin'])
                <strong>Hasta:</strong> {{ $filtros['fecha_fin'] }}
            @endif
            </p>
        @endif
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Vendedor</th>
                <th>Producto</th>
                <th class="text-center">Cantidad</th>
                <th class="text-right">Monto</th>
                <th class="text-center">% Comisión</th>
                <th class="text-center">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($comisiones as $comision)
            <tr>
                <td>{{ $comision->created_at->format('d/m/Y H:i') }}</td>
                <td>
                    {{ $comision->nombre_vendedor }}
                    <br><small>{{ $comision->correo_vendedor }}</small>
                </td>
                <td>{{ $comision->codigo_producto }}</td>
                <td class="text-center">{{ $comision->cantidad }}</td>
                <td class="text-right">${{ number_format($comision->monto_comision, 2) }}</td>
                <td class="text-center">{{ number_format($comision->porcentaje_comision, 2) }}%</td>
                <td class="text-center">
                    <span class="badge badge-{{ $comision->estatus_comision == 'pagada' ? 'success' : 'warning' }}">
                        {{ ucfirst($comision->estatus_comision) }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">No hay comisiones que mostrar</td>
            </tr>
            @endforelse
        </tbody>
        @if($comisiones->count() > 0)
        <tfoot>
            <tr>
                <th colspan="4" class="text-right">Total:</th>
                <th class="text-right">${{ number_format($comisiones->sum('monto_comision'), 2) }}</th>
                <th colspan="2"></th>
            </tr>
            <tr>
                <th colspan="4" class="text-right">Pendiente:</th>
                <th class="text-right">${{ number_format($comisiones->where('estatus_comision', 'pendiente')->sum('monto_comision'), 2) }}</th>
                <th colspan="2"></th>
            </tr>
            <tr>
                <th colspan="4" class="text-right">Pagado:</th>
                <th class="text-right">${{ number_format($comisiones->where('estatus_comision', 'pagada')->sum('monto_comision'), 2) }}</th>
                <th colspan="2"></th>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="footer">
        Generado por {{ config('app.name') }} - {{ date('d/m/Y H:i:s') }}
    </div>
</body>
</html>
