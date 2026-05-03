            <!-- Modal para Editar Monto de Comisión -->
            <div class="modal fade" id="modalEditarMonto" tabindex="-1" role="dialog" aria-labelledby="modalEditarMontoLabel" aria-hidden="true">
                <div class="modal-dialog modal-md modal-dialog-centered" role="document">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-gradient-navy text-white border-0">
                            <h5 class="modal-title font-weight-bold" id="modalEditarMontoLabel">
                                <i class="fas fa-pen mr-2"></i>Editar Monto de Comisión
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form id="formEditarMontoComision">
                            @csrf
                            <input type="hidden" name="pago_id" id="editar_monto_pago_id">
                            <div class="modal-body p-4 bg-light">
                                <div class="form-group">
                                    <label class="font-weight-bold text-navy"><i class="fas fa-tag mr-1"></i> % Comisión aplicada</label>
                                    <div class="input-group">
                                        <input type="text" id="dcto_display" class="form-control" readonly
                                               style="background:#fff; font-weight:600; color:#495057;">
                                        <div class="input-group-append">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">Porcentaje promedio de comisión usado para este pedido.</small>
                                </div>
                                <div class="form-group">
                                    <label class="font-weight-bold text-navy"><i class="fas fa-dollar-sign mr-1"></i> Nuevo Monto de Comisión ($)</label>
                                    <input type="number" step="0.01" min="0" name="nuevo_monto" id="nuevo_monto_comision" class="form-control" required>
                                    <small class="form-text text-muted">Ingrese el nuevo monto total de comisión para este pago.</small>
                                </div>
                            </div>
                            <div class="modal-footer border-0 bg-white">
                                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                                    <i class="fas fa-times mr-1"></i>Cancelar
                                </button>
                                <button type="submit" class="btn btn-primary shadow-sm px-4">
                                    <i class="fas fa-save mr-1"></i>Actualizar Monto
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-gradient-navy text-white">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-chart-line mr-2"></i>Panel de Comisiones - Vista Ejecutiva
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body bg-light-silver p-4">
                        <!-- Filtros Mejorados -->
                        <form method="GET" action="{{ route('comisiones.index') }}" class="mb-0">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold text-primary mb-1"><i
                                                class="fas fa-user-tie mr-1"></i> Vendedor</label>
                                        <select name="vendedor" class="form-control select2" style="width: 100%;">
                                            <option value="">Todos los vendedores</option>
                                            @foreach ($vendedores as $email => $nombre)
                                                <option value="{{ $email }}"
                                                    {{ request('vendedor') == $email ? 'selected' : '' }}>
                                                    {{ $nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label for="cliente" class="font-weight-bold text-primary mb-1">
                                            <i class="fas fa-user mr-1"></i>Cliente
                                        </label>
                                        <input type="text" name="cliente" id="cliente" class="form-control"
                                            value="{{ request('cliente') }}" placeholder="Nombre del cliente">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-0">
                                        <label for="estado" class="font-weight-bold text-primary mb-1">
                                            <i class="fas fa-tag mr-1"></i>Estado
                                        </label>
                                        <select name="estado" id="estado" class="form-control">
                                            <option value="">Todos los estados</option>
                                            <option value="pendiente"
                                                {{ request('estado', 'pendiente') == 'pendiente' ? 'selected' : '' }}>
                                                Pendiente</option>
                                            <option value="pagada" {{ request('estado') == 'pagada' ? 'selected' : '' }}>
                                                Pagada</option>
                                            <option value="rechazada"
                                                {{ request('estado') == 'rechazada' ? 'selected' : '' }}>Rechazada</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold text-primary mb-1"><i
                                                class="far fa-calendar-alt mr-1"></i> Fecha Inicio</label>
                                        <input type="date" name="fecha_inicio" class="form-control"
                                            value="{{ request('fecha_inicio') }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold text-primary mb-1"><i
                                                class="far fa-calendar-check mr-1"></i> Fecha Fin</label>
                                        <input type="date" name="fecha_fin" class="form-control"
                                            value="{{ request('fecha_fin') }}">
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex flex-column">
                                    <button type="submit" class="btn btn-primary btn-block mt-2">
                                        <i class="fas fa-filter mr-1"></i> Filtrar
                                    </button>
                                    @if (request()->hasAny(['vendedor', 'cliente', 'estado', 'fecha_inicio', 'fecha_fin']))
                                        <a href="{{ route('comisiones.index') }}"
                                            class="btn btn-outline-secondary btn-sm mt-2" title="Limpiar filtros">
                                            <i class="fas fa-undo-alt"></i>
                                        </a>
                                    @endif
                                </div>

                            </div>
                        </form>

                        <!-- Resumen con Efectos -->
                        <style>
                            .stat-card {
                                transition: all 0.3s ease;
                                border: none;
                                border-radius: 12px;
                                overflow: hidden;
                                position: relative;
                                z-index: 1;
                            }

                            .stat-card::before {
                                content: '';
                                position: absolute;
                                top: 0;
                                left: 0;
                                width: 100%;
                                height: 100%;
                                background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
                                z-index: -1;
                                opacity: 0;
                                transition: all 0.3s ease;
                            }

                            .stat-card:hover {
                                transform: translateY(-5px);
                                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
                            }

                            .stat-card:hover::before {
                                opacity: 1;
                            }

                            .stat-icon {
                                width: 50px;
                                height: 50px;
                                border-radius: 12px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                font-size: 1.5rem;
                                transition: all 0.3s ease;
                            }

                            .stat-card:hover .stat-icon {
                                transform: scale(1.1) rotate(5deg);
                            }

                            .stat-badge {
                                transition: all 0.3s ease;
                                border-radius: 20px;
                                padding: 5px 12px;
                                font-weight: 500;
                            }

                            .stat-card:hover .stat-badge {
                                transform: translateX(5px);
                            }
                        </style>

                        <div class="row mb-4 mt-4">
                            <!-- Total Comisiones -->
                            <div class="col-md-3 mb-4">
                                <div class="stat-card h-100"
                                    style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
                                    <div class="card-body text-white position-relative">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="pr-3">
                                                <h6 class="text-uppercase text-white-50 mb-1 small font-weight-bold">
                                                    <i class="fas fa-chart-pie mr-2"></i>Total Comisiones
                                                </h6>
                                                <h3 class="mb-0 font-weight-bold">${{ number_format($totalComision, 2) }}
                                                </h3>
                                            </div>
                                            <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                                                <i class="fas fa-dollar-sign"></i>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <span class="stat-badge" style="background: rgba(255,255,255,0.2);">
                                                <i class="fas fa-chart-line mr-1"></i> Total general
                                            </span>
                                        </div>
                                        <div class="position-absolute" style="right: 10px; bottom: 10px; opacity: 0.1;">
                                            <i class="fas fa-chart-pie" style="font-size: 4rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pendientes -->
                            <div class="col-md-3 mb-4">
                                <div class="stat-card h-100"
                                    style="background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);">
                                    <div class="card-body text-white position-relative">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="pr-3">
                                                <h6 class="text-uppercase text-white-50 mb-1 small font-weight-bold">
                                                    <i class="fas fa-clock mr-2"></i>Pendientes
                                                </h6>
                                                <h3 class="mb-0 font-weight-bold">${{ number_format($totalPendiente, 2) }}
                                                </h3>
                                            </div>
                                            <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                                                <i class="fas fa-exclamation-circle"></i>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <span class="stat-badge" style="background: rgba(255,255,255,0.2);">
                                                <i class="fas fa-info-circle mr-1"></i> Por procesar
                                            </span>
                                        </div>
                                        <div class="position-absolute" style="right: 10px; bottom: 10px; opacity: 0.1;">
                                            <i class="fas fa-clock" style="font-size: 4rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pagadas -->
                            <div class="col-md-3 mb-4">
                                <div class="stat-card h-100"
                                    style="background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);">
                                    <div class="card-body text-white position-relative">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="pr-3">
                                                <h6 class="text-uppercase text-white-50 mb-1 small font-weight-bold">
                                                    <i class="fas fa-check-circle mr-2"></i>Pagadas
                                                </h6>
                                                <h3 class="mb-0 font-weight-bold">${{ number_format($totalPagada, 2) }}
                                                </h3>
                                            </div>
                                            <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                                                <i class="fas fa-check-double"></i>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <span class="stat-badge" style="background: rgba(255,255,255,0.2);">
                                                <i class="fas fa-check mr-1"></i> Completadas
                                            </span>
                                        </div>
                                        <div class="position-absolute" style="right: 10px; bottom: 10px; opacity: 0.1;">
                                            <i class="fas fa-check-circle" style="font-size: 4rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Rechazadas -->
                            <div class="col-md-3 mb-4">
                                <div class="stat-card h-100"
                                    style="background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%);">
                                    <div class="card-body text-white position-relative">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="pr-3">
                                                <h6 class="text-uppercase text-white-50 mb-1 small font-weight-bold">
                                                    <i class="fas fa-times-circle mr-2"></i>Rechazadas
                                                </h6>
                                                <h3 class="mb-0 font-weight-bold">${{ number_format($totalRechazada, 2) }}
                                                </h3>
                                            </div>
                                            <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                                                <i class="fas fa-ban"></i>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <span class="stat-badge" style="background: rgba(255,255,255,0.2);">
                                                <i class="fas fa-times mr-1"></i> No aprobadas
                                            </span>
                                        </div>
                                        <div class="position-absolute" style="right: 10px; bottom: 10px; opacity: 0.1;">
                                            <i class="fas fa-times-circle" style="font-size: 4rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabs de Análisis -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-bottom">
                                <ul class="nav nav-tabs card-header-tabs" id="comisionesTabs" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="pedidos-tab" data-toggle="tab" href="#pedidos"
                                            role="tab">
                                            <i class="fas fa-list-ul mr-2"></i>Comisiones por Pedido
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="productos-tab" data-toggle="tab" href="#productos"
                                            role="tab">
                                            <i class="fas fa-box mr-2"></i>Top 10 Productos
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="vendedores-tab" data-toggle="tab" href="#vendedores"
                                            role="tab">
                                            <i class="fas fa-users mr-2"></i>Top 10 Vendedores
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-body p-0">
                                <div class="tab-content" id="comisionesTabContent">
                                    <!-- Tab 1: Comisiones por Pedido -->
                                    <div class="tab-pane fade show active" id="pedidos" role="tabpanel">
                                        <div class="table-responsive">
                                            <table class="table table-hover table-striped mb-0" id="comisionesTable">
                                                <thead class="bg-light">
                                                    <tr>
                                                        @php
                                                            $currentSort = request('sort', 'fecha');
                                                            $currentDirection = request('direction', 'desc');

                                                            if (!function_exists('getSortUrl')) {
                                                                function getSortUrl(
                                                                    $field,
                                                                    $currentSort,
                                                                    $currentDirection,
                                                                ) {
                                                                    $direction = 'asc';
                                                                    if (
                                                                        $currentSort === $field &&
                                                                        $currentDirection === 'asc'
                                                                    ) {
                                                                        $direction = 'desc';
                                                                    }
                                                                    return request()->fullUrlWithQuery([
                                                                        'sort' => $field,
                                                                        'direction' => $direction,
                                                                    ]);
                                                                }
                                                            }

                                                            if (!function_exists('getSortIcon')) {
                                                                function getSortIcon(
                                                                    $field,
                                                                    $currentSort,
                                                                    $currentDirection,
                                                                ) {
                                                                    if ($currentSort !== $field) {
                                                                        return '<i class="fas fa-sort text-muted"></i>';
                                                                    }
                                                                    return $currentDirection === 'asc'
                                                                        ? '<i class="fas fa-sort-up text-primary"></i>'
                                                                        : '<i class="fas fa-sort-down text-primary"></i>';
                                                                }
                                                            }
                                                        @endphp
                                                        <th class="text-center">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input"
                                                                    id="selectAllComisiones">
                                                                <label class="custom-control-label"
                                                                    for="selectAllComisiones"></label>
                                                            </div>
                                                        </th>
                                                        <th class="text-center sortable">
                                                            <a href="{{ getSortUrl('fecha', $currentSort, $currentDirection) }}"
                                                                class="text-dark text-decoration-none">
                                                                Fecha Pago {!! getSortIcon('fecha', $currentSort, $currentDirection) !!}
                                                            </a>
                                                        </th>
                                                        <th class="text-center sortable">
                                                            <a href="{{ getSortUrl('pedido', $currentSort, $currentDirection) }}"
                                                                class="text-dark text-decoration-none">
                                                                ID Pedido {!! getSortIcon('pedido', $currentSort, $currentDirection) !!}
                                                            </a>
                                                        </th>
                                                        <th class="sortable">
                                                            <a href="{{ getSortUrl('vendedor', $currentSort, $currentDirection) }}"
                                                                class="text-dark text-decoration-none">
                                                                Vendedor {!! getSortIcon('vendedor', $currentSort, $currentDirection) !!}
                                                            </a>
                                                        </th>
                                                        <th class="text-center">Método Pago</th>
                                                        <th class="text-right sortable">
                                                            <a href="{{ getSortUrl('total', $currentSort, $currentDirection) }}"
                                                                class="text-dark text-decoration-none">
                                                                Total Comisión {!! getSortIcon('total', $currentSort, $currentDirection) !!}
                                                            </a>
                                                        </th>
                                                        <th class="text-center sortable">
                                                            <a href="{{ getSortUrl('estado', $currentSort, $currentDirection) }}"
                                                                class="text-dark text-decoration-none">
                                                                Estado {!! getSortIcon('estado', $currentSort, $currentDirection) !!}
                                                            </a>
                                                        </th>
                                                        <th class="text-center">Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($comisiones as $comision)
                                                        @php
                                                            $rowClass = '';
                                                            // Nueva lógica: si tiene_divisa = 1, marcar como divisa (prioridad sobre bolivares)
                                                            if ($comision->tiene_divisa == 1) {
                                                                $rowClass = 'row-highlight-divisa-efectivo';
                                                            }
                                                            // Usar el campo moneda_pago calculado
                                                            $metodoPago = trim($comision->moneda_pago ?? '');
                                                        @endphp
                                                        <tr class="{{ $rowClass }}" data-pedido-id="{{ $comision->pedido_id }}"
                                                            data-vendedor="{{ $comision->correo_vendedor }}">
                                                            <td class="text-center">
                                                                @if ($comision->estatus_comision == 'pendiente')
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox"
                                                                            class="custom-control-input check-comision"
                                                                            id="check_{{ $comision->pedido_id }}"
                                                                            data-id="{{ $comision->pedido_id }}"
                                                                            data-monto="{{ $comision->total_comision }}"
                                                                            data-vendedor="{{ $comision->correo_vendedor }}">
                                                                        <label class="custom-control-label"
                                                                            for="check_{{ $comision->pedido_id }}"></label>
                                                                    </div>
                                                                @endif
                                                            </td>
                                                            <td class="text-center">
                                                                <i class="far fa-calendar-alt text-primary mr-1"></i>
                                                                {{ \Carbon\Carbon::parse($comision->fecha_pedido)->format('d/m/Y') }}
                                                                @if($comision->tiene_divisa == 1)
                                                                <div class="mt-1">
                                                                    <span class="badge badge-success badge-pill" style="font-size: 0.75rem; background: linear-gradient(45deg, #28a745, #20c997); border: none; box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);">
                                                                        <i class="fas fa-dollar-sign mr-1"></i>DIVISA EFECTIVO
                                                                    </span>
                                                                </div>
                                                                @elseif($comision->tiene_bolivares == 1)
                                                                <div class="mt-1">
                                                                    <span class="badge badge-info badge-pill" style="font-size: 0.75rem; background: linear-gradient(45deg, #17a2b8, #138496); border: none; box-shadow: 0 2px 4px rgba(23, 162, 184, 0.3);">
                                                                        <i class="fas fa-bolivar-sign mr-1"></i>BOLIVARES
                                                                    </span>
                                                                </div>
                                                                @endif
                                                            </td>
                                                            <td class="text-center">
                                                                <span class="badge badge-info badge-pill">
                                                                    <i
                                                                        class="fas fa-receipt mr-1"></i>#{{ $comision->pedido_id }}
                                                                </span>
                                                                @if ($comision->facturas)
                                                                    <span class="badge badge-success badge-pill">
                                                                        <i class="fas fa-file-invoice mr-1"></i> Fact: {{ explode(',', $comision->facturas)[0] }}

                                                                    </span>
                                                                @endif
                                                                <br>
                                                                <small class="text-primary"><i
                                                                        class="fas fa-user-circle mr-1"></i>{{ $comision->descripcion_pedido }}</small>
                                                            </td>
                                                            <td>
                                                                <div>
                                                                    <div class="font-weight-bold">
                                                                        <i
                                                                            class="fas fa-user-circle mr-1"></i>{{ $comision->nombre_vendedor }}
                                                                    </div>
                                                                    <small
                                                                        class="text-muted">{{ $comision->correo_vendedor }}</small>
                                                                </div>
                                                            </td>
                                                            <td class="text-center">
                                                                <span class="badge badge-light border">
                                                                    {{ $metodoPago ?? '-' }}
                                                                </span>
                                                            </td>
                                                            <td class="text-right">
                                                                ${{ number_format($comision->total_comision, 2) }}
                                                                </span>
                                                                <input type="hidden" class="total-monto-hidden"
                                                                    value="{{ $comision->total_comision }}">
                                                                <br>
                                                                <small
                                                                    class="text-muted">{{ $comision->cantidad_productos }}
                                                                    producto(s)</small>
                                                            </td>
                                                            <td class="text-center">
                                                                @php
                                                                    $badgeClass =
                                                                        [
                                                                            'pendiente' => 'warning',
                                                                            'pagada' => 'success',
                                                                            'rechazada' => 'danger',
                                                                        ][$comision->estatus_comision] ?? 'secondary';
                                                                    $iconClass =
                                                                        [
                                                                            'pendiente' => 'fa-clock',
                                                                            'pagada' => 'fa-check-circle',
                                                                            'rechazada' => 'fa-times-circle',
                                                                        ][$comision->estatus_comision] ??
                                                                        'fa-question-circle';
                                                                @endphp
                                                                <span
                                                                    class="badge badge-{{ $badgeClass }} badge-lg px-3 py-2">
                                                                    <i class="fas {{ $iconClass }} mr-1"></i>
                                                                    {{ ucfirst($comision->estatus_comision) }}
                                                                </span>
                                                                @if ($comision->recibido)
                                                                    <div class="mt-1">
                                                                        <span class="badge badge-success shadow-sm"
                                                                            style="font-size: 0.8rem; border: 1px solid #fff;">
                                                                            <i class="fas fa-check-double mr-1"></i>
                                                                            RECIBIDO
                                                                        </span>
                                                                    </div>
                                                                @endif
                                                            </td>
                                                            <td class="text-center">
                                                                <div class="d-flex flex-column" style="gap: 0.5rem;">
                                                                    <!-- Primera fila: Aprobar y Rechazar -->
                                                                    <div class="d-flex justify-content-center" style="gap: 0.5rem;">
                                                                        @if ($comision->estatus_comision == 'pendiente')
                                                                            <button
                                                                                class="btn btn-sm btn-success btn-aprobar"
                                                                                data-pedido-id="{{ $comision->pedido_id }}"
                                                                                data-toggle="tooltip"
                                                                                title="Pagar comisión"
                                                                                style="min-width: 90px;">
                                                                                <i class="fas fa-check"></i> Pagar
                                                                            </button>
                                                                            <button
                                                                                class="btn btn-sm btn-danger btn-rechazar"
                                                                                data-pedido-id="{{ $comision->pedido_id }}"
                                                                                data-toggle="tooltip"
                                                                                title="Rechazar comisión"
                                                                                style="min-width: 90px;">
                                                                                <i class="fas fa-times"></i> Rechazar
                                                                            </button>
                                                                        @endif
                                                                        <!-- Botón para editar monto de comisión -->
                                                                        <button
                                                                            class="btn btn-sm btn-warning btn-editar-monto"
                                                                            data-pedido-id="{{ $comision->pedido_id }}"
                                                                            data-monto="{{ $comision->total_comision }}"
                                                                            data-porcentaje="{{ number_format((float)($comision->porcentaje_comision ?? 0), 2, '.', '') }}"
                                                                            data-moneda="{{ $comision->moneda_pago ?? '' }}"
                                                                            data-toggle="tooltip"
                                                                            title="Editar monto de comisión"
                                                                            style="min-width: 90px;">
                                                                            <i class="fas fa-pen"></i> Editar
                                                                        </button>
                                                                        @if($comision->estatus_comision == 'rechazada')
                                                                            <button
                                                                                class="btn btn-sm btn-success btn-aprobar"
                                                                                data-pedido-id="{{ $comision->pedido_id }}"
                                                                                data-toggle="tooltip"
                                                                                title="Aprobar comisión"
                                                                                style="min-width: 90px;">
                                                                                <i class="fas fa-check"></i> Aprobar
                                                                            </button>
                                                                        @endif
                                                                    </div>
                                                                    <!-- Segunda fila: Ver Pago y Ver Detalle -->
                                                                    <div class="d-flex justify-content-center"
                                                                        style="gap: 0.5rem;">

                                                                        <button class="btn btn-sm btn-info btn-ver-detalle"
                                                                            data-pedido-id="{{ $comision->pedido_id }}"
                                                                            data-toggle="tooltip"
                                                                            title="Ver detalle de comisiones"
                                                                            style="min-width: 90px;">
                                                                            <i class="fas fa-eye"></i> Detalle
                                                                        </button>

                                                                        @if ($comision->estatus_comision == 'pagada')
                                                                            <button
                                                                                class="btn btn-sm btn-success btn-ver-pago-comision"
                                                                                data-pedido-id="{{ $comision->pedido_id }}"
                                                                                data-toggle="tooltip"
                                                                                title="Ver comprobante de pago de comisión"
                                                                                style="min-width: 90px;">
                                                                                <i class="fas fa-file-invoice-dollar"></i>
                                                                                Ver Pago
                                                                            </button>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="7" class="text-center py-5">
                                                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                                                <p class="text-muted">No se encontraron comisiones con los
                                                                    filtros
                                                                    seleccionados</p>
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="p-3 border-top bg-light">
                                            {{ $comisiones->withQueryString()->links() }}
                                        </div>
                                    </div>

                                    <!-- Tab 2: Top 10 Productos -->
                                    <div class="tab-pane fade" id="productos" role="tabpanel">
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th class="text-center">#</th>
                                                        <th>Código</th>
                                                        <th>Producto</th>
                                                        <th class="text-center">Cantidad Vendida</th>
                                                        <th class="text-right">Total Comisión</th>
                                                        <th class="text-center">% Promedio</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($topProductos as $index => $producto)
                                                        <tr>
                                                            <td class="text-center">
                                                                <span
                                                                    class="badge badge-pill 
                                                                    @if ($index == 0) badge-warning
                                                                    @elseif($index == 1) badge-secondary
                                                                    @elseif($index == 2) badge-info
                                                                    @else badge-light @endif">
                                                                    {{ $index + 1 }}
                                                                </span>
                                                            </td>
                                                            <td><span
                                                                    class="badge badge-dark">{{ $producto->codigo_producto }}</span>
                                                            </td>
                                                            <td class="font-weight-bold">{{ $producto->nombre_producto }}
                                                            </td>
                                                            <td class="text-center">
                                                                <span
                                                                    class="badge badge-primary badge-pill">{{ number_format($producto->total_cantidad) }}</span>
                                                            </td>
                                                            <td class="text-right">
                                                                <span class="font-weight-bold text-success"
                                                                    style="font-size: 1.1rem;">
                                                                    ${{ number_format($producto->total_comision, 2) }}
                                                                </span>
                                                            </td>
                                                            <td class="text-center">
                                                                <span
                                                                    class="badge badge-info">{{ number_format($producto->promedio_porcentaje, 2) }}%</span>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="6" class="text-center text-muted py-4">
                                                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                                                No hay datos disponibles
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Tab 3: Top 10 Vendedores -->
                                    <div class="tab-pane fade" id="vendedores" role="tabpanel">
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th class="text-center">#</th>
                                                        <th>Vendedor</th>
                                                        <th class="text-center">Total Pedidos</th>
                                                        <th class="text-center">Productos Distintos</th>
                                                        <th class="text-right">Total Comisión</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($topVendedores as $index => $vendedor)
                                                        <tr>
                                                            <td class="text-center">
                                                                <span
                                                                    class="badge badge-pill 
                                                                    @if ($index == 0) badge-warning
                                                                    @elseif($index == 1) badge-secondary
                                                                    @elseif($index == 2) badge-info
                                                                    @else badge-light @endif">
                                                                    {{ $index + 1 }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <div>
                                                                    <div class="font-weight-bold">
                                                                        <i
                                                                            class="fas fa-user-circle mr-1"></i>{{ $vendedor->nombre_vendedor }}
                                                                    </div>
                                                                    <small
                                                                        class="text-muted">{{ $vendedor->correo_vendedor }}</small>
                                                                </div>
                                                            </td>
                                                            <td class="text-center">
                                                                <span
                                                                    class="badge badge-primary badge-pill">{{ number_format($vendedor->total_pedidos) }}</span>
                                                            </td>
                                                            <td class="text-center">
                                                                <span
                                                                    class="badge badge-info badge-pill">{{ number_format($vendedor->total_productos) }}</span>
                                                            </td>
                                                            <td class="text-right">
                                                                <span class="font-weight-bold text-success"
                                                                    style="font-size: 1.1rem;">
                                                                    ${{ number_format($vendedor->total_comision, 2) }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="5" class="text-center text-muted py-4">
                                                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                                                No hay datos disponibles
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Barra Flotante de Resumen (Bulk Payment) -->
            <div id="bulkPaymentBar" class="bulk-payment-bar shadow-lg" style="display: none;">
                <div class="container-fluid">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center text-white">
                                <div class="bg-white text-primary rounded-circle p-2 mr-3 pulse-light">
                                    <i class="fas fa-hand-holding-usd fa-lg"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 font-weight-bold">Total a Pagar Seleccionado</h5>
                                    <div class="d-flex align-items-center">
                                        <span class="badge badge-light mr-2" id="selectedCount">0</span>
                                        <span class="small opacity-75">registros marcados de un mismo vendedor</span>
                                    </div>
                                </div>
                                <div class="ml-auto text-right mr-4">
                                    <div class="h3 mb-0 font-weight-bold" id="selectedTotalUSD">$0.00</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-right">
                            <button class="btn btn-light btn-lg px-5 font-weight-bold text-primary shadow-sm hov-scale"
                                id="btnPagarSeleccionados">
                                <i class="fas fa-check-circle mr-2"></i>PAGAR SELECCIONADOS
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal para Ver Detalle -->
            <div class="modal fade" id="modalDetalle" tabindex="-1" role="dialog" aria-labelledby="modalDetalleLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-gradient-primary text-white border-0">
                            <h5 class="modal-title font-weight-bold" id="modalDetalleLabel">
                                <i class="fas fa-list-alt mr-2"></i>Detalle de Comisiones
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Código</th>
                                            <th>Descripción</th>
                                            <th class="text-center">Cantidad</th>
                                            <th class="text-right">Monto Comisión</th>
                                            <th class="text-center">% Comisión</th>
                                        </tr>
                                    </thead>
                                    <tbody id="detalleComisionesBody">
                                        <!-- Se llenará dinámicamente con JavaScript -->
                                    </tbody>
                                    <tfoot class="bg-light font-weight-bold">
                                        <tr>
                                            <td colspan="3" class="text-right">Total:</td>
                                            <td class="text-right" id="totalComisionDetalle">$0.00</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer border-0 bg-light">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                <i class="fas fa-times mr-1"></i>Cerrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal para Ver Pago -->
            <div class="modal fade" id="modalPago" tabindex="-1" role="dialog" aria-labelledby="modalPagoLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-gradient-primary text-white border-0">
                            <h5 class="modal-title font-weight-bold" id="modalPagoLabel">
                                <i class="fas fa-file-invoice-dollar mr-2"></i>Detalle del Pago
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="info-box bg-light p-3 rounded">
                                        <div class="text-muted small mb-1">ID Pago</div>
                                        <div class="font-weight-bold" id="pagoId">-</div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="info-box bg-light p-3 rounded">
                                        <div class="text-muted small mb-1">ID Pedido</div>
                                        <div class="font-weight-bold" id="pagoPedidoId">-</div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="info-box bg-light p-3 rounded">
                                        <div class="text-muted small mb-1">Fecha</div>
                                        <div class="font-weight-bold" id="pagoFecha">-</div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="info-box bg-light p-3 rounded">
                                        <div class="text-muted small mb-1">Monto</div>
                                        <div class="font-weight-bold text-primary" id="pagoMonto">$0.00</div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <h6 class="font-weight-bold mb-3"><i class="fas fa-calculator mr-2"></i>Desglose Financiero
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-borderless">
                                    <tbody>
                                        <tr>
                                            <td class="text-muted">Base Imponible:</td>
                                            <td class="text-right font-weight-bold" id="pagoBase">$0.00</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Descuento:</td>
                                            <td class="text-right font-weight-bold text-danger" id="pagoDescuento">$0.00
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">IVA:</td>
                                            <td class="text-right font-weight-bold" id="pagoIva">$0.00</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Retención:</td>
                                            <td class="text-right font-weight-bold text-warning" id="pagoRetencion">$0.00
                                            </td>
                                        </tr>
                                        <tr class="border-top">
                                            <td class="text-muted font-weight-bold">Total:</td>
                                            <td class="text-right font-weight-bold text-success"
                                                style="font-size: 1.2rem;" id="pagoTotal">$0.00</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer border-0 bg-light">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                <i class="fas fa-times mr-1"></i>Cerrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal para Pagar Comisión -->
            <div class="modal fade" id="modalPagarComision" tabindex="-1" role="dialog"
                aria-labelledby="modalPagarComisionLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-gradient-navy text-white border-0">
                            <h5 class="modal-title font-weight-bold" id="modalPagarComisionLabel">
                                <i class="fas fa-hand-holding-usd mr-2"></i>Registrar Pago de Comisión
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form id="formPagarComision">
                            @csrf
                            <input type="hidden" name="pago_id" id="modal_pago_id">
                            <input type="hidden" name="correo_vendedor" id="modal_correo_vendedor">
                            <input type="hidden" name="saldo_aplicado" id="modal_saldo_aplicado" value="0">
                            <div class="modal-body p-4 bg-light">

                                {{-- Saldo del vendedor --}}
                                <div id="saldo-vendedor-section" class="mb-3">
                                    <div id="saldo-loading" class="text-center py-2">
                                        <div class="spinner-border spinner-border-sm text-primary mr-2"></div>
                                        <small class="text-muted">Cargando saldo del vendedor...</small>
                                    </div>
                                    <div id="saldo-info" style="display:none;"></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label class="font-weight-bold text-navy"><i
                                                    class="fas fa-university mr-1"></i> Destino del Pago</label>
                                            <select name="pagos_destino_id" id="pagos_destino_id"
                                                class="form-control select2-modal" style="width: 100%;" required>
                                                <option value="">Seleccione un destino</option>
                                                @if(isset($pagoDestinos))
                                                    @foreach($pagoDestinos as $destino)
                                                        <option value="{{ $destino->id }}">{{ $destino->nombre }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label class="font-weight-bold text-navy"><i
                                                    class="far fa-calendar-alt mr-1"></i> Fecha de Pago</label>
                                            <input type="date" name="fecha_pago" class="form-control"
                                                value="{{ date('Y-m-d') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="form-group">
                                            <label class="font-weight-bold text-navy"><i
                                                    class="fas fa-money-bill-wave mr-1"></i> Monto (Bs)</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-white border-right-0"><i
                                                            class="text-success small fas fa-vnv"></i></span>
                                                </div>
                                                <input type="number" step="0.01" name="monto_bs" id="monto_bs"
                                                    class="form-control border-left-0" placeholder="0.00" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="form-group">
                                            <label class="font-weight-bold text-navy"><i
                                                    class="fas fa-dollar-sign mr-1"></i> Monto ($)</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-white border-right-0"><i
                                                            class="text-primary small fas fa-dollar-sign"></i></span>
                                                </div>
                                                <input type="number" step="0.01" name="monto_divisa"
                                                    id="monto_divisa" class="form-control border-left-0"
                                                    placeholder="0.00" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="form-group">
                                            <label class="font-weight-bold text-navy"><i
                                                    class="fas fa-exchange-alt mr-1"></i> Tasa</label>
                                            <input type="number" step="0.0001" name="tasa" id="tasa_pago"
                                                class="form-control" placeholder="0.0000" value="{{ $tasaDia }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label class="font-weight-bold text-navy"><i
                                                    class="fas fa-credit-card mr-1"></i> Forma de Pago</label>
                                            <select name="forma_pago" class="form-control" required>
                                                <option value="Divisa">Divisa</option>
                                                <option value="Bolivares">Bolivares</option>
                                                <option value="Otro">Otro</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label class="font-weight-bold text-navy"><i class="fas fa-hashtag mr-1"></i>
                                                Nro. Referencia</label>
                                            <input type="text" name="numero_referencia" class="form-control"
                                                placeholder="Ej: 123456">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="font-weight-bold text-navy"><i
                                                    class="fas fa-comment-dots mr-1"></i> Observaciones</label>
                                            <textarea name="observaciones" class="form-control" rows="2" placeholder="Notas adicionales sobre el pago..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-0 bg-white">
                                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                                    <i class="fas fa-times mr-1"></i>Cancelar
                                </button>
                                <button type="submit" class="btn btn-primary shadow-sm px-4">
                                    <i class="fas fa-save mr-1"></i>Guardar y Aprobar Pago
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal para Ver Pago de Comisión (Diseño Premium) -->
            <div class="modal fade modal-premium" id="modalVerPagoComision" tabindex="-1" role="dialog"
                aria-labelledby="modalVerPagoComisionLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content border-0 shadow-2xl">
                        <!-- Sello de Recibido (Sólo visible cuando está confirmado) -->
                        <div id="ver_pago_recibido_wrapper" class="recibido-stamp" style="display: none;">
                            <i class="fas fa-check-double mr-1"></i> Confirmado
                        </div>

                        <div class="modal-header bg-gradient-navy text-white border-0">
                            <div class="d-flex align-items-center">
                                <div class="bg-white-10 p-2 rounded-circle mr-3">
                                    <i class="fas fa-file-invoice-dollar fa-lg"></i>
                                </div>
                                <div>
                                    <h5 class="modal-title font-weight-bold mb-0" id="modalVerPagoComisionLabel">
                                        Comprobante de Comisión
                                    </h5>
                                    <small class="text-white-50">Consulta detallada del desembolso</small>
                                </div>
                            </div>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                                <span aria-hidden="true" style="font-size: 1.5rem;">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body p-4 bg-white">
                            <!-- Header de Montos -->
                            <div class="payment-amount-header text-center" style="background-color: #3275b8;">
                                <div class="row align-items-center">
                                    <div class="col-md-5">
                                        <div class="detail-label mb-0 text-white">Monto en Divisas</div>
                                        <div class="h2 font-weight-bold text-white mb-0" id="ver_pago_monto_divisa">
                                            $0.00</div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="bg-light rounded-pill py-1 px-2 text-sm text-muted border">
                                            <i class="fas fa-exchange-alt"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="detail-label mb-0 text-white">Equivalente en Bolívares</div>
                                        <div class="h2 font-weight-bold text-white mb-0" id="ver_pago_monto_bs">0.00 Bs
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Detalle en Grid -->
                            <div class="row g-3">
                                <div class="col-md-4 mb-3">
                                    <div class="payment-detail-item">
                                        <div class="detail-label">Fecha de Operación</div>
                                        <div class="detail-value text-navy" id="ver_pago_fecha">-</div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="payment-detail-item">
                                        <div class="detail-label">Tasa de Cambio</div>
                                        <div class="detail-value" id="ver_pago_tasa">0.0000</div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="payment-detail-item border-left-info"
                                        style="border-left-width: 3px !important;">
                                        <div class="detail-label">Nro. Referencia</div>
                                        <div class="detail-value text-navy" id="ver_pago_referencia">-</div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="payment-detail-item">
                                        <div class="detail-label">Banco / Destino</div>
                                        <div class="detail-value" id="ver_pago_destino">-</div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="payment-detail-item">
                                        <div class="detail-label">Método de Pago</div>
                                        <div class="detail-value" id="ver_pago_forma">-</div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="payment-detail-item bg-light border-0">
                                        <div class="detail-label"><i class="fas fa-sticky-note mr-1"></i> Notas
                                            Administrativas</div>
                                        <div class="detail-value font-italic text-muted small"
                                            id="ver_pago_observaciones">Sin observaciones adicionales</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer bg-light border-0 py-3">
                            <input type="hidden" id="ver_pago_id_oculto">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <p class="text-muted small mb-0"><i class="fas fa-shield-alt mr-1"></i> Documento
                                    verificado por el sistema</p>
                                <div>
                                    <button type="button" class="btn btn-success px-4 font-weight-bold shadow-sm"
                                        id="btnConfirmarRecibido" style="display: none; border-radius: 10px;">
                                        <i class="fas fa-check-double mr-1"></i> Marcar como RECIBIDO
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary px-4 hov-danger"
                                        data-dismiss="modal" style="border-radius: 10px;">
                                        Cerrar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endsection

        @section('styles')
            <!-- Select2 -->
            <link rel="stylesheet" href="{{ asset('theme/plugins/select2/css/select2.min.css') }}">
            <link rel="stylesheet"
                href="{{ asset('theme/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
            <!-- Toastr -->
            <link rel="stylesheet" href="{{ asset('theme/plugins/toastr/toastr.min.css') }}">
            <style>
                .bg-lightblue {
                    background-color: #3c8dbc !important;
                }

                /* Estilo elegante para comisiones en DIVISA EFECTIVO */
                .row-highlight-divisa-efectivo {
                    background: linear-gradient(135deg, #e8f5e8 0%, #d1fae5 100%) !important;
                    border-left: 4px solid #28a745;
                    box-shadow: 0 1px 3px rgba(40, 167, 69, 0.2);
                    position: relative;
                    transition: all 0.3s ease;
                }

                .row-highlight-divisa-efectivo2::before {
                    content: '';
                    position: absolute;
                    left: 0;
                    top: 0;
                    bottom: 0;
                    width: 4px;
                    background: linear-gradient(180deg, #28a745 0%, #20c997 100%);
                    animation: shimmer 2s infinite;
                }

                .row-highlight-divisa-efectivo:hover {
                    background: linear-gradient(135deg, #d1fae5 0%, #e8f5e8 100%) !important;
                    transform: translateY(-1px);
                    box-shadow: 0 2px 6px rgba(40, 167, 69, 0.3);
                }

                .row-highlight-divisa-efectivo td {
                    background: transparent !important;
                    border-color: rgba(40, 167, 69, 0.1) !important;
                }

                @keyframes shimmer {
                    0% { opacity: 0.3; }
                    50% { opacity: 0.7; }
                    100% { opacity: 0.3; }
                }

                .bg-gradient-primary {
                    background: linear-gradient(45deg, #3c8dbc, #00c6ff) !important;
                }

                .bg-gradient-navy {
                    background: linear-gradient(45deg, #001f3f, #003366) !important;
                }

                .btn-navy {
                    background-color: #001f3f;
                    color: white;
                }

                .btn-navy:hover {
                    background-color: #003366;
                    color: white;
                }

                .text-navy {
                    color: #001f3f;
                }

                .card {
                    border: none;
                    border-radius: 8px;
                    overflow: hidden;
                    box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
                    transition: transform 0.2s, box-shadow 0.2s;
                }

                .card:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
                }

                .card-header {
                    border-bottom: none;
                    padding: 1rem 1.25rem;
                }

                .card-title {
                    font-weight: 600;
                    font-size: 1.1rem;
                }

                .table th {
                    border-top: none;
                    font-weight: 600;
                    text-transform: uppercase;
                    font-size: 0.75rem;
                    letter-spacing: 0.5px;
                }

                .badge {
                    font-weight: 500;
                    padding: 0.35em 0.65em;
                    font-size: 0.75em;
                }

                .badge-lg {
                    font-size: 0.85em;
                    padding: 0.5em 0.8em;
                }

                .symbol {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    vertical-align: middle;
                }

                .symbol.symbol-40>.symbol-label {
                    width: 40px;
                    height: 40px;
                }

                .symbol.symbol-circle .symbol-label {
                    border-radius: 50%;
                }

                .pagination {
                    margin-bottom: 0;
                }

                .page-item.active .page-link {
                    background-color: #3c8dbc;
                    border-color: #3c8dbc;
                }

                .page-link {
                    color: #3c8dbc;
                }

                .form-control:focus,
                .select2-container--default .select2-selection--single {
                    border-color: #3c8dbc;
                }

                .select2-container--default .select2-selection--single {
                    height: calc(2.25rem + 2px);
                    padding: 0.375rem 0.75rem;
                }

                .select2-container--default .select2-selection--single .select2-selection__arrow {
                    height: 36px;
                }

                .modal-content {
                    border-radius: 12px;
                }

                .btn-group .btn {
                    margin: 0 2px;
                }

                .table-hover tbody tr:hover {
                    background-color: rgba(60, 141, 188, 0.05);
                }

                /* Sortable column styles */
                .sortable a {
                    cursor: pointer;
                    user-select: none;
                    display: inline-block;
                    width: 100%;
                }

                .sortable a:hover {
                    color: #3c8dbc !important;
                }

                .sortable i {
                    font-size: 0.8em;
                    margin-left: 5px;
                }

                /* Tab styles */
                .nav-tabs .nav-link {
                    color: #6c757d;
                    font-weight: 500;
                    border: none;
                    border-bottom: 3px solid transparent;
                    transition: all 0.3s ease;
                }

                .nav-tabs .nav-link:hover {
                    color: #3c8dbc;
                    border-bottom-color: #3c8dbc;
                    background-color: rgba(60, 141, 188, 0.05);
                }

                .nav-tabs .nav-link.active {
                    color: #3c8dbc;
                    border-bottom-color: #3c8dbc;
                    background-color: transparent;
                    font-weight: 600;
                }

                .tab-content {
                    padding: 0;
                }

                /* Gap spacing for button rows */
                .gap-2>*+* {
                    margin-top: 0.5rem;
                }

                /* Spacing between buttons in groups */
                .btn-group .btn {
                    margin-right: 0.25rem;
                }

                .btn-group .btn:last-child {
                    margin-right: 0;
                }

                /* Estilos para Modales Premium */
                .modal-premium .modal-content {
                    border-radius: 20px;
                    overflow: hidden;
                    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
                }

                .modal-premium .modal-header {
                    padding: 1.5rem;
                }

                .payment-amount-header {
                    background: rgba(0, 123, 255, 0.05);
                    border-radius: 15px;
                    padding: 1.5rem;
                    margin-bottom: 2rem;
                    border: 1px solid rgba(0, 123, 255, 0.1);
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
                    border: 3px solid #28a745;
                    color: #28a745;
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

                /* Floating Bulk Payment Bar */
                .bulk-payment-bar {
                    position: fixed;
                    bottom: 20px;
                    left: 50%;
                    transform: translateX(-50%);
                    width: 90%;
                    max-width: 1000px;
                    background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
                    padding: 1.5rem;
                    border-radius: 20px;
                    z-index: 1050;
                    color: white;
                    border: 2px solid rgba(255, 255, 255, 0.1);
                    animation: slideUp 0.4s ease-out;
                }

                @keyframes slideUp {
                    from {
                        transform: translate(-50%, 100%);
                        opacity: 0;
                    }

                    to {
                        transform: translate(-50%, 0);
                        opacity: 1;
                    }
                }

                .pulse-light {
                    animation: pulse-light 2s infinite;
                }

                @keyframes pulse-light {
                    0% {
                        box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4);
                    }

                    70% {
                        box-shadow: 0 0 0 10px rgba(255, 255, 255, 0);
                    }

                    100% {
                        box-shadow: 0 0 0 0 rgba(255, 255, 255, 0);
                    }
                }

                .hov-scale {
                    transition: transform 0.2s;
                }

                .hov-scale:hover {
                    transform: scale(1.05);
                }
            </style>
        @endsection

        @section('scripts')
            <!-- Select2 -->
            <script src="{{ asset('theme/plugins/select2/js/select2.full.min.js') }}"></script>
            <!-- Toastr -->
            <script src="{{ asset('theme/plugins/toastr/toastr.min.js') }}"></script>
            <!-- Custom Scripts -->
            <script>
                $(document).ready(function() {
                    // Check if jQuery and Bootstrap tooltip are available
                    if (typeof $ !== 'undefined' && typeof $.fn.tooltip !== 'undefined') {
                        // Inicializar tooltips
                        $('[data-toggle="tooltip"]').tooltip({
                            trigger: 'hover',
                            placement: 'top'
                        });
                    } else {
                        console.warn('Tooltip function not available. Check Bootstrap JS loading.');
                    }

                    // Inicializar select2
                    $('.select2').select2({
                        theme: 'bootstrap4',
                        placeholder: 'Seleccione un vendedor',
                        allowClear: true,
                        width: '100%'
                    });

                    // Inicializar select2 en modal
                    $('.select2-modal').select2({
                        theme: 'bootstrap4',
                        dropdownParent: $('#modalPagarComision'),
                        width: '100%'
                    });

                    // Auto-cálculo de montos
                    function calcularMontos(origen) {
                        const montoBs = parseFloat($('#monto_bs').val()) || 0;
                        const montoDivisa = parseFloat($('#monto_divisa').val()) || 0;
                        const tasa = parseFloat($('#tasa_pago').val()) || 0;

                        // Prevent division by zero
                        if (tasa <= 0) {
                            console.warn('Tasa is zero or negative, skipping calculation');
                            return;
                        }

                        if (origen === 'bs') {
                            $('#monto_divisa').val((montoBs / tasa).toFixed(2));
                        } else if (origen === 'divisa') {
                            $('#monto_bs').val((montoDivisa * tasa).toFixed(2));
                        } else if (origen === 'tasa') {
                            if (montoDivisa > 0) {
                                $('#monto_bs').val((montoDivisa * tasa).toFixed(2));
                            } else if (montoBs > 0) {
                                $('#monto_divisa').val((montoBs / tasa).toFixed(2));
                            }
                        }
                    }

                    // Calcular automáticamente cuando se muestra la modal (en caso de que haya tasa cargada)
                    $('#modalPagarComision').on('shown.bs.modal', function() {
                        const montoDivisa = parseFloat($('#monto_divisa').val()) || 0;
                        let tasa = parseFloat($('#tasa_pago').val()) || 0;
                        
                        // If tasa is 0, try to get it from the default value
                        if (tasa <= 0) {
                            const defaultTasa = parseFloat('{{ $tasaDia }}') || 0;
                            if (defaultTasa > 0) {
                                $('#tasa_pago').val(defaultTasa);
                                tasa = defaultTasa;
                                console.log('Using default tasa:', defaultTasa);
                            }
                        }
                        
                        console.log('Modal shown - montoDivisa:', montoDivisa, 'tasa:', tasa);
                        
                        // Prevent division by zero
                        if (montoDivisa > 0 && tasa > 0) {
                            $('#monto_bs').val((montoDivisa * tasa).toFixed(2));
                        } else if (tasa <= 0) {
                            console.warn('Tasa is zero or negative in modal shown, skipping calculation');
                        }
                    });

                    $('#monto_bs').on('input', () => calcularMontos('bs'));
                    $('#monto_divisa').on('input', () => calcularMontos('divisa'));
                    $('#tasa_pago').on('input', () => calcularMontos('tasa'));

                    // Las opciones de destino ya están pre-renderizadas en Blade

                    // Manejar clic en botón Aprobar (ahora muestra el modal de pago)
                    $(document).on('click', '.btn-aprobar', function(event) {
                        event.preventDefault();
                        const pedidoId   = $(this).data('pedido-id');
                        const totalMonto = $(this).closest('tr').find('.total-monto-hidden').val();
                        const correo    = $(this).closest('tr').data('vendedor');

                        $('#formPagarComision')[0].reset();
                        $('#pagos_destino_id').val('').trigger('change');
                        $('#modal_pago_id').val(pedidoId);
                        $('#modal_correo_vendedor').val(correo || '');
                        $('#modal_saldo_aplicado').val(0);
                        $('#monto_divisa').val(totalMonto);
                        calcularMontos('divisa');

                        // Cargar saldo del vendedor
                        $('#saldo-loading').show();
                        $('#saldo-info').hide().html('');
                        if (correo) {
                            $.get('{{ url("comisiones/saldo-vendedor") }}/' + encodeURIComponent(correo), function(res) {
                                $('#saldo-loading').hide();
                                const saldo = parseFloat(res.saldo) || 0;
                                let html = '';
                                if (saldo < -0.01) {
                                    // Admin tiene crédito (sobrepagó antes)
                                    html = `
                                        <div class="alert alert-warning mb-2 py-2 px-3">
                                            <i class="fas fa-piggy-bank mr-1"></i>
                                            <strong>Admin tiene crédito de $${Math.abs(saldo).toFixed(2)}</strong>
                                            (sobrepago anterior)
                                            <div class="mt-2">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" id="chk-aplicar-saldo">
                                                    <label class="custom-control-label" for="chk-aplicar-saldo">
                                                        Aplicar este crédito al pago actual
                                                    </label>
                                                </div>
                                                <div id="saldo-aplicar-wrap" style="display:none;" class="mt-2">
                                                    <div class="input-group input-group-sm" style="max-width:220px;">
                                                        <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                                        <input type="number" step="0.01" min="0"
                                                               max="${Math.abs(saldo).toFixed(2)}"
                                                               id="input-saldo-aplicar"
                                                               class="form-control"
                                                               value="${Math.abs(saldo).toFixed(2)}"
                                                               placeholder="Monto a aplicar">
                                                    </div>
                                                    <small class="text-muted">Máx: $${ Math.abs(saldo).toFixed(2) }</small>
                                                </div>
                                            </div>
                                        </div>`;
                                } else if (saldo > 0.01) {
                                    // Admin le debe al vendedor
                                    html = `
                                        <div class="alert alert-info mb-2 py-2 px-3">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Este vendedor tiene <strong>$${saldo.toFixed(2)} pendientes</strong> de pagos anteriores (pago parcial previo).
                                        </div>`;
                                } else {
                                    html = `<div class="alert alert-success mb-2 py-2 px-3 small">
                                        <i class="fas fa-check-circle mr-1"></i>Saldo en cero — sin deuda previa.</div>`;
                                }
                                $('#saldo-info').html(html).show();

                                // Checkbox aplicar saldo
                                $(document).off('change','#chk-aplicar-saldo').on('change','#chk-aplicar-saldo', function(){
                                    if ($(this).is(':checked')) {
                                        $('#saldo-aplicar-wrap').show();
                                        const aplicar = parseFloat($('#input-saldo-aplicar').val()) || 0;
                                        $('#modal_saldo_aplicado').val(aplicar.toFixed(2));
                                        // Ajustar monto sugerido
                                        const base = parseFloat($('#monto_divisa').val()) || 0;
                                        $('#monto_divisa').val(Math.max(0, base - aplicar).toFixed(2));
                                        calcularMontos('divisa');
                                    } else {
                                        $('#saldo-aplicar-wrap').hide();
                                        $('#modal_saldo_aplicado').val(0);
                                        $('#monto_divisa').val(parseFloat(totalMonto).toFixed(2));
                                        calcularMontos('divisa');
                                    }
                                });
                                $(document).off('input','#input-saldo-aplicar').on('input','#input-saldo-aplicar', function(){
                                    const aplicar = parseFloat($(this).val()) || 0;
                                    $('#modal_saldo_aplicado').val(aplicar.toFixed(2));
                                    const base = parseFloat(totalMonto) || 0;
                                    $('#monto_divisa').val(Math.max(0, base - aplicar).toFixed(2));
                                    calcularMontos('divisa');
                                });
                            }).fail(function(){
                                $('#saldo-loading').hide();
                                $('#saldo-info').html('<div class="small text-muted">No se pudo cargar el saldo.</div>').show();
                            });
                        } else {
                            $('#saldo-loading').hide();
                        }

                        $('#modalPagarComision').modal('show');
                    });

                    // Manejar envío del formulario de pago
                    $('#formPagarComision').on('submit', function(e) {
                        e.preventDefault();

                        const pagoId = $('#modal_pago_id').val();
                        if (!pagoId) {
                            toastr.error('Error: no se pudo identificar el pedido.');
                            return false;
                        }

                        const destinoId = $('#pagos_destino_id').val();
                        if (!destinoId) {
                            toastr.error('Por favor seleccione un destino del pago.');
                            return false;
                        }

                        const formData = $(this).serializeArray();
                        const url = '{{ url('comisiones') }}/' + pagoId + '/aprobar';

                        const submitBtn = $(this).find('button[type="submit"]');
                        submitBtn.prop('disabled', true).html(
                            '<i class="fas fa-spinner fa-spin mr-1"></i> Procesando...');

                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: formData,
                            success: function(response) {
                                if (response.success) {
                                    toastr.success(response.message);
                                    $('#modalPagarComision').modal('hide');
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 1000);
                                } else {
                                    toastr.error(response.message || 'Error al procesar el pago');
                                    submitBtn.prop('disabled', false).html(
                                        '<i class="fas fa-save mr-1"></i>Guardar y Aprobar Pago');
                                }
                            },
                            error: function(xhr) {
                                let errorMessage = 'Error al conectar con el servidor';
                                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                                    errorMessage = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }
                                toastr.error(errorMessage);
                                submitBtn.prop('disabled', false).html(
                                    '<i class="fas fa-save mr-1"></i>Guardar y Aprobar Pago');
                            }
                        });
                    });

                    // --- Lógica de Selección Masiva ---
                    let selectedComisiones = [];

                    function actualizarBarraBulk() {
                        const total = selectedComisiones.reduce((sum, item) => sum + item.monto, 0);
                        $('#selectedCount').text(selectedComisiones.length);
                        $('#selectedTotalUSD').text('$' + total.toLocaleString('en-US', {
                            minimumFractionDigits: 2
                        }));

                        if (selectedComisiones.length > 0) {
                            $('#bulkPaymentBar').fadeIn();
                        } else {
                            $('#bulkPaymentBar').fadeOut();
                        }
                    }

                    $(document).on('change', '.check-comision', function() {
                        const id = $(this).data('id');
                        const monto = parseFloat($(this).data('monto'));
                        const vendedor = $(this).data('vendedor');

                        if (this.checked) {
                            // Validar que sea del mismo vendedor si ya hay seleccionados
                            if (selectedComisiones.length > 0 && selectedComisiones[0].vendedor !== vendedor) {
                                toastr.warning(
                                    'Solo puedes seleccionar comisiones de un mismo vendedor para pagos en lote.'
                                    );
                                this.checked = false;
                                return;
                            }
                            selectedComisiones.push({
                                id,
                                monto,
                                vendedor
                            });
                        } else {
                            selectedComisiones = selectedComisiones.filter(item => item.id !== id);
                        }
                        actualizarBarraBulk();
                    });

                    $('#selectAllComisiones').on('change', function() {
                        const isChecked = this.checked;
                        const checkboxes = $('.check-comision');

                        if (isChecked) {
                            let firstVendedor = null;
                            let countPossible = 0;

                            checkboxes.each(function() {
                                const v = $(this).data('vendedor');
                                if (firstVendedor === null) {
                                    firstVendedor = v;
                                }

                                if (v === firstVendedor) {
                                    $(this).prop('checked', true);
                                    const id = $(this).data('id');
                                    const monto = parseFloat($(this).data('monto'));
                                    if (!selectedComisiones.find(item => item.id === id)) {
                                        selectedComisiones.push({
                                            id,
                                            monto,
                                            vendedor: v
                                        });
                                    }
                                    countPossible++;
                                }
                            });

                            if (countPossible < checkboxes.length) {
                                toastr.info(
                                    'Se han seleccionado solo los registros del primer vendedor visible.');
                            }
                        } else {
                            checkboxes.prop('checked', false);
                            selectedComisiones = [];
                        }
                        actualizarBarraBulk();
                    });

                    $('#btnPagarSeleccionados').on('click', function() {
                        if (selectedComisiones.length === 0) return;

                        const ids        = selectedComisiones.map(item => item.id);
                        const totalMonto = selectedComisiones.reduce((sum, item) => sum + item.monto, 0);
                        const correo     = selectedComisiones[0].vendedor || '';

                        // Configurar modal para pago masivo
                        $('#formPagarComision')[0].reset();
                        $('#pagos_destino_id').val('').trigger('change');
                        $('#modal_pago_id').val(ids.join(','));
                        $('#modal_correo_vendedor').val(correo);
                        $('#modal_saldo_aplicado').val(0);
                        $('#monto_divisa').val(totalMonto.toFixed(2));
                        calcularMontos('divisa');

                        $('#modalPagarComisionLabel').html(
                            '<i class="fas fa-hand-holding-usd mr-2"></i>Registrar Pago de Comisiones en Lote (' +
                            ids.length + ')');

                        // Cargar saldo del vendedor (mismo bloque que btn-aprobar)
                        $('#saldo-loading').show();
                        $('#saldo-info').hide().html('');
                        if (correo) {
                            $.get('{{ url("comisiones/saldo-vendedor") }}/' + encodeURIComponent(correo), function(res) {
                                $('#saldo-loading').hide();
                                const saldo = parseFloat(res.saldo) || 0;
                                let html = '';
                                if (saldo < -0.01) {
                                    html = `
                                        <div class="alert alert-warning mb-2 py-2 px-3">
                                            <i class="fas fa-piggy-bank mr-1"></i>
                                            <strong>Admin tiene crédito de $${Math.abs(saldo).toFixed(2)}</strong>
                                            (sobrepago anterior)
                                            <div class="mt-2">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" id="chk-aplicar-saldo">
                                                    <label class="custom-control-label" for="chk-aplicar-saldo">
                                                        Aplicar este crédito al pago actual
                                                    </label>
                                                </div>
                                                <div id="saldo-aplicar-wrap" style="display:none;" class="mt-2">
                                                    <div class="input-group input-group-sm" style="max-width:220px;">
                                                        <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                                        <input type="number" step="0.01" min="0"
                                                               max="${Math.abs(saldo).toFixed(2)}"
                                                               id="input-saldo-aplicar"
                                                               class="form-control"
                                                               value="${Math.abs(saldo).toFixed(2)}"
                                                               placeholder="Monto a aplicar">
                                                    </div>
                                                    <small class="text-muted">Máx: $${Math.abs(saldo).toFixed(2)}</small>
                                                </div>
                                            </div>
                                        </div>`;
                                } else if (saldo > 0.01) {
                                    html = `
                                        <div class="alert alert-info mb-2 py-2 px-3">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Este vendedor tiene <strong>$${saldo.toFixed(2)} pendientes</strong> de pagos anteriores.
                                        </div>`;
                                } else {
                                    html = `<div class="alert alert-success mb-2 py-2 px-3 small">
                                        <i class="fas fa-check-circle mr-1"></i>Saldo en cero — sin deuda previa.</div>`;
                                }
                                $('#saldo-info').html(html).show();

                                // Handlers para aplicar crédito (lote)
                                $(document).off('change','#chk-aplicar-saldo').on('change','#chk-aplicar-saldo', function(){
                                    if ($(this).is(':checked')) {
                                        $('#saldo-aplicar-wrap').show();
                                        const aplicar = parseFloat($('#input-saldo-aplicar').val()) || 0;
                                        $('#modal_saldo_aplicado').val(aplicar.toFixed(2));
                                        const base = parseFloat(totalMonto) || 0;
                                        $('#monto_divisa').val(Math.max(0, base - aplicar).toFixed(2));
                                        calcularMontos('divisa');
                                    } else {
                                        $('#saldo-aplicar-wrap').hide();
                                        $('#modal_saldo_aplicado').val(0);
                                        $('#monto_divisa').val(parseFloat(totalMonto).toFixed(2));
                                        calcularMontos('divisa');
                                    }
                                });
                                $(document).off('input','#input-saldo-aplicar').on('input','#input-saldo-aplicar', function(){
                                    const aplicar = parseFloat($(this).val()) || 0;
                                    $('#modal_saldo_aplicado').val(aplicar.toFixed(2));
                                    const base = parseFloat(totalMonto) || 0;
                                    $('#monto_divisa').val(Math.max(0, base - aplicar).toFixed(2));
                                    calcularMontos('divisa');
                                });
                            }).fail(function() {
                                $('#saldo-loading').hide();
                                $('#saldo-info').html('<div class="small text-muted">No se pudo cargar el saldo.</div>').show();
                            });
                        } else {
                            $('#saldo-loading').hide();
                        }

                        $('#modalPagarComision').modal('show');
                    });
                });

                // --- Lógica para editar monto de comisión ---
                $(document).on('click', '.btn-editar-monto', function(event) {
                    event.preventDefault();
                    const pagoId   = $(this).data('pedido-id');
                    const monto    = $(this).data('monto');
                    const porcentaje = parseFloat($(this).data('porcentaje')) || 0;
                    const moneda   = $(this).data('moneda') || '';

                    $('#editar_monto_pago_id').val(pagoId);
                    $('#nuevo_monto_comision').val(monto);

                    $('#dcto_display').val(porcentaje.toFixed(2));

                    $('#modalEditarMonto').modal('show');
                });

                $('#formEditarMontoComision').on('submit', function(e) {
                    e.preventDefault();
                    const pagoId = $('#editar_monto_pago_id').val();
                    const nuevoMonto = $('#nuevo_monto_comision').val();
                    if (!pagoId) {
                        toastr.error('No se pudo identificar el pedido para actualizar el monto.');
                        return;
                    }
                    const urlTemplate = "{{ route('comisiones.editar_monto', ['pedidoId' => '__ID__']) }}";
                    const url = urlTemplate.replace('__ID__', encodeURIComponent(pagoId));
                    const submitBtn = $(this).find('button[type="submit"]');
                    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Actualizando...');

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            nuevo_monto: nuevoMonto
                        },
                        success: function(response) {
                            if (response.success) {
                                toastr.success(response.message);
                                $('#modalEditarMonto').modal('hide');
                                setTimeout(() => { window.location.reload(); }, 1000);
                            } else {
                                toastr.error(response.message || 'Error al actualizar el monto');
                                submitBtn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Actualizar Monto');
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = 'Error al conectar con el servidor';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            toastr.error(errorMessage);
                            submitBtn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Actualizar Monto');
                        }
                    });
                });

                // Manejar clic en botón Rechazar
                $(document).on('click', '.btn-rechazar', function(event) {
                    event.preventDefault();
                    const pagoId = $(this).data('pago-id');

                    if (confirm('¿Está seguro de que desea rechazar todas las comisiones de este pedido?')) {
                        actualizarEstadoComision(pagoId, 'rechazar');
                    }
                });

                // Manejar clic en botón Ver Detalle
                $(document).on('click', '.btn-ver-detalle', function(event) {
                    event.preventDefault();
                    const pedidoId = $(this).data('pedido-id');
                    cargarDetalleComisiones(pedidoId);
                });

                // Manejar clic en botón Ver Pago
                $(document).on('click', '.btn-ver-pago', function(event) {
                    event.preventDefault();
                    const pagoId = $(this).data('pago-id');
                    cargarDetallePago(pagoId);
                });

                // Función para actualizar el estado de la comisión
                function actualizarEstadoComision(pagoId, accion) {
                    const url = '{{ url('comisiones') }}/' + pagoId + '/' + accion;

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                toastr.success(response.message);
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1000);
                            } else {
                                toastr.error(response.message || 'Error al actualizar el estado');
                            }
                        },
                        error: function(xhr) {
                            console.error('Error en la petición:', xhr);
                            let errorMessage = 'Error al conectar con el servidor';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            toastr.error(errorMessage);
                        }
                    });
                }

                // Manejar clic en botón Ver Pago de Comisión
                $(document).on('click', '.btn-ver-pago-comision', function(event) {
                    event.preventDefault();
                    const pagoId = $(this).data('pago-id');
                    const url = '{{ url('comisiones') }}/' + pagoId + '/pago-comision';

                    $.ajax({
                        url: url,
                        type: 'GET',
                        success: function(response) {
                            if (response.success) {
                                console.warn(response)
                                const pago = response.pago;
                                $('#ver_pago_destino').text(pago.pago_destino ? pago.pago_destino
                                    .nombre : 'N/A');
                                $('#ver_pago_fecha').text(new Date(pago.fecha_pago)
                                    .toLocaleDateString('es-ES'));
                                $('#ver_pago_monto_bs').text(parseFloat(pago.monto_bs || 0)
                                    .toLocaleString('es-VE', {
                                        minimumFractionDigits: 2
                                    }) + ' Bs');
                                $('#ver_pago_monto_divisa').text('$' + parseFloat(pago
                                        .monto_divisa || 0)
                                    .toLocaleString('en-US', {
                                        minimumFractionDigits: 2
                                    }));
                                $('#ver_pago_tasa').text(parseFloat(pago.tasa || 0).toFixed(4));
                                $('#ver_pago_forma').text(pago.forma_pago);
                                $('#ver_pago_referencia').text(pago.numero_referencia ||
                                    'N/A');
                                $('#ver_pago_observaciones').text(pago.observaciones ||
                                    'Sin observaciones');

                                // Manejar estado de recibido
                                $('#ver_pago_id_oculto').val(pagoId);
                                if (pago.recibido) {
                                    $('#ver_pago_recibido_wrapper').show();
                                    $('#btnConfirmarRecibido').hide();
                                } else {
                                    $('#ver_pago_recibido_wrapper').hide();
                                    $('#btnConfirmarRecibido').show();
                                }

                                $('#modalVerPagoComision').modal('show');
                            } else {
                                toastr.error(response.message ||
                                    'Error al cargar los detalles del pago');
                            }
                        },
                        error: function(xhr) {
                            console.error('Error:', xhr);
                            toastr.error('Error al conectar con el servidor');
                        }
                    });
                });

                // Manejar clic en botón Confirmar Recepción
                $(document).on('click', '#btnConfirmarRecibido', function() {
                    const pagoId = $('#ver_pago_id_oculto').val();
                    if (!pagoId) return;

                    const btn = $(this);
                    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Procesando...');

                    $.ajax({
                        url: '{{ url('comisiones') }}/' + pagoId + '/confirmar-recibido',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                toastr.success(response.message);
                                $('#modalVerPagoComision').modal('hide');
                                location.reload(); // Recargar para ver el cambio en el listado
                            } else {
                                toastr.error(response.message || 'Error al confirmar');
                                btn.prop('disabled', false).html(
                                    '<i class="fas fa-check-double mr-1"></i> Marcar como RECIBIDO');
                            }
                        },
                        error: function(xhr) {
                            toastr.error('Error al conectar con el servidor');
                            btn.prop('disabled', false).html(
                                '<i class="fas fa-check-double mr-1"></i> Marcar como RECIBIDO');
                        }
                    });
                });

                // Función para cargar el detalle de comisiones
                function cargarDetalleComisiones(pedidoId) {
                    const url = '{{ url('comisiones') }}/' + pedidoId + '/detalles';

                    $.ajax({
                        url: url,
                        type: 'GET',
                        success: function(response) {
                            if (response.success) {
                                mostrarDetalleModal(response.detalles);
                            } else {
                                toastr.error('Error al cargar los detalles');
                            }
                        },
                        error: function(xhr) {
                            console.error('Error en la petición:', xhr);
                            toastr.error('Error al cargar los detalles');
                        }
                    });
                }

                // Función para mostrar el modal con los detalles
                function mostrarDetalleModal(detalles) {
                    const tbody = $('#detalleComisionesBody');
                    tbody.empty();

                    let totalComision = 0;

                    detalles.forEach(function(detalle) {
                        totalComision += parseFloat(detalle.monto_comision);

                        const row = `
                        <tr>
                            <td><span class="badge badge-secondary">${detalle.codigo_producto}</span></td>
                            <td>${detalle.nombre_producto || 'N/A'}</td>
                            <td class="text-center"><span class="badge badge-primary badge-pill">${detalle.cantidad}</span></td>
                            <td class="text-right font-weight-bold text-success">$${parseFloat(detalle.monto_comision).toFixed(2)}</td>
                            <td class="text-center">${parseFloat(detalle.porcentaje_comision).toFixed(2)}%</td>
                        </tr>
                    `;
                        tbody.append(row);
                    });

                    $('#totalComisionDetalle').text('$' + totalComision.toFixed(2));
                    $('#modalDetalle').modal('show');
                }

                // Función para cargar el detalle del pago
                function cargarDetallePago(pagoId) {
                    const url = '{{ url('comisiones') }}/' + pagoId + '/pago';

                    $.ajax({
                        url: url,
                        type: 'GET',
                        success: function(response) {
                            if (response.success) {
                                mostrarPagoModal(response.pago);
                            } else {
                                toastr.error('Error al cargar los detalles del pago');
                            }
                        },
                        error: function(xhr) {
                            console.error('Error en la petición:', xhr);
                            toastr.error('Error al cargar los detalles del pago');
                        }
                    });
                }

                // Función para mostrar el modal con los detalles del pago
                function mostrarPagoModal(pago) {
                    $('#pagoId').text(pago.id);
                    $('#pagoPedidoId').text(pago.pedido_id);
                    $('#pagoFecha').text(new Date(pago.created_at).toLocaleDateString('es-ES'));
                    $('#pagoMonto').text('$' + parseFloat(pago.monto || 0).toFixed(2));
                    $('#pagoBase').text('$' + parseFloat(pago.base || 0).toFixed(2));
                    $('#pagoDescuento').text('$' + parseFloat(pago.descuento || 0).toFixed(2));
                    $('#pagoIva').text('$' + parseFloat(pago.iva || 0).toFixed(2));
                    $('#pagoRetencion').text('$' + parseFloat(pago.retencion || 0).toFixed(2));
                    $('#pagoTotal').text('$' + parseFloat(pago.total || 0).toFixed(2));

                    $('#modalPago').modal('show');
                }

                // Inicializar datepickers
                $('input[type="date"]').on('change', function() {
                    const startDate = $('input[name="fecha_inicio"]').val();
                    const endDate = $('input[name="fecha_fin"]').val();

                    if (startDate && endDate && startDate > endDate) {
                        $('input[name="fecha_fin"]').val(startDate);
                    }
                });
            </script>
        @endsection
