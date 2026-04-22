@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel') . ' - Inventario Inicial')
@section('titulo_header', 'Gestión de Inventario - Executive Dashboard')
@section('subtitulo_header', 'Inventory Management - Executive Dashboard')

@section('styles')
    <link rel="stylesheet" href="{{ asset('theme/plugins/sweetalert2/sweetalert2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/plugins/select2/select2.min.css') }}">
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

        .table-responsive {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .inventory-table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
        }

        .inventory-table .table {
            margin-bottom: 0;
        }

        .inventory-table th {
            background: var(--primary-navy);
            color: white;
            font-weight: 600;
            border: none;
            padding: 15px;
        }

        .inventory-table td {
            vertical-align: middle;
            padding: 12px 15px;
            border-bottom: 1px solid #e9ecef;
        }

        .btn-add {
            background: var(--success-green);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-add:hover {
            background: #27ae60;
            transform: scale(1.02);
            box-shadow: 0 5px 15px rgba(39, 174, 96, 0.4);
        }

        .form-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .date-input {
            max-width: 200px;
        }

        .observations-input {
            min-width: 300px;
        }

        .recent-record {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }

        .recent-record:hover {
            background: #e9ecef;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .badge-product {
            background: var(--primary-navy);
            color: white;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-quantity {
            background: var(--success-green);
            color: white;
            padding: 4px 8px;
            border-radius: 6px;
            font-weight: 600;
        }

        .select2-container--default .select2-selection--single {
            height: 38px;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }

        #productos-list-table thead th {
            background: var(--primary-navy);
            color: white;
            font-size: 0.82rem;
            padding: 10px 12px;
        }

        #productos-list-table tbody td {
            vertical-align: middle;
            padding: 8px 12px;
        }

        #productos-list-table tbody tr:hover {
            background: #f1f3f9;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid py-4 mt-5">

        @include('layouts.partials.info')

        <!-- Dashboard de Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-widget exec-card">
                    <div class="stat-label"><i class="fas fa-boxes mr-1"></i> Total Productos</div>
                    <div class="stat-value">{{ number_format($productos->count()) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-widget exec-card" style="border-left-color: var(--accent-gold);">
                    <div class="stat-label"><i class="fas fa-plus mr-1"></i> Total Registros</div>
                    <div class="stat-value" id="total-registros">{{ number_format($recientes->count()) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-widget exec-card" style="border-left-color: var(--success-green);">
                    <div class="stat-label"><i class="fas fa-calculator mr-1"></i> Cantidad Total</div>
                    <div class="stat-value" id="cantidad-total">{{ number_format($recientes->sum('cantidad'), 2, ',', '.') }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-widget exec-card" style="border-left-color: var(--warning-amber);">
                    <div class="stat-label"><i class="fas fa-clock mr-1"></i> Último Registro</div>
                    <div class="stat-value" id="ultimo-registro">
                        @if($recientes->first())
                            {{ $recientes->first()->created_at->format('d/m/Y H:i') }}
                        @else
                            Sin registros
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulario de Carga de Inventario -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="exec-card">
                    <div class="gradient-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 font-weight-bold">
                            <i class="fas fa-plus-circle mr-2"></i> Cargar Inventario Inicial
                        </h4>
                        <div class="d-flex align-items-center">
                            <small class="mr-3 opacity-75">Inventario Inicial</small>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.inventario.inicial.store') }}" id="inventory-form">
                        @csrf
                        <div class="row align-items-end mb-3">
                            <div class="col-md-2">
                                <div class="form-group mb-0">
                                    <label class="form-label">Fecha <span class="text-danger">*</span></label>
                                    <input type="date" name="fecha" id="fecha" class="form-control"
                                           value="{{ old('fecha') ?? date('Y-m-d') }}" required>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group mb-0">
                                    <label class="form-label">Buscar Producto</label>
                                    <select id="producto-select" class="form-control w-100">
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-0">
                                    <label class="form-label">Cantidad</label>
                                    <input type="number" id="cantidad-input" class="form-control"
                                           step="0.01" min="0.01" placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <button type="button" id="btn-agregar" class="btn btn-primary btn-block">
                                    <i class="fas fa-plus mr-1"></i> Agregar Producto
                                </button>
                            </div>
                        </div>

                        <!-- Lista de productos a cargar -->
                        <div id="productos-list-wrapper" class="mb-3" style="display:none;">
                            <table class="table table-bordered mb-0" id="productos-list-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Producto</th>
                                        <th style="width:140px;">Cantidad</th>
                                        <th style="width:60px;" class="text-center">Quitar</th>
                                    </tr>
                                </thead>
                                <tbody id="productos-list-body">
                                </tbody>
                            </table>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">Observaciones</label>
                                    <textarea name="observacion" class="form-control"
                                              rows="2" placeholder="Ingrese observaciones...">{{ old('observacion') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-2">
                            <button type="submit" class="btn btn-add btn-lg" id="btn-guardar" disabled>
                                <i class="fas fa-save mr-2"></i> Guardar Inventario
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Registros Recientes -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="exec-card">
                    <div class="gradient-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 font-weight-bold">
                            <i class="fas fa-history mr-2"></i> Registros Recientes
                        </h4>
                        <div class="d-flex align-items-center">
                            <small class="mr-3 opacity-75">Últimos 10 registros</small>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive inventory-table">
                            <table class="table table-hover mb-0" id="recent-records-table">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Usuario</th>
                                        <th>Observaciones</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recientes as $record)
                                        <tr id="record-{{ $record->id }}">
                                            <td>
                                                <span class="badge badge-product">
                                                    {{ $record->created_at->format('d/m/Y') }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-product">
                                                    {{ $record->producto_descripcion }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-quantity">
                                                    {{ number_format($record->cantidad, 2, ',', '.') }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-muted">
                                                    {{ $record->user->name ?? 'Sistema' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-muted small">
                                                    {{ $record->observacion }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete-record"
                                                        data-id="{{ $record->id }}"
                                                        title="Eliminar registro">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr id="no-records-row">
                                            <td colspan="6" class="text-center text-muted py-4">Sin registros</td>
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
@endsection

@section('scripts')
    <script src="{{ asset('theme/plugins/select2/select2.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            var isProcessing = false;
            // Array that holds the products to be saved
            var productosLista = [];

            // ── Select2 single search ──────────────────────────────────────
            console.log('Initializing select2...');
            $('#producto-select').select2({
                placeholder: 'Buscar por nombre o código...',
                allowClear: true,
                minimumInputLength: 1,
                width: '100%',
                dropdownParent: $('#inventory-form'),
                language: {
                    noResults:     function () { return 'No se encontraron productos'; },
                    searching:     function () { return 'Buscando...'; },
                    inputTooShort: function () { return 'Digite al menos 1 carácter'; }
                },
                ajax: {
                    url: '{{ route("admin.productos.search") }}',
                    dataType: 'json',
                    delay: 300,
                    data: function (params) {
                        console.log('AJAX call with params:', params);
                        return { q: params.term || '', page: params.page || 1 };
                    },
                    processResults: function (data) {
                        console.log('=== DEBUG SERVER RESPONSE ===');
                        console.log('Raw data from server:', data);
                        console.log('Debug info:', data.debug);
                        
                        var mapped = $.map(data.data, function (item) {
                            console.log('Mapping item:', item);
                            console.log('item.CODIGO:', item.CODIGO);
                            console.log('typeof item.CODIGO:', typeof item.CODIGO);
                            
                            var result = { id: item.CODIGO, text: item.DESCR + ' [' + item.CODIGO + ']' };
                            console.log('Mapped result:', result);
                            return result;
                        });
                        console.log('Final mapped results:', mapped);
                        return {
                            results: mapped,
                            pagination: { more: data.current_page < data.last_page }
                        };
                    },
                    cache: true
                }
            });
            console.log('Select2 initialized');

            // ── Agregar producto a la lista ────────────────────────────────
            $('#btn-agregar').on('click', function () {
                var selectedData = $('#producto-select').select2('data');
                var cantidad = parseFloat($('#cantidad-input').val());
                
                // Debug: Log everything
                console.log('=== DEBUG AGREGAR ===');
                console.log('selectedData:', selectedData);
                console.log('selectedData length:', selectedData ? selectedData.length : 'null/undefined');
                console.log('cantidad:', cantidad);

                if (!selectedData || selectedData.length === 0 || !selectedData[0] || !selectedData[0].id) {
                    Swal.fire({ icon: 'warning', title: 'Seleccione un producto', text: 'Busque y seleccione un producto antes de agregar.' });
                    return;
                }
                if (isNaN(cantidad) || cantidad <= 0) {
                    Swal.fire({ icon: 'warning', title: 'Cantidad inválida', text: 'Ingrese una cantidad mayor a 0.' });
                    return;
                }

                var codigo = selectedData[0].id;
                var nombre = selectedData[0].text;

                // Debug: Log selected data
                console.log('Selected codigo:', codigo);
                console.log('Selected nombre:', nombre);
                console.log('Current productosLista:', productosLista);

                // Check for duplicates
                var existente = productosLista.find(function (p) { return p.codigo === codigo; });
                console.log('Existente:', existente);
                
                if (existente) {
                    Swal.fire({ icon: 'info', title: 'Ya agregado', text: 'Este producto ya está en la lista. Edite la cantidad directamente en la tabla.' });
                    return;
                }

                productosLista.push({ codigo: codigo, nombre: nombre, cantidad: cantidad });
                console.log('Added to lista:', productosLista);
                renderLista();

                // Clear inputs completely - destroy and recreate select2
                $('#producto-select').select2('destroy');
                $('#producto-select').empty();
                
                // Reinitialize select2
                $('#producto-select').select2({
                    placeholder: 'Buscar por nombre o código...',
                    allowClear: true,
                    minimumInputLength: 1,
                    width: '100%',
                    dropdownParent: $('#inventory-form'),
                    language: {
                        noResults:     function () { return 'No se encontraron productos'; },
                        searching:     function () { return 'Buscando...'; },
                        inputTooShort: function () { return 'Digite al menos 1 carácter'; }
                    },
                    ajax: {
                        url: '{{ route("admin.productos.search") }}',
                        dataType: 'json',
                        delay: 300,
                        data: function (params) {
                            return { q: params.term || '', page: params.page || 1 };
                        },
                        processResults: function (data) {
                            return {
                                results: $.map(data.data, function (item) {
                                    return { id: item.CODIGO, text: item.DESCR + ' [' + item.CODIGO + ']' };
                                }),
                                pagination: { more: data.current_page < data.last_page }
                            };
                        },
                        cache: true
                    }
                });
                
                $('#cantidad-input').val('');
                $('#producto-select').focus();
            });

            // Allow pressing Enter in cantidad to agregar
            $('#cantidad-input').on('keydown', function (e) {
                if (e.key === 'Enter') { e.preventDefault(); $('#btn-agregar').trigger('click'); }
            });

            // ── Render the products list table ────────────────────────────
            function renderLista() {
                var tbody = $('#productos-list-body');
                tbody.empty();

                if (productosLista.length === 0) {
                    $('#productos-list-wrapper').hide();
                    $('#btn-guardar').prop('disabled', true);
                    return;
                }

                $('#productos-list-wrapper').show();
                $('#btn-guardar').prop('disabled', false);

                productosLista.forEach(function (item, index) {
                    var row = '<tr>' +
                        '<td class="text-muted">' + (index + 1) + '</td>' +
                        '<td>' + item.nombre + '</td>' +
                        '<td>' +
                            '<input type="number" class="form-control form-control-sm qty-edit" ' +
                                   'value="' + item.cantidad + '" min="0.01" step="0.01" ' +
                                   'data-index="' + index + '">' +
                        '</td>' +
                        '<td class="text-center">' +
                            '<button type="button" class="btn btn-sm btn-outline-danger btn-quitar" data-index="' + index + '">' +
                                '<i class="fas fa-times"></i>' +
                            '</button>' +
                        '</td>' +
                    '</tr>';
                    tbody.append(row);
                });
            }

            // Inline quantity edit
            $(document).on('change', '.qty-edit', function () {
                var idx = parseInt($(this).data('index'));
                var val = parseFloat($(this).val());
                if (val > 0) { productosLista[idx].cantidad = val; }
                else { $(this).val(productosLista[idx].cantidad); }
            });

            // Remove from list
            $(document).on('click', '.btn-quitar', function () {
                var idx = parseInt($(this).data('index'));
                productosLista.splice(idx, 1);
                renderLista();
            });

            // ── Form submit ───────────────────────────────────────────────
            $('#inventory-form').on('submit', function (e) {
                e.preventDefault();

                if (isProcessing) return;

                if (productosLista.length === 0) {
                    Swal.fire({ icon: 'warning', title: 'Lista vacía', text: 'Agregue al menos un producto antes de guardar.' });
                    return;
                }

                isProcessing = true;
                $('#btn-guardar').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Guardando...');

                var formData = new FormData(this);
                
                // Clear any existing productos data
                formData.delete('productos');
                
                // Build productos array for Laravel validation
                var productosArray = [];
                productosLista.forEach(function (item, index) {
                    productosArray.push({
                        codigo: item.codigo,
                        cantidad: item.cantidad
                    });
                });
                
                // Add productos as JSON string
                formData.append('productos', JSON.stringify(productosArray));

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        isProcessing = false;
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Guardado!',
                                text: 'Inventario inicial cargado exitosamente.',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(function () { location.reload(); });
                        } else {
                            $('#btn-guardar').prop('disabled', false).html('<i class="fas fa-save mr-2"></i> Guardar Inventario');
                            Swal.fire({ icon: 'error', title: 'Error', text: response.message || 'Error al cargar el inventario.' });
                        }
                    },
                    error: function (xhr) {
                        isProcessing = false;
                        $('#btn-guardar').prop('disabled', false).html('<i class="fas fa-save mr-2"></i> Guardar Inventario');
                        var msg = 'Error al cargar el inventario inicial.';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            msg = Object.values(xhr.responseJSON.errors).map(function (e) { return e.join(' '); }).join('\n');
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire({ icon: 'error', title: 'Error', text: msg });
                    }
                });
            });
            // ── Delete saved record ──────────────────────────────────────
            $(document).on('click', '.btn-delete-record', function () {
                var id = $(this).data('id');
                Swal.fire({
                    title: '¿Eliminar registro?',
                    text: 'Esta acción eliminará el registro del inventario inicial.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ url("inventario-inicial") }}/' + id,
                            type: 'DELETE',
                            dataType: 'json',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                                'Accept': 'application/json'
                            },
                            success: function (response) {
                                if (response.success) {
                                    Swal.fire({ icon: 'success', title: 'Eliminado', text: 'Registro eliminado.', timer: 1500, showConfirmButton: false });
                                    $('#record-' + id).fadeOut(400, function () { location.reload(); });
                                } else {
                                    Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                                }
                            },
                            error: function (xhr) {
                                var errorMsg = 'No se pudo eliminar el registro.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMsg = xhr.responseJSON.message;
                                }
                                Swal.fire({ icon: 'error', title: 'Error', text: errorMsg });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
