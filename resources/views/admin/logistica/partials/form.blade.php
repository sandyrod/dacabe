@php
    $selectedCliente = old('cliente_rif', $caja->cliente_rif ?? '');
@endphp

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title font-weight-bold mb-0"><i class="fas fa-box-open mr-2"></i>Datos de la caja</h3>
    </div>
    <form method="POST" action="{{ $action }}" id="logistica-form">
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif

        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label>Bulto</label>
                    <input type="text" class="form-control" name="bulto_codigo" id="bulto_codigo" value="{{ old('bulto_codigo', $caja->bulto_codigo) }}" placeholder="Ej: LOT-RIF-20260709">
                    <small class="text-muted">Usa el mismo código en todas las cajas del lote. Se sugiere uno al elegir el cliente.</small>
                </div>
                <div class="col-md-2">
                    <label>Nro. caja</label>
                    <input type="number" class="form-control" name="bulto_posicion" value="{{ old('bulto_posicion', $caja->bulto_posicion) }}" min="1" placeholder="1">
                </div>
                <div class="col-md-2">
                    <label>Total bulto</label>
                    <input type="number" class="form-control" name="bulto_total" value="{{ old('bulto_total', $caja->bulto_total) }}" min="1" placeholder="3">
                </div>
                <div class="col-md-2">
                    <label>Peso caja (kg)</label>
                    <input type="number" class="form-control" name="peso_kg" id="peso_kg" value="{{ old('peso_kg', $caja->peso_kg) }}" min="0" step="0.001" placeholder="0.000">
                    <small class="text-muted" id="peso_kg_hint">Peso sugerido por items seleccionados.</small>
                </div>
                <div class="col-md-3">
                    <label>Cliente</label>
                    <select id="cliente_rif_select" class="form-control select2bs4" required>
                        <option value="">Seleccione cliente...</option>
                        @foreach($clientes as $cliente)
                            <option
                                value="{{ $cliente->rif }}"
                                data-codcli="{{ $cliente->codcli }}"
                                data-nombre="{{ $cliente->nombre }}"
                                data-telefono="{{ $cliente->telefono }}"
                                data-direccion-fiscal="{{ $cliente->direccion_fiscal }}"
                                data-direccion-entrega="{{ $cliente->direccion_entrega }}"
                                data-ciudad="{{ $cliente->ciudad }}"
                                data-estado="{{ $cliente->estado }}"
                                @selected($selectedCliente === $cliente->rif)
                            >{{ $cliente->nombre }} ({{ $cliente->rif }})</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="cliente_rif" id="cliente_rif" value="{{ old('cliente_rif', $caja->cliente_rif) }}">
                    <input type="hidden" name="cliente_codcli" id="cliente_codcli" value="{{ old('cliente_codcli', $caja->cliente_codcli) }}">
                </div>
                <div class="col-md-4">
                    <label>Nombre cliente</label>
                    <input type="text" class="form-control" name="cliente_nombre" id="cliente_nombre" value="{{ old('cliente_nombre', $caja->cliente_nombre) }}" required>
                </div>
                <div class="col-md-4">
                    <label>Teléfono</label>
                    <input type="text" class="form-control" name="telefono" id="telefono" value="{{ old('telefono', $caja->telefono) }}">
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-4">
                    <label>Dirección fiscal</label>
                    <input type="text" class="form-control" name="direccion_fiscal" id="direccion_fiscal" value="{{ old('direccion_fiscal', $caja->direccion_fiscal) }}">
                </div>
                <div class="col-md-4">
                    <label>Dirección entrega</label>
                    <input type="text" class="form-control" name="direccion_entrega" id="direccion_entrega" value="{{ old('direccion_entrega', $caja->direccion_entrega) }}" required>
                    <small class="text-muted d-none" id="direccion_hint"></small>
                </div>
                <div class="col-md-2">
                    <label>Ciudad</label>
                    <input type="text" class="form-control" name="ciudad" id="ciudad" value="{{ old('ciudad', $caja->ciudad) }}">
                    <small class="text-muted d-none" id="ciudad_hint"></small>
                </div>
                <div class="col-md-2">
                    <label>Estado</label>
                    <input type="text" class="form-control" name="estado" id="estado" value="{{ old('estado', $caja->estado) }}">
                    <small class="text-muted d-none" id="estado_hint"></small>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-3">
                    <label>Chofer</label>
                    <input type="text" class="form-control" name="chofer_nombre" id="chofer_nombre" value="{{ old('chofer_nombre', $caja->chofer_nombre) }}" placeholder="Nombre del chofer">
                    <input type="hidden" name="chofer_user_id" value="{{ old('chofer_user_id', $caja->chofer_user_id) }}">
                </div>
                <div class="col-md-3">
                    <label>Vendedor (texto)</label>
                    <input type="text" class="form-control" name="vendedor_nombre" id="vendedor_nombre" value="{{ old('vendedor_nombre', $caja->vendedor_nombre) }}" placeholder="Se autocompleta por pedidos">
                </div>
                <div class="col-md-2">
                    <label>Estatus</label>
                    <select name="estatus" class="form-control" required>
                        @foreach($estatusList as $status)
                            <option value="{{ $status }}" @selected(old('estatus', $caja->estatus ?: 'ARMADA') === $status)>{{ str_replace('_', ' ', $status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Observaciones</label>
                    <input type="text" class="form-control" name="observaciones" value="{{ old('observaciones', $caja->observaciones) }}">
                </div>
            </div>

            <hr>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0 font-weight-bold"><i class="fas fa-boxes mr-1"></i>Productos de pedidos aprobados</h5>
                <small class="text-muted">Seleccione cantidades para armar la caja</small>
            </div>
            <div id="cliente-feedback" class="alert alert-info py-2 px-3 mb-2 d-none"></div>
            <div id="pedidos-contenedor" class="border rounded p-2" style="min-height: 170px; max-height: 480px; overflow:auto;"></div>

            <div id="items-hidden"></div>
        </div>

        <div class="card-footer text-right">
            <a href="{{ route('admin.logistica.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary" id="submit-btn">{{ $submitLabel }}</button>
        </div>
    </form>
</div>

@section('scripts')
<script type="application/json" id="selected-items-json">@json($selectedItems ?? [])</script>
<script type="application/json" id="pedidos-iniciales-json">@json($pedidosIniciales ?? [])</script>
<script type="application/json" id="caja-id-json">@json($caja->id ?? null)</script>
<script>
(function() {
    const pedidosContenedor = document.getElementById('pedidos-contenedor');
    const clienteSelect = document.getElementById('cliente_rif_select');
    const clienteRif = document.getElementById('cliente_rif');
    const clienteCodcli = document.getElementById('cliente_codcli');
    const clienteNombre = document.getElementById('cliente_nombre');
    const telefono = document.getElementById('telefono');
    const direccionFiscal = document.getElementById('direccion_fiscal');
    const direccionEntrega = document.getElementById('direccion_entrega');
    const ciudad = document.getElementById('ciudad');
    const estado = document.getElementById('estado');
    const vendedorNombre = document.getElementById('vendedor_nombre');
    const bultoCodigo = document.getElementById('bulto_codigo');
    const pesoKgInput = document.getElementById('peso_kg');
    const pesoKgHint = document.getElementById('peso_kg_hint');
    const ciudadHint = document.getElementById('ciudad_hint');
    const estadoHint = document.getElementById('estado_hint');
    const direccionHint = document.getElementById('direccion_hint');
    const clienteFeedback = document.getElementById('cliente-feedback');
    const hiddenItems = document.getElementById('items-hidden');
    const form = document.getElementById('logistica-form');

    if (window.jQuery && typeof window.jQuery.fn.select2 === 'function') {
        window.jQuery('#cliente_rif_select').select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: 'Buscar cliente por nombre o RIF...',
            allowClear: true,
        });
    }

    const selectedItems = JSON.parse(document.getElementById('selected-items-json').textContent || '{}');
    let pedidosData = JSON.parse(document.getElementById('pedidos-iniciales-json').textContent || '[]');
    const cajaId = JSON.parse(document.getElementById('caja-id-json').textContent || 'null');
    let suggestedPesoKg = null;
    let userEditedPesoKg = Boolean((pesoKgInput.value || '').trim());

    function parseNum(value) {
        const parsed = parseFloat(value);
        return Number.isFinite(parsed) ? parsed : 0;
    }

    function setClienteFromOption(option, overrideFields = true) {
        if (!option) {
            return;
        }

        clienteRif.value = option.value || '';
        clienteCodcli.value = option.dataset.codcli || '';

        if (overrideFields) {
            clienteNombre.value = option.dataset.nombre || '';
            telefono.value = option.dataset.telefono || '';
            direccionFiscal.value = option.dataset.direccionFiscal || '';
            direccionEntrega.value = option.dataset.direccionEntrega || option.dataset.direccionFiscal || '';
            ciudad.value = option.dataset.ciudad || '';
            estado.value = option.dataset.estado || '';
        }
    }

    function formatNum(value) {
        return Number(value || 0).toFixed(2);
    }

    function formatNum3(value) {
        return Number(value || 0).toFixed(3);
    }

    function updateSuggestionHint(element, message) {
        if (!element) {
            return;
        }

        if (!message) {
            element.textContent = '';
            element.classList.add('d-none');
            return;
        }

        element.textContent = message;
        element.classList.remove('d-none');
    }

    function setFeedback(message, level = 'info') {
        clienteFeedback.className = `alert alert-${level} py-2 px-3 mb-2`;
        clienteFeedback.textContent = message;
        clienteFeedback.classList.remove('d-none');
    }

    function clearFeedback() {
        clienteFeedback.classList.add('d-none');
        clienteFeedback.textContent = '';
    }

    function drawPedidos() {
        if (!pedidosData.length) {
            pedidosContenedor.innerHTML = '<div class="text-muted p-3">No hay pedidos aprobados con cantidades disponibles para este cliente.</div>';
            setFeedback('Cliente cargado. No tiene productos disponibles en pedidos aprobados.', 'warning');
            return;
        }

        setFeedback(`Cliente cargado correctamente. Se encontraron ${pedidosData.length} pedido(s) aprobado(s).`, 'success');

        let html = '';
        pedidosData.forEach((pedido, idx) => {
            html += `
                <div class="card card-outline card-info mb-2">
                    <div class="card-header py-2">
                        <strong>Pedido #${pedido.pedido_id}</strong>
                        <span class="ml-2">Fecha: ${pedido.pedido_fecha || '-'}</span>
                        <span class="ml-2">Factura: ${pedido.factura_numero || '-'}</span>
                        <span class="ml-2">Vendedor: ${(pedido.vendedor_nombre || '-') }</span>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Sel.</th>
                                    <th>Código</th>
                                    <th>Descripción</th>
                                    <th>Disponible</th>
                                    <th>Cargar</th>
                                </tr>
                            </thead>
                            <tbody>
            `;

            pedido.items.forEach(item => {
                const selected = selectedItems[item.pedido_detalle_id] || null;
                const checked = selected ? 'checked' : '';
                const qtyValue = selected ? selected.cantidad : '';

                html += `
                    <tr data-row="${item.pedido_detalle_id}">
                        <td><input type="checkbox" class="item-check" data-detalle="${item.pedido_detalle_id}" ${checked}></td>
                        <td>${item.producto_codigo || '-'}</td>
                        <td>${item.producto_descripcion || '-'}</td>
                        <td>${formatNum(item.cantidad_disponible)}</td>
                        <td>
                            <input
                                type="number"
                                class="form-control form-control-sm item-cantidad"
                                step="0.01"
                                min="0.01"
                                max="${formatNum(item.cantidad_disponible)}"
                                value="${qtyValue}"
                                data-pedido-id="${pedido.pedido_id}"
                                data-detalle-id="${item.pedido_detalle_id}"
                                data-factura="${pedido.factura_numero || ''}"
                                data-codigo="${item.producto_codigo || ''}"
                                data-descripcion="${(item.producto_descripcion || '').replace(/"/g, '&quot;')}"
                                data-unidad="${item.unidad || ''}"
                                data-peso-gramos="${item.peso_gramos || 0}"
                                data-vendedor-codigo="${pedido.vendedor_codigo || ''}"
                                data-vendedor-nombre="${(pedido.vendedor_nombre || '').replace(/"/g, '&quot;')}"
                            >
                        </td>
                    </tr>
                `;
            });

            html += '</tbody></table></div></div>';
        });

        pedidosContenedor.innerHTML = html;
        recalculateSuggestedPesoKg();
    }

    function recalculateSuggestedPesoKg() {
        let totalGramos = 0;
        const checks = pedidosContenedor.querySelectorAll('.item-check:checked');

        checks.forEach(check => {
            const detalleId = check.dataset.detalle;
            const row = pedidosContenedor.querySelector(`input.item-cantidad[data-detalle-id="${detalleId}"]`);
            if (!row) {
                return;
            }

            const qty = parseNum(row.value);
            const max = parseNum(row.max);
            const pesoGramos = parseNum(row.dataset.pesoGramos);

            if (!(qty > 0) || qty > max || !(pesoGramos > 0)) {
                return;
            }

            totalGramos += (qty * pesoGramos);
        });

        suggestedPesoKg = totalGramos > 0 ? (totalGramos / 1000) : null;

        if (!userEditedPesoKg && suggestedPesoKg !== null) {
            pesoKgInput.value = formatNum3(suggestedPesoKg);
        }

        if (suggestedPesoKg !== null) {
            const hint = userEditedPesoKg
                ? `Sugerido por items: ${formatNum3(suggestedPesoKg)} kg (editable).`
                : `Sugerido por items: ${formatNum3(suggestedPesoKg)} kg.`;
            pesoKgHint.textContent = hint;
        } else {
            pesoKgHint.textContent = 'Peso sugerido por items seleccionados.';
            if (!userEditedPesoKg) {
                pesoKgInput.value = '';
            }
        }
    }

    function buildHiddenItems() {
        hiddenItems.innerHTML = '';

        const checks = pedidosContenedor.querySelectorAll('.item-check:checked');
        let index = 0;

        checks.forEach(check => {
            const detalleId = check.dataset.detalle;
            const row = pedidosContenedor.querySelector(`input.item-cantidad[data-detalle-id="${detalleId}"]`);
            if (!row) {
                return;
            }

            const qty = parseFloat(row.value || '0');
            const max = parseFloat(row.max || '0');

            if (!(qty > 0) || qty > max) {
                return;
            }

            const fields = {
                pedido_id: row.dataset.pedidoId,
                pedido_detalle_id: row.dataset.detalleId,
                factura_numero: row.dataset.factura,
                producto_codigo: row.dataset.codigo,
                producto_descripcion: row.dataset.descripcion,
                unidad: row.dataset.unidad,
                peso_gramos: row.dataset.pesoGramos || 0,
                cantidad: qty,
                vendedor_codigo: row.dataset.vendedorCodigo,
                vendedor_nombre: row.dataset.vendedorNombre,
            };

            Object.keys(fields).forEach((key) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `items[${index}][${key}]`;
                input.value = fields[key] ?? '';
                hiddenItems.appendChild(input);
            });

            index += 1;
        });

        return index;
    }

    function inferVendedorName() {
        const checked = pedidosContenedor.querySelector('.item-check:checked');
        if (!checked) {
            return;
        }

        const qtyInput = pedidosContenedor.querySelector(`input.item-cantidad[data-detalle-id="${checked.dataset.detalle}"]`);
        if (qtyInput && qtyInput.dataset.vendedorNombre && !vendedorNombre.value) {
            vendedorNombre.value = qtyInput.dataset.vendedorNombre;
        }
    }

    async function loadPedidos(rif) {
        setFeedback('Cargando datos del cliente y pedidos aprobados...', 'info');
        pedidosContenedor.innerHTML = '<div class="p-3 text-muted">Cargando pedidos aprobados...</div>';
        let url = `{{ url('admin/logistica/clientes') }}/${encodeURIComponent(rif)}/pedidos`;
        if (cajaId) {
            url += `?caja_id=${cajaId}`;
        }

        try {
            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();
            pedidosData = data.pedidos || [];

            if (data.cliente) {
                telefono.value = telefono.value || data.cliente.telefono || '';
                direccionFiscal.value = direccionFiscal.value || data.cliente.direccion_fiscal || '';
                direccionEntrega.value = direccionEntrega.value || data.cliente.direccion_entrega || data.cliente.direccion_fiscal || '';
                ciudad.value = ciudad.value || data.cliente.ciudad || '';
                estado.value = estado.value || data.cliente.estado || '';
            }

            if (data.ultima_caja) {
                const sugestCiudad = data.ultima_caja.ciudad || '';
                const sugestEstado = data.ultima_caja.estado || '';
                const sugestDireccion = data.ultima_caja.direccion_entrega || '';

                updateSuggestionHint(ciudadHint, sugestCiudad ? `Sugerencia por ultima caja: ${sugestCiudad}` : '');
                updateSuggestionHint(estadoHint, sugestEstado ? `Sugerencia por ultima caja: ${sugestEstado}` : '');
                updateSuggestionHint(direccionHint, sugestDireccion ? 'Sugerencia de direccion de entrega disponible para este cliente.' : '');

                if (!ciudad.value && sugestCiudad) {
                    ciudad.value = sugestCiudad;
                }
                if (!estado.value && sugestEstado) {
                    estado.value = sugestEstado;
                }
                if (!direccionEntrega.value && sugestDireccion) {
                    direccionEntrega.value = sugestDireccion;
                }
            } else {
                updateSuggestionHint(ciudadHint, '');
                updateSuggestionHint(estadoHint, '');
                updateSuggestionHint(direccionHint, '');
            }

            if (data.sugerido_bulto_codigo && (!bultoCodigo.value || bultoCodigo.dataset.auto === '1')) {
                bultoCodigo.value = data.sugerido_bulto_codigo;
                bultoCodigo.dataset.auto = '1';
            }

            drawPedidos();
            inferVendedorName();
            recalculateSuggestedPesoKg();
        } catch (e) {
            setFeedback('No se pudieron cargar los datos del cliente o sus pedidos aprobados.', 'danger');
            pedidosContenedor.innerHTML = '<div class="p-3 text-danger">No se pudieron cargar los pedidos del cliente.</div>';
        }
    }

    function onClienteChanged() {
        const option = clienteSelect.options[clienteSelect.selectedIndex];

        if (!option || !option.value) {
            clienteRif.value = '';
            pedidosData = [];
            drawPedidos();
            setFeedback('Seleccione un cliente para cargar su información y pedidos aprobados.', 'info');
            return;
        }

        setClienteFromOption(option, true);
        if (bultoCodigo && !bultoCodigo.value) {
            bultoCodigo.dataset.auto = '1';
        }
        pedidosData = [];
        drawPedidos();

        if (clienteRif.value) {
            loadPedidos(clienteRif.value);
        }
    }

    clienteSelect.addEventListener('change', onClienteChanged);

    if (window.jQuery) {
        window.jQuery('#cliente_rif_select').on('change', onClienteChanged);
    }

    pedidosContenedor.addEventListener('input', function(e) {
        if (e.target.classList.contains('item-cantidad')) {
            const max = parseFloat(e.target.max || '0');
            const value = parseFloat(e.target.value || '0');
            if (value > max) {
                e.target.value = max;
            }

            recalculateSuggestedPesoKg();
        }
    });

    pedidosContenedor.addEventListener('change', function(e) {
        if (e.target.classList.contains('item-check')) {
            recalculateSuggestedPesoKg();
        }
    });

    pesoKgInput.addEventListener('input', function() {
        const current = (pesoKgInput.value || '').trim();
        if (current === '') {
            userEditedPesoKg = false;
            recalculateSuggestedPesoKg();
            return;
        }

        userEditedPesoKg = true;
        if (suggestedPesoKg !== null) {
            pesoKgHint.textContent = `Sugerido por items: ${formatNum3(suggestedPesoKg)} kg (editable).`;
        }
    });

    bultoCodigo.addEventListener('input', function() {
        bultoCodigo.dataset.auto = '0';
    });

    form.addEventListener('submit', function(e) {
        const count = buildHiddenItems();
        if (count < 1) {
            e.preventDefault();
            alert('Debe seleccionar al menos un producto con cantidad válida para la caja.');
            return false;
        }

        inferVendedorName();
        return true;
    });

    if (clienteSelect.value) {
        const option = clienteSelect.options[clienteSelect.selectedIndex];
        setClienteFromOption(option, false);
        if (!pedidosData.length) {
            loadPedidos(clienteSelect.value);
        } else {
            drawPedidos();
            recalculateSuggestedPesoKg();
        }
    } else {
        drawPedidos();
        recalculateSuggestedPesoKg();
        setFeedback('Seleccione un cliente para cargar su información y pedidos aprobados.', 'info');
    }

})();
</script>
@endsection
