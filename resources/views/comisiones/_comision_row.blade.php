<tr data-id="{{ $comision->id }}">
    <td>{{ $comision->created_at->format('d/m/Y H:i') }}</td>
    <td>
        {{ $comision->nombre_vendedor }}
        <br><small class="text-muted">{{ $comision->correo_vendedor }}</small>
    </td>
    <td>{{ $comision->codigo_producto }}</td>
    <td>{{ $comision->cantidad }}</td>
    <td class="text-right">${{ number_format($comision->monto_comision, 2) }}</td>
    <td class="text-center">{{ $comision->porcentaje_comision }}%</td>
    <td class="text-center">
        <span class="badge badge-{{ $comision->estatus_comision == 'pagada' ? 'success' : 'warning' }}">
            {{ ucfirst($comision->estatus_comision) }}
        </span>
    </td>
    <td class="text-right">
        @if($comision->estatus_comision == 'pendiente')
            <button class="btn btn-sm btn-success btn-marcar-pagada" data-id="{{ $comision->id }}">
                <i class="fas fa-check"></i> Marcar como Pagada
            </button>
        @else
            <button class="btn btn-sm btn-warning btn-marcar-pendiente" data-id="{{ $comision->id }}">
                <i class="fas fa-undo"></i> Marcar como Pendiente
            </button>
        @endif
    </td>
</tr>
