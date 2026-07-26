@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel') . ' - Conciliación Bancaria')
@section('titulo_header', 'Conciliación Bancaria')
@section('subtitulo_header', 'Control gerencial de ingresos por banco y forma de pago')

@section('content')
@php
    $fmtUsd = function ($value) {
        return '$ ' . number_format((float) $value, 2, ',', '.');
    };
    $fmtBs = function ($value) {
        return 'Bs. ' . number_format((float) $value, 2, ',', '.');
    };
@endphp

<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('info'))
        <div class="alert alert-info">{{ session('info') }}</div>
    @endif

    @if(!$tablaMovimientosDisponible)
        <div class="alert alert-warning">
            El histórico Debe/Haber por reclasificación requiere la tabla <strong>conciliacion_bancaria_movimientos</strong>.
            Ejecuta el SQL manual suministrado para habilitar trazabilidad completa.
        </div>
    @endif

    <div class="row mb-3">
        <div class="col-md-4 mb-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted d-block">Total Ingresos (Haber)</small>
                    <h4 class="mb-1">{{ $fmtUsd($resumen->total_haber_usd ?? 0) }}</h4>
                    <small class="text-primary">{{ $fmtBs($resumen->total_haber_bs ?? 0) }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted d-block">Total Aprobado</small>
                    <h4 class="mb-1">{{ $fmtUsd($resumen->total_aprobado_usd ?? 0) }}</h4>
                    <small class="text-success">{{ $fmtBs($resumen->total_aprobado_bs ?? 0) }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted d-block">Registros de Pago</small>
                    <h4 class="mb-1">{{ number_format((int) ($resumen->total_pagos ?? 0), 0, ',', '.') }}</h4>
                    <small class="text-secondary">Filtrados según criterios actuales</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <strong>Filtros de Conciliación</strong>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.conciliacion_bancaria.index') }}">
                <div class="form-row">
                    <div class="form-group col-md-2">
                        <label>Desde</label>
                        <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
                    </div>
                    <div class="form-group col-md-2">
                        <label>Hasta</label>
                        <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
                    </div>
                    <div class="form-group col-md-2">
                        <label>Vendedor</label>
                        <select name="vendedor_codigo" class="form-control">
                            <option value="">Todos</option>
                            @foreach($vendedores as $v)
                                <option value="{{ $v->codigo }}" {{ request('vendedor_codigo') === $v->codigo ? 'selected' : '' }}>
                                    {{ $v->codigo }} - {{ $v->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Banco Origen</label>
                        <select name="banco_codigo" class="form-control">
                            <option value="">Todos</option>
                            @foreach($bancos as $b)
                                <option value="{{ $b->CODIGO }}" {{ request('banco_codigo') == $b->CODIGO ? 'selected' : '' }}>
                                    {{ $b->NOMBRE }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Destino</label>
                        <select name="pago_destino_id" class="form-control">
                            <option value="">Todos</option>
                            @foreach($destinos as $d)
                                <option value="{{ $d->id }}" {{ (string)request('pago_destino_id') === (string)$d->id ? 'selected' : '' }}>
                                    {{ $d->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Tipo de Pago</label>
                        <select name="tpago_id" class="form-control">
                            <option value="">Todos</option>
                            @foreach($tiposPago as $tp)
                                <option value="{{ $tp->CPAGO }}" {{ request('tpago_id') == $tp->CPAGO ? 'selected' : '' }}>
                                    {{ $tp->DPAGO }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-2">
                        <label>Estatus</label>
                        <select name="estatus" class="form-control">
                            <option value="">Todos</option>
                            @foreach(['PENDIENTE', 'EN REVISION', 'APROBADO', 'RECHAZADO'] as $estado)
                                <option value="{{ $estado }}" {{ request('estatus') === $estado ? 'selected' : '' }}>{{ $estado }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Moneda</label>
                        <select name="moneda_pago" class="form-control">
                            <option value="">Todas</option>
                            <option value="usd" {{ request('moneda_pago') === 'usd' ? 'selected' : '' }}>USD</option>
                            <option value="bs" {{ request('moneda_pago') === 'bs' ? 'selected' : '' }}>BS</option>
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Pedido</label>
                        <input type="text" name="pedido_id" class="form-control" value="{{ request('pedido_id') }}" placeholder="# pedido">
                    </div>
                    <div class="form-group col-md-2">
                        <label>Factura</label>
                        <input type="text" name="factura" class="form-control" value="{{ request('factura') }}" placeholder="# factura">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Buscar</label>
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Referencia, cliente, pedido, factura">
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('admin.conciliacion_bancaria.index') }}" class="btn btn-outline-secondary mr-2">Limpiar</a>
                    <button class="btn btn-primary">Aplicar Filtros</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <strong>Balance Banco/Pago (Debe - Haber - Saldo)</strong>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Destino</th>
                            <th>Forma de Pago</th>
                            <th class="text-right">Debe $</th>
                            <th class="text-right">Haber $</th>
                            <th class="text-right">Saldo $</th>
                            <th class="text-right">Debe Bs</th>
                            <th class="text-right">Haber Bs</th>
                            <th class="text-right">Saldo Bs</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($balanceDestinoTipo as $item)
                            <tr>
                                <td>{{ $item->destino_nombre }}</td>
                                <td>{{ $item->tpago_nombre }}</td>
                                <td class="text-right text-danger">{{ number_format($item->debe_usd, 2, ',', '.') }}</td>
                                <td class="text-right text-success">{{ number_format($item->haber_usd, 2, ',', '.') }}</td>
                                <td class="text-right font-weight-bold">{{ number_format($item->saldo_usd, 2, ',', '.') }}</td>
                                <td class="text-right text-danger">{{ number_format($item->debe_bs, 2, ',', '.') }}</td>
                                <td class="text-right text-success">{{ number_format($item->haber_bs, 2, ',', '.') }}</td>
                                <td class="text-right font-weight-bold">{{ number_format($item->saldo_bs, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center">No hay datos para el rango seleccionado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <strong>Detalle de Ingresos y Reclasificación</strong>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Ref</th>
                            <th>Vendedor</th>
                            <th>Clientes</th>
                            <th>Pedido(s)</th>
                            <th>Factura(s)</th>
                            <th>Destino</th>
                            <th>Tipo Pago</th>
                            <th>Motivo</th>
                            <th class="text-right">Monto $</th>
                            <th class="text-right">Monto Bs</th>
                            <th>Reclasificar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pagos as $p)
                            <tr>
                                <td>{{ $p->fecha ? \Carbon\Carbon::parse($p->fecha)->format('d/m/Y') : '-' }}</td>
                                <td>{{ $p->referencia ?: '-' }}<br><small class="text-muted">{{ $p->estatus }}</small></td>
                                <td>{{ $p->vendedor_codigo ?: 'N/D' }}<br><small>{{ $p->vendedor_nombre }}</small></td>
                                <td style="max-width:180px; white-space:normal;">{{ $p->clientes ?: '-' }}</td>
                                <td>{{ $p->pedidos ?: '-' }}</td>
                                <td>{{ $p->facturas ?: '-' }}</td>
                                <td>{{ $p->destino_pago ?: '-' }}</td>
                                <td>{{ $p->tipo_pago ?: '-' }}</td>
                                <td style="max-width:220px; white-space:normal;">{{ $p->motivo_ajuste ?: '-' }}</td>
                                <td class="text-right">{{ number_format($p->monto_usd, 2, ',', '.') }}</td>
                                <td class="text-right">{{ number_format($p->monto_bs, 2, ',', '.') }}</td>
                                <td style="min-width:300px;">
                                    <form method="POST" action="{{ route('admin.conciliacion_bancaria.reclasificar', $p->id) }}">
                                        @csrf
                                        <div class="form-row">
                                            <div class="col-12 mb-1">
                                                <select name="pago_destino_id" class="form-control form-control-sm" required>
                                                    @foreach($destinos as $d)
                                                        <option value="{{ $d->id }}" {{ (int)$p->pago_destino_id === (int)$d->id ? 'selected' : '' }}>
                                                            {{ $d->nombre }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12 mb-1">
                                                <input type="text" name="motivo" class="form-control form-control-sm" maxlength="500" placeholder="Motivo de ajuste (opcional)" value="{{ $p->motivo_ajuste }}">
                                            </div>
                                            <div class="col-12 text-right">
                                                <button class="btn btn-sm btn-primary">Guardar</button>
                                            </div>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center py-4">No hay pagos para los filtros seleccionados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-center">
            {{ $pagos->links() }}
        </div>
    </div>
</div>
@endsection
