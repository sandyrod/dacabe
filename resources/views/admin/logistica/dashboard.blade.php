@extends('layouts.app')

@section('titulo', 'Logística - Dashboard')
@section('titulo_header', 'Dashboard Logística')
@section('subtitulo_header', 'Indicadores y Operación')

@section('styles')
<style>
    .log-kpi { border: 0; border-radius: 14px; color: #fff; box-shadow: 0 10px 26px rgba(0,0,0,.12); }
    .log-kpi .card-body { padding: 1.1rem 1.2rem; }
    .log-kpi h3 { margin: 0; font-size: 2rem; font-weight: 800; }
    .log-kpi p { margin: 0; letter-spacing: .5px; text-transform: uppercase; font-size: .83rem; }
    .bg-ocean { background: linear-gradient(135deg,#0b4f6c,#01baef); }
    .bg-amber { background: linear-gradient(135deg,#ac6c00,#f2b705); }
    .bg-forest { background: linear-gradient(135deg,#115740,#31a24c); }
    .bg-slate { background: linear-gradient(135deg,#2d3142,#4f5d75); }
    .bg-crimson { background: linear-gradient(135deg,#8a1c1c,#d64545); }
    .log-card { border: 0; border-radius: 14px; box-shadow: 0 8px 20px rgba(0,0,0,.08); }
</style>
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 col-6"><div class="card log-kpi bg-slate"><div class="card-body"><h3>{{ $kpis['total'] }}</h3><p>Total cajas</p></div></div></div>
            <div class="col-md-2 col-6"><div class="card log-kpi bg-ocean"><div class="card-body"><h3>{{ $kpis['armadas'] }}</h3><p>Armadas</p></div></div></div>
            <div class="col-md-2 col-6"><div class="card log-kpi bg-amber"><div class="card-body"><h3>{{ $kpis['transito'] }}</h3><p>En tránsito</p></div></div></div>
            <div class="col-md-2 col-6"><div class="card log-kpi bg-forest"><div class="card-body"><h3>{{ $kpis['entregadas'] }}</h3><p>Entregadas</p></div></div></div>
            <div class="col-md-2 col-6"><div class="card log-kpi bg-crimson"><div class="card-body"><h3>{{ $kpis['canceladas'] }}</h3><p>Canceladas</p></div></div></div>
            <div class="col-md-2 col-6"><div class="card log-kpi bg-ocean"><div class="card-body"><h3>{{ $porcentajeEntrega }}%</h3><p>Efectividad</p></div></div></div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card log-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title font-weight-bold mb-0"><i class="fas fa-boxes mr-2"></i>Últimas cajas</h3>
                        <a href="{{ route('admin.logistica.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus mr-1"></i>Nueva caja</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Cliente</th>
                                        <th>Estatus</th>
                                        <th>Items</th>
                                        <th>Chofer</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($ultimas as $caja)
                                        <tr>
                                            <td>{{ $caja->codigo }}</td>
                                            <td>{{ $caja->cliente_nombre }}</td>
                                            <td><span class="badge badge-info">{{ str_replace('_', ' ', $caja->estatus) }}</span></td>
                                            <td>{{ $caja->items_count }}</td>
                                            <td>{{ $caja->chofer_nombre ?: '-' }}</td>
                                            <td>
                                                <a href="{{ route('admin.logistica.show', $caja->id) }}" class="btn btn-xs btn-outline-primary"><i class="fas fa-eye"></i></a>
                                                <a href="{{ route('admin.logistica.edit', $caja->id) }}" class="btn btn-xs btn-outline-warning"><i class="fas fa-edit"></i></a>
                                                <a href="{{ route('admin.logistica.label', $caja->id) }}" target="_blank" class="btn btn-xs btn-outline-success"><i class="fas fa-print"></i></a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center py-4">No hay cajas registradas.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card log-card">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold mb-0"><i class="fas fa-chart-line mr-2"></i>Resumen operativo</h3>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>Pendientes de entrega:</strong> {{ $pendientes }}</p>
                        <p class="mb-2"><strong>Entregas registradas (7 días):</strong> {{ $entregasSemana->sum('total') }}</p>
                        <p class="mb-0"><strong>Último corte:</strong> {{ now()->format('d/m/Y H:i') }}</p>
                        <hr>
                        <a href="{{ route('admin.logistica.index') }}" class="btn btn-dark btn-block"><i class="fas fa-list mr-1"></i>Gestionar cajas</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
