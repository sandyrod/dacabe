@extends('layouts.app')

@section('titulo', 'Sincronización de Clientes')

@section('styles')
<style>
    .manager-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .manager-card-header {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white;
        padding: 1.5rem;
        text-align: center;
    }

    .stat-card-sinc {
        border: none;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s;
    }

    .stat-card-sinc:hover {
        transform: translateY(-3px);
    }

    .table th {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white;
        font-weight: 600;
        font-size: 0.85rem;
        letter-spacing: 0.4px;
        border: none;
        position: sticky;
        top: 0;
        z-index: 5;
    }

    .table td {
        vertical-align: middle;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }

    .modal-header-sinc {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px 15px 0 0;
        border: none;
    }

    .cliente-result {
        cursor: pointer;
        border-radius: 8px;
        padding: 10px 14px;
        margin-bottom: 6px;
        border: 1px solid rgba(0,0,0,0.06);
        transition: all 0.2s;
    }

    .cliente-result:hover {
        background: linear-gradient(135deg, rgba(102,126,234,0.08) 0%, rgba(118,75,162,0.08) 100%);
        border-color: #667eea;
    }

    .cliente-result.selected {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-color: #764ba2;
    }

    .cliente-result.selected small {
        color: rgba(255,255,255,0.8) !important;
    }

    #search-modal-input {
        border: 2px solid rgba(102,126,234,0.3);
        border-radius: 8px;
        transition: border-color 0.2s;
    }

    #search-modal-input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102,126,234,0.2);
    }
</style>
@endsection

