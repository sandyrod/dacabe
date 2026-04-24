@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel') . ' - Editor de Pedidos')
@section('titulo_header', 'Editor de Pedidos')
@section('subtitulo_header', $pedido ? ('Pedido #' . $pedido->id) : 'Nuevo Pedido')

@section('styles')
<style>
    :root {
        --poe-ink: #102a43;
        --poe-navy: #0f2a46;
        --poe-cyan: #0ea5a8;
        --poe-soft: #f3f8fc;
        --poe-border: #d4e2ef;
        --poe-highlight: #f59e0b;
    }

    .poe-muted { color: #6c757d; }
    .poe-product-table td, .poe-product-table th { vertical-align: middle; }
    .poe-card {
        border: 1px solid #dbe8f4;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 12px 24px rgba(10, 36, 61, 0.08);
    }

    .poe-card .card-header {
        border-bottom: 1px solid #e5eef7;
        background: linear-gradient(115deg, #fafdff 0%, #eef5fc 100%);
    }

    .poe-readonly-panel {
        border: 1px solid var(--poe-border);
        border-radius: 14px;
        background:
            radial-gradient(circle at 85% 15%, rgba(14, 165, 168, 0.12), transparent 34%),
            radial-gradient(circle at 12% 88%, rgba(15, 42, 70, 0.1), transparent 32%),
            linear-gradient(140deg, #f8fbff 0%, #eef4fb 100%);
        padding: 14px;
        margin-bottom: 16px;
    }

    .poe-info-chip {
        background: rgba(255, 255, 255, 0.72);
        border: 1px solid #d5e4f1;
        border-radius: 10px;
        padding: 10px;
        height: 100%;
    }

    .poe-readonly-label {
        color: #59718a;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.85px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .poe-readonly-value {
        color: var(--poe-ink);
        font-weight: 800;
        font-size: 1rem;
        margin-top: 4px;
    }

    .poe-readonly-sub {
        color: #567089;
        font-size: 0.78rem;
        margin-top: 2px;
    }

    .poe-section-title {
        color: var(--poe-ink);
        font-weight: 800;
        font-size: 0.84rem;
        text-transform: uppercase;
        letter-spacing: 0.9px;
        border-bottom: 1px dashed #c8daea;
        padding-bottom: 7px;
        margin-bottom: 12px;
    }

    .poe-config-frame {
        border: 1px solid #d9e7f4;
        border-radius: 12px;
        background: linear-gradient(180deg, #fbfdff 0%, #f4f8fc 100%);
        padding: 12px;
        margin-bottom: 14px;
    }

    .poe-footer-navy {
        background: linear-gradient(120deg, #0f2a46 0%, #153c63 100%);
        border: 1px solid #1f4a77;
        border-radius: 14px;
        box-shadow: 0 12px 26px rgba(9, 31, 53, 0.26);
        position: relative;
    }

    .poe-footer-kpi {
        color: #d9e8f7;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        margin-bottom: 2px;
    }

    .poe-footer-value {
        color: #ffffff;
        font-weight: 800;
        font-size: 1.05rem;
        line-height: 1.1;
    }

    .poe-btn-primary {
        border: none;
        color: #fff;
        font-weight: 700;
        background: linear-gradient(120deg, var(--poe-cyan) 0%, #1d7fd8 100%);
        box-shadow: 0 10px 20px rgba(13, 112, 180, 0.3);
    }

    .poe-btn-primary:hover { color: #fff; filter: brightness(1.05); }

    #poe_search {
        border: 1px solid #c8dbee;
        box-shadow: inset 0 1px 2px rgba(7, 32, 56, 0.04);
        transition: all 0.18s ease;
    }

    #poe_search:focus {
        border-color: #0ea5a8;
        box-shadow: 0 0 0 0.2rem rgba(14, 165, 168, 0.18);
    }

    #poe_product_results {
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid #d7e6f3;
    }

    #poe_product_results thead th {
        background: linear-gradient(120deg, #0f2a46 0%, #185182 100%);
        color: #f7fbff;
        font-size: 0.79rem;
        text-transform: uppercase;
        letter-spacing: 0.45px;
        border-color: rgba(255, 255, 255, 0.14);
    }

    #poe_product_results tbody tr {
        transition: transform 0.14s ease, box-shadow 0.14s ease, background-color 0.14s ease;
        animation: poeFadeInRow 0.22s ease both;
    }

    #poe_product_results tbody tr:hover {
        background: #f4fbff;
        transform: translateY(-1px);
        box-shadow: inset 0 0 0 1px #cce9f0;
    }

    #poe_product_results tbody tr td:nth-child(3),
    #poe_product_results tbody tr td:nth-child(4),
    #poe_product_results tbody tr td:nth-child(5) {
        background: linear-gradient(180deg, #fbfdff 0%, #f4f9ff 100%);
        font-weight: 700;
        color: #134164;
    }

    #poe_product_results tbody tr td:last-child {
        background: #fbfeff;
    }

    @keyframes poeFadeInRow {
        from {
            opacity: 0;
            transform: translateY(5px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 767px) {
        .poe-footer-navy { border-radius: 10px; }
        .poe-info-chip { margin-bottom: 8px; }
    }
</style>
@endsection

@section('content')
@php
    $vendedorActual = null;
    if ($pedido && !empty($pedido->seller_code)) {
        $vendedorActual = $vendedores->firstWhere('codigo', $pedido->seller_code);
    }

    $vendedorSeleccionado = old('vendedor_id', optional($vendedorActual)->id);
    $cdeposSeleccionado = old('cdepos', $pedido->cdepos ?? '');
    $estatusActual = old('estatus', $pedido ? $pedido->estatus : 'CARGANDO');

    $vendedorPanel = $pedido && !empty($pedido->seller_code)
        ? $pedido->seller_code
        : ($vendedorActual ? $vendedorActual->codigo : 'No seleccionado');

    $vendedorPanelSub = $vendedorActual ? $vendedorActual->nombre_completo : '';

    $depositoPanel = 'No seleccionado';
    if ($pedido && !empty($pedido->cdepos)) {
        $depositoPanel = $pedido->cdepos . (!empty($depositoNombrePedido) ? (' - ' . $depositoNombrePedido) : '');
    }
@endphp

<div class="container-fluid">
    @include('layouts.partials.info')

    <input type="hidden" id="poe_pedido_id" value="{{ $pedido ? (int) $pedido->id : 0 }}">
    <input type="hidden" id="poe_current_cdepos" value="{{ $cdeposSeleccionado }}">
    <textarea id="poe_depos_map" class="d-none">{{ json_encode($deposByVendedor, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</textarea>

    <div class="d-flex justify-content-between mb-3">
        <a href="{{ route('admin.pedidos_editor.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i>Volver al listado
        </a>
        @if($pedido)
            <span class="badge p-2" style="background: linear-gradient(120deg, #0ea5a8 0%, #1d7fd8 100%); color:#fff;">Pedido #{{ $pedido->id }}</span>
        @endif
    </div>

    <div class="card poe-card mb-3">
        <div class="card-header">
            <strong><i class="fas fa-file-alt mr-1"></i>Datos del Pedido</strong>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ $pedido ? route('admin.pedidos_editor.update', $pedido->id) : route('admin.pedidos_editor.store') }}" id="poe-head-form">
                @csrf
                @if($pedido)
                    @method('PUT')
                @endif

                <div class="poe-readonly-panel">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="poe-info-chip">
                                <div class="poe-readonly-label"><i class="fas fa-user-tie"></i>Vendedor</div>
                                <div class="poe-readonly-value" id="poe_readonly_vendedor">{{ $vendedorPanel }}</div>
                                @if($vendedorPanelSub)
                                    <div class="poe-readonly-sub">{{ $vendedorPanelSub }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="poe-info-chip">
                                <div class="poe-readonly-label"><i class="fas fa-warehouse"></i>Depósito</div>
                                <div class="poe-readonly-value" id="poe_readonly_deposito">{{ $depositoPanel }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="poe-info-chip">
                                <div class="poe-readonly-label"><i class="fas fa-flag-checkered"></i>Estatus</div>
                                <div class="poe-readonly-value" id="poe_readonly_estatus">{{ $estatusActual }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" id="poe_is_editing" value="{{ $pedido ? 1 : 0 }}">
                <input type="hidden" name="estatus" value="{{ $estatusActual }}">
                <input type="hidden" name="codcli" id="codcli" value="{{ old('codcli', $pedido->codcli ?? '') }}">
                <input type="hidden" name="email" id="email" value="{{ old('email', $pedido->email ?? '') }}">
                <input type="hidden" name="cliageret" id="cliageret" value="{{ old('cliageret', $pedido->cliageret ?? 0) }}">
                <input type="hidden" name="fecha_despacho" id="fecha_despacho" value="{{ old('fecha_despacho', !empty($pedido->fecha_despacho) ? \Carbon\Carbon::parse($pedido->fecha_despacho)->format('Y-m-d') : '') }}">

                @if($pedido)
                    <input type="hidden" name="vendedor_id" id="vendedor_id" value="{{ $vendedorEditId ?? $vendedorSeleccionado }}">
                    <input type="hidden" name="cdepos" id="cdepos" value="{{ $cdeposSeleccionado }}">
                    <input type="hidden" id="poe_search_vendedor_id" value="{{ $vendedorEditId ?? $vendedorSeleccionado }}">
                    <input type="hidden" id="poe_search_cdepos" value="{{ $pedido->cdepos }}">
                @else
                    <div class="poe-section-title">Asignación Comercial</div>
                    <div class="poe-config-frame">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label><i class="fas fa-user-check mr-1 text-info"></i>Vendedor</label>
                                <select name="vendedor_id" id="vendedor_id" class="form-control" required>
                                    <option value="">Seleccione...</option>
                                    @foreach($vendedores as $vendedor)
                                        <option value="{{ $vendedor->id }}" {{ (string) $vendedorSeleccionado === (string) $vendedor->id ? 'selected' : '' }}>
                                            {{ $vendedor->codigo }} - {{ $vendedor->nombre_completo }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label><i class="fas fa-warehouse mr-1 text-info"></i>Depósito</label>
                                <select name="cdepos" id="cdepos" class="form-control" required>
                                    <option value="">Seleccione...</option>
                                </select>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="poe-section-title">Configuración de Pedido</div>
                <div class="poe-config-frame">
                    <div class="form-row">
                        <div class="form-group col-md-4 mb-0">
                            <label><i class="fas fa-percent mr-1 text-info"></i>Descuento</label>
                            <input type="number" step="0.01" min="0" class="form-control" name="descuento" id="descuento" value="{{ old('descuento', $pedido->descuento ?? 0) }}">
                        </div>
                    </div>
                </div>

                <div class="poe-section-title">Datos del Cliente</div>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>RIF</label>
                        <input type="text" class="form-control" name="rif" id="rif" value="{{ old('rif', $pedido->rif ?? '') }}">
                    </div>
                    <div class="form-group col-md-5">
                        <label>Razón Social</label>
                        <input type="text" class="form-control" name="descripcion" id="descripcion" value="{{ old('descripcion', $pedido->descripcion ?? '') }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Teléfono</label>
                        <input type="text" class="form-control" name="telefono" id="telefono" value="{{ old('telefono', $pedido->telefono ?? '') }}">
                    </div>
                </div>

                <div class="poe-section-title">Condiciones Comerciales</div>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>Factura</label>
                        <select name="factura" id="factura" class="form-control">
                            <option value="SI" {{ old('factura', $pedido->factura ?? 'SI') === 'SI' ? 'selected' : '' }}>SI</option>
                            <option value="NO" {{ old('factura', $pedido->factura ?? 'SI') === 'NO' ? 'selected' : '' }}>NO</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>% Retención</label>
                        <select name="porc_retencion" id="porc_retencion" class="form-control">
                            @foreach([0, 75, 100] as $pret)
                                <option value="{{ $pret }}" {{ (int) old('porc_retencion', $pedido->porc_retencion ?? 0) === $pret ? 'selected' : '' }}>{{ $pret }}%</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Condiciones</label>
                        <input type="text" class="form-control" name="conditions" id="conditions" value="{{ old('conditions', $pedido->conditions ?? '') }}">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Observaciones</label>
                        <input type="text" class="form-control" name="observations" id="observations" value="{{ old('observations', $pedido->observations ?? '') }}">
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($pedido)
        <div class="card poe-card mb-3">
            <div class="card-header">
                <strong><i class="fas fa-search mr-1"></i>Buscar y Agregar Productos</strong>
                <small class="poe-muted ml-2">Búsqueda por código o descripción, con precio sugerido según vendedor y depósito.</small>
            </div>
            <div class="card-body">
                <div class="form-row align-items-end">
                    <div class="form-group col-md-3">
                        <label>Grupo</label>
                        <select class="form-control" id="poe_cgrupo">
                            <option value="TODOS">TODOS</option>
                            @foreach($grupos as $grupo)
                                <option value="{{ $grupo->CGRUPO }}">{{ $grupo->DGRUPO }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-5">
                        <label>Buscar</label>
                        <input type="text" id="poe_search" class="form-control" placeholder="Código o descripción">
                    </div>
                    <div class="form-group col-md-2">
                        <button type="button" id="poe_btn_search" class="btn poe-btn-primary btn-block"><i class="fas fa-search mr-1"></i>Buscar</button>
                    </div>
                    <div class="form-group col-md-2">
                        <button type="button" id="poe_btn_clear" class="btn btn-outline-secondary btn-block"><i class="fas fa-eraser mr-1"></i>Limpiar</button>
                    </div>
                </div>

                <div class="table-responsive mt-2">
                    <table class="table table-sm table-bordered poe-product-table" id="poe_product_results">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Descripción</th>
                                <th>Stock</th>
                                <th>IVA %</th>
                                <th>Precio sugerido</th>
                                <th>Cant.</th>
                                <th>Precio base</th>
                                <th class="text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="8" class="text-center text-muted">Usa el buscador para cargar productos.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card poe-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong><i class="fas fa-box-open mr-1"></i>Detalle del Pedido</strong>
                <span class="badge badge-light border" id="poe_totals_badge">Items: {{ number_format((float) $detalle->sum('cantidad'), 0, ',', '.') }}</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover poe-product-table" id="poe_detail_table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Descripción</th>
                                <th style="width:110px;">Cantidad</th>
                                <th style="width:140px;">Precio base</th>
                                <th style="width:90px;">IVA %</th>
                                <th class="text-right">Subtotal</th>
                                <th style="width:130px;" class="text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($detalle as $item)
                                <tr id="row_{{ $item->id }}" data-item-id="{{ $item->id }}" data-codigo="{{ $item->codigo_inven }}">
                                    <td>{{ $item->codigo_inven }}</td>
                                    <td>{{ $item->inven_descr }}</td>
                                    <td><input type="number" min="1" step="1" class="form-control form-control-sm poe-cantidad" value="{{ (int) $item->cantidad }}"></td>
                                    <td><input type="number" min="0" step="0.0001" class="form-control form-control-sm poe-precio" value="{{ $item->precio_dolar }}"></td>
                                    <td>
                                        <select class="form-control form-control-sm poe-iva">
                                            <option value="16" {{ (float) $item->iva > 0 ? 'selected' : '' }}>16</option>
                                            <option value="0" {{ (float) $item->iva <= 0 ? 'selected' : '' }}>0</option>
                                        </select>
                                    </td>
                                    <td class="text-right poe-subtotal">{{ number_format((float) $item->cantidad * (float) $item->precio_dolar, 4, ',', '.') }}</td>
                                    <td class="text-right">
                                        <button type="button" class="btn btn-sm btn-outline-success poe-save-item"><i class="fas fa-save"></i></button>
                                        <button type="button" class="btn btn-sm btn-outline-danger poe-delete-item"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            @empty
                                <tr id="poe_empty_row"><td colspan="7" class="text-center text-muted">Este pedido no tiene productos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info">
            Crea primero el pedido base para poder agregar/modificar productos, precios, IVA, retención y descuentos.
        </div>
    @endif

    <div class="poe-footer-navy p-3 p-md-4 mt-3 mb-4">
        <div class="row align-items-center">
            <div class="col-md-8 mb-3 mb-md-0">
                <div class="row">
                    <div class="col-sm-4 mb-2 mb-sm-0">
                        <div class="poe-footer-kpi">Base</div>
                        <div class="poe-footer-value">Ref <span id="poe_total_base">{{ number_format((float) ($pedido->base ?? 0), 2, ',', '.') }}</span></div>
                    </div>
                    <div class="col-sm-4 mb-2 mb-sm-0">
                        <div class="poe-footer-kpi">IVA</div>
                        <div class="poe-footer-value">Bs. <span id="poe_total_iva">{{ number_format((float) ($pedido->iva_bs ?? 0), 2, ',', '.') }}</span></div>
                    </div>
                    <div class="col-sm-4">
                        <div class="poe-footer-kpi">Total Ref</div>
                        <div class="poe-footer-value">Ref <span id="poe_total_ref">{{ number_format((float) ($detalle->sum(function ($i) { return $i->cantidad * $i->precio_dolar; })), 2, ',', '.') }}</span></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-right">
                <button class="btn poe-btn-primary btn-lg font-weight-bold" type="submit" form="poe-head-form">
                    <i class="fas fa-save mr-1"></i>{{ $pedido ? 'Guardar Cambios' : 'Crear Pedido Base' }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    const deposRaw = document.getElementById('poe_depos_map').value || '{}';
    const deposByVendedor = JSON.parse(deposRaw);
    const pedidoId = Number(document.getElementById('poe_pedido_id').value || 0);
    const csrf = '{{ csrf_token() }}';

    function formatNumber(value, decimals = 2) {
        const fixed = Number(value || 0).toFixed(decimals);
        const parts = fixed.split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        return parts.join(',');
    }

    function renderDepositos() {
        const isEditing = String(document.getElementById('poe_is_editing')?.value || '0') === '1';
        const vendedorSelect = document.getElementById('vendedor_id');
        const cdeposSelect = document.getElementById('cdepos');

        if (isEditing || !vendedorSelect || !cdeposSelect || vendedorSelect.tagName !== 'SELECT' || cdeposSelect.tagName !== 'SELECT') {
            return;
        }

        const vendedorId = vendedorSelect.value;
        const current = String(document.getElementById('poe_current_cdepos').value || '');
        const depos = deposByVendedor[vendedorId] || [];

        const vendedorText = vendedorSelect.options[vendedorSelect.selectedIndex]
            ? vendedorSelect.options[vendedorSelect.selectedIndex].textContent.trim()
            : 'No seleccionado';

        const vendedorReadonly = document.getElementById('poe_readonly_vendedor');
        if (vendedorReadonly) {
            vendedorReadonly.textContent = vendedorText && vendedorText !== 'Seleccione...' ? vendedorText : 'No seleccionado';
        }

        cdeposSelect.innerHTML = '<option value="">Seleccione...</option>';
        depos.forEach((item) => {
            const selected = item.CDEPOS === current ? 'selected' : '';
            cdeposSelect.insertAdjacentHTML('beforeend', `<option value="${item.CDEPOS}" ${selected}>${item.DDEPOS}</option>`);
        });

        if (!cdeposSelect.value && depos.length) {
            cdeposSelect.value = depos[0].CDEPOS;
        }

        const depoReadonly = document.getElementById('poe_readonly_deposito');
        if (depoReadonly) {
            const depoText = cdeposSelect.options[cdeposSelect.selectedIndex]
                ? cdeposSelect.options[cdeposSelect.selectedIndex].textContent.trim()
                : 'No seleccionado';
            depoReadonly.textContent = depoText && depoText !== 'Seleccione...' ? depoText : 'No seleccionado';
        }
    }

    const vendedorEl = document.getElementById('vendedor_id');
    const cdeposEl = document.getElementById('cdepos');
    if (vendedorEl && vendedorEl.tagName === 'SELECT') vendedorEl.addEventListener('change', renderDepositos);
    if (cdeposEl && cdeposEl.tagName === 'SELECT') cdeposEl.addEventListener('change', renderDepositos);
    renderDepositos();

    if (!pedidoId) {
        return;
    }

    function refreshTotals(totales) {
        if (!totales) return;
        document.getElementById('poe_total_base').textContent = formatNumber(totales.total_precio, 2);
        document.getElementById('poe_total_ref').textContent = formatNumber(totales.total_precio, 2);
        document.getElementById('poe_total_iva').textContent = formatNumber(totales.total_iva, 2);
        document.getElementById('poe_totals_badge').textContent = 'Items: ' + formatNumber(totales.total_cantidad, 0);
    }

    function productResultRow(producto) {
        const stock = Number(producto.stock || 0) - Number(producto.reserva || 0);
        const ivaDefault = Number(producto.impuesto || 0) > 0 ? 16 : 0;
        return `
            <tr>
                <td>${producto.codigo}</td>
                <td>${producto.descripcion}</td>
                <td>${formatNumber(stock, 0)}</td>
                <td>
                    <select class="form-control form-control-sm poe-add-iva" style="max-width:74px;">
                        <option value="16" ${ivaDefault === 16 ? 'selected' : ''}>16</option>
                        <option value="0" ${ivaDefault === 0 ? 'selected' : ''}>0</option>
                    </select>
                </td>
                <td>${formatNumber(producto.precio_sugerido, 4)}</td>
                <td><input type="number" min="1" step="1" class="form-control form-control-sm poe-add-cant" value="1"></td>
                <td><input type="number" min="0" step="0.0001" class="form-control form-control-sm poe-add-precio" value="${Number(producto.precio_sugerido || 0).toFixed(4)}"></td>
                <td class="text-right">
                    <button type="button" class="btn btn-sm btn-outline-primary poe-add-product" data-codigo="${producto.codigo}" data-iva="${producto.impuesto}">
                        <i class="fas fa-plus mr-1"></i>Agregar
                    </button>
                </td>
            </tr>
        `;
    }

    function bindAddButtons() {
        document.querySelectorAll('.poe-add-product').forEach((btn) => {
            btn.addEventListener('click', async function () {
                const tr = this.closest('tr');
                const cantidad = parseInt(tr.querySelector('.poe-add-cant').value || '0', 10);
                const precio = tr.querySelector('.poe-add-precio').value;
                const codigo = this.dataset.codigo;
                const iva = parseInt(tr.querySelector('.poe-add-iva').value || '0', 10);

                if (!Number.isInteger(cantidad) || cantidad < 1) {
                    alert('La cantidad debe ser un entero mayor o igual a 1.');
                    return;
                }

                if (!(iva === 0 || iva === 16)) {
                    alert('El IVA solo puede ser 0 o 16.');
                    return;
                }

                const res = await fetch(`{{ url('admin/pedidos-editor') }}/${pedidoId}/items`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ codigo_inven: codigo, cantidad: cantidad, precio_dolar: precio, iva: iva, pago: 'Bs' })
                });

                const json = await res.json();
                if (json.type !== 'success') {
                    alert(json.message || 'No se pudo agregar el producto.');
                    return;
                }
                location.reload();
            });
        });
    }

    const btnSearch = document.getElementById('poe_btn_search');
    const searchInput = document.getElementById('poe_search');

    if (searchInput && btnSearch) {
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                btnSearch.click();
            }
        });
    }

    if (btnSearch) {
        btnSearch.addEventListener('click', async function () {
            const isEdit = String(document.getElementById('poe_is_editing')?.value || '0') === '1';
            const vendedorId = isEdit
                ? document.getElementById('poe_search_vendedor_id').value
                : document.getElementById('vendedor_id').value;
            const cdepos = isEdit
                ? document.getElementById('poe_search_cdepos').value
                : document.getElementById('cdepos').value;
            const cgrupo = document.getElementById('poe_cgrupo').value;
            const search = document.getElementById('poe_search').value;

            const res = await fetch(`{{ route('admin.pedidos_editor.productos') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ vendedor_id: vendedorId, cdepos: cdepos, cgrupo: cgrupo, search: search })
            });

            const json = await res.json();
            const tbody = document.querySelector('#poe_product_results tbody');
            if (!tbody) return;

            if (!json.data || !json.data.length) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">Sin productos para ese criterio.</td></tr>';
                return;
            }

            tbody.innerHTML = json.data.map(productResultRow).join('');
            bindAddButtons();
        });
    }

    const btnClear = document.getElementById('poe_btn_clear');
    if (btnClear) {
        btnClear.addEventListener('click', function () {
            document.getElementById('poe_search').value = '';
            document.querySelector('#poe_product_results tbody').innerHTML = '<tr><td colspan="8" class="text-center text-muted">Usa el buscador para cargar productos.</td></tr>';
        });
    }

    document.querySelectorAll('.poe-save-item').forEach((btn) => {
        btn.addEventListener('click', async function () {
            const tr = this.closest('tr');
            const itemId = tr.dataset.itemId;
            const cantidad = parseInt(tr.querySelector('.poe-cantidad').value || '0', 10);
            const precio = tr.querySelector('.poe-precio').value;
            const iva = parseInt(tr.querySelector('.poe-iva').value || '0', 10);

            if (!Number.isInteger(cantidad) || cantidad < 1) {
                alert('La cantidad debe ser un entero mayor o igual a 1.');
                return;
            }

            if (!(iva === 0 || iva === 16)) {
                alert('El IVA solo puede ser 0 o 16.');
                return;
            }

            const res = await fetch(`{{ url('admin/pedidos-editor') }}/${pedidoId}/items/${itemId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ cantidad: cantidad, precio_dolar: precio, iva: iva, pago: 'Bs' })
            });

            const json = await res.json();
            if (json.type !== 'success') {
                alert('No se pudo actualizar el item.');
                return;
            }

            tr.querySelector('.poe-subtotal').textContent = formatNumber((Number(cantidad) * Number(precio)), 4);
            refreshTotals(json.totales);
        });
    });

    document.querySelectorAll('.poe-delete-item').forEach((btn) => {
        btn.addEventListener('click', async function () {
            if (!confirm('¿Eliminar este producto del pedido?')) return;

            const tr = this.closest('tr');
            const itemId = tr.dataset.itemId;

            const res = await fetch(`{{ url('admin/pedidos-editor') }}/${pedidoId}/items/${itemId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                }
            });

            const json = await res.json();
            if (json.type !== 'success') {
                alert('No se pudo eliminar el item.');
                return;
            }

            tr.remove();
            refreshTotals(json.totales);

            const tbody = document.querySelector('#poe_detail_table tbody');
            if (tbody && !tbody.querySelector('tr')) {
                tbody.innerHTML = '<tr id="poe_empty_row"><td colspan="7" class="text-center text-muted">Este pedido no tiene productos.</td></tr>';
            }
        });
    });
})();
</script>
@endsection
