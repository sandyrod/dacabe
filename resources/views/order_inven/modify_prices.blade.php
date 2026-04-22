@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel') . ' - Actualización de Precios')

@section('titulo_header', 'Actualización Masiva de Precios')
@section('subtitulo_header', 'Inventory Management - Executive Dashboard')

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
    </style>
@endsection

@section('content')
    <div class="container-fluid py-4">

        @include('layouts.partials.info')

        <!-- Dashboard de Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-widget exec-card">
                    <div class="stat-label"><i class="fas fa-boxes mr-1"></i> Total Productos</div>
                    <div class="stat-value">{{ number_format($stats['total_products']) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-widget exec-card" style="border-left-color: var(--accent-gold);">
                    <div class="stat-label"><i class="fas fa-dollar-sign mr-1"></i> Promedio BASE 1</div>
                    <div class="stat-value">${{ number_format($stats['avg_base1'], 2) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-widget exec-card" style="border-left-color: var(--success-green);">
                    <div class="stat-label"><i class="fas fa-chart-line mr-1"></i> Precio Máximo</div>
                    <div class="stat-value">${{ number_format($stats['max_price'], 2) }}</div>
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
                        <i class="fas fa-filter mr-2"></i> Refinar Búsqueda de Inventario
                    </h5>
                </div>
                <div class="col-md-3 text-right">
                    <span class="badge badge-primary p-2">Vista de Actualización Masiva</span>
                </div>
            </div>
            <hr>
            <form method="GET" action="{{ route('order-inven.modify-prices') }}">
                <div class="row items-center">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="text-muted small font-weight-bold uppercase">Código</label>
                            <input type="text" name="codigo" class="form-control" value="{{ request('codigo') }}"
                                placeholder="Ej: ART-01">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="text-muted small font-weight-bold uppercase">Descripción</label>
                            <input type="text" name="descr" class="form-control" value="{{ request('descr') }}"
                                placeholder="Nombre...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="text-muted small font-weight-bold uppercase">Departamento</label>
                            <select name="cgrupo" class="form-control select2">
                                <option value="TODOS">Todos los Departamentos</option>
                                @foreach ($groups as $group)
                                    <option value="{{ $group->CGRUPO }}"
                                        {{ request('cgrupo') == $group->CGRUPO ? 'selected' : '' }}>
                                        {{ $group->DGRUPO }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="form-group mb-3 d-flex w-100" style="gap: 5px;">
                            <button type="submit" class="btn btn-exec flex-grow-1 shadow" title="Aplicar Filtros">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="{{ route('order-inven.modify-prices') }}"
                                class="btn btn-outline-secondary shadow d-flex align-items-center justify-content-center"
                                style="border-radius: 8px; width: 45px;" title="Limpiar Filtros">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabla de Productos -->
        <div class="exec-card overflow-hidden mb-5">
            <div class="gradient-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0 font-weight-bold">
                    <i class="fas fa-list mr-2"></i> Editor de Precios en Lote
                </h4>
                <div class="d-flex align-items-center">
                    <small class="mr-3 opacity-75">Decimales: Use punto (.)</small>
                    <div class="bg-white text-primary rounded-pill px-3 py-1 font-weight-bold" style="font-size: 0.8rem;">
                        Página {{ $products->currentPage() }} de {{ $products->lastPage() }}
                    </div>
                </div>
            </div>

            @php
                function getSortUrl($column)
                {
                    $currentSort = request('sort', 'DESCR');
                    $currentDirection = request('direction', 'asc');
                    $newDirection = $currentSort == $column && $currentDirection == 'asc' ? 'desc' : 'asc';

                    return request()->fullUrlWithQuery([
                        'sort' => $column,
                        'direction' => $newDirection,
                    ]);
                }

                function getSortIcon($column)
                {
                    if (request('sort', 'DESCR') != $column) {
                        return '<i class="fas fa-sort text-muted small ml-1"></i>';
                    }
                    return request('direction', 'asc') == 'asc'
                        ? '<i class="fas fa-sort-up text-primary ml-1"></i>'
                        : '<i class="fas fa-sort-down text-primary ml-1"></i>';
                }
            @endphp

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table exec-table table-hover mb-0">
                        <thead>
                            <tr>
                                <th width="150">
                                    <a href="{{ getSortUrl('CODIGO') }}" class="text-decoration-none"
                                        style="color: inherit;">
                                        Código {!! getSortIcon('CODIGO') !!}
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ getSortUrl('DESCR') }}" class="text-decoration-none"
                                        style="color: inherit;">
                                        Descripción {!! getSortIcon('DESCR') !!}
                                    </a>
                                </th>
                                <th width="120" class="text-center">Existencias</th>
                                <th class="text-right" width="160">
                                    <a href="{{ getSortUrl('BASE1') }}" class="text-decoration-none"
                                        style="color: inherit;">
                                        BASE 1 {!! getSortIcon('BASE1') !!}
                                    </a>
                                </th>
                                <th class="text-right" width="180">
                                    <a href="{{ getSortUrl('BASE2') }}" class="text-decoration-none"
                                        style="color: inherit;">
                                        BASE 2 {!! getSortIcon('BASE2') !!}
                                    </a>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                                <tr>
                                    <td class="align-middle">
                                        <span class="badge p-2 font-weight-bold"
                                            style="color: white; background-color: var(--primary-navy); font-size: 0.75rem; min-width: 100px; display: inline-block; text-align: center; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                            {{ $product->CODIGO }}
                                        </span>
                                    </td>
                                    <td class="align-middle">
                                        <div class="font-weight-bold" style="color: var(--primary-navy);">
                                            {{ $product->DESCR }}</div>
                                        <small class="text-muted"><i class="fas fa-tag mr-1"></i>
                                            {{ $product->grupo->DGRUPO ?? 'Sin Grupo' }}</small>
                                    </td>
                                    <td class="align-middle text-center">
                                        @php
                                            $totalStock = $product->artdepos->sum('EUNIDAD');
                                        @endphp
                                        <div class="d-flex flex-column text-start">
                                            <span
                                                class="badge {{ $totalStock > 0 ? 'badge-success' : 'badge-danger' }} mb-1"
                                                style="font-size: 0.85rem;">
                                                Total: {{ number_format($totalStock, 0) }}
                                            </span>
                                            <div style="font-size: 0.65rem; line-height: 1.1; max-width: 150px;">
                                                @foreach ($product->artdepos->where('EUNIDAD', '>', 0) as $stock)
                                                    <div class="text-muted text-nowrap text-start"
                                                        title="{{ $stock->NOMBRE }}">
                                                        <span
                                                            class="font-weight-bold text-success">{{ round($stock->EUNIDAD) }}</span>
                                                        - {{ Str::limit($stock->NOMBRE, 12) }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-right align-middle">
                                        <input type="number" step="0.01" class="price-input update-price"
                                            data-codigo="{{ $product->CODIGO }}" data-field="BASE1"
                                            data-original="{{ number_format($product->BASE1, 2, '.', '') }}"
                                            value="{{ number_format($product->BASE1, 2, '.', '') }}">
                                        <span class="badge-dirty-tag">Pte</span>
                                    </td>
                                    <td class="text-right align-middle">
                                        <input type="number" step="0.01" class="price-input update-price"
                                            data-codigo="{{ $product->CODIGO }}" data-field="BASE2"
                                            data-original="{{ number_format($product->BASE2, 2, '.', '') }}"
                                            value="{{ number_format($product->BASE2, 2, '.', '') }}">
                                        <span class="badge-dirty-tag">Pte</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                        <p class="text-muted font-weight-bold">No se encontraron productos.</p>
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
                        Mostrando del {{ $products->firstItem() }} al {{ $products->lastItem() }} de
                        {{ $products->total() }} registros.
                    </div>
                    <div>
                        {{ $products->appends(request()->query())->links() }}
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
                <small class="opacity-75"><span id="dirty-count">0</span> productos modificados en esta sesión.</small>
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
            let dirtyChanges = {}; // key: codigo-field, value: {codigo, field, value}

            // Logic to track changes
            $('.update-price').on('input change', function() {
                const input = $(this);
                const codigo = input.data('codigo');
                const field = input.data('field');
                const originalValue = parseFloat(input.data('original')).toFixed(2);
                const newValue = parseFloat(input.val() || 0).toFixed(2);
                const key = `${codigo}-${field}`;

                if (newValue !== originalValue) {
                    input.addClass('dirty');
                    dirtyChanges[key] = {
                        codigo: codigo,
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
                const uniqueProducts = new Set(Object.values(dirtyChanges).map(d => d.codigo)).size;

                $('#dirty-count').text(uniqueProducts);
                $('#pending-changes-count').text(uniqueProducts);

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
                    url: "{{ route('order-inven.batch-update') }}",
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
            $('.update-price').on('keypress', function(e) {
                if (e.which === 44) { // Replace comma with dot
                    e.preventDefault();
                    const start = this.selectionStart;
                    const end = this.selectionEnd;
                    const val = $(this).val();
                    $(this).val(val.slice(0, start) + "." + val.slice(end));
                    this.selectionStart = this.selectionEnd = start + 1;
                }
            });
        });
    </script>
@endsection