@section('content')
<div class="container mt-5 py-4">

    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card manager-card">
                <div class="manager-card-header">
                    <h4 class="mb-1 font-weight-bold">
                        <i class="fas fa-sync-alt mr-2"></i>
                        Sincronización de Clientes
                    </h4>
                    <p class="mb-0 small" style="opacity:.75;">
                        Pedidos sin cliente asignado — crúcelos contra el catálogo de clientes DACABE
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Stat cards --}}
    <div class="row mb-4">
        <div class="col-12 col-sm-4 mb-3">
            <div class="card stat-card-sinc h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-2"><i class="fas fa-exclamation-triangle mr-1"></i> Pendientes</h6>
                    <h2 class="mb-0 font-weight-bold">{{ $pedidos->count() }}</h2>
                    <small style="opacity:.75;">Pedidos sin cliente</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4 mb-3">
            <div class="card stat-card-sinc h-100" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-2"><i class="fas fa-check-circle mr-1"></i> Sincronizados hoy</h6>
                    <h2 class="mb-0 font-weight-bold" id="sinc-hoy">—</h2>
                    <small style="opacity:.75;">Esta sesión</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4 mb-3">
            <div class="card stat-card-sinc h-100" style="background: linear-gradient(135deg, #F2994A 0%, #F2C94C 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-2"><i class="fas fa-shopping-bag mr-1"></i> Total pedidos</h6>
                    <h2 class="mb-0 font-weight-bold">{{ $pedidos->count() }}</h2>
                    <small style="opacity:.75;">Con codcli nulo</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card manager-card">
        <div class="card-header d-flex justify-content-between align-items-center py-3"
             style="background: #f8f9fa; border-bottom: 1px solid #e9ecef;">
            <h5 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-list mr-2 text-primary"></i>
                Pedidos sin cliente asignado
            </h5>
            <span class="badge badge-warning">{{ $pedidos->count() }} pendientes</span>
        </div>
        <div class="card-body pb-3">
            <form method="GET" action="{{ route('sincronizar.clientes.index') }}" class="mb-0">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="small font-weight-bold text-muted mb-1">Filtrar por RIF</label>
                        <input type="text" name="pedido_rif" value="{{ old('pedido_rif', $pedido_rif ?? '') }}" class="form-control form-control-sm" placeholder="RIF del pedido">
                    </div>
                    <div class="col-md-3">
                        <label class="small font-weight-bold text-muted mb-1">Filtrar por Nombre cliente</label>
                        <input type="text" name="pedido_nombre" value="{{ old('pedido_nombre', $pedido_nombre ?? '') }}" class="form-control form-control-sm" placeholder="Nombre del cliente">
                    </div>
                    <div class="col-md-3">
                        <label class="small font-weight-bold text-muted mb-1">Filtrar por Vendedor</label>
                        <select name="vendedor" class="form-control form-control-sm select2">
                            <option value="">— Todos los vendedores —</option>
                            @foreach($vendedores as $vendedor)
                                <option value="{{ $vendedor->email }}" @selected(($vendedor_filter ?? '') == $vendedor->email)>
                                    {{ $vendedor->display_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="small font-weight-bold text-muted mb-1">Filtrar por Estatus</label>
                        <select name="pedido_status" class="form-control form-control-sm select2">
                            <option value="">— Todos los estatus —</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" @selected(($pedido_status ?? '') == $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row g-2 align-items-end mt-2">
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm btn-block">
                            <i class="fas fa-filter mr-1"></i>Filtrar
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('sincronizar.clientes.index') }}" class="btn btn-outline-secondary btn-sm btn-block">
                            <i class="fas fa-times mr-1"></i>Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            @if($pedidos->isEmpty())
                <div class="text-center p-5">
                    @if(request()->filled('pedido_rif') || request()->filled('pedido_nombre') || request()->filled('vendedor') || request()->filled('pedido_status'))
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No se encontraron pedidos con los filtros aplicados.</h5>
                        <p class="text-muted mb-0">Ajusta los filtros o limpia la búsqueda para ver todos los pedidos pendientes.</p>
                    @else
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h5 class="text-success">¡Todos los pedidos tienen cliente asignado!</h5>
                        <p class="text-muted mb-0">No hay pedidos pendientes de sincronización.</p>
                    @endif
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="tabla-pedidos">
                        <thead>
                            <tr>
                                <th width="70">#</th>
                                <th width="170">RIF</th>
                                <th>Descripción actual</th>
                                <th>Vendedor asignado</th>
                                <th width="140">Estatus</th>
                                <th width="160" class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pedidos as $pedido)
                            @php
                                $relacion = $relacionesPorRif[$pedido->rif] ?? null;
                                $vendedorAsignado = optional(optional($relacion)->vendedor)->user;
                            @endphp
                            <tr id="row-pedido-{{ $pedido->id }}">
                                <td><small class="text-muted">{{ $pedido->id }}</small></td>
                                <td>
                                    <code class="text-primary">{{ $pedido->rif ?: '—' }}</code>
                                </td>
                                <td>
                                    <span class="text-muted" id="desc-{{ $pedido->id }}">
                                        {{ $pedido->descripcion ?: 'Sin descripción' }}
                                    </span>
                                </td>
                                <td>
                                    {{ $vendedorAsignado ? trim($vendedorAsignado->name . ' ' . $vendedorAsignado->last_name) : ($relacion ? $relacion->email_vendedor : 'N/A') }}
                                </td>
                                <td>
                                    @if($pedido->estatus ?? false)
                                        <span class="badge badge-secondary">{{ $pedido->estatus }}</span>
                                    @else
                                        <span class="badge badge-light text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-primary btn-asignar"
                                            data-pedido-id="{{ $pedido->id }}"
                                            data-rif="{{ $pedido->rif }}"
                                            data-desc="{{ $pedido->descripcion }}"
                                            data-vendedor-email="{{ optional($relacionesPorRif[$pedido->rif] ?? null)->email_vendedor }}"
                                            data-vendedor-name="{{ optional(optional(optional($relacionesPorRif[$pedido->rif] ?? null)->vendedor)->user)->name ? trim(optional(optional(optional($relacionesPorRif[$pedido->rif] ?? null)->vendedor)->user)->name . ' ' . optional(optional(optional($relacionesPorRif[$pedido->rif] ?? null)->vendedor)->user)->last_name) : '' }}">
                                        <i class="fas fa-link mr-1"></i> Asignar
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

</div>

