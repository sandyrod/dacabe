@extends('layouts.app')

@php
$cliente = \App\Models\OrderClient::select('RIF', 'NOMBRE')->where('RIF', $clienteRif)->first();
@endphp

@section('titulo', config('app.name', 'Laravel') . ' - Pagos de Pedidos v2')
@section('titulo_header', 'Gestión de Pagos v2')
@section('subtitulo_header', 'Pendientes de Pago - ' . ($cliente->NOMBRE ?? 'Cliente'))

@section('styles')
<style>
    .total-container,
    #resumen-pago .card-body {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important;
        border: none !important;
        border-radius: 10px;
        padding: 25px 30px;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        color: #ffffff !important;
    }

    .total-container h4 {
        color: #e0e9ff;
        font-size: 1.2rem;
        margin-bottom: 10px;
        font-weight: 500;
    }

    .card-title {
        font-size: 1.1rem;
        font-weight: 600;
    }

    .card {
        display: flex;
        flex-direction: column;
        min-height: 100%;
        border-radius: 12px !important;
        overflow: hidden;
        transition: all 0.3s ease-in-out;
        transform: translateY(0);
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2) !important;
    }

    .card-body {
        flex: 1;
    }

    .total-value {
        font-size: 28px;
        font-weight: 700;
        color: #ffffff;
        margin-bottom: 20px;
    }

    .btn-procesar {
        background-color: #ffffff;
        color: #1e3c72;
        border: 2px solid #ffffff;
        font-weight: 600;
        padding: 10px 25px;
        border-radius: 6px;
        transition: all 0.3s ease;
    }

    .btn-procesar:hover {
        background-color: transparent;
        color: #ffffff;
    }

    .total-label {
        font-size: 18px;
        color: #e0e9ff;
        opacity: 0.9;
    }

    .table-active {
        background-color: rgba(0, 123, 255, 0.1) !important;
    }

    .payment-status {
        min-width: 60px;
        text-align: center;
    }

    .btn-action {
        font-weight: 600;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .btn-primary {
        background: linear-gradient(45deg, #4e73df, #224abe);
        border: none;
    }

    .btn-success {
        background: linear-gradient(45deg, #1cc88a, #13855c);
        border: none;
    }

    .btn-danger {
        background: linear-gradient(45deg, #e74a3b, #be2617);
        border: none;
    }
</style>
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css" rel="stylesheet">
@endsection

@section('content')
<div class="container">
    <div class="row mb-4 g-4">
        <!-- Card 1: Información de Moneda -->
        <div class="col-md-6 d-flex">
            <div class="card border-0 shadow-lg w-100 hover-effect"
                style="background: linear-gradient(145deg, #1a237e 0%, #283593 100%); border-radius: 12px !important;">
                <div class="card-header bg-transparent border-0 py-3">
                    <h3 class="text-white mb-0 d-flex align-items-center"
                        style="font-weight: 600; letter-spacing: 0.5px;">
                        <i class="fas fa-money-bill-wave me-2 pr-2"></i>
                        INFORMACIÓN DE PAGO
                    </h3>
                </div>
                <div class="card-body p-4 d-flex flex-column" style="padding: 1.5rem !important;">
                    <div class="d-flex align-items-center flex-grow-1">
                        <div class="flex-grow-1">
                            <div class="d-flex flex-column">
                                <div class="mb-2 d-flex align-items-center">
                                    <i class="fas fa-user text-white-50" style="width: 24px; text-align: center;"></i>
                                    <span
                                        class="text-white ms-2">{{ $cliente->NOMBRE ?? 'Cliente no seleccionado' }}</span>
                                </div>
                                <div class="mb-2 d-flex align-items-center">
                                    <i class="fas fa-shopping-cart text-white-50"
                                        style="width: 24px; text-align: center;"></i>
                                    <span class="text-white">
                                        <span id="pedidos-seleccionados" class="fw-bold"> Pedidos seleccionados:
                                            {{ is_string($pedidos_seleccionados) ? count(explode(',', $pedidos_seleccionados)) : (is_array($pedidos_seleccionados) ? count($pedidos_seleccionados) : 0) }}</span>
                                    </span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-money-bill-wave text-white-50"
                                        style="width: 24px; text-align: center;"></i>
                                    <span class="text-white">
                                        Moneda:
                                        <span id="tipo-moneda" class="badge bg-white text-dark ms-1">
                                            {{ session('pago_v2_cliente.tipo_pago') == 'divisa_total'
                                                    ? 'Divisa Total'
                                                    : (session('pago_v2_cliente.tipo_pago') == 'divisa_parcial'
                                                        ? 'Divisa Parcial'
                                                        : 'Bolívares') }}
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Total a Pagar -->
        <div class="col-md-6 d-flex">
            <div class="card border-0 shadow-lg w-100 hover-effect"
                style="background: linear-gradient(145deg, #0d47a1 0%, #1565c0 100%); border-radius: 12px !important;">
                <div class="card-header bg-transparent border-0 py-3">
                    <h3 class="text-white mb-0 d-flex align-items-center"
                        style="font-weight: 600; letter-spacing: 0.5px;">
                        <i class="fas fa-calculator me-2 pr-2"></i>
                        TOTAL A PAGAR
                    </h3>
                </div>
                <div class="card-body p-4 d-flex flex-column" style="padding: 1.5rem !important;">
                    <div class="d-flex align-items-center flex-grow-1">
                        <div class="flex-grow-1">
                            @if (session('pago_v2_cliente.tipo_pago') == 'bs')
                            <!-- Mostrar en Bolívares con conversión a USD -->
                            <div class="mb-1">
                                <div class="d-flex align-items-end">
                                    <span class="display-4 fw-bold text-white me-2 lh-1"
                                        id="total-bolivares">{{ number_format($total_pagar, 2, ',', '.') }}</span>
                                    <span class="h4 text-white-50 mb-1">Bs.</span>
                                </div>

                                <div class="mt-2">
                                    <div class="d-flex align-items-center text-white-50 small">
                                        <span class="me-1">Tasa: Bs.
                                            {{ number_format($tasa_bcv, 2, ',', '.') }}</span>
                                        <i class="ml-1 mr-1 fas fa-exchange-alt me-1"></i>
                                        <span>$ {{ number_format($total_pagar / $tasa_bcv, 2, ',', '.') }} </span>
                                    </div>
                                </div>
                            </div>
                            @else
                            <!-- Mostrar en Dólares con conversión a Bs. -->
                            <div class="mb-1">
                                @if (isset($total_pagar_divisa_parcial) && $total_pagar_divisa_parcial > 0)
                                <div class="d-flex align-items-end">
                                    <span class="display-4 fw-bold me-1" style="color:#1cc88a;"
                                        id="total-bolivares2">
                                        {{ number_format($total_pagar_divisa_parcial, 2, ',', '.') }}</span>
                                    <span class="h4 text-white-50 mb-1" style="color:#1cc88a !important;">
                                        Bs.</span>
                                </div>
                                @endif
                                <div class="d-flex align-items-end">
                                    <span class="display-4 fw-bold text-white me-2 lh-1" id="total-dolares2">
                                        {{ number_format($total_pagar, 2, ',', '.') }}</span>
                                    <span class="h4 text-white-50 mb-1"> USD</span>
                                </div>

                                <div class="mt-2">
                                    <div class="d-flex align-items-center text-white-50 small">
                                        <span class="me-1">Tasa: Bs. {{ number_format($tasa_bcv, 2, ',', '.') }}
                                        </span>
                                        <i class="ml-1 mr-1 fas fa-exchange-alt me-1"></i>
                                        @php
                                        $total_pagar_tasa_bcv =
                                        (float) ($total_pagar ?? 0) * (float) ($tasa_bcv ?? 1);
                                        @endphp
                                        <span>Bs. {{ number_format($total_pagar_tasa_bcv, 2, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <h5 class="mb-0 text-dark">Pedidos Pendientes de Pago (v2)</h5>
                        <button type="button" onclick="window.history.back()" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Volver
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                    @endif
                    @if (session('danger'))
                    <div class="alert alert-danger">
                        {{ session('danger') }}
                    </div>
                    @endif

                    <div class="alert alert-info mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-credit-card me-2 mr-2"></i>
                                <span class="me-3 fw-bold">
                                    {{ session('pago_v2_cliente.tipo_pago') == 'divisa_total'
                                            ? 'Divisa Total'
                                            : (session('pago_v2_cliente.tipo_pago') == 'divisa_parcial'
                                                ? 'Divisa Parcial'
                                                : 'Bolívares') }}</span>
                                @php
                                $isDollar = str_contains(session('pago_v2_cliente.forma_pago_desc', ''), '$');
                                $currency = $isDollar ? 'USD' : '$';
                                @endphp
                                <span class="d-none ml-2 badge bg-{{ $isDollar ? 'success' : 'danger' }} px-3 py-2">
                                    <i class="fas fa-{{ $isDollar ? 'dollar-sign' : 'money-bill-wave' }} me-1"></i>
                                    {{ $currency }}
                                </span>
                            </div>
                            <div class="fw-bold fs-5">
                                <i class="fas fa-money-bill-wave me-3"></i>
                                @php
                                $tipoPago = session('pago_v2_cliente.tipo_pago');
                                $monto = session('pago_v2_cliente.monto', 0);
                                $tasaBcvValue = $tasa_bcv ?? 1;
                                $montoMostrar = $tipoPago === 'bolivares' ? $total_pagar : $total_pagar;
                                @endphp
                                Monto a pagar: <b>{{ number_format($total_pagar, 2, ',', '.') }}</b>
                                {{ $tipoPago === 'bolivares' ? 'Bs.' : '$' }}
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('vendedores.pagos_new.store') }}"
                        id="multiplePaymentsForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="metodo_pago" value="{{ session('pago_v2_cliente.metodo_pago') }}">
                        <input type="hidden" name="moneda_pago"
                            value="{{ session('pago_v2_cliente.tipo_pago') == 'divisa_total'
                                    ? 'Divisa Total'
                                    : (session('pago_v2_cliente.tipo_pago') == 'divisa_parcial'
                                        ? 'Divisa Parcial'
                                        : 'Bolívares') }}">
                        <input type="hidden" name="total_iva" value="{{ $total_iva ?? 0 }}">
                        <input type="hidden" name="total_retencion" value="{{ $total_retencion ?? 0 }}">
                        <input type="hidden" name="total_descuento_pago" value="{{ $total_descuento_pago ?? 0 }}">
                        <input type="hidden" name="detallePedidos" value="{{ $detallePedidos ?? '' }}">
                        <input type="hidden" name="rif" value="{{ $clienteRif }}">
                        <input type="hidden" name="pedidos_seleccionados" value="{{ $pedidos_seleccionados }}">
                        <input type="hidden" name="tasa_bcv" value="{{ $tasa_bcv }}">

                        <div class="row">
                            <!-- Hidden field to store the final amount -->
                            <input type="hidden" id="monto_total_oculto" name="monto_total" value="{{ $total_pagar ?? 0 }}">

                            <div class="col-md-3 mb-3">
                                <label for="monto_bs" class="form-label">Monto a Registrar</label>
                                <input type="number" class="form-control @error('monto_bs') is-invalid @enderror"
                                    id="monto_bs" name="monto_bs" step="0.01"
                                    value="{{ number_format($total_pagar ?? 0, 2, '.', '') }}">
                                @error('monto_bs')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="pago_destino_id" class="form-label">Banco Receptor</label>
                                <select class="form-select @error('pago_destino_id') is-invalid @enderror"
                                    id="pago_destino_id" name="pago_destino_id">
                                    <option value="">Seleccione un destino</option>
                                    @foreach ($pago_destinos as $destino)
                                    <option value="{{ $destino->id }}">{{ $destino->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('pago_destino_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="fecha_pago" class="form-label">Fecha de Pago</label>
                                <input type="date"
                                    class="form-control @error('fecha_pago') is-invalid @enderror" id="fecha_pago"
                                    name="fecha_pago" value="{{ date('Y-m-d') }}">
                                @error('fecha_pago')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="tpago_id" class="form-label">Tipo de Pago</label>
                                <select class="form-select @error('tpago_id') is-invalid @enderror"
                                    id="tpago_id" name="tpago_id">
                                    <option value="" disabled>Seleccione el tipo</option>
                                    @foreach ($tipos_pago as $tipo)
                                    <option value="{{ $tipo->CPAGO }}"
                                        {{ $loop->first ? 'selected' : '' }}>
                                        {{ $tipo->DPAGO }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('tpago_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>

                            @if (isset($bancos) && count($bancos) > 0)
                            <div class="col-md-3 mb-3" id="campo_banco_origen">
                                <label for="banco_codigo" class="form-label">Banco Origen</label>
                                <select class="form-select @error('banco_codigo') is-invalid @enderror"
                                    id="banco_codigo" name="banco_codigo">
                                    <option value="" disabled>Seleccione el banco</option>
                                    @foreach ($bancos as $banco)
                                    <option value="{{ $banco->codigo }}">
                                        {{ $banco->nombre }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            <div class="col-md-3 mb-3">
                                <label for="referencia" class="form-label">Referencia</label>
                                <input type="text" class="form-control @error('referencia') is-invalid @enderror"
                                    id="referencia" name="referencia" placeholder="Número de confirmación">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="descripcion" class="form-label">Detalles del pago</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="1"></textarea>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="comprobantes" class="form-label">Comprobantes de Pago (opcional)</label>
                                <input type="file" class="form-control" id="comprobantes" name="comprobantes[]" multiple accept="image/*,.pdf">
                                <small class="text-muted">Puede seleccionar varios archivos.</small>
                            </div>
                        </div>

                        <div class="row justify-content-center mt-3 mb-4">
                            <div class="col-auto">
                                <button type="button" id="btn-agregar-pago"
                                    class="btn btn-primary btn-action py-2 px-4 shadow-sm"
                                    style="min-width: 200px;">
                                    <i class="fas fa-plus-circle me-2"></i>Agregar Pago
                                </button>
                            </div>
                            <div class="col-auto">
                                <button type="submit" id="btn-aplicar-cambios"
                                    class="btn btn-success btn-action py-2 px-4 shadow-sm" style="min-width: 200px;"
                                    disabled>
                                    <i class="fas fa-check-circle me-2"></i>Finalizar Registro
                                </button>
                            </div>
                        </div>

                        <!-- Lista de Pagos Agregados -->
                        <div class="card mb-4 border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h5 class="mb-0 small fw-bold text-uppercase text-muted">Pagos Registrados en esta sesión</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0" id="pagos-lista">
                                        <thead class="bg-light small">
                                            <tr>
                                                <th>Referencia</th>
                                                <th>Origen</th>
                                                <th>Destino</th>
                                                <th class="text-end">Monto</th>
                                                <th>Fecha</th>
                                                <th class="text-center">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="pagos-body">
                                            <!-- Los pagos se agregarán aquí dinámicamente -->
                                        </tbody>
                                        <tfoot class="table-light fw-bold">
                                            <tr>
                                                <td colspan="3" class="text-end">Total Registrado:</td>
                                                <td id="total-pagado-acumulado" class="text-end text-primary">0,00</td>
                                                <td colspan="2"></td>
                                            </tr>
                                            <tr>
                                                <td colspan="3" class="text-end">Saldo Restante:</td>
                                                <td id="saldo-restante-final" class="text-end text-danger">
                                                    {{ number_format($total_pagar, 2, ',', '.') }}
                                                </td>
                                                <td colspan="2"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                    <div class="text-center py-4" id="sin-pagos">
                                        <div class="text-muted mb-2"><i class="fas fa-info-circle me-1"></i> No hay pagos registrados todavía</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="pagos_json" id="pagos-json">
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
<script>
    $(document).ready(function() {
        const totalA_Cubrir = {
            {
                $total_pagar
            }
        };
        const tipoMoneda = '{{ session('pago_v2_cliente.tipo_pago') === '
        bolivares ' ? '
        Bs.
        ' : '
        $ ' }}';
        let pagos = [];

        function actualizarInterfaz() {
            const tbody = $('#pagos-body');
            tbody.empty();

            let totalAcumulado = 0;

            if (pagos.length === 0) {
                $('#sin-pagos').show();
                $('#pagos-lista').hide();
            } else {
                $('#sin-pagos').hide();
                $('#pagos-lista').show();

                pagos.forEach((pago, index) => {
                    totalAcumulado += parseFloat(pago.monto);

                    const tr = `
                            <tr>
                                <td>${pago.referencia || '<span class="text-muted">N/A</span>'}</td>
                                <td>${pago.banco_origen || '<span class="text-muted">-</span>'}</td>
                                <td>${pago.banco_destino || '<span class="text-muted">-</span>'}</td>
                                <td class="text-end fw-bold">${tipoMoneda} ${pago.monto.toLocaleString('es-VE', {minimumFractionDigits: 2})}</td>
                                <td>${pago.fecha_pago}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-outline-danger btn-sm border-0 btn-eliminar-pago" data-index="${index}">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    tbody.append(tr);
                });
            }

            const saldoRestante = Math.max(0, totalA_Cubrir - totalAcumulado);

            $('#total-pagado-acumulado').text(totalAcumulado.toLocaleString('es-VE', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) + ' ' + tipoMoneda);
            $('#saldo-restante-final').text(saldoRestante.toLocaleString('es-VE', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) + ' ' + tipoMoneda);

            if (saldoRestante <= 0.01) {
                $('#saldo-restante-final').removeClass('text-danger').addClass('text-success');
                $('#btn-aplicar-cambios').prop('disabled', false).addClass('animate__animated animate__pulse animate__infinite');
            } else {
                $('#saldo-restante-final').removeClass('text-success').addClass('text-danger');
                $('#btn-aplicar-cambios').prop('disabled', true).removeClass('animate__animated animate__pulse animate__infinite');
            }

            $('#pagos-json').val(JSON.stringify(pagos));
            $('#monto_bs').val(saldoRestante.toFixed(2));
        }

        $('#btn-agregar-pago').on('click', function() {
            const monto = parseFloat($('#monto_bs').val());
            const bancoReceptor = $('#pago_destino_id option:selected').text();
            const bancoReceptorId = $('#pago_destino_id').val();
            const tipoPagoId = $('#tpago_id').val();
            const tipoPagoDesc = $('#tpago_id option:selected').text();
            const bancoOrigen = $('#banco_codigo option:selected').text();
            const bancoOrigenCod = $('#banco_codigo').val();
            const referencia = $('#referencia').val();
            const fecha = $('#fecha_pago').val();
            const desc = $('#descripcion').val();

            if (isNaN(monto) || monto <= 0) {
                Swal.fire('Error', 'Ingrese un monto válido', 'error');
                return;
            }

            const totalYaAgregado = pagos.reduce((acc, p) => acc + p.monto, 0);
            if (monto > (totalA_Cubrir - totalYaAgregado) + 0.01) {
                Swal.fire('Atención', 'El monto ingresado excede el saldo restante', 'warning');
                return;
            }

            pagos.push({
                monto: monto,
                pago_destino_id: bancoReceptorId,
                banco_destino: bancoReceptorId ? bancoReceptor : '',
                tpago_id: tipoPagoId,
                tipo_pago_desc: tipoPagoDesc,
                banco_codigo: bancoOrigenCod,
                banco_origen: bancoOrigenCod ? bancoOrigen : '',
                referencia: referencia,
                fecha_pago: fecha,
                descripcion: desc
            });

            // Limpiar campos para el siguiente
            $('#referencia').val('');
            $('#descripcion').val('');

            actualizarInterfaz();
        });

        $(document).on('click', '.btn-eliminar-pago', function() {
            const index = $(this).data('index');
            pagos.splice(index, 1);
            actualizarInterfaz();
        });

        $('#multiplePaymentsForm').on('submit', function() {
            Swal.fire({
                title: 'Procesando...',
                text: 'Guardando registro de pagos',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        });

        // Inicializar
        actualizarInterfaz();
    });
</script>
@endsection