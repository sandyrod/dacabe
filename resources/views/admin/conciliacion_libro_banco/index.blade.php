@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel') . ' - Conciliación Bancaria')
@section('titulo_header', 'Conciliación Bancaria')
@section('subtitulo_header', 'Libro contable bancario mensual por banco')

@section('styles')
<style>
    .cb-header {
        background: linear-gradient(120deg, #113a67 0%, #2d5b88 100%);
        color: #fff;
        border-radius: 10px;
        padding: 16px 18px;
        margin-bottom: 16px;
    }
    .cb-kpi {
        background: #f7f9fc;
        border: 1px solid #e3e8ef;
        border-radius: 10px;
        padding: 10px 12px;
    }
    .cb-table th {
        background: #1f4f82;
        color: #fff;
        border-color: #1f4f82;
        vertical-align: middle;
    }
    .cb-sale {
        background: #f7c5c5;
        text-align: right;
        font-weight: 700;
    }
    .cb-entra {
        background: #ffd8b2;
        text-align: right;
        font-weight: 700;
    }
    .cb-total-table {
        max-width: 560px;
        margin-left: auto;
    }
    @media print {
        .no-print {
            display: none !important;
        }
    }
</style>
@endsection

@section('content')
@php
    $fmt = function ($value) {
        return number_format((float) $value, 2, ',', '.');
    };
@endphp
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 pl-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="cb-header d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h4 class="mb-0">LIBRO CONTABLE BANCO</h4>
            <small>Conciliación por banco, mes y año</small>
        </div>
        <div class="no-print mt-2 mt-md-0">
            <a class="btn btn-light btn-sm" href="{{ route('admin.conciliacion_bancaria.export', ['month' => $selectedMonth, 'year' => $selectedYear, 'banco_codigo' => $selectedBancoCodigo]) }}">
                <i class="fas fa-file-excel"></i> Exportar Excel
            </a>
            <button type="button" class="btn btn-outline-light btn-sm" onclick="window.print();">
                <i class="fas fa-print"></i> Imprimir
            </button>
        </div>
    </div>

    <div class="card mb-3 no-print">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.conciliacion_bancaria.index') }}">
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>Banco</label>
                        <select name="banco_codigo" class="form-control" required>
                            @foreach($bancos as $b)
                                <option value="{{ $b->CODIGO }}" {{ $selectedBancoCodigo === (string)$b->CODIGO ? 'selected' : '' }}>
                                    {{ $b->NOMBRE }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Mes</label>
                        <select name="month" class="form-control" required>
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ (int)$selectedMonth === $m ? 'selected' : '' }}>{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Año</label>
                        <input type="number" min="2000" max="2100" name="year" class="form-control" value="{{ $selectedYear }}" required>
                    </div>
                    <div class="form-group col-md-3 align-self-end">
                        <button class="btn btn-primary btn-block">Actualizar Vista</button>
                    </div>
                </div>
            </form>

            <div class="row">
                <div class="col-md-6">
                    <div class="cb-kpi">
                        <form method="POST" action="{{ route('admin.conciliacion_bancaria.saldo_inicial') }}" class="form-inline">
                            @csrf
                            <input type="hidden" name="periodo_id" value="{{ $periodo->id }}">
                            <label class="mr-2 font-weight-bold">Saldo inicial</label>
                            <input type="number" step="0.01" class="form-control mr-2" name="saldo_inicial" value="{{ $totales['saldo_inicial'] }}" required>
                            <button class="btn btn-outline-primary btn-sm">Guardar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3 no-print">
        <div class="card-header">
            <strong>Agregar Movimiento</strong>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.conciliacion_bancaria.entradas.store') }}">
                @csrf
                <input type="hidden" name="periodo_id" value="{{ $periodo->id }}">
                <div class="form-row">
                    <div class="form-group col-md-2">
                        <label>Fecha</label>
                        <input type="date" name="fecha" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Tipo</label>
                        <select name="tipo_movimiento" class="form-control" required>
                            <option value="entrada">Entrada</option>
                            <option value="salida">Salida</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Descripcion</label>
                        <input type="text" name="descripcion" class="form-control" placeholder="Ej: TRASPASO ENTRE CUENTAS" required>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Monto</label>
                        <input type="number" step="0.01" min="0.01" name="monto" class="form-control" required>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Concepto Frecuente</label>
                        <select id="concepto_selector" class="form-control">
                            <option value="">Seleccionar</option>
                            @foreach($conceptos as $c)
                                <option value="{{ $c->id }}" data-nombre="{{ $c->nombre }}" data-monto="{{ $c->monto_sugerido }}" data-fijo="{{ $c->monto_fijo }}">
                                    {{ $c->nombre }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="concepto_id" id="concepto_id">
                    </div>
                    <div class="form-group col-md-1 align-self-end">
                        <button class="btn btn-success btn-block">Agregar</button>
                    </div>
                </div>
            </form>

            <hr>
            <form method="POST" action="{{ route('admin.conciliacion_bancaria.conceptos.store') }}" class="form-row">
                @csrf
                <div class="form-group col-md-5">
                    <label>Nuevo Concepto Frecuente</label>
                    <input type="text" name="nombre" class="form-control" placeholder="Nombre del concepto" required>
                </div>
                <div class="form-group col-md-2">
                    <label>Monto sugerido</label>
                    <input type="number" step="0.01" min="0" name="monto_sugerido" class="form-control">
                </div>
                <div class="form-group col-md-2">
                    <label>Monto fijo</label>
                    <select name="monto_fijo" class="form-control">
                        <option value="0">No</option>
                        <option value="1">Sí</option>
                    </select>
                </div>
                <div class="form-group col-md-3 align-self-end">
                    <button class="btn btn-outline-secondary btn-block">Guardar concepto</button>
                </div>
            </form>
        </div>
    </div>

    @php
        $movimientos = collect();
        foreach($salidas as $s) {
            $movimientos->push((object) [
                'tipo' => 'entrada',
                'fecha' => $s->fecha,
                'descripcion' => $s->descripcion,
                'monto' => (float) $s->monto,
            ]);
        }
        foreach($entradas as $e) {
            $movimientos->push((object) [
                'tipo' => 'entrada',
                'fecha' => $e->fecha,
                'descripcion' => $e->descripcion,
                'monto' => (float) $e->monto,
                'entrada_id' => $e->id,
                'concepto_id' => $e->concepto_id,
                'tipo_movimiento' => 'entrada',
            ]);
        }
        foreach($salidas_manuales as $s) {
            $movimientos->push((object) [
                'tipo' => 'salida',
                'fecha' => $s->fecha,
                'descripcion' => $s->descripcion,
                'monto' => (float) $s->monto,
                'entrada_id' => $s->id,
                'concepto_id' => $s->concepto_id,
                'tipo_movimiento' => 'salida',
            ]);
        }
        $movimientos = $movimientos->sortBy('fecha')->values();
    @endphp

    <div class="card mb-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0 cb-table">
                    <thead>
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th style="width: 120px;">Fecha</th>
                            <th>Detalle</th>
                            <th style="width: 160px;" class="text-right">ENTRADA</th>
                            <th style="width: 160px;" class="text-right">SALIDA</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movimientos as $idx => $row)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($row->fecha)->format('d/m/Y') }}</td>
                                <td>
                                    {{ $row->descripcion }}
                                    @if(isset($row->entrada_id))
                                        <div class="no-print mt-1 d-flex" style="gap:4px;">
                                            <button class="btn btn-xs btn-outline-primary" data-toggle="collapse" data-target="#edit_{{ $row->entrada_id }}">Editar</button>
                                            <form method="POST" action="{{ route('admin.conciliacion_bancaria.entradas.destroy', $row->entrada_id) }}" onsubmit="return confirm('¿Eliminar entrada?');">
                                                @csrf
                                                <button class="btn btn-xs btn-outline-danger">Eliminar</button>
                                            </form>
                                        </div>
                                        <div class="collapse mt-2 no-print" id="edit_{{ $row->entrada_id }}">
                                            <form method="POST" action="{{ route('admin.conciliacion_bancaria.entradas.update', $row->entrada_id) }}">
                                                @csrf
                                                <div class="form-row">
                                                    <div class="col-md-2 mb-1"><input type="date" name="fecha" class="form-control form-control-sm" value="{{ $row->fecha }}" required></div>
                                                    <div class="col-md-5 mb-1"><input type="text" name="descripcion" class="form-control form-control-sm" value="{{ $row->descripcion }}" required></div>
                                                    <div class="col-md-2 mb-1"><input type="number" step="0.01" min="0.01" name="monto" class="form-control form-control-sm" value="{{ $row->monto }}" required></div>
                                                    <div class="col-md-2 mb-1">
                                                        <select name="concepto_id" class="form-control form-control-sm">
                                                            <option value="">Sin concepto</option>
                                                            @foreach($conceptos as $c)
                                                                <option value="{{ $c->id }}" {{ (int)$row->concepto_id === (int)$c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-1 mb-1"><button class="btn btn-sm btn-primary btn-block">OK</button></div>
                                                </div>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                                <td class="{{ $row->tipo === 'entrada' ? 'cb-entra' : '' }}">{{ $row->tipo === 'entrada' ? $fmt($row->monto) : '' }}</td>
                                <td class="{{ $row->tipo === 'salida' ? 'cb-sale' : '' }}">{{ $row->tipo === 'salida' ? $fmt($row->monto) : '' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">No hay movimientos para este banco/periodo.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <table class="table table-bordered table-sm cb-total-table">
        <tr>
            <th>SALDO INICIAL</th>
            <td class="text-right">{{ $fmt($totales['saldo_inicial']) }}</td>
        </tr>
        <tr>
            <th>TOTAL CARGOS</th>
            <td class="text-right">{{ $fmt($totales['total_cargos']) }}</td>
        </tr>
        <tr>
            <th>TOTAL ABONOS</th>
            <td class="text-right text-success">{{ $fmt($totales['total_abonos']) }}</td>
        </tr>
        <tr>
            <th>SALDO SEGÚN LIBRO BANCOS</th>
            <td class="text-right font-weight-bold">{{ $fmt($totales['saldo_final']) }}</td>
        </tr>
    </table>
</div>
@endsection

@section('scripts')
<script>
    (function () {
        var selector = document.getElementById('concepto_selector');
        if (!selector) {
            return;
        }

        selector.addEventListener('change', function () {
            var opt = selector.options[selector.selectedIndex];
            var conceptoId = document.getElementById('concepto_id');
            var descInput = document.querySelector('input[name="descripcion"]');
            var montoInput = document.querySelector('input[name="monto"]');

            if (!opt || !conceptoId || !descInput || !montoInput) {
                return;
            }

            conceptoId.value = opt.value || '';
            if (!opt.value) {
                return;
            }

            var nombre = opt.getAttribute('data-nombre') || '';
            var monto = opt.getAttribute('data-monto') || '';
            var fijo = opt.getAttribute('data-fijo') === '1';

            if (nombre !== '') {
                descInput.value = nombre;
            }

            if (monto !== '') {
                montoInput.value = parseFloat(monto).toFixed(2);
                montoInput.readOnly = fijo;
            } else {
                montoInput.readOnly = false;
            }
        });
    })();
</script>
@endsection