{{-- Modal: Asignar Cliente --}}
<div class="modal fade" id="modalAsignar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:15px; border:none; box-shadow:0 20px 60px rgba(0,0,0,0.2);">
            <div class="modal-header modal-header-sinc">
                <h5 class="modal-title">
                    <i class="fas fa-link mr-2"></i>
                    Asignar Cliente al Pedido <span id="modal-pedido-id"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle mr-1"></i>
                        RIF actual: <strong id="modal-rif-actual"></strong>
                        &nbsp;&mdash;&nbsp; Descripción: <span id="modal-desc-actual"></span>
                    </small>
                </div>

                <div class="form-group mb-3">
                    <label class="font-weight-bold small text-muted text-uppercase" for="modal-vendedor-select">Vendedor (opcional)</label>
                    <select id="modal-vendedor-select" class="form-control">
                        <option value="">— Seleccione vendedor —</option>
                        @foreach($vendedores as $vendedor)
                            <option value="{{ $vendedor->email }}">{{ $vendedor->display_name }}</option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Si seleccionas un vendedor, se guardará la relación cliente-vendedor cuando confirmes.</small>
                </div>

                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                    <input type="text" class="form-control" id="search-modal-input"
                           placeholder="Buscar por RIF, nombre o código...">
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label class="font-weight-bold small text-muted text-uppercase">Resultados</label>
                        <div id="resultados-modal" style="max-height:280px; overflow-y:auto; border:1px solid #e9ecef; border-radius:8px; padding:8px;">
                            <div class="text-muted text-center p-3 small">
                                <i class="fas fa-keyboard mr-1"></i> Escriba para buscar...
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="font-weight-bold small text-muted text-uppercase">Cliente seleccionado</label>
                        <div id="cliente-seleccionado" style="border:1px solid #e9ecef; border-radius:8px; padding:16px; min-height:100px;">
                            <div class="text-muted text-center small">
                                <i class="fas fa-mouse-pointer mr-1"></i> Seleccione un cliente...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-confirmar-asignar" disabled
                        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border:none;">
                    <i class="fas fa-check mr-1"></i> Confirmar Asignación
                </button>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script src="{{ asset('theme/plugins/select2/js/select2.full.min.js') }}"></script>
<script>
let pedidoActual = null;
let clienteElegido = null;
let vendedorSeleccionado = null;
let sincHoy = 0;

$(document).ready(function() {
    $('select.select2').select2({
        theme: 'bootstrap4',
        placeholder: 'Seleccione una opción',
        allowClear: true,
        width: '100%'
    });

    $('#modal-vendedor-select').select2({
        theme: 'bootstrap4',
        placeholder: 'Seleccione una opción',
        allowClear: true,
        width: '100%',
        dropdownParent: $('#modalAsignar')
    });
});

// Abrir modal al hacer click en Asignar
$(document).on('click', '.btn-asignar', function () {
    pedidoActual = {
        id:   $(this).data('pedido-id'),
        rif:  $(this).data('rif'),
        desc: $(this).data('desc'),
    };
    clienteElegido = null;

    $('#modal-pedido-id').text('#' + pedidoActual.id);
    $('#modal-rif-actual').text(pedidoActual.rif || '—');
    $('#modal-desc-actual').text(pedidoActual.desc || 'Sin descripción');
    $('#search-modal-input').val('');
    $('#resultados-modal').html('<div class="text-muted text-center p-3 small"><i class="fas fa-keyboard mr-1"></i> Escriba para buscar...</div>');
    $('#cliente-seleccionado').html('<div class="text-muted text-center small"><i class="fas fa-mouse-pointer mr-1"></i> Seleccione un cliente...</div>');
    $('#btn-confirmar-asignar').prop('disabled', true);

    vendedorSeleccionado = $(this).data('vendedor-email') || null;
    $('#modal-vendedor-select').val(vendedorSeleccionado || '').trigger('change');

    $('#modalAsignar').modal('show');
    $('#modalAsignar').on('shown.bs.modal', function () {
        $('#search-modal-input').focus();
    });
});

// Búsqueda con debounce
let searchTimer = null;
$('#modal-vendedor-select').on('change', function () {
    vendedorSeleccionado = $(this).val() || null;
});

$('#search-modal-input').on('input', function () {
    clearTimeout(searchTimer);
    const q = $(this).val().trim();
    if (q.length < 2) {
        $('#resultados-modal').html('<div class="text-muted text-center p-3 small">Escriba al menos 2 caracteres...</div>');
        return;
    }
    searchTimer = setTimeout(() => buscarCliente(q), 350);
});

