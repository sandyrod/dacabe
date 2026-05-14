@extends('layouts.app')

@section('titulo', 'Reporte de Clientes Inactivos')
@section('titulo_header', 'Clientes sin Compra Reciente')
@section('subtitulo_header', 'Segmentación comercial para reactivación de cartera')

@section('styles')
<style>
    :root {
        --ri-navy: #10233d;
        --ri-blue: #1d5c9b;
        --ri-green: #1f8f4d;
        --ri-gold: #c89a3f;
        --ri-muted: #6b7280;
        --ri-soft: #eef4fb;
    }

    .ri-hero {
        border-radius: 24px;
        background: linear-gradient(135deg, #0c1f34 0%, #143a63 44%, #1d5c9b 100%);
        color: #fff;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(13, 34, 58, .22);
    }

    .ri-hero::before,
    .ri-hero::after {
        content: "";
        position: absolute;
        border-radius: 50%;
        background: rgba(255,255,255,.08);
    }

    .ri-hero::before {
        width: 240px;
        height: 240px;
        right: -60px;
        top: -70px;
    }

    .ri-hero::after {
        width: 180px;
        height: 180px;
        left: -50px;
        bottom: -70px;
        background: rgba(200,154,63,.14);
    }

    .ri-chip {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .4rem .8rem;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,.26);
        background: rgba(255,255,255,.10);
        font-size: .78rem;
        font-weight: 700;
    }

    .ri-kpi {
        border: 1px solid #dbe5f1;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 8px 22px rgba(12, 36, 66, .08);
        padding: 16px;
        height: 100%;
    }

    .ri-kpi .label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .9px;
        color: var(--ri-muted);
        font-weight: 800;
        margin-bottom: .2rem;
    }

    .ri-kpi .value {
        font-size: 1.65rem;
        font-weight: 900;
        line-height: 1.1;
        color: var(--ri-navy);
    }

    .ri-kpi .sub {
        font-size: .78rem;
        color: var(--ri-muted);
        margin-top: .25rem;
    }

    .ri-panel {
        border-radius: 18px;
        border: 1px solid #e2eaf3;
        background: #fff;
        box-shadow: 0 8px 22px rgba(12, 36, 66, .08);
        overflow: hidden;
    }

    .ri-panel-header {
        background: linear-gradient(180deg, #f9fbff 0%, #eff5fb 100%);
        border-bottom: 1px solid #e2eaf3;
    }

    .ri-filter label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .8px;
        color: var(--ri-muted);
        font-weight: 800;
        margin-bottom: .35rem;
    }

    .ri-table thead th {
        background: #f7fbff;
        color: var(--ri-navy);
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .8px;
        white-space: nowrap;
        border-bottom: 1px solid #e2eaf3;
    }

    .ri-table tbody td {
        vertical-align: middle;
        font-size: .88rem;
        color: #263645;
    }

    .ri-client-name {
        font-weight: 800;
        color: #10233d;
    }

    .ri-meta {
        font-size: .78rem;
        color: var(--ri-muted);
    }

    .ri-days {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border-radius: 999px;
        padding: .28rem .7rem;
        font-size: .75rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .ri-days.hot {
        background: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fca5a5;
    }

    .ri-days.warn {
        background: #fff4db;
        color: #a16207;
        border: 1px solid #f5d06f;
    }

    .ri-days.ok {
        background: #dcfce7;
        color: #15803d;
        border: 1px solid #86efac;
    }

    .ri-contact-actions .btn {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .ri-ranking-item {
        border: 1px solid #e7edf5;
        border-radius: 14px;
        padding: 12px;
        background: linear-gradient(180deg, #fff 0%, #fafcff 100%);
        margin-bottom: 10px;
    }

    .ri-ranking-badge {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #e7f0fb;
        color: #1d4f8a;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .78rem;
        font-weight: 900;
    }

    .ri-progress {
        height: 7px;
        border-radius: 999px;
        background: #edf2f7;
        overflow: hidden;
    }

    .ri-progress > span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #1d5c9b 0%, #1f8f4d 100%);
        width: 0;
    }

    .ri-pagination-wrap {
        border-top: 1px solid #e7edf5;
        background: linear-gradient(180deg, #fbfdff 0%, #f4f8fc 100%);
        padding: 14px 16px;
    }

    .ri-pagination-wrap .pagination {
        margin-bottom: 0;
        justify-content: center;
        flex-wrap: wrap;
        gap: 6px;
    }

    .ri-pagination-wrap .page-item .page-link {
        border: 1px solid #d7e1ec;
        color: var(--ri-navy);
        background: #fff;
        border-radius: 999px;
        min-width: 38px;
        padding: .42rem .8rem;
        font-weight: 700;
        box-shadow: 0 2px 6px rgba(12, 36, 66, .05);
    }

    .ri-pagination-wrap .page-item .page-link:hover {
        background: #eef4fb;
        border-color: #b8cce0;
        color: #10233d;
    }

    .ri-pagination-wrap .page-item.active .page-link {
        background: linear-gradient(135deg, #1d5c9b 0%, #1f8f4d 100%);
        border-color: transparent;
        color: #fff;
        box-shadow: 0 6px 14px rgba(29, 92, 155, .22);
    }

    .ri-pagination-wrap .page-item.disabled .page-link {
        background: #f3f6fa;
        color: #a3b1c2;
        border-color: #e4ebf3;
        box-shadow: none;
    }

    .ri-pagination-wrap svg {
        display: none !important;
    }

    @media (max-width: 991px) {
        .ri-kpi .value { font-size: 1.4rem; }
    }
</style>
@endsection

@section('content')
@php
    $fmtDays = function ($value) {
        return number_format((float) $value, 0, ',', '.');
    };
    $daysValue = function ($cliente) {
        return is_null($cliente->dias_sin_comprar) ? 99999 : (int) $cliente->dias_sin_comprar;
    };
@endphp
<div class="container-fluid py-3">
    <div class="ri-hero p-4 mb-4">
        <div class="row align-items-center">
            <div class="col-lg-8 position-relative" style="z-index:2;">
                <h3 class="font-weight-bold mb-2">
                    <i class="fas fa-user-clock mr-2"></i>Reporte de Clientes Inactivos
                </h3>
                <p class="mb-0" style="opacity:.92; max-width: 820px;">
                    Vista gerencial para identificar clientes con más tiempo sin comprar, su información de contacto y el vendedor asociado, con foco en reactivación comercial.
                </p>
            </div>
            <div class="col-lg-4 text-lg-right mt-3 mt-lg-0 position-relative" style="z-index:2;">
                <div class="ri-chip mb-2"><i class="fas fa-calendar-day"></i> Generado {{ $today->format('d/m/Y') }}</div>
                <div class="ri-chip"><i class="fas fa-filter"></i> Segmentación comercial y seguimiento</div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="ri-kpi" style="border-color:#9dc2ea; background:linear-gradient(145deg,#ddebfb 0%,#fff 75%);">
                <div class="label">Clientes analizados</div>
                <div class="value">{{ number_format((int) ($resumen->total_clientes ?? 0)) }}</div>
                <div class="sub">Base total de clientes filtrados</div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="ri-kpi" style="border-color:#f1c97b; background:linear-gradient(145deg,#fff4da 0%,#fff 75%);">
                <div class="label">Sin compra</div>
                <div class="value">{{ number_format((int) ($resumen->clientes_sin_compra ?? 0)) }}</div>
                <div class="sub">Clientes sin pedido registrado</div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="ri-kpi" style="border-color:#91d0a7; background:linear-gradient(145deg,#e8f9ee 0%,#fff 75%);">
                <div class="label">30+ días</div>
                <div class="value">{{ number_format((int) ($resumen->mas_30_dias ?? 0)) }}</div>
                <div class="sub">Clientes para seguimiento táctico</div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="ri-kpi" style="border-color:#efb1b1; background:linear-gradient(145deg,#ffe9e9 0%,#fff 75%);">
                <div class="label">90+ días</div>
                <div class="value">{{ number_format((int) ($resumen->mas_90_dias ?? 0)) }}</div>
                <div class="sub">Prioridad de reactivación</div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="ri-kpi" style="border-color:#c7a6ea; background:linear-gradient(145deg,#f2e9ff 0%,#fff 75%);">
                <div class="label">180+ días</div>
                <div class="value">{{ number_format((int) ($resumen->mas_180_dias ?? 0)) }}</div>
                <div class="sub">Riesgo alto de fuga</div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="ri-kpi" style="border-color:#8ed0c7; background:linear-gradient(145deg,#e6fbf7 0%,#fff 75%);">
                <div class="label">Promedio días</div>
                <div class="value">{{ number_format((float) ($resumen->promedio_dias ?? 0), 0, ',', '.') }}</div>
                <div class="sub">Antigüedad promedio sin compra</div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-8 col-lg-7 mb-3">
            <div class="ri-panel h-100">
                <div class="ri-panel-header p-3">
                    <h6 class="mb-0 font-weight-bold" style="color:var(--ri-navy);">
                        <i class="fas fa-sliders-h mr-2"></i>Filtros de Segmentación
                    </h6>
                </div>
                <div class="p-3">
                    <form method="GET" action="{{ route('admin.reportes.clientes.inactivos') }}" class="ri-filter">
                        <div class="form-row">
                            <div class="form-group col-lg-5 col-md-12">
                                <label>Buscar</label>
                                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cliente, RIF, email, teléfono...">
                            </div>
                            <div class="form-group col-lg-3 col-md-6">
                                <label>Vendedor</label>
                                <select name="vendedor" class="form-control">
                                    <option value="">Todos</option>
                                    @foreach($vendedores as $vendedor)
                                        <option value="{{ $vendedor->codigo }}" @selected(request('vendedor') == $vendedor->codigo)>
                                            {{ $vendedor->codigo }} - {{ $vendedor->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-lg-2 col-md-6">
                                <label>Mín. días</label>
                                <input type="number" min="0" name="min_days" value="{{ request('min_days') }}" class="form-control" placeholder="30">
                            </div>
                            <div class="form-group col-lg-2 col-md-6">
                                <label>Máx. días</label>
                                <input type="number" min="0" name="max_days" value="{{ request('max_days') }}" class="form-control" placeholder="999">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-lg-3 col-md-4 d-flex align-items-end">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" value="1" id="sin_compra" name="sin_compra" {{ request()->boolean('sin_compra') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="sin_compra" style="font-size:.8rem;">Solo sin compras</label>
                                </div>
                            </div>
                            <div class="form-group col-lg-3 col-md-4 d-flex align-items-end">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" value="1" id="sin_vendedor" name="sin_vendedor" {{ request()->boolean('sin_vendedor') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="sin_vendedor" style="font-size:.8rem;">Sin vendedor asignado</label>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end border-top pt-3">
                            <a href="{{ route('admin.reportes.clientes.inactivos') }}" class="btn btn-outline-secondary btn-sm mr-2">
                                <i class="fas fa-undo mr-1"></i>Limpiar
                            </a>
                            <button class="btn btn-primary btn-sm">
                                <i class="fas fa-search mr-1"></i>Aplicar filtros
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5 mb-3">
            <div class="ri-panel h-100">
                <div class="ri-panel-header p-3">
                    <h6 class="mb-0 font-weight-bold" style="color:var(--ri-navy);">
                        <i class="fas fa-fire-alt mr-2"></i>Clientes Prioritarios
                    </h6>
                </div>
                <div class="p-3">
                    @forelse($topInactivos as $cliente)
                        @php
                            $dias = $daysValue($cliente);
                            $progressWidth = $dias === 99999 ? 100 : min(100, max(0, $dias) / 2);
                        @endphp
                        <div class="ri-ranking-item">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="d-flex align-items-center">
                                    <span class="ri-ranking-badge mr-2">{{ $loop->iteration }}</span>
                                    <div>
                                        <div class="font-weight-bold">{{ $cliente->nombre }}</div>
                                        <small class="text-muted">{{ $cliente->rif }}</small>
                                    </div>
                                </div>
                                <span class="ri-days {{ $dias >= 180 ? 'hot' : ($dias >= 90 ? 'warn' : 'ok') }}">
                                    <i class="fas fa-clock"></i>{{ $dias === 99999 ? 'Sin compras' : $fmtDays($dias) . ' días' }}
                                </span>
                            </div>
                            <div class="ri-meta mb-2">
                                <i class="fas fa-user-tie mr-1"></i>{{ $cliente->vendedor_nombre }}
                            </div>
                            <div class="ri-progress mb-1">
                                <span data-width="{{ $progressWidth }}"></span>
                            </div>
                            <div class="d-flex justify-content-between ri-meta">
                                <span>{{ $cliente->total_pedidos }} pedidos</span>
                                <span>{{ is_null($cliente->ultima_compra) ? 'Sin última compra' : \Carbon\Carbon::parse($cliente->ultima_compra)->format('d/m/Y') }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted small">No hay clientes para mostrar con estos filtros.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="ri-panel">
        <div class="ri-panel-header px-3 py-2 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 font-weight-bold" style="color:var(--ri-navy);">
                <i class="fas fa-list-alt mr-2"></i>Detalle de Clientes para Reactivación
            </h6>
            <span class="badge badge-light border">{{ number_format($clientesPrioridad->total()) }} registros</span>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 ri-table">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Contacto</th>
                        <th>Vendedor Asociado</th>
                        <th>Último Pedido</th>
                        <th class="text-right">Días sin comprar</th>
                        <th class="text-right">Pedidos</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clientesPrioridad as $cliente)
                        @php
                            $dias = $daysValue($cliente);
                            $telefono = preg_replace('/\D+/', '', (string) $cliente->telefono);
                            $waUrl = strlen($telefono) >= 10 ? 'https://wa.me/' . $telefono : null;
                        @endphp
                        <tr>
                            <td>
                                <div class="ri-client-name">{{ $cliente->nombre }}</div>
                                <div class="ri-meta">RIF: {{ $cliente->rif }}</div>
                                <div class="ri-meta">Cod: {{ $cliente->codcli ?: 'N/D' }}</div>
                            </td>
                            <td>
                                <div><i class="fas fa-phone-alt mr-1 text-muted"></i>{{ $cliente->telefono ?: 'Sin teléfono' }}</div>
                                <div><i class="fas fa-envelope mr-1 text-muted"></i>{{ $cliente->email ?: 'Sin email' }}</div>
                            </td>
                            <td>
                                <div class="font-weight-bold">{{ $cliente->vendedor_nombre }}</div>
                                <div class="ri-meta">{{ $cliente->vendedor_codigo ?: 'Sin código' }}</div>
                                <div class="ri-meta">{{ $cliente->vendedor_email ?: 'Sin correo' }}</div>
                            </td>
                            <td>
                                @if($cliente->ultima_compra)
                                    <div class="font-weight-bold">{{ \Carbon\Carbon::parse($cliente->ultima_compra)->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($cliente->ultima_compra)->diffForHumans(null, true) }}</small>
                                @else
                                    <span class="badge badge-danger">Sin compras registradas</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <span class="ri-days {{ $dias >= 180 ? 'hot' : ($dias >= 90 ? 'warn' : 'ok') }}">
                                    <i class="fas fa-calendar-times"></i>
                                    {{ $dias === 99999 ? 'Sin compras' : $fmtDays($dias) }}
                                </span>
                            </td>
                            <td class="text-right font-weight-bold">{{ number_format((int) $cliente->total_pedidos) }}</td>
                            <td>
                                <div class="ri-contact-actions d-flex flex-wrap">
                                    @if($cliente->telefono)
                                        <a href="tel:{{ $cliente->telefono }}" class="btn btn-outline-primary btn-sm mr-1 mb-1" title="Llamar">
                                            <i class="fas fa-phone"></i>
                                        </a>
                                    @endif
                                    @if($cliente->email)
                                        <a href="mailto:{{ $cliente->email }}" class="btn btn-outline-secondary btn-sm mr-1 mb-1" title="Enviar email">
                                            <i class="fas fa-envelope"></i>
                                        </a>
                                    @endif
                                    @if($waUrl)
                                        <a href="{{ $waUrl }}" target="_blank" class="btn btn-success btn-sm mr-1 mb-1" title="WhatsApp">
                                            <i class="fab fa-whatsapp"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fas fa-info-circle mr-1"></i>
                                No hay clientes que cumplan los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($clientesPrioridad->hasPages())
            <div class="ri-pagination-wrap">
                {{ $clientesPrioridad->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.ri-progress > span[data-width]').forEach(function (bar) {
            bar.style.width = bar.dataset.width + '%';
        });
    });
</script>
@endsection
