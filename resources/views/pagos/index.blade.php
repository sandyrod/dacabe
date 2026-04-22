@extends('layouts.app')

@section('content')
<style>
    .card-header.manager { background: linear-gradient(135deg, #3c8dbc 0%, #1f5f8b 100%); color: #fff; border-bottom: 0; }
    .manager .filter-label { font-size: 12px; color: rgba(255,255,255,0.9); letter-spacing: .5px; text-transform: uppercase; }
    .table thead th { white-space: nowrap; }
    .table-hover tbody tr:hover { background-color: #f6fbff; }
    .badge-pill { padding: .35rem .6rem; font-weight: 600; }
    .amount { font-variant-numeric: tabular-nums; }
    .btn-icon { display: inline-flex; align-items: center; gap: .35rem; }
    .subheader { color: #e8f1f7; font-size: .9rem; }
    .shadow-soft { box-shadow: 0 6px 18px rgba(0,0,0,.06); }
    .pointer { cursor: pointer; }
    .badge-tipo-divisa { background-color: #17a2b8; }
    .badge-tipo-transferencia { background-color: #6f42c1; }
    .badge-tipo-otro { background-color: #20c997; }
    .input-group-text { background-color: #f1f5f9; }
    .table td, .table th { vertical-align: middle; }
    .badge-light.border { border-color: #e3e6ea !important; }
    .mr-2 { margin-right: .5rem; }
    .mr-1 { margin-right: .25rem; }
</style>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-12">
            <h3>Listado de Pagos</h3>
        </div>
    </div>

    <div class="card shadow-soft">
        <div class="card-header manager">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <h5 class="mb-1"><i class="fas fa-money-check-alt mr-2"></i>Gestión de Pagos Recibidos</h5>
                    <div class="subheader">
                        <span class="mr-2"><i class="far fa-list-alt"></i> Resultados: <b>{{ number_format($pagos->total()) }}</b></span>
                        @if(request('fecha_inicio') || request('fecha_fin'))
                            <span class="mr-2"><i class="far fa-calendar-alt"></i> Rango: <b>{{ request('fecha_inicio') ?: '—' }}</b> a <b>{{ request('fecha_fin') ?: '—' }}</b></span>
                        @endif
                        @if(request('vendedor'))
                            <span class="mr-2"><i class="far fa-user"></i> Vendedor: <b>{{ request('vendedor') }}</b></span>
                        @endif
                        @if(request('tipo_pago'))
                            <span class="mr-2"><i class="far fa-credit-card"></i> Tipo: <b>{{ request('tipo_pago') }}</b></span>
                        @endif
                    </div>
                </div>
                <div>
                    <a href="{{ route('pagos.export', request()->all()) }}" class="btn btn-success btn-icon" data-toggle="tooltip" title="Exportar a Excel">
                        <i class="fas fa-file-excel"></i> Exportar
                    </a>
                </div>
            </div>
            <form method="GET" action="{{ route('pagos.index') }}">
                <div class="row">
                    <div class="col-md-2">
                        <label class="filter-label">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" class="form-control" value="{{ request('fecha_inicio') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="filter-label">Fecha Fin</label>
                        <input type="date" name="fecha_fin" class="form-control" value="{{ request('fecha_fin') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="filter-label">Vendedor</label>
                        <select name="vendedor" class="form-control">
                            <option value="">Todos los Vendedores</option>
                            @foreach($vendedores as $vendedor)
                                <option value="{{ $vendedor->email }}" {{ request('vendedor') == $vendedor->email ? 'selected' : '' }}>
                                    {{ $vendedor->codigo }} - {{ $vendedor->nombre_completo }} ({{ $vendedor->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="filter-label">Tipo de Pago</label>
                        <select name="tipo_pago" class="form-control">
                            <option value="">Todos los Tipos de Pago</option>
                            @foreach($tipos_pago as $tipo)
                                <option value="{{ $tipo->CPAGO }}" {{ request('tipo_pago') == $tipo->CPAGO ? 'selected' : '' }}>
                                    {{ $tipo->DPAGO }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="filter-label">Buscar</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" name="search" class="form-control" placeholder="Pedido, referencia o cliente" value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end justify-content-end">
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary btn-icon"><i class="fas fa-filter"></i> Filtrar</button>
                            <button type="button" id="reset-filtros" class="btn btn-outline-light btn-icon" title="Reiniciar filtros" data-toggle="tooltip"><i class="fas fa-undo"></i> Reiniciar</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th><a href="{{ route('pagos.index', array_merge(request()->all(), ['sort' => 'fecha_pago', 'direction' => request('direction', 'desc') == 'desc' ? 'asc' : 'desc'])) }}"><i class="far fa-calendar-alt"></i> Fecha Pago</a></th>
                            <th><a href="{{ route('pagos.index', array_merge(request()->all(), ['sort' => 'pedido_id', 'direction' => request('direction', 'desc') == 'desc' ? 'asc' : 'desc'])) }}"><i class="fas fa-receipt"></i> Pedido</a></th>
                            <th><i class="far fa-user"></i> Cliente</th>
                            <th><i class="fas fa-user-tie"></i> Vendedor</th>
                            <th><a href="{{ route('pagos.index', array_merge(request()->all(), ['sort' => 'tipo_pago', 'direction' => request('direction', 'desc') == 'desc' ? 'asc' : 'desc'])) }}"><i class="far fa-credit-card"></i> Tipo Pago</a></th>
                            <th><i class="fas fa-hashtag"></i> Referencia</th>
                            <th class="text-right"><a href="{{ route('pagos.index', array_merge(request()->all(), ['sort' => 'monto', 'direction' => request('direction', 'desc') == 'desc' ? 'asc' : 'desc'])) }}"><i class="fas fa-dollar-sign"></i> Monto $</a></th>
                            <th class="text-right"><i class="fas fa-exchange-alt"></i> Tasa</th>
                            <th class="text-right"><i class="fas fa-coins"></i> Monto Bs.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pagos as $pago)
                            <tr>
                                <td><span class="badge badge-light border"><i class="far fa-clock mr-1"></i>{{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y H:i') }}</span></td>
                                <td><span class="font-weight-bold">#{{ $pago->pedido_id }}</span></td>
                                <td>{{ $pago->cliente }}</td>
                                <td><i class="far fa-id-badge text-muted mr-1"></i>{{ $pago->codigo_vendedor }} - {{ $pago->nombre_vendedor }}</td>
                                <td>
                                    @php
                                        $tipo = strtoupper($pago->tipo_pago ?? '');
                                        $class = 'badge-secondary';
                                        if(stripos($tipo,'DIVISA') !== false) $class = 'badge-tipo-divisa';
                                        elseif(stripos($tipo,'TRANS') !== false) $class = 'badge-tipo-transferencia';
                                        elseif($tipo) $class = 'badge-tipo-otro';
                                    @endphp
                                    <span class="badge badge-pill {{ $class }}">{{ $pago->tipo_pago ?? '—' }}</span>
                                </td>
                                <td>
                                    <span class="pointer" data-toggle="tooltip" title="Copiar referencia" onclick="navigator.clipboard.writeText('{{ $pago->referencia }}');"><i class="far fa-copy mr-1"></i></span>
                                    {{ $pago->referencia }}
                                </td>
                                <td class="text-right amount text-success">${{ number_format($pago->monto, 2, ',', '.') }}</td>
                                <td class="text-right amount"><span class="badge badge-info">{{ number_format($pago->tasa, 2, ',', '.') }}</span></td>
                                <td class="text-right amount text-primary">Bs {{ number_format($pago->monto_bolivares, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">No se encontraron pagos.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center">
                {{ $pagos->links() }}
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){
    if (typeof $ !== 'undefined' && $.fn.tooltip) {
        $('[data-toggle="tooltip"]').tooltip();
    }
    var resetBtn = document.getElementById('reset-filtros');
    if (resetBtn) {
        resetBtn.addEventListener('click', function(){
            window.location.href = '{{ route('pagos.index') }}';
        });
    }
});
</script>
@endsection
