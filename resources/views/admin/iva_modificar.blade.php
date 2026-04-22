@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel') . ' - Actualización de IVA')

@section('titulo_header', 'Actualización Masiva de IVA')
@section('subtitulo_header', 'Pedidos Management - Executive Dashboard')

@section('styles')
    <link rel="stylesheet" href="{{ asset('theme/plugins/sweetalert2/sweetalert2.min.css') }}">
    <style>
        :root {
            --primary-navy: #1a237e;
            --secondary-silver: #f5f7fa;
            --accent-gold: #c5a02e;
            --success-green: #2ecc71;
            --warning-amber: #f39c12;
            --glass-bg: rgba(255, 255, 255, 0.9);
        }

        .exec-card {
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.1);
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .stat-widget {
            background: white;
            border-radius: 15px;
            padding: 20px;
            border-left: 5px solid var(--primary-navy);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }

        .stat-widget:hover {
            transform: translateY(-3px);
        }

        .stat-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary-navy);
        }

        .gradient-header {
            background: linear-gradient(135deg, #1a237e 0%, #3949ab 100%);
            color: white;
            padding: 20px;
            border-bottom: none;
        }

        .exec-table thead {
            background-color: var(--secondary-silver);
            color: var(--primary-navy);
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 1px;
        }

        .exec-table th {
            border-top: none !important;
            font-weight: 700;
            padding: 15px !important;
        }

        .price-input {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 8px 12px;
            width: 130px;
            text-align: right;
            font-weight: 600;
            color: var(--primary-navy);
            background: white;
            transition: all 0.2s ease;
        }

        .status-select {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 8px 10px;
            width: 160px;
            font-weight: 600;
            color: var(--primary-navy);
            background: white;
            transition: all 0.2s ease;
        }

        .status-select:focus {
            outline: none;
            border-color: var(--primary-navy);
            box-shadow: 0 0 0 3px rgba(26, 35, 126, 0.1);
        }

        .price-input:focus {
            outline: none;
            border-color: var(--primary-navy);
            box-shadow: 0 0 0 3px rgba(26, 35, 126, 0.1);
        }

        .price-input.dirty {
            border-color: var(--warning-amber);
            background-color: #fffaf0;
            color: #d35400;
            animation: pulse-border 2s infinite;
        }

        @keyframes pulse-border {
            0% {
                box-shadow: 0 0 0 0 rgba(243, 156, 18, 0.4);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(243, 156, 18, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(243, 156, 18, 0);
            }
        }

        /* Floating Save Bar */
        .batch-save-bar {
            position: fixed;
            bottom: -100px;
            left: 50%;
            transform: translateX(-50%);
            width: 90%;
            max-width: 800px;
            background: var(--primary-navy);
            color: white;
            padding: 15px 30px;
            border-radius: 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            transition: bottom 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .batch-save-bar.visible {
            bottom: 30px;
        }

        .btn-save-batch {
            background: var(--success-green);
            color: white;
            border: none;
            padding: 10px 30px;
            border-radius: 25px;
            font-weight: 700;
            text-transform: uppercase;
            transition: all 0.3s ease;
        }

        .btn-save-batch:hover {
            background: #27ae60;
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(39, 174, 96, 0.4);
        }

        .btn-exec {
            background: var(--primary-navy);
            color: white;
            border-radius: 8px;
            padding: 10px 25px;
            font-weight: 600;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-exec:hover {
            background: #283593;
            transform: scale(1.02);
            color: white;
        }

        .badge-dirty-tag {
            background: var(--warning-amber);
            color: white;
            font-size: 0.65rem;
            padding: 2px 6px;
            border-radius: 4px;
            margin-left: 5px;
            display: none;
        }

        .price-input.dirty+.badge-dirty-tag {
            display: inline-block;
        }

        /* Specific Fix for Laravel/Tailwind Pagination Arrows */
        .card-footer svg {
            width: 1.25rem !important;
            height: 1.25rem !important;
            display: inline-block !important;
            vertical-align: middle;
        }

        /* Ensure navigation containers are visible */
        .card-footer nav {
            display: block !important;
        }

        .card-footer nav div.flex.justify-between.flex-1 {
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
        }

        /* Hide 'Showing...' text specifically */
        .card-footer nav p.text-sm {
            display: none !important;
        }

        .card-footer nav .hidden.sm\:flex-1.sm\:flex {
            display: flex !important;
            justify-content: center !important;
            width: 100%;
        }

        /* Style for the link buttons */
        .card-footer .relative.z-0.inline-flex {
            display: flex !important;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .card-footer .relative.z-0.inline-flex a,
        .card-footer .relative.z-0.inline-flex span {
            padding: 10px 18px !important;
            font-size: 0.9rem !important;
            font-weight: 600 !important;
            border: 1px solid #e2e8f0 !important;
            background: white;
            color: var(--primary-navy) !important;
            text-decoration: none !important;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
        }

        .card-footer .relative.z-0.inline-flex span[aria-current="page"] span {
            background-color: var(--primary-navy) !important;
            color: white !important;
            border-color: var(--primary-navy) !important;
        }

        .card-footer .relative.z-0.inline-flex a:hover {
            background-color: var(--secondary-silver) !important;
            color: var(--primary-navy) !important;
        }
    /* Style for the highlight-zero-iva class */
        .table-warning.highlight-zero-iva {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%) !important;
            border-left: 4px solid #f39c12 !important;
            animation: pulse-warning 2s infinite;
        }
        
        .table-warning.highlight-zero-iva td {
            color: #856404 !important;
            font-weight: 700 !important;
        }
        
        .table-warning.highlight-zero-iva:hover {
            background: linear-gradient(135deg, #ffeaa7 0%, #ffcc02 100%) !important;
            box-shadow: 0 4px 15px rgba(243, 156, 18, 0.2) !important;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid py-4">

        @include('layouts.partials.info')

        <!-- Dashboard de Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-widget exec-card">
                    <div class="stat-label"><i class="fas fa-file-invoice mr-1"></i> Total Pedidos</div>
                    <div class="stat-value">{{ number_format($stats['total_pedidos']) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-widget exec-card" style="border-left-color: var(--accent-gold);">
                    <div class="stat-label"><i class="fas fa-dollar-sign mr-1"></i> Promedio IVA (Bs.)</div>
                    <div class="stat-value">Bs. {{ number_format($stats['avg_iva'], 2) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-widget exec-card" style="border-left-color: var(--success-green);">
                    <div class="stat-label"><i class="fas fa-chart-line mr-1"></i> Mayor IVA (Bs.)</div>
                    <div class="stat-value">Bs. {{ number_format($stats['max_iva'], 2) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-widget exec-card" style="border-left-color: var(--warning-amber);">
                    <div class="stat-label"><i class="fas fa-edit mr-1"></i> Cambios Pendientes</div>
                    <div class="stat-value" id="pending-changes-count">0</div>
                </div>
            </div>
        </div>

        <!-- Filtros de Búsqueda -->
        <div class="exec-card p-4 mb-4">
            <div class="row items-center">
                <div class="col-md-9">
                    <h5 class="font-weight-bold mb-0" style="color: var(--primary-navy);">
                        <i class="fas fa-filter mr-2"></i> Refinar Búsqueda de Pedidos
                    </h5>
                </div>
                <div class="col-md-3 text-right">
                    <span class="badge badge-primary p-2">Vista de Actualización Masiva de IVA</span>
                </div>
            </div>
            <hr>
            @php
                $estatusFiltro = ['PENDIENTE', 'APROBADO', 'EN REVISION', 'PAGADO', 'RECHAZADO', 'ANULADO', 'CANCELADO', 'ENTREGADO'];
            @endphp
            <form method="GET" action="{{ route('admin.pedidos.iva.modificar') }}">
                <div class="row items-center">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="text-muted small font-weight-bold uppercase">ID (Referencia)</label>
                            <input type="text" name="id" class="form-control" value="{{ request('id') }}"
                                placeholder="Ej: 1234">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="text-muted small font-weight-bold uppercase">RIF</label>
                            <input type="text" name="rif" class="form-control" value="{{ request('rif') }}"
                                placeholder="J-12345678-9">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="text-muted small font-weight-bold uppercase">Cliente / Descripción</label>
                            <input type="text" name="descripcion" class="form-control" value="{{ request('descripcion') }}"
                                placeholder="Nombre del cliente...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="text-muted small font-weight-bold uppercase">Estatus</label>
                            <select name="estatus" class="form-control">
                                <option value="">Todos</option>
                                @foreach($estatusFiltro as $estatus)
                                    <option value="{{ $estatus }}" {{ request('estatus') === $estatus ? 'selected' : '' }}>
                                        {{ $estatus }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 mt-4 text-center">
                        <div class="custom-control custom-checkbox pt-2">
                            <input type="checkbox" class="custom-control-input" id="base_zero" name="base_zero" value="1" {{ request('base_zero') == '1' ? 'checked' : '' }}>
                            <label class="custom-control-label text-primary font-weight-bold" for="base_zero" style="cursor: pointer;">Sólo Base 0</label>
                        </div>
                    </div>
                    <div class="col-md-2 text-right d-flex align-items-end justify-content-end">
                        <div class="form-group mb-3 d-flex" style="gap: 5px; width:100%;">
                            <button type="submit" class="btn btn-exec flex-grow-1 shadow" title="Aplicar Filtros">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="{{ route('admin.pedidos.iva.modificar') }}"
                                class="btn btn-outline-secondary shadow d-flex align-items-center justify-content-center"
                                style="border-radius: 8px; width: 45px;" title="Limpiar Filtros">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabla de Pedidos -->
        <div class="exec-card overflow-hidden mb-5">
            <div class="gradient-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0 font-weight-bold">
                    <i class="fas fa-list mr-2"></i> Editor de IVA en Lote
                </h4>
                <div class="d-flex align-items-center">
                    <small class="mr-3 opacity-75">Decimales: Use punto (.)</small>
                    <div class="bg-white text-primary rounded-pill px-3 py-1 font-weight-bold" style="font-size: 0.8rem;">
                        Página {{ $pedidos->currentPage() }} de {{ $pedidos->lastPage() }}
                    </div>
                </div>
            </div>

            @php
                function getSortUrl($column)
                {
                    $currentSort = request('sort', 'id');
                    $currentDirection = request('direction', 'desc');
                    $newDirection = $currentSort == $column && $currentDirection == 'desc' ? 'asc' : 'desc';

                    return request()->fullUrlWithQuery([
                        'sort' => $column,
                        'direction' => $newDirection,
                    ]);
                }

                function getSortIcon($column)
                {
                    if (request('sort', 'id') != $column) {
                        return '<i class="fas fa-sort text-muted small ml-1"></i>';
                    }
                    return request('direction', 'desc') == 'asc'
                        ? '<i class="fas fa-sort-up text-primary ml-1"></i>'
                        : '<i class="fas fa-sort-down text-primary ml-1"></i>';
                }
            @endphp

            @php
                $allowedStatuses = ['PENDIENTE', 'APROBADO', 'EN REVISION', 'PAGADO', 'RECHAZADO', 'ANULADO', 'CANCELADO', 'ENTREGADO'];
            @endphp

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table exec-table table-hover mb-0">
                        <thead>
                            <tr>
                                <th width="120">
                                    <a href="{{ getSortUrl('id') }}" class="text-decoration-none"
                                        style="color: inherit;">
                                        Nº Pedido {!! getSortIcon('id') !!}
                                    </a>
                                </th>
                                <th width="150">
                                    <a href="{{ getSortUrl('rif') }}" class="text-decoration-none"
                                        style="color: inherit;">
                                        RIF {!! getSortIcon('rif') !!}
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ getSortUrl('descripcion') }}" class="text-decoration-none"
                                        style="color: inherit;">
                                        Cliente {!! getSortIcon('descripcion') !!}
                                    </a>
                                </th>
                                <th width="120" class="text-center">
                                    <a href="{{ getSortUrl('created_at') }}" class="text-decoration-none"
                                        style="color: inherit;">
                                        Fecha {!! getSortIcon('created_at') !!}
                                    </a>
                                </th>
                                <th width="150" class="text-center">
                                    Estatus
                                </th>
                                <th class="text-right" width="150">
                                    <a href="{{ getSortUrl('tasa') }}" class="text-decoration-none"
                                        style="color: inherit;">
                                        Tasa {!! getSortIcon('tasa') !!}
                                    </a>
                                </th>
                                <th class="text-right" width="160">
                                    <a href="{{ getSortUrl('base') }}" class="text-decoration-none"
                                        style="color: inherit;">
                                        Base ($) {!! getSortIcon('base') !!}
                                    </a>
                                </th>
                                <th class="text-right" width="180">
                                    <a href="{{ getSortUrl('iva_bs') }}" class="text-decoration-none"
                                        style="color: inherit;">
                                        IVA (Bs.) {!! getSortIcon('iva_bs') !!}
                                    </a>
                                </th>
                                <th class="text-right" width="170">
                                    <a href="{{ getSortUrl('saldo_base') }}" class="text-decoration-none"
                                        style="color: inherit;">
                                        Saldo Base ($) {!! getSortIcon('saldo_base') !!}
                                    </a>
                                </th>
                                <th class="text-right" width="190">
                                    <a href="{{ getSortUrl('saldo_iva_bs') }}" class="text-decoration-none"
                                        style="color: inherit;">
                                        Saldo IVA (Bs.) {!! getSortIcon('saldo_iva_bs') !!}
                                    </a>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pedidos as $pedido)
                                <tr class="{{ ($pedido->factura == 'SI' && $pedido->iva_bs == 0) ? 'table-warning highlight-zero-iva' : '' }}">
                                    <td class="align-middle text-center">
                                        <span class="badge p-2 font-weight-bold"
                                            style="color: white; 
                                                   background-color: var(--primary-navy); 
                                                   font-size: 0.75rem; 
                                                   min-width: 80px; 
                                                   display: inline-block; 
                                                   text-align: center; 
                                                   border-radius: 6px; 
                                                   box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                            #{{ $pedido->id }}
                                        </span>
                                        @if($pedido->pedido_factura)
                                            <div class="mt-1" style="font-size: 0.70rem; line-height: 1;">
                                                <span class="text-muted font-weight-bold">Factura:</span>
                                                <br>
                                                <span class="text-primary font-weight-bold">{{ $pedido->pedido_factura->factura }}</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        {{ $pedido->rif }}
                                    </td>
                                    <td class="align-middle">
                                        <div class="font-weight-bold" style="color: var(--primary-navy);">
                                            {{ $pedido->descripcion }}
                                        </div>
                                    </td>
                                    <td class="align-middle text-center text-muted small font-weight-bold">
                                        {{ $pedido->created_at ? \Carbon\Carbon::parse($pedido->created_at)->format('d/m/Y') : '' }}
                                        @if($pedido->factura)
                                            <div class="mt-1">
                                                <span class="badge p-1 font-weight-bold"
                                                      style="color: white; 
                                                             background-color: {{ $pedido->factura == 'SI' ? 'var(--success-green)' : 'var(--danger)' }}; 
                                                             font-size: 0.65rem; 
                                                             min-width: 70px; 
                                                             display: inline-block; 
                                                             text-align: center; 
                                                             border-radius: 4px; 
                                                             box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                                                    {{ $pedido->factura == 'SI' ? 'Con Factura' : 'Sin Factura' }}
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center">
                                        <select class="update-iva status-select"
                                                data-id="{{ $pedido->id }}"
                                                data-field="estatus"
                                                data-type="string"
                                                data-original="{{ $pedido->estatus }}">
                                            @foreach($allowedStatuses as $status)
                                                <option value="{{ $status }}" {{ $pedido->estatus === $status ? 'selected' : '' }}>
                                                    {{ $status }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <span class="badge-dirty-tag">Pte</span>
                                    </td>
                                    <td class="text-right align-middle">
                                        <div class="input-group input-group-sm" style="width: 130px; margin-left: auto;">
                                            <input type="number" step="0.01" class="form-control price-input update-iva ml-0"
                                                style="width: 70%;"
                                                data-id="{{ $pedido->id }}" data-field="tasa" data-type="number"
                                                data-original="{{ number_format($pedido->tasa, 2, '.', '') }}"
                                                value="{{ number_format($pedido->tasa, 2, '.', '') }}">
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-primary btn-calcular-tasa" type="button" 
                                                    data-id="{{ $pedido->id }}" title="Recalcular Base e IVA con esta Tasa">
                                                    <i class="fas fa-calculator"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <span class="badge-dirty-tag" style="display:none; text-align: right;">Pte</span>
                                    </td>
                                    <td class="text-right align-middle">
                                        <input type="number" step="0.01" class="price-input update-iva"
                                            data-id="{{ $pedido->id }}" data-field="base" data-type="number"
                                            data-original="{{ number_format($pedido->base, 2, '.', '') }}"
                                            value="{{ number_format($pedido->base, 2, '.', '') }}">
                                        <span class="badge-dirty-tag">Pte</span>
                                    </td>
                                    <td class="text-right align-middle">
                                        <input type="number" step="0.01" class="price-input update-iva"
                                            data-id="{{ $pedido->id }}" data-field="iva_bs" data-type="number"
                                            data-original="{{ number_format($pedido->iva_bs, 2, '.', '') }}"
                                            value="{{ number_format($pedido->iva_bs, 2, '.', '') }}">
                                        <span class="badge-dirty-tag">Pte</span>
                                    </td>
                                    <td class="text-right align-middle">
                                        <input type="number" step="0.01" class="price-input update-iva"
                                            data-id="{{ $pedido->id }}" data-field="saldo_base" data-type="number"
                                            data-original="{{ number_format($pedido->saldo_base, 2, '.', '') }}"
                                            value="{{ number_format($pedido->saldo_base, 2, '.', '') }}">
                                        <span class="badge-dirty-tag">Pte</span>
                                    </td>
                                    <td class="text-right align-middle">
                                        <input type="number" step="0.01" class="price-input update-iva"
                                            data-id="{{ $pedido->id }}" data-field="saldo_iva_bs" data-type="number"
                                            data-original="{{ number_format($pedido->saldo_iva_bs, 2, '.', '') }}"
                                            value="{{ number_format($pedido->saldo_iva_bs, 2, '.', '') }}">
                                        <span class="badge-dirty-tag">Pte</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-5">
                                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                        <p class="text-muted font-weight-bold">No se encontraron pedidos.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Mostrando del {{ $pedidos->firstItem() }} al {{ $pedidos->lastItem() }} de
                        {{ $pedidos->total() }} registros.
                    </div>
                    <div>
                        {{ $pedidos->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Barra Flotante de Guardado -->
    <div id="batch-save-bar" class="batch-save-bar">
        <div class="d-flex align-items-center">
            <div class="bg-white text-primary rounded-circle p-2 mr-3">
                <i class="fas fa-save"></i>
            </div>
            <div>
                <h5 class="mb-0 font-weight-bold" id="bar-title">Cambios sin guardar</h5>
                <small class="opacity-75"><span id="dirty-count">0</span> pedidos modificados en esta sesión.</small>
            </div>
        </div>
        <div class="d-flex" style="gap: 15px;">
            <button class="btn btn-link text-white font-weight-bold"
                onclick="window.location.reload();">Descartar</button>
            <button id="btn-save-all" class="btn-save-batch shadow">
                Aplicar Cambios <i class="fas fa-arrow-right ml-2"></i>
            </button>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('theme/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            let dirtyChanges = {}; // key: id-field, value: {id, field, value}

            // Logic to track changes
            $('.update-iva').on('input change', function() {
                const input = $(this);
                const id = input.data('id');
                const field = input.data('field');
                const type = input.data('type') || 'number';
                let originalValue = String(input.data('original') ?? '');
                let newValue = String(input.val() ?? '');

                if (type === 'number') {
                    originalValue = Number(originalValue || 0).toFixed(2);
                    newValue = Number(newValue || 0).toFixed(2);
                } else {
                    originalValue = originalValue.trim().toUpperCase();
                    newValue = newValue.trim().toUpperCase();
                }

                const key = `${id}-${field}`;

                if (newValue !== originalValue) {
                    input.addClass('dirty');
                    dirtyChanges[key] = {
                        id: id,
                        field: field,
                        value: newValue
                    };
                } else {
                    input.removeClass('dirty');
                    delete dirtyChanges[key];
                }

                updateBatchUI();
            });

            function updateBatchUI() {
                const count = Object.keys(dirtyChanges).length;
                const uniquePedidoss = new Set(Object.values(dirtyChanges).map(d => d.id)).size;

                $('#dirty-count').text(uniquePedidoss);
                $('#pending-changes-count').text(uniquePedidoss);

                if (count > 0) {
                    $('#batch-save-bar').addClass('visible');
                } else {
                    $('#batch-save-bar').removeClass('visible');
                }
            }

            $('#btn-save-all').on('click', function() {
                const updates = Object.values(dirtyChanges);
                const btn = $(this);
                const token = $('meta[name="csrf-token"]').attr('content');

                if (updates.length === 0) return;

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Guardando...');

                $.ajax({
                    url: "{{ route('admin.pedidos.iva.batch-update') }}",
                    method: "POST",
                    data: {
                        _token: token,
                        updates: updates
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Éxito!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html(
                            'Aplicar Cambios <i class="fas fa-arrow-right ml-2"></i>');
                        Swal.fire({
                            icon: 'error',
                            title: 'Error al actualizar',
                            text: xhr.responseJSON ? xhr.responseJSON.message :
                                'Error desconocido'
                        });
                    }
                });
            });

            // Decimal handling with dots
            $('.update-iva[data-type="number"], .update-iva:not([data-type])').on('keypress', function(e) {
                if (e.which === 44) { // Replace comma with dot
                    e.preventDefault();
                    const start = this.selectionStart;
                    const end = this.selectionEnd;
                    const val = $(this).val();
                    $(this).val(val.slice(0, start) + "." + val.slice(end));
                    this.selectionStart = this.selectionEnd = start + 1;
                }
            });

            // Recalculator magic button
            $('.btn-calcular-tasa').on('click', function(e) {
                e.preventDefault();
                const btn = $(this);
                const id = btn.data('id');
                // Encontrar el input de la tasa en el mismo contenedor
                const inputTasa = btn.closest('.input-group').find('.update-iva[data-field="tasa"]');
                const tasaVal = inputTasa.val();

                if (!tasaVal || tasaVal <= 0) {
                    Swal.fire({icon: 'warning', title: 'Calculadora', text: 'Por favor, ingrese una tasa válida mayor a 0.'});
                    return;
                }

                // Deshabilitar botón temporalmente para evitar doble clicks
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: "{{ url('admin/pedidos-iva-calcular') }}/" + id + "?tasa=" + tasaVal,
                    method: 'GET',
                    success: function(res) {
                        btn.prop('disabled', false).html('<i class="fas fa-calculator"></i>');

                        if (res.success) {
                            // Localizamos la fila
                            const tr = btn.closest('tr');
                            
                            // Input de BASE
                            const inputBase = tr.find('.update-iva[data-field="base"]');
                            inputBase.val(res.base.toFixed(2));
                            inputBase.trigger('change');

                            // Input de IVA BS
                            const inputIvaBs = tr.find('.update-iva[data-field="iva_bs"]');
                            inputIvaBs.val(res.iva_bs.toFixed(2));
                            inputIvaBs.trigger('change');
                            
                            // También trigger a la tasa por si no se había detectado su cambio manual antes de presionar el botón
                            inputTasa.trigger('change');
                        } else {
                            Swal.fire({icon: 'error', title: 'Error de cálculo', text: res.message});
                        }
                    },
                    error: function() {
                        btn.prop('disabled', false).html('<i class="fas fa-calculator"></i>');
                        Swal.fire({icon: 'error', title: 'Error servidor', text: 'No se pudo contactar con el calculador.'});
                    }
                });
            });

        });
    </script>
@endsection