function buscarCliente(q) {
    $('#resultados-modal').html('<div class="text-center p-3"><div class="spinner-border spinner-border-sm text-primary"></div></div>');

    $.get('{{ route("sincronizar.clientes.buscar.cliente") }}', { q: q }, function (res) {
        if (!res.success || !res.clientes.length) {
            $('#resultados-modal').html('<div class="text-muted text-center p-3 small">Sin resultados.</div>');
            return;
        }

        let html = '';
        res.clientes.forEach(function (c) {
            html += `
                <div class="cliente-result"
                     data-rif="${c.RIF}" data-codcli="${c.CODCLI}" data-nombre="${c.NOMBRE}"
                     onclick="seleccionarCliente(this)">
                    <div class="fw-bold small">${c.NOMBRE}</div>
                    <small class="text-muted">
                        RIF: ${c.RIF} &nbsp;|&nbsp; Cod: ${c.CODCLI}
                        ${c.TELEFONO ? ' &nbsp;|&nbsp; ' + c.TELEFONO : ''}
                    </small>
                </div>`;
        });
        $('#resultados-modal').html(html);
    }).fail(function () {
        $('#resultados-modal').html('<div class="alert alert-danger small p-2 m-0">Error al buscar clientes.</div>');
    });
}

function seleccionarCliente(el) {
    // Quitar selección previa
    $('.cliente-result').removeClass('selected');
    $(el).addClass('selected');

    clienteElegido = {
        rif:    $(el).data('rif'),
        codcli: $(el).data('codcli'),
        nombre: $(el).data('nombre'),
    };

    $('#cliente-seleccionado').html(`
        <div class="mb-1"><i class="fas fa-building text-primary mr-1"></i> <strong>${clienteElegido.nombre}</strong></div>
        <div class="small text-muted mb-1">RIF: <code>${clienteElegido.rif}</code></div>
        <div class="small text-muted">Código: <code>${clienteElegido.codcli}</code></div>
    `);

    $('#btn-confirmar-asignar').prop('disabled', false);
}

// Confirmar asignación
$('#btn-confirmar-asignar').on('click', function () {
    if (!pedidoActual || !clienteElegido) return;

    Swal.fire({
        title: '¿Cómo desea aplicar la asignación?',
        html: `
            <div class="text-left small">
                <div class="alert alert-info py-2 px-3 mb-3">
                    <strong>${clienteElegido.nombre}</strong><br>
                    RIF: <code>${clienteElegido.rif}</code> &nbsp;|&nbsp; Cod: <code>${clienteElegido.codcli}</code>
                </div>
                <p class="mb-1">
                    <i class="fas fa-tag mr-1 text-warning"></i>
                    RIF original del pedido: <strong>${pedidoActual.rif || '—'}</strong>
                </p>
                <p class="mb-0 text-muted">
                    Puede actualizar solo el pedido <strong>#${pedidoActual.id}</strong>
                    o todos los pedidos que tengan el mismo RIF original.
                </p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonText: '<i class="fas fa-layer-group mr-1"></i> Todos con este RIF',
        denyButtonText: '<i class="fas fa-hashtag mr-1"></i> Solo pedido #' + pedidoActual.id,
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#667eea',
        denyButtonColor: '#38a169',
    }).then((result) => {
        if (result.isDismissed) return;

        const soloPedido = result.isDenied ? 1 : 0;

        Swal.fire({
            title: 'Procesando...',
            html: '<i class="fas fa-spinner fa-spin fa-2x"></i>',
            allowOutsideClick: false,
            showConfirmButton: false,
        });

        $.ajax({
            url: '{{ url("sincronizar/clientes/asignar") }}/' + pedidoActual.id,
            type: 'POST',
            data: {
                _token:        '{{ csrf_token() }}',
                rif:           clienteElegido.rif,
                codcli:        clienteElegido.codcli,
                nombre:        clienteElegido.nombre,
                solo_pedido:   soloPedido,
                vendedor_email: vendedorSeleccionado,
            },
            success: function (res) {
                if (res.success) {
                    $('#modalAsignar').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: '¡Asignado!',
                        text: res.message,
                        timer: 2500,
                        showConfirmButton: false,
                    }).then(() => window.location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message });
                }
            },
            error: function () {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo completar la asignación.' });
            }
        });
    });
});
</script>
@endsection
