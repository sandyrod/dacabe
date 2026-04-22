@extends('layouts.app')

@section('titulo', 'Configuración de Bultos por Producto')
@section('titulo_header', 'Bultos por Producto')
@section('subtitulo_header', 'Defina cuántas unidades contiene cada bulto/caja')

@section('styles')
<style>
    .upb-input { width: 90px; text-align: right; }
    .table-hover tbody tr:hover { background-color: rgba(67, 97, 238, 0.04); }
    .badge-sin-conf { background: #dee2e6; color: #495057; font-weight: 500; }
    .saving-spinner { display: none; }
</style>
@endsection

@section('content')
<div class="container-fluid">

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="row">

        {{-- ── Panel principal ── --}}
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-gradient-navy text-white d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">
                        <i class="fas fa-box-open mr-2"></i>Productos Configurados
                        <span class="badge badge-light ml-2">{{ $bultos->total() }}</span>
                    </h5>
                    <button class="btn btn-sm btn-light" data-toggle="modal" data-target="#modalAgregar">
                        <i class="fas fa-plus mr-1"></i>Agregar producto
                    </button>
                </div>

                {{-- Filtro --}}
                <div class="card-body pb-0">
                    <form method="GET" class="form-inline mb-3">
                        <div class="input-group" style="max-width:360px;">
                            <input type="text" name="search" value="{{ $search }}" class="form-control"
                                   placeholder="Buscar por código o descripción…">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                                @if($search)
                                    <a href="{{ route('admin.producto_bultos.index') }}" class="btn btn-outline-danger">
                                        <i class="fas fa-times"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover table-sm align-middle mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Descripción</th>
                                    <th>Grupo</th>
                                    <th class="text-center">Uds./Bulto</th>
                                    <th class="text-center">Actualizado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bultos as $b)
                                <tr data-id="{{ $b->id }}">
                                    <td><code class="text-primary font-weight-bold">{{ $b->codigo }}</code></td>
                                    <td class="small">{{ $b->DESCR }}</td>
                                    <td><span class="badge badge-secondary small">{{ $b->CGRUPO }}</span></td>
                                    <td class="text-center">
                                        <span class="upb-display font-weight-bold text-success">
                                            {{ number_format($b->unidades_por_bulto, 0) }}
                                        </span>
                                        <input type="number" min="1" step="1"
                                               class="form-control form-control-sm upb-input upb-edit d-none"
                                               value="{{ $b->unidades_por_bulto }}">
                                    </td>
                                    <td class="text-center text-muted small">
                                        {{ \Carbon\Carbon::parse($b->updated_at)->format('d/m/Y') }}
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-xs btn-outline-primary btn-editar mr-1" title="Editar">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                        <button class="btn btn-xs btn-success btn-guardar d-none mr-1" title="Guardar">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-xs btn-secondary btn-cancelar d-none mr-1" title="Cancelar">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <form method="POST"
                                              action="{{ route('admin.producto_bultos.destroy', $b->id) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('¿Eliminar configuración de bulto para {{ $b->codigo }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-outline-danger" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        No hay productos configurados todavía.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $bultos->links() }}
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /row --}}
</div>

{{-- ── Modal: Agregar producto ── --}}
<div class="modal fade" id="modalAgregar" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.producto_bultos.store') }}">
                @csrf
                <div class="modal-header bg-gradient-navy text-white">
                    <h5 class="modal-title"><i class="fas fa-box-open mr-2"></i>Agregar Configuración de Bulto</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Producto <span class="text-danger">*</span></label>
                        <select name="codigo" id="selectProducto" class="form-control" required style="width:100%"></select>
                        <small class="text-muted">
                            <i class="fas fa-search mr-1"></i>Escriba el código o parte del nombre para buscar.
                            Solo aparecen productos sin configuración previa.
                        </small>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Unidades por bulto/caja <span class="text-danger">*</span></label>
                        <input type="number" name="unidades_por_bulto" min="1" step="1"
                               class="form-control" style="max-width:180px;" placeholder="Ej: 24" required>
                        <small class="text-muted">Cantidad de unidades individuales que contiene un bulto o caja.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(function () {
    // Select2 con AJAX para búsqueda de productos
    $('#selectProducto').select2({
        dropdownParent: $('#modalAgregar'),
        width: '100%',
        placeholder: 'Escriba código o nombre del producto…',
        minimumInputLength: 1,
        language: {
            inputTooShort: () => 'Escriba al menos 1 carácter para buscar…',
            searching:     () => 'Buscando…',
            noResults:     () => 'No se encontraron productos.',
        },
        ajax: {
            url: '{{ route('admin.producto_bultos.buscar') }}',
            dataType: 'json',
            delay: 250,
            data: params => ({ q: params.term }),
            processResults: data => data,
            cache: true,
        },
    });

    // Limpiar selección al cerrar el modal
    $('#modalAgregar').on('hidden.bs.modal', function () {
        $('#selectProducto').val(null).trigger('change');
    });

    // Edición inline: activar modo edición
    $(document).on('click', '.btn-editar', function () {
        const $row = $(this).closest('tr');
        $row.find('.upb-display').addClass('d-none');
        $row.find('.upb-edit').removeClass('d-none').focus();
        $row.find('.btn-editar').addClass('d-none');
        $row.find('.btn-guardar, .btn-cancelar').removeClass('d-none');
    });

    // Cancelar edición
    $(document).on('click', '.btn-cancelar', function () {
        const $row = $(this).closest('tr');
        $row.find('.upb-display').removeClass('d-none');
        $row.find('.upb-edit').addClass('d-none');
        $row.find('.btn-editar').removeClass('d-none');
        $row.find('.btn-guardar, .btn-cancelar').addClass('d-none');
    });

    // Guardar via AJAX
    $(document).on('click', '.btn-guardar', function () {
        const $row    = $(this).closest('tr');
        const id      = $row.data('id');
        const valor   = $row.find('.upb-edit').val();
        const $btn    = $(this);

        $btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);

        $.ajax({
            url: '{{ url("admin/producto-bultos") }}/' + id,
            type: 'PUT',
            data: { _token: '{{ csrf_token() }}', unidades_por_bulto: valor },
            success: function () {
                $row.find('.upb-display').text(Math.round(valor)).removeClass('d-none');
                $row.find('.upb-edit').addClass('d-none');
                $row.find('.btn-editar').removeClass('d-none');
                $row.find('.btn-guardar, .btn-cancelar').addClass('d-none');
                $btn.html('<i class="fas fa-check"></i>').prop('disabled', false);

                // Toast de éxito
                toastr.success('Guardado correctamente.');
            },
            error: function () {
                $btn.html('<i class="fas fa-check"></i>').prop('disabled', false);
                toastr.error('Error al guardar. Intente de nuevo.');
            }
        });
    });
});
</script>
@endsection
