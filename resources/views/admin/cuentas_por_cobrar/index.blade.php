@extends('layouts.app')

@section('styles')
<style>
    :root {
        --cxr-navy: #0d223a;
        --cxr-blue: #165d9c;
        --cxr-sky: #2a93c9;
        --cxr-ink: #1f2937;
        --cxr-muted: #6b7280;
        --cxr-soft: #eef3f8;
        --cxr-gold: #d4a552;
    }

    .cxr-hero {
        border-radius: 22px;
        background: linear-gradient(122deg, #0f2f4e 0%, #1a5f98 45%, #2b93c8 100%);
        color: #fff;
        box-shadow: 0 20px 52px rgba(8, 33, 59, 0.28);
        overflow: hidden;
        position: relative;
    }

    .cxr-hero::after {
        content: "";
        position: absolute;
        width: 280px;
        height: 280px;
        border-radius: 50%;
        right: -70px;
        top: -80px;
        background: rgba(255, 255, 255, 0.12);
    }

    .cxr-hero::before {
        content: "";
        position: absolute;
        width: 220px;
        height: 220px;
        border-radius: 50%;
        left: -80px;
        bottom: -95px;
        background: rgba(212, 165, 82, 0.18);
    }

    .cxr-rate-chip {
        border: 1px solid rgba(255, 255, 255, 0.35);
        background: rgba(255, 255, 255, 0.12);
        border-radius: 999px;
        padding: 6px 12px;
        font-size: 0.78rem;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .cxr-kpi {
        border: 1px solid #dfe8f2;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 8px 20px rgba(9, 36, 62, 0.08);
        padding: 18px;
        height: 100%;
        position: relative;
        overflow: hidden;
    }

    .cxr-kpi::after {
        content: "";
        position: absolute;
        width: 74px;
        height: 74px;
        border-radius: 50%;
        right: -16px;
        bottom: -24px;
        background: linear-gradient(135deg, rgba(22, 93, 156, 0.12), rgba(42, 147, 201, 0.06));
    }

    .cxr-kpi-base {
        background: linear-gradient(145deg, #dcebff 0%, #ffffff 72%);
        border-color: #9ac1f7;
    }

    .cxr-kpi-iva {
        background: linear-gradient(145deg, #e8f8db 0%, #ffffff 72%);
        border-color: #a9d37a;
    }

    .cxr-kpi-ajustes {
        background: linear-gradient(145deg, #ffefda 0%, #ffffff 72%);
        border-color: #efc27a;
    }

    .cxr-kpi-vencidos {
        background: linear-gradient(145deg, #ffe0e0 0%, #ffffff 72%);
        border-color: #efaaaa;
    }

    .cxr-kpi-title {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--cxr-muted);
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .cxr-kpi-value {
        font-size: 1.45rem;
        font-weight: 800;
        color: var(--cxr-ink);
        line-height: 1.15;
    }

    .cxr-kpi-sub {
        font-size: 0.78rem;
        color: var(--cxr-muted);
        margin-top: 4px;
    }

    .cxr-panel {
        border-radius: 16px;
        border: 1px solid #e6edf5;
        box-shadow: 0 8px 20px rgba(15, 39, 66, 0.08);
        overflow: hidden;
        background: #fff;
    }

    .cxr-panel-header {
        background: linear-gradient(180deg, #f9fbff 0%, #f1f6fc 100%);
        border-bottom: 1px solid #e6edf5;
    }

    .cxr-table thead th {
        background: #f8fbff;
        color: var(--cxr-navy);
        font-size: 0.73rem;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        border-bottom: 1px solid #e6edf5;
        white-space: nowrap;
    }

    .cxr-table tbody td {
        vertical-align: middle;
        font-size: 0.88rem;
        color: #243447;
    }

    .cxr-amount {
        font-weight: 700;
        color: #122b45;
        white-space: nowrap;
    }

    .cxr-amount-usd {
        display: block;
        font-size: 0.76rem;
        color: #5e6b78;
        font-weight: 600;
    }

    .cxr-status {
        font-size: 0.72rem;
        font-weight: 700;
        border-radius: 999px;
        padding: 4px 10px;
        border: 1px solid transparent;
        display: inline-block;
    }

    .cxr-status-en-proceso { background: #e8f2ff; color: #1d4f91; border-color: #c7dfff; }
    .cxr-status-revision { background: #fff7e8; color: #a16207; border-color: #f9dfb3; }
    .cxr-status-aprobado { background: #e8faef; color: #166534; border-color: #bce9cb; }
    .cxr-status-rechazado { background: #ffe8e8; color: #b91c1c; border-color: #f6bcbc; }
    .cxr-status-default { background: #f3f4f6; color: #374151; border-color: #e5e7eb; }

    .cxr-vencido {
        background: #fee2e2;
        color: #b91c1c;
        font-weight: 700;
        border-radius: 999px;
        padding: 2px 10px;
        font-size: 0.75rem;
    }

    .cxr-vigente {
        background: #dcfce7;
        color: #15803d;
        font-weight: 700;
        border-radius: 999px;
        padding: 2px 10px;
        font-size: 0.75rem;
    }

    .cxr-top-item {
        border: 1px solid #e8eef6;
        border-radius: 12px;
        padding: 12px;
        margin-bottom: 10px;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
    }

    .cxr-top-rank {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: #e8f1fc;
        color: #1c4b79;
        font-size: 0.75rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .cxr-filter label {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        font-weight: 700;
        color: var(--cxr-muted);
        margin-bottom: 0.3rem;
    }

    .cxr-filter .form-control,
    .cxr-filter .custom-select {
        min-height: 38px;
    }

    .cxr-filter-actions {
        border-top: 1px dashed #dde7f3;
        padding-top: 12px;
        margin-top: 2px;
    }

    .cxr-kpi-prox {
        background: linear-gradient(145deg, #fff8e1 0%, #ffffff 72%);
        border-color: #ffe082;
    }

    .cxr-kpi-dias {
        background: linear-gradient(145deg, #fce4ec 0%, #ffffff 72%);
        border-color: #f48fb1;
    }

    .cxr-dias-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        border-radius: 999px;
        padding: 3px 10px;
        font-size: 0.73rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .cxr-dias-vencido {
        background: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fca5a5;
    }

    .cxr-dias-vigente {
        background: #dcfce7;
        color: #15803d;
        border: 1px solid #86efac;
    }

    .cxr-dias-hoy {
        background: #fef9c3;
        color: #92400e;
        border: 1px solid #fde68a;
    }

    .cxr-dias-sin {
        background: #f3f4f6;
        color: #6b7280;
        border: 1px solid #e5e7eb;
    }

    @media (max-width: 991px) {
        .cxr-table thead th { font-size: 0.68rem; }
        .cxr-kpi-value { font-size: 1.25rem; }
    }
</style>
@endsection

@section('content')
@php
    $tasa = (float) ($tasaDelDia ?? 0);
    $selectedVendedor = trim((string) request('vendedor', ''));
    $fmtBs = function ($value) {
        return 'Bs. ' . number_format((float) $value, 2, ',', '.');
    };
    $fmtUsd = function ($value) use ($tasa) {
        if ($tasa <= 0) {
            return 'N/D';
        }
        return '$ ' . number_format(((float) $value) / $tasa, 2, ',', '.');
    };
    $statusClass = function ($estatus) {
        $value = strtoupper(trim((string) $estatus));
        if (strpos($value, 'REV') !== false) return 'cxr-status-revision';
        if (strpos($value, 'APROB') !== false || strpos($value, 'PAG') !== false) return 'cxr-status-aprobado';
        if (strpos($value, 'RECH') !== false || strpos($value, 'ANUL') !== false) return 'cxr-status-rechazado';
        if (strpos($value, 'PROC') !== false || strpos($value, 'PEND') !== false) return 'cxr-status-en-proceso';
        return 'cxr-status-default';
    };
@endphp
<div class="container-fluid py-3">
    <div class="cxr-hero p-4 mb-4">
        <div class="row align-items-center">
            <div class="col-lg-8 position-relative" style="z-index:2;">
                <h3 class="font-weight-bold mb-2">
                    <i class="fas fa-balance-scale mr-2"></i>Cuentas por Cobrar
                </h3>
                <p class="mb-0" style="opacity:.9;">
                    Vista ejecutiva de toda la cartera pendiente por pedido, con indicadores de riesgo, antiguedad y detalle operativo.
                </p>
            </div>
            <div class="col-lg-4 text-lg-right mt-3 mt-lg-0 position-relative" style="z-index:2;">
                <div class="cxr-rate-chip mb-2">
                    <i class="fas fa-dollar-sign"></i>
                    Tasa del dia: {{ $tasa > 0 ? number_format($tasa, 2, ',', '.') : 'N/D' }}
                </div>
                <div style="font-size:.78rem; text-transform:uppercase; letter-spacing:1px; opacity:.8;">Cartera Total</div>
                <div style="font-size:2rem; font-weight:800; line-height:1.1;">
                    {{ $fmtBs($resumen->total_cartera ?? 0) }}
                </div>
                <div style="font-size:.9rem; opacity:.9;">{{ $fmtUsd($resumen->total_cartera ?? 0) }}</div>
                <small style="opacity:.85;">{{ number_format((int) ($resumen->total_pedidos ?? 0)) }} pedidos en cartera</small>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="cxr-kpi cxr-kpi-base">
                <div class="cxr-kpi-title">Saldo Base</div>
                <div class="cxr-kpi-value">{{ $fmtBs($resumen->total_saldo_base ?? 0) }}</div>
                <div class="cxr-kpi-sub">{{ $fmtUsd($resumen->total_saldo_base ?? 0) }}</div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="cxr-kpi cxr-kpi-iva">
                <div class="cxr-kpi-title">Saldo IVA</div>
                <div class="cxr-kpi-value">{{ $fmtBs($resumen->total_saldo_iva ?? 0) }}</div>
                <div class="cxr-kpi-sub">{{ $fmtUsd($resumen->total_saldo_iva ?? 0) }}</div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="cxr-kpi cxr-kpi-ajustes">
                <div class="cxr-kpi-title">Saldo Ajustes</div>
                <div class="cxr-kpi-value">{{ $fmtBs($resumen->total_saldo_ajustes ?? 0) }}</div>
                <div class="cxr-kpi-sub">{{ $fmtUsd($resumen->total_saldo_ajustes ?? 0) }}</div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="cxr-kpi cxr-kpi-vencidos">
                <div class="cxr-kpi-title">Pedidos Vencidos</div>
                <div class="cxr-kpi-value">{{ number_format((int) $vencidosCount) }}</div>
                <div class="cxr-kpi-sub">
                    <i class="fas fa-clock mr-1"></i>Prom.
                    {{ number_format((float) ($diasVencidosPromedio ?? 0), 0, ',', '.') }} días vencidos
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="cxr-kpi cxr-kpi-prox">
                <div class="cxr-kpi-title">Vencen en 7 días</div>
                <div class="cxr-kpi-value">{{ number_format((int) ($proxVencerCount ?? 0)) }}</div>
                <div class="cxr-kpi-sub">Con crédito próximo</div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="cxr-kpi cxr-kpi-dias">
                <div class="cxr-kpi-title">Antigüedad Promedio</div>
                <div class="cxr-kpi-value">{{ number_format((float) ($antiguedad->dias_promedio ?? 0), 0, ',', '.') }} días</div>
                <div class="cxr-kpi-sub">Máx. {{ number_format((float) ($antiguedad->dias_maximos ?? 0), 0, ',', '.') }} días</div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-8 col-lg-7 mb-3">
            <div class="cxr-panel">
                <div class="cxr-panel-header p-3">
                    <h6 class="mb-0 font-weight-bold" style="color:var(--cxr-navy);">
                        <i class="fas fa-filter mr-2"></i>Filtros de Cartera
                    </h6>
                </div>
                <div class="p-3">
                    <form method="GET" action="{{ route('admin.cuentas_por_cobrar.index') }}" class="cxr-filter">
                        <div class="form-row">
                            <div class="form-group col-lg-6 col-md-12">
                                <label>Buscar</label>
                                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Pedido, cliente, rif...">
                            </div>
                            <div class="form-group col-lg-3 col-md-6">
                                <label>Vendedor</label>
                                <select name="vendedor" class="form-control">
                                    <option value="">Todos</option>
                                    @foreach($vendedores as $vendedor)
                                        <option value="{{ trim((string) $vendedor->codigo) }}" {{ $selectedVendedor === trim((string) $vendedor->codigo) ? 'selected' : '' }}>
                                            {{ $vendedor->codigo }} - {{ $vendedor->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-lg-3 col-md-6">
                                <label>Estatus</label>
                                <select name="estatus" class="form-control">
                                    <option value="">Todos</option>
                                    @foreach($estatuses as $estatus)
                                        <option value="{{ $estatus }}" {{ request('estatus') == $estatus ? 'selected' : '' }}>
                                            {{ $estatus }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-lg-3 col-md-4">
                                <label>Desde</label>
                                <input type="date" name="desde" value="{{ request('desde') }}" class="form-control">
                            </div>
                            <div class="form-group col-lg-3 col-md-4">
                                <label>Hasta</label>
                                <input type="date" name="hasta" value="{{ request('hasta') }}" class="form-control">
                            </div>
                            <div class="form-group col-lg-3 col-md-4 d-flex align-items-end">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" value="1" id="solo_vencidos" name="solo_vencidos" {{ request()->boolean('solo_vencidos') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="solo_vencidos" style="font-size:.75rem;">Solo vencidos</label>
                                </div>
                            </div>
                            <div class="form-group col-lg-3 d-none d-lg-block"></div>
                        </div>
                        <div class="d-flex justify-content-end cxr-filter-actions">
                            <a href="{{ route('admin.cuentas_por_cobrar.index') }}" class="btn btn-outline-secondary btn-sm mr-2">
                                <i class="fas fa-undo mr-1"></i>Limpiar
                            </a>
                            <button class="btn btn-primary btn-sm">
                                <i class="fas fa-search mr-1"></i>Aplicar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-5 mb-3">
            <div class="cxr-panel h-100">
                <div class="cxr-panel-header p-3">
                    <h6 class="mb-0 font-weight-bold" style="color:var(--cxr-navy);">
                        <i class="fas fa-chart-bar mr-2"></i>Top Vendedores (Cartera)
                    </h6>
                </div>
                <div class="p-3">
                    @forelse($topVendedores as $item)
                        <div class="cxr-top-item">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <div class="font-weight-bold d-flex align-items-center">
                                        <span class="cxr-top-rank mr-2">{{ $loop->iteration }}</span>
                                        <span>{{ $item->codigo_vendedor }} - {{ $item->vendedor_nombre }}</span>
                                    </div>
                                    <small class="text-muted">{{ number_format((int) $item->pedidos_pendientes) }} pedidos pendientes</small>
                                </div>
                                <div class="text-right">
                                    <div class="font-weight-bold">{{ $fmtBs($item->saldo_total) }}</div>
                                    <small class="text-muted">{{ $fmtUsd($item->saldo_total) }}</small>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted small">Sin datos para mostrar.</div>
                    @endforelse

                    <div class="mt-3 pt-2 border-top">
                        <div class="d-flex justify-content-between small">
                            <span class="text-muted">Antiguedad promedio</span>
                            <strong>{{ number_format((float) ($antiguedad->dias_promedio ?? 0), 0, ',', '.') }} dias</strong>
                        </div>
                        <div class="d-flex justify-content-between small mt-1">
                            <span class="text-muted">Antiguedad maxima</span>
                            <strong>{{ number_format((float) ($antiguedad->dias_maximos ?? 0), 0, ',', '.') }} dias</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="cxr-panel">
        <div class="cxr-panel-header px-3 py-2 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 font-weight-bold" style="color:var(--cxr-navy);">
                <i class="fas fa-list-alt mr-2"></i>Detalle de Pedidos Pendientes
            </h6>
            <span class="badge badge-light border">{{ number_format($pedidos->total()) }} registros</span>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 cxr-table">
                <thead>
                    <tr>
                        <th>Pedido</th>
                        <th>Fecha</th>
                        <th>Despacho / Crédito</th>
                        <th>Cliente</th>
                        <th>Vendedor</th>
                        <th class="text-right">Saldo Base</th>
                        <th class="text-right">Saldo IVA</th>
                        <th class="text-right">Saldo Ajustes</th>
                        <th class="text-right">Saldo Total</th>
                        <th>Estatus</th>
                        <th>Accion</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pedidos as $pedido)
                        <tr>
                            <td>
                                <div class="font-weight-bold">#{{ $pedido->id }}</div>
                                @if(!empty($pedido->referencia))
                                    <small class="text-muted">Ref: {{ $pedido->referencia }}</small>
                                @endif
                                @if(!empty($pedido->numero_factura))
                                    <div><span class="badge badge-info mt-1">Fact: {{ $pedido->numero_factura }}</span></div>
                                @endif
                            </td>
                            <td>
                                <div>{{ \Carbon\Carbon::parse($pedido->fecha)->format('d/m/Y') }}</div>
                                <small class="text-muted">{{ number_format((float) $pedido->antiguedad_dias, 0, ',', '.') }} días</small>
                            </td>
                            <td>
                                @if(!empty($pedido->fecha_despacho))
                                    <div>{{ \Carbon\Carbon::parse($pedido->fecha_despacho)->format('d/m/Y') }}</div>
                                @endif
                                @if($pedido->dias_credito > 0)
                                    @php $dr = (int) $pedido->dias_restantes; @endphp
                                    @if($dr > 0)
                                        <span class="cxr-dias-badge cxr-dias-vigente">
                                            <i class="fas fa-hourglass-half" style="font-size:.65rem;"></i>
                                            {{ $dr }} día{{ $dr !== 1 ? 's' : '' }} restante{{ $dr !== 1 ? 's' : '' }}
                                        </span>
                                    @elseif($dr === 0)
                                        <span class="cxr-dias-badge cxr-dias-hoy">
                                            <i class="fas fa-exclamation-circle" style="font-size:.65rem;"></i>
                                            Vence hoy
                                        </span>
                                    @else
                                        <span class="cxr-dias-badge cxr-dias-vencido">
                                            <i class="fas fa-times-circle" style="font-size:.65rem;"></i>
                                            {{ abs($dr) }} día{{ abs($dr) !== 1 ? 's' : '' }} vencido{{ abs($dr) !== 1 ? 's' : '' }}
                                        </span>
                                    @endif
                                    <div><small class="text-muted">Crédito: {{ $pedido->dias_credito }}d</small></div>
                                @else
                                    <span class="cxr-dias-badge cxr-dias-sin">Sin crédito</span>
                                @endif
                            </td>
                            <td>
                                <div class="font-weight-bold">{{ $pedido->descripcion }}</div>
                                <small class="text-muted">{{ $pedido->rif }} | {{ $pedido->telefono ?: 'S/T' }}</small>
                            </td>
                            <td>
                                <div>{{ $pedido->seller_code ?: 'S/COD' }}</div>
                                @if(!empty($pedido->vendedor_nombre) && strtoupper(trim($pedido->vendedor_nombre)) !== 'SIN ASIGNAR')
                                    <small class="text-muted">{{ $pedido->vendedor_nombre }}</small>
                                @endif
                            </td>
                            <td class="text-right cxr-amount">{{ $fmtBs($pedido->saldo_base) }}<span class="cxr-amount-usd">{{ $fmtUsd($pedido->saldo_base) }}</span></td>
                            <td class="text-right cxr-amount">{{ $fmtBs($pedido->saldo_iva_bs) }}<span class="cxr-amount-usd">{{ $fmtUsd($pedido->saldo_iva_bs) }}</span></td>
                            <td class="text-right cxr-amount">{{ $fmtBs($pedido->saldo_ajustes) }}<span class="cxr-amount-usd">{{ $fmtUsd($pedido->saldo_ajustes) }}</span></td>
                            <td class="text-right cxr-amount">{{ $fmtBs($pedido->saldo_total) }}<span class="cxr-amount-usd">{{ $fmtUsd($pedido->saldo_total) }}</span></td>
                            <td>
                                <span class="cxr-status {{ $statusClass($pedido->estatus) }}">{{ $pedido->estatus }}</span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-outline-primary btn-xs js-cxr-trazabilidad" title="Ver trazabilidad" data-pedido-id="{{ $pedido->id }}">
                                    <i class="fas fa-project-diagram"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-4 text-muted">
                                <i class="fas fa-check-circle mr-1 text-success"></i>
                                No hay pedidos pendientes con saldo para los filtros aplicados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($pedidos->hasPages())
            <div class="p-3 border-top">
                {{ $pedidos->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).on('click', '.js-cxr-trazabilidad', function () {
        const pedidoId = $(this).data('pedido-id');
        cxrVerTrazabilidad(pedidoId);
    });

    function cxrVerTrazabilidad(pedidoId) {
        if (typeof Swal === 'undefined' || typeof $ === 'undefined') {
            window.open("{{ url('admin/pagos/trazabilidad') }}/" + pedidoId, '_blank');
            return;
        }

        Swal.fire({
            title: 'Trazabilidad de Pagos',
            html: '<div class="py-3 text-center"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><div class="mt-2">Cargando...</div></div>',
            showConfirmButton: false,
            allowOutsideClick: false
        });

        $.ajax({
            url: "{{ url('admin/pagos/trazabilidad') }}/" + pedidoId,
            type: 'GET',
            success: function (response) {
                if (!response || response.type !== 'success') {
                    Swal.fire('Error', 'No se pudo cargar la trazabilidad.', 'error');
                    return;
                }

                let html = '';
                if (response.pagos && response.pagos.length > 0) {
                    html += '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>Fecha</th><th>Monto</th><th>Moneda</th><th>Referencia</th><th>Estatus</th><th>Tasa</th><th>Tipo</th></tr></thead><tbody>';
                    response.pagos.forEach(function (pago) {
                        const fecha = pago.fecha ? new Date(pago.fecha).toLocaleDateString('es-VE') : 'N/D';
                        const monto = Number(pago.monto || 0).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        const moneda = pago.moneda_pago || 'N/D';
                        const referencia = pago.referencia || '-';
                        const estatus = pago.estatus || 'N/D';
                        const tasa = pago.rate ? Number(pago.rate).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : 'N/D';
                        const tipo = pago.tipo_pago || 'N/D';
                        html += '<tr>' +
                            '<td>' + fecha + '</td>' +
                            '<td>' + monto + '</td>' +
                            '<td>' + moneda + '</td>' +
                            '<td>' + referencia + '</td>' +
                            '<td><span class="badge badge-light border">' + estatus + '</span></td>' +
                            '<td>' + tasa + '</td>' +
                            '<td>' + tipo + '</td>' +
                        '</tr>';
                    });
                    html += '</tbody></table></div>';
                } else {
                    html = '<div class="py-3 text-center text-muted">No hay pagos registrados para este pedido.</div>';
                }

                Swal.fire({
                    title: 'Trazabilidad - Pedido #' + pedidoId,
                    html: html,
                    width: 900,
                    confirmButtonText: 'Cerrar'
                });
            },
            error: function () {
                Swal.fire('Error', 'No se pudo consultar la trazabilidad de pagos.', 'error');
            }
        });
    }
</script>
@endsection
