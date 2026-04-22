@extends('layouts.app')

@section('titulo_header', 'Gestión de Pagos')
@section('subtitulo_header', 'Registrar Pago')
@section('styles')
<style>
    .card {
        margin-bottom: 1rem;
    }
    .card-header {
        padding: 1rem;
        border-bottom: 1px solid rgba(0,0,0,.125);
    }
    .card-body {
        padding: 1.5rem;
    }
    .form-group {
        margin-bottom: 1.5rem;
    }
    .alert {
        margin-bottom: 1.5rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    
    @if(session('danger'))
    <div class="alert alert-danger">
        {{ session('danger') }}
    </div>
    @endif

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif
    
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Registrar Pago para Pedido #{{ $pedido->referencia }}</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('vendedores.pagos.store') }}" id="pagoForm" enctype="multipart/form-data">
                        @csrf
                        
                        <input type="hidden" name="pedido_id" value="{{ $pedido->id }}">

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="monto">Monto a Pagar REF</label>
                                    <input type="number" 
                                           class="form-control @error('monto') is-invalid @enderror" 
                                           id="monto" 
                                           name="monto" 
                                           step="0.01"
                                           value="{{ old('monto') }}"
                                           required>
                                    @error('monto')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="rate">Tasa de Cambio</label>
                                    <input type="number" 
                                           class="form-control @error('rate') is-invalid @enderror" 
                                           id="rate" 
                                           name="rate" 
                                           step="0.01" 
                                           min="0" 
                                           value="{{ old('rate') }}"
                                           required>
                                    @error('rate')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="monto_bs">Monto en Bs</label>
                                    <input type="number" 
                                           class="form-control @error('monto_bs') is-invalid @enderror" 
                                           id="monto_bs" 
                                           name="monto_bs" 
                                           step="0.01"
                                           value="{{ old('monto_bs') }}"
                                           required>
                                    @error('monto_bs')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="fecha_pago">Fecha de Pago</label>
                                    <input type="date" 
                                           class="form-control @error('fecha_pago') is-invalid @enderror" 
                                           id="fecha_pago" 
                                           name="fecha_pago"
                                           value="{{ old('fecha_pago', date('Y-m-d')) }}"
                                           required>
                                    @error('fecha_pago')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="tpago_id">Tipo de Pago</label>
                                    <select class="form-control @error('tpago_id') is-invalid @enderror" 
                                        id="tpago_id" 
                                        name="tpago_id" 
                                        required>
                                        <option value="" disabled>Seleccione el tipo de pago</option>
                                        @foreach($tipos_pago as $i => $tipo)
                                        <option value="{{ $tipo->CPAGO }}" 
                                            {{ old('tpago_id', $tipos_pago[0]->CPAGO ?? '') == $tipo->CPAGO ? 'selected' : '' }}>
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
                            </div>

                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="banco_codigo">Banco</label>
                                    <select class="form-control @error('banco_codigo') is-invalid @enderror" 
                                        id="banco_codigo" 
                                        name="banco_codigo" 
                                        required>
                                        <option value="" disabled>Seleccione el banco</option>
                                        @foreach($bancos as $i => $banco)
                                        <option value="{{ $banco->CODIGO }}" 
                                            {{ old('banco_codigo', $bancos[0]->CODIGO ?? '') == $banco->CODIGO ? 'selected' : '' }}>
                                            {{ $banco->NOMBRE }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('banco_codigo')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="referencia">Referencia</label>
                                    <input type="text" 
                                           class="form-control @error('referencia') is-invalid @enderror" 
                                           id="referencia" 
                                           name="referencia" 
                                           value="{{ old('referencia') }}"
                                           required>
                                    @error('referencia')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="pago_destino_id">Pago realizado a</label>
                                    <select class="form-control @error('pago_destino_id') is-invalid @enderror" 
                                            id="pago_destino_id" 
                                            name="pago_destino_id" 
                                            required>
                                        <option value="">Seleccione un destino</option>
                                        @foreach($pago_destinos as $destino)
                                            <option value="{{ $destino->id }}" {{ old('pago_destino_id') == $destino->id ? 'selected' : '' }}>{{ $destino->nombre }}</option>
                                        @endforeach
                                    </select>
                                    @error('pago_destino_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6 d-none">
                                <div class="form-group mb-3">
                                    <label for="comprobante">Comprobante de Pago</label>
                                    <input type="file" 
                                           class="form-control @error('comprobante') is-invalid @enderror" 
                                           id="comprobante" 
                                           name="comprobante" 
                                           accept=".pdf,.jpg,.jpeg,.png">
                                    <small class="form-text text-muted">Formatos permitidos: PDF, JPG, JPEG, PNG</small>
                                    @error('comprobante')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <strong>Saldo Pendiente:</strong> {{ $saldoPendiente }} Bs
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                Registrar Pago
                            </button>
                            <a href="{{ route('vendedores.pagos.index') }}" class="btn btn-secondary">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const URL_TASA = '{{url("get-tasa-bcv")}}';
    $(document).ready(function() {
        // Función para formatear números con separador de miles y 2 decimales
        function formatNumber(number) {
            // Asegurarse de que number sea un número
            number = parseFloat(number) || 0;
            return number.toLocaleString('es-ES', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
        
        // Función para parsear un valor formateado a número
        function parseFormattedNumber(formattedValue) {
            if (!formattedValue) return 0;
            // Para inputs de tipo number, parseFloat funciona directamente
            return parseFloat(formattedValue) || 0;
        }

        // Función para calcular y actualizar monto_bs
        function updateMontoBs() {
            // Con input type="number", el valor ya es numérico
            const monto = parseFloat($('#monto').val()) || 0;
            const rate = parseFloat($('#rate').val()) || 0;
            const monto_bs = monto * rate;
            
            console.log('Calculando monto_bs:', monto, '*', rate, '=', monto_bs);
            
            // Actualizar el campo monto_bs con el valor calculado
            $('#monto_bs').val(monto_bs.toFixed(2));
        }

        // Función para calcular y actualizar monto cuando cambia monto_bs
        function updateMonto() {
            // Con input type="number", el valor ya es numérico
            const monto_bs = parseFloat($('#monto_bs').val()) || 0;
            const rate = parseFloat($('#rate').val()) || 0;
            
            console.log('Calculando monto:', monto_bs, '/', rate);
            
            // Evitar división por cero
            if (rate > 0) {
                const monto = monto_bs / rate;
                
                console.log('Resultado monto:', monto);
                
                // Actualizar el campo monto con el valor calculado
                document.getElementById('monto').value = monto.toFixed(2);
            }
        }

        // Manejar cambios en los campos monto y rate
        $('#monto').on('input', function() {
            // Actualizar monto_bs
            updateMontoBs();
        });
        
        $('#rate').on('input', function() {
            updateMontoBs();
        });
        
        // Manejar cambios en el campo monto_bs
        $('#monto_bs').on('input', function() {
            // Actualizar monto inmediatamente
            updateMonto();
        });

        // Obtener tasa de cambio
        $.ajax({
            url: URL_TASA,
            type: 'GET',
            success: function(response) {
                $('#rate').val(response.data.rate);
                // Actualizar monto_bs cuando se obtiene la tasa
                updateMontoBs();
            }
        });
    });
</script>
@endsection
