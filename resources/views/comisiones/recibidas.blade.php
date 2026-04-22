@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-gradient-navy text-white">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-hand-holding-usd mr-2"></i>Mis Comisiones Recibidas
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body bg-light-silver p-4">
                        <!-- Filtros Simplificados -->
                        <form method="GET" action="{{ route('comisiones.recibidas') }}" class="mb-0">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label for="cliente" class="font-weight-bold text-primary mb-1">
                                            <i class="fas fa-user mr-1"></i>Cliente / Pedido
                                        </label>
                                        <input type="text" name="cliente" id="cliente" class="form-control"
                                            value="{{ request('cliente') }}" placeholder="Nombre o descripción...">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-0">
                                        <label for="estado" class="font-weight-bold text-primary mb-1">
                                            <i class="fas fa-tag mr-1"></i>Estado
                                        </label>
                                        <select name="estado" id="estado" class="form-control">
                                            <option value="pagada"
                                                {{ request('estado', 'pagada') == 'pagada' ? 'selected' : '' }}>Pagada
                                            </option>
                                            <option value="pendiente"
                                                {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                            <option value="rechazada"
                                                {{ request('estado') == 'rechazada' ? 'selected' : '' }}>Rechazada</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold text-primary mb-1"><i
                                                class="far fa-calendar-alt mr-1"></i> Desde</label>
                                        <input type="date" name="fecha_inicio" class="form-control"
                                            value="{{ request('fecha_inicio') }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold text-primary mb-1"><i
                                                class="far fa-calendar-check mr-1"></i> Hasta</label>
                                        <input type="date" name="fecha_fin" class="form-control"
                                            value="{{ request('fecha_fin') }}">
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex flex-column">
                                    <button type="submit" class="btn btn-primary btn-block mt-2">
                                        <i class="fas fa-filter mr-1"></i> Filtrar
                                    </button>
                                    <a href="{{ route('comisiones.recibidas') }}" class="btn btn-secondary btn-block mt-2">
                                        <i class="fas fa-undo mr-1"></i> Limpiar
                                    </a>
                                </div>
                            </div>
                        </form>

                        <!-- Barra Elegante de Selección (Estilo Stat-Card) -->
                        <div id="selectionSummaryBar" class="selection-summary-bar-wrapper" style="display: none;">
                            <div class="container-fluid">
                                <div class="selection-summary-card shadow-sm">
                                    <div class="row align-items-center">
                                        <div class="col-md-3">
                                            <div class="summary-stat">
                                                <div class="summary-icon">
                                                    <i class="fas fa-check-circle"></i>
                                                </div>
                                                <div class="summary-content">
                                                    <div class="summary-label">Pedidos</div>
                                                    <div class="summary-value" id="selectedOrdersCount">0</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="summary-stat">
                                                <div class="summary-icon">
                                                    <i class="fas fa-dollar-sign"></i>
                                                </div>
                                                <div class="summary-content">
                                                    <div class="summary-label">Total Acumulado</div>
                                                    <div class="summary-value" id="selectedTotalAmount">$0.00</div>
                                                    <div class="summary-secondary" id="selectedTotalBs">0.00 Bs</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-right">
                                            <button type="button" class="btn btn-light btn-lg btn-block mr-2"
                                                id="btnCancelarSeleccion">
                                                <i class="fas fa-times mr-1"></i> Cancelar
                                            </button>
                                            <button type="button" class="btn btn-success btn-lg btn-block mt-2"
                                                id="btnConfirmarLote">
                                                <i class="fas fa-check-double mr-1"></i> Confirmar Pagos
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Resumen para el Vendedor -->
                        <div class="row mb-4 mt-4">
                            <div class="col-md-4 mb-4">
                                <div class="stat-card h-100 shadow-sm"
                                    style="background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);">
                                    <div class="card-body text-white position-relative">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="pr-3">
                                                <h6 class="text-uppercase text-white-50 mb-1 small font-weight-bold">
                                                    <i class="fas fa-check-circle mr-2"></i>Total Pagadas
                                                </h6>
                                                <h3 class="mb-0 font-weight-bold">${{ number_format($totalPagada, 2) }}
                                                </h3>
                                            </div>
                                            <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                                                <i class="fas fa-hand-holding-usd"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 mb-4">
                                <div class="stat-card h-100 shadow-sm"
                                    style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
                                    <div class="card-body text-white position-relative">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="pr-3">
                                                <h6 class="text-uppercase text-white-50 mb-1 small font-weight-bold">
                                                    <i class="fas fa-thumbs-up mr-2"></i>Confirmadas por Mí
                                                </h6>
                                                <h3 class="mb-0 font-weight-bold">${{ number_format($totalConfirmado, 2) }}
                                                </h3>
                                            </div>
                                            <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                                                <i class="fas fa-user-check"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 mb-4">
                                <div class="stat-card h-100 shadow-sm"
                                    style="background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);">
                                    <div class="card-body text-white position-relative">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="pr-3">
                                                <h6 class="text-uppercase text-white-50 mb-1 small font-weight-bold">
                                                    <i class="fas fa-clock mr-2"></i>Pendientes de Pago
                                                </h6>
                                                <h3 class="mb-0 font-weight-bold">${{ number_format($totalPendiente, 2) }}
                                                </h3>
                                            </div>
                                            <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                                                <i class="fas fa-hourglass-half"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Listado de Comisiones -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped mb-0">
                                        <thead class="bg-light text-navy">
                                            <tr>
                                                <th class="text-center" style="width: 40px;">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input"
                                                            id="checkAll">
                                                        <label class="custom-control-label" for="checkAll"></label>
                                                    </div>
                                                </th>
                                                <th class="text-center">Fecha</th>
                                                <th class="text-center">Cliente(s)</th>
                                                <th class="text-center">Referencia</th>
                                                <th class="text-center">Banco/Destino</th>
                                                <th class="text-center">Método</th>
                                                <th class="text-center">Nota</th>
                                                <th class="text-right">Total $</th>
                                                <th class="text-right">Total Bs</th>
                                                <th class="text-center">Tasa</th>
                                                <th class="text-center">Estado</th>
                                                <th class="text-center">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($comisiones as $comision)
                                                <tr
                                                    class="{{ $comision->estatus_comision == 'pagada' && !$comision->recibido ? 'row-highlight-pending' : '' }}">
                                                    <td class="text-center">
                                                        @if ($comision->estatus_comision == 'pagada' && !$comision->recibido)
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox"
                                                                    class="custom-control-input comision-checkbox"
                                                                    id="check_{{ $comision->identificador_pago }}"
                                                                    value="{{ $comision->identificador_pago }}"
                                                                    data-total="{{ $comision->total_divisa ?: $comision->total_comision }}"
                                                                    data-total-bs="{{ $comision->total_bs ?: 0 }}">
                                                                <label class="custom-control-label"
                                                                    for="check_{{ $comision->identificador_pago }}"></label>
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <i class="far fa-calendar-alt text-primary mr-2"></i>
                                                        {{ $comision->fecha_desembolso ? \Carbon\Carbon::parse($comision->fecha_desembolso)->format('d/m/Y') : 'N/A' }}
                                                    </td>
                                                    <td class="text-center">
                                                        <small
                                                            class="font-weight-bold text-navy">{{ $comision->clientes ?? 'N/A' }}</small>
                                                    </td>
                                                    <td class="text-center font-weight-bold">
                                                        {{ $comision->referencia ?? '-' }}
                                                    </td>
                                                    <td class="text-center small">
                                                        {{ $comision->banco_destino ?? '-' }}
                                                    </td>
                                                    <td class="text-center">
                                                        <span
                                                            class="badge badge-light border">{{ $comision->forma_pago ?? '-' }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="text-truncate mx-auto" style="max-width: 150px;"
                                                            title="{{ $comision->observaciones }}">
                                                            <small
                                                                class="text-muted">{{ $comision->observaciones ?: '-' }}</small>
                                                        </div>
                                                    </td>
                                                    <td class="text-right text-navy font-weight-bold">
                                                        ${{ number_format($comision->total_divisa ?: $comision->total_comision, 2) }}
                                                    </td>
                                                    <td class="text-right text-success small">
                                                        {{ number_format($comision->total_bs ?: 0, 2) }} Bs
                                                    </td>
                                                    <td class="text-center small">
                                                        {{ number_format($comision->tasa ?: 0, 4) }}
                                                    </td>
                                                    <td class="text-center">
                                                        @php
                                                            $badgeClass =
                                                                $comision->estatus_comision == 'pagada'
                                                                    ? 'success'
                                                                    : ($comision->estatus_comision == 'pendiente'
                                                                        ? 'warning'
                                                                        : 'danger');
                                                            $iconClass =
                                                                $comision->estatus_comision == 'pagada'
                                                                    ? 'fa-check-circle'
                                                                    : ($comision->estatus_comision == 'pendiente'
                                                                        ? 'fa-clock'
                                                                        : 'fa-times-circle');
                                                        @endphp
                                                        <span class="badge badge-{{ $badgeClass }} px-3 py-2">
                                                            <i
                                                                class="fas {{ $iconClass }} mr-1"></i>{{ ucfirst($comision->estatus_comision) }}
                                                        </span>
                                                        @if ($comision->recibido)
                                                            <div class="mt-1">
                                                                <small class="text-success font-weight-bold"><i
                                                                        class="fas fa-check-double"></i> Recibido</small>
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @if ($comision->estatus_comision == 'pagada')
                                                            <button
                                                                class="btn btn-sm btn-{{ !$comision->recibido ? 'warning pulse-warning' : 'info' }} btn-ver-grupo"
                                                                data-identificador="{{ $comision->identificador_pago }}"
                                                                data-recibido="{{ $comision->recibido }}">
                                                                <i class="fas fa-file-invoice-dollar mr-1"></i>
                                                                {{ !$comision->recibido ? 'Confirmar Pago' : 'Ver Detalles' }}
                                                            </button>
                                                        @else
                                                            <span class="text-muted small">N/A</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="11" class="text-center py-5">
                                                        <i class="fas fa-file-invoice-dollar fa-4x text-muted mb-3"
                                                            style="opacity: 0.3;"></i>
                                                        <p class="text-muted font-weight-bold">No se encontraron comisiones
                                                            para este criterio.</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="p-3">
                                    {{ $comisiones->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('comisiones.partials.modales_vendedor')
@endsection

@section('styles')
    <style>
        .stat-card {
            transition: all 0.3s ease;
            border-radius: 12px;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .bg-gradient-navy {
            background: linear-gradient(45deg, #001f3f, #003366);
        }

        .text-navy {
            color: #001f3f;
        }

        .row-highlight-pending {
            background-color: rgba(255, 193, 7, 0.08) !important;
            border-left: 4px solid #ffc107 !important;
        }

        @keyframes pulse-orange {
            0% {
                box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(255, 193, 7, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(255, 193, 7, 0);
            }
        }

        .pulse-warning {
            animation: pulse-orange 2s infinite;
        }

        .modal-premium .modal-content {
            border-radius: 20px;
            overflow: hidden;
        }

        .payment-amount-header {
            background: #3275b8;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            color: white;
        }

        .payment-detail-item {
            padding: 1rem;
            border-radius: 12px;
            background: #fff;
            border: 1px solid #eee;
            height: 100%;
            transition: all 0.2s ease;
        }

        .payment-detail-item:hover {
            border-color: #007bff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .detail-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #6c757d;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .detail-value {
            font-size: 1rem;
            color: #2d3436;
            font-weight: 600;
        }

        .recibido-stamp {
            position: absolute;
            top: 20px;
            right: 20px;
            transform: rotate(15deg);
            border: 3px solid #18ca1a;
            color: #1fc222;
            padding: 5px 15px;
            font-weight: 900;
            text-transform: uppercase;
            border-radius: 8px;
            opacity: 0.8;
            font-size: 1rem;
            pointer-events: none;
            z-index: 10;
            background: rgba(255, 255, 255, 0.9);
        }

        /* Arreglo para múltiples modales (Nivel 2 sobre Nivel 1) */
        #modalDetalleComisiones {
            z-index: 1080 !important;
        }

        #modalDetalleComisiones.modal.fade.show~.modal-backdrop.show:last-child {
            z-index: 1075 !important;
        }

        /* Asegurar que el scroll funcione en el modal superior */
        .modal.show {
            overflow-y: auto !important;
        }

        /* Barra de Resumen de Selección - Estilo Stat-Card */
        .selection-summary-bar-wrapper {
            position: fixed;
            bottom: 20px;
            left: 10%;
            right: 5%;
            z-index: 1000;
            pointer-events: none;
            max-width: 1000px;
            background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
            border-radius: 15px;
        }

        @media (max-width: 768px) {
            .selection-summary-bar-wrapper {
                bottom: 80px;
            }
        }

        .selection-summary-card {
            background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
            border-radius: 15px;
            padding: 20px 30px;
            margin: 0 auto;
            max-width: 1200px;
            pointer-events: auto;
            animation: slideUpBounce 0.4s ease-out;
            box-shadow: 0 8px 25px rgba(0, 31, 63, 0.3);
        }

        @keyframes slideUpBounce {
            0% {
                transform: translateY(100px);
                opacity: 0;
            }

            60% {
                transform: translateY(-10px);
                opacity: 1;
            }

            100% {
                transform: translateY(0);
            }
        }

        .summary-stat {
            display: flex;
            align-items: center;
            color: white;
        }

        .summary-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .summary-icon i {
            font-size: 1.5rem;
            color: white;
        }

        .summary-content {
            flex-grow: 1;
        }

        .summary-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 600;
            margin-bottom: 2px;
        }

        .summary-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: white;
            line-height: 1;
        }

        .selection-summary-card .btn-light {
            background: rgba(255, 255, 255, 0.95);
            border: none;
            font-weight: 600;
            padding: 10px 25px;
            border-radius: 10px;
            transition: all 0.3s ease;
            color: #001f3f;
        }

        .selection-summary-card .btn-light:hover {
            background: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .selection-summary-card .btn-success {
            background: #1cc88a;
            border: none;
            font-weight: 700;
            padding: 10px 30px;
            border-radius: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(28, 200, 138, 0.3);
        }

        .selection-summary-card .btn-success:hover {
            background: #17a673;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(28, 200, 138, 0.4);
        }
    </style>
@endsection

@section('scripts')
    <script>
        $(function() {
            $('[data-toggle="tooltip"]').tooltip();

            // Lógica para Selección en Lote
            let selectedIds = [];
            let selectedData = {}; // Almacenar datos de cada comisión seleccionada

            function updateBatchUI() {
                const count = selectedIds.length;

                // Calcular totales acumulados (USD y Bs)
                let totalAmount = 0;
                let totalBs = 0;
                selectedIds.forEach(id => {
                    if (selectedData[id]) {
                        totalAmount += selectedData[id].total;
                        totalBs += selectedData[id].totalBs;
                    }
                });

                // Actualizar valores en la barra
                $('#selectedOrdersCount').text(count);
                $('#selectedTotalAmount').text('$' + totalAmount.toFixed(2));
                $('#selectedTotalBs').text(totalBs.toFixed(2) + ' Bs');

                // Mostrar/ocultar barra elegante
                if (count > 0) {
                    $('#selectionSummaryBar').fadeIn(300);
                } else {
                    $('#selectionSummaryBar').fadeOut(300);
                }

                // Actualizar estado del checkbox "Seleccionar Todo"
                const totalCheckboxes = $('.comision-checkbox').length;
                const checkedCheckboxes = $('.comision-checkbox:checked').length;
                $('#checkAll').prop('checked', totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes);
            }

            // Checkbox "Seleccionar Todo"
            $('#checkAll').on('change', function() {
                const isChecked = $(this).is(':checked');
                $('.comision-checkbox').prop('checked', isChecked);

                selectedIds = [];
                selectedData = {};
                if (isChecked) {
                    $('.comision-checkbox').each(function() {
                        const id = $(this).val();
                        const total = parseFloat($(this).data('total')) || 0;
                        const totalBs = parseFloat($(this).data('total-bs')) || 0;
                        selectedIds.push(id);
                        selectedData[id] = {
                            total: total,
                            totalBs: totalBs
                        };
                    });
                }
                updateBatchUI();
            });

            // Checkboxes individuales
            $(document).on('change', '.comision-checkbox', function() {
                const value = $(this).val();
                const total = parseFloat($(this).data('total')) || 0;
                const totalBs = parseFloat($(this).data('total-bs')) || 0;

                if ($(this).is(':checked')) {
                    if (!selectedIds.includes(value)) {
                        selectedIds.push(value);
                        selectedData[value] = {
                            total: total,
                            totalBs: totalBs
                        };
                    }
                } else {
                    selectedIds = selectedIds.filter(id => id !== value);
                    delete selectedData[value];
                }
                updateBatchUI();
            });

            // Botón Cancelar Selección
            $('#btnCancelarSeleccion').on('click', function() {
                $('.comision-checkbox').prop('checked', false);
                $('#checkAll').prop('checked', false);
                selectedIds = [];
                selectedData = {};
                updateBatchUI();
            });

            // Botón Confirmar Lote
            $('#btnConfirmarLote').on('click', function() {
                if (selectedIds.length === 0) {
                    toastr.warning('Debe seleccionar al menos una comisión para confirmar');
                    return;
                }

                if (!confirm(`¿Está seguro de confirmar la recepción de ${selectedIds.length} pago(s)?`)) {
                    return;
                }

                const btn = $(this);
                btn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin mr-1"></i> Procesando...');

                $.ajax({
                    url: '{{ route('comisiones.confirmar_recibido_lote') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        identificadores: selectedIds
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            location.reload();
                        } else {
                            toastr.error(response.message);
                            btn.prop('disabled', false).html(
                                '<i class="fas fa-check-double mr-1"></i> Confirmar Pagos Seleccionados (<span id="selectedCount">' +
                                selectedIds.length + '</span>)');
                        }
                    },
                    error: function() {
                        toastr.error('Error al procesar la confirmación en lote');
                        btn.prop('disabled', false).html(
                            '<i class="fas fa-check-double mr-1"></i> Confirmar Pagos Seleccionados (<span id="selectedCount">' +
                            selectedIds.length + '</span>)');
                    }
                });
            });

            // Lógica para Ver Grupo y Confirmar Pago (Individual - Mantener funcionalidad existente)
            $(document).on('click', '.btn-ver-grupo', function() {
                const identificador = $(this).data('identificador');
                const recibido = $(this).data('recibido');

                $('#modal_grupo_identificador').val(identificador);

                if (recibido) {
                    $('#btnConfirmarRecibidoGrupo').hide();
                } else {
                    $('#btnConfirmarRecibidoGrupo').show();
                }

                const url = '{{ url('comisiones') }}/' + identificador + '/comisiones-grupo';
                $.get(url, function(response) {
                    if (response.success) {
                        const tbody = $('#grupoComisionesBody');
                        tbody.empty();
                        response.items.forEach(item => {
                            tbody.append(`
                                <tr>
                                    <td>${new Date(item.fecha_pedido).toLocaleDateString('es-ES')}</td>
                                    <td><span class="badge badge-info">#${item.pedido_id}</span></td>
                                    <td><small class="font-weight-bold">${item.cliente}</small></td>
                                    <td class="text-right font-weight-bold">$${parseFloat(item.total_comision).toFixed(2)}</td>
                                    <td class="text-center">
                                        <button class="btn btn-xs btn-outline-primary btn-ver-detalle" 
                                                data-pago-id="${item.pago_id}"
                                                data-toggle="tooltip" title="Ver productos">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            `);
                        });
                        $('#modalGrupoComisiones').modal('show');
                        $('[data-toggle="tooltip"]').tooltip();
                    } else {
                        toastr.error('Error al cargar las comisiones del grupo');
                    }
                });
            });

            // Confirmar Recepción desde la Modal de Grupo (Individual)
            $(document).on('click', '#btnConfirmarRecibidoGrupo', function() {
                const identificador = $('#modal_grupo_identificador').val();
                if (!identificador) return;

                const btn = $(this);
                btn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin mr-1"></i> Procesando...');

                $.ajax({
                    url: '{{ url('comisiones') }}/' + identificador + '/confirmar-recibido',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            $('#modalGrupoComisiones').modal('hide');
                            location.reload();
                        } else {
                            toastr.error(response.message);
                            btn.prop('disabled', false).html(
                                '<i class="fas fa-check-double mr-1"></i> CONFIRMAR RECEPCIÓN'
                            );
                        }
                    },
                    error: function() {
                        toastr.error('Error al procesar la confirmación');
                        btn.prop('disabled', false).html(
                            '<i class="fas fa-check-double mr-1"></i> CONFIRMAR RECEPCIÓN');
                    }
                });
            });

            // Lógica para Ver Detalle de Productos (Nivel 2)
            $(document).on('click', '.btn-ver-detalle', function() {
                const pagoId = $(this).data('pago-id');
                cargarDetalleComisiones(pagoId);
            });

            function cargarDetalleComisiones(pagoId) {
                const url = '{{ url('comisiones') }}/' + pagoId + '/detalles';
                $.get(url, function(response) {
                    if (response.success) {
                        const tbody = $('#detalleComisionesBody');
                        tbody.empty();
                        let total = 0;
                        response.detalles.forEach(d => {
                            total += parseFloat(d.monto_comision);
                            tbody.append(`
                                <tr>
                                    <td><span class="badge badge-secondary">${d.codigo_producto}</span></td>
                                    <td>${d.nombre_producto || 'N/A'}</td>
                                    <td class="text-center">${d.cantidad}</td>
                                    <td class="text-right font-weight-bold">$${parseFloat(d.monto_comision).toFixed(2)}</td>
                                    <td class="text-center">${parseFloat(d.porcentaje_comision).toFixed(2)}%</td>
                                </tr>
                            `);
                        });
                        $('#totalDetalleComision').text('$' + total.toFixed(2));
                        $('#modalDetalleComisiones').modal('show');
                    }
                });
            }
        });
    </script>
@endsection
