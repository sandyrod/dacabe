@extends('layouts.app')

@section('titulo', 'Reporte Gerencial de Inventario')
@section('titulo_header', 'Análisis Gerencial de Inventario')
@section('subtitulo_header', 'Stock por producto y depósito')

@section('styles')
<style>
/* ── Variables de color ───────────────────────────────────── */
:root {
    --rep-navy:  #1e3a5f;
    --rep-gold:  #c9a84c;
    --rep-light: #f4f6f9;
}

/* ── Layout general ──────────────────────────────────────── */
.rep-header {
    background: linear-gradient(135deg, var(--rep-navy) 0%, #2d5282 100%);
    color: #fff;
    border-radius: .75rem .75rem 0 0;
    padding: 1.25rem 1.5rem;
}
.rep-header h4 { font-weight: 700; letter-spacing: .5px; margin: 0; }
.rep-header .subtitle { font-size: .82rem; opacity: .75; margin-top: 2px; }

.filter-card {
    border: 1px solid #d1d9e6;
    border-radius: .5rem;
    background: #fff;
}

/* ── KPI cards ────────────────────────────────────────────── */
.kpi-card {
    border-radius: .6rem;
    padding: .9rem 1.1rem;
    color: #fff;
    display: flex;
    align-items: center;
    gap: .9rem;
}
.kpi-card i { font-size: 1.9rem; opacity: .7; }
.kpi-card .val { font-size: 1.4rem; font-weight: 700; line-height: 1.1; }
.kpi-card .lbl { font-size: .72rem; opacity: .85; text-transform: uppercase; letter-spacing: .5px; }

/* ── Tabla de reporte ──────────────────────────────────────── */
.rep-table-wrap { overflow-x: auto; }

.rep-table {
    font-size: .8rem;
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
}
.rep-table thead th {
    background: var(--rep-navy);
    color: #fff;
    position: sticky;
    top: 0;
    z-index: 2;
    padding: .55rem .6rem;
    white-space: nowrap;
    border-right: 1px solid rgba(255,255,255,.15);
    font-weight: 600;
}
.rep-table thead th.dep-header {
    background: #2d5282;
    text-align: center;
}
.rep-table tbody td {
    padding: .4rem .6rem;
    border-bottom: 1px solid #eef0f4;
    vertical-align: middle;
}
.rep-table tbody tr:nth-child(even) { background: #fafbfc; }
.rep-table tbody tr:hover { background: rgba(30,58,95,.05); }

/* Stock crítico */
.stock-critico td { background: #fff5f5 !important; }
.stock-critico:hover td { background: #ffe8e8 !important; }
.badge-critico {
    background: #e53e3e; color: #fff;
    font-size: .65rem; padding: 2px 5px; border-radius: 3px;
}

/* Fila de totales */
.rep-table tfoot td {
    background: var(--rep-navy);
    color: #fff;
    font-weight: 700;
    padding: .6rem .6rem;
    position: sticky;
    bottom: 0;
}

/* Valores numéricos */
.num { text-align: right; font-variant-numeric: tabular-nums; }
.num-zero { color: #ccc; }
.price-usd { color: #2d6a4f; font-weight: 600; }
.price-bs  { color: #5c4033; }
.inv-inicial { 
    color: #16a085; 
    font-weight: 600; 
    text-align: center;
}

/* Código de producto */
.prod-code {
    font-family: 'SFMono-Regular', Consolas, monospace;
    font-size: .78rem;
    font-weight: 700;
    color: var(--rep-navy);
    white-space: nowrap;
}

/* Bultos footer */
.bultos-cell { font-size: .75rem; line-height: 1.2; }
.bultos-val  { font-weight: 700; color: #553c9a; }
.bultos-na   { color: #bbb; font-style: italic; }

/* ── Sección de variantes (tallas) ─────────────────────────── */
.variantes-section { margin-top: 2rem; }
.variante-card {
    border: 1px solid #d1d9e6;
    border-radius: .6rem;
    margin-bottom: 1rem;
    overflow: hidden;
}
.variante-header {
    background: linear-gradient(90deg, #553c9a, #7b5ea7);
    color: #fff;
    padding: .5rem 1rem;
    font-weight: 600;
    font-size: .85rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.variante-table { font-size: .78rem; }
.variante-table th { background: #f4f0ff; color: #553c9a; font-weight: 700; white-space: nowrap; }
.variante-table td.talla { font-weight: 700; color: #553c9a; }

/* ── Print ────────────────────────────────────────────────── */
@media print {
    .no-print, .sidebar-wrapper, .main-header, .content-header { display: none !important; }
    body, .content-wrapper { margin: 0 !important; padding: 0 !important; background: #fff !important; }
    .rep-table thead th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .rep-table tfoot td { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .kpi-card  { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .variante-header { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .filter-card { display: none !important; }
    .print-title {
        display: block !important;
        font-size: 18pt;
        font-weight: bold;
        text-align: center;
        margin-bottom: 6pt;
    }
    .print-date {
        display: block !important;
        font-size: 9pt;
        text-align: center;
        color: #555;
        margin-bottom: 12pt;
    }
}
.print-title, .print-date { display: none; }
</style>
@endsection

@section('content')
<div class="container-fluid">

    {{-- Título impreso --}}
    <div class="print-title">Análisis Gerencial de Inventario</div>
    <div class="print-date">Generado el {{ now()->format('d/m/Y H:i') }}</div>

    {{-- ── Filtros ── --}}
    <div class="filter-card p-3 mb-3 no-print">
        <form method="GET" action="{{ route('admin.reportes.inventario') }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="small font-weight-bold text-muted mb-1">Código Inicia con</label>
                    <input type="text" name="codigo_inicia" value="{{ request('codigo_inicia') }}"
                           class="form-control form-control-sm text-uppercase" placeholder="Ej: SG3">
                </div>
                <div class="col-md-2">
                    <label class="small font-weight-bold text-muted mb-1">Código desde</label>
                    <input type="text" name="codigo_desde" value="{{ request('codigo_desde') }}"
                           class="form-control form-control-sm text-uppercase" placeholder="Ej:  SG3.5AL">
                </div>
                <div class="col-md-2">
                    <label class="small font-weight-bold text-muted mb-1">Código hasta</label>
                    <input type="text" name="codigo_hasta" value="{{ request('codigo_hasta') }}"
                           class="form-control form-control-sm text-uppercase" placeholder="Ej: SGLVS">
                </div>
                <div class="col-md-2">
                    <label class="small font-weight-bold text-muted mb-1">Grupo</label>
                    <select name="cgrupo" class="form-control form-control-sm">
                        <option value="">— Todos —</option>
                        @foreach($grupos as $g)
                            <option value="{{ $g->CGRUPO }}" @selected(request('cgrupo') == $g->CGRUPO)>
                                {{ $g->CGRUPO }} – {{ $g->DGRUPO }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small font-weight-bold text-muted mb-1">Ordenar por</label>
                    <select name="sort_by" class="form-control form-control-sm">
                        <option value="codigo" @selected(request('sort_by','codigo')=='codigo')>Código</option>
                        <option value="nombre" @selected(request('sort_by')=='nombre')>Nombre</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" name="solo_con_stock" id="scs"
                               value="1" @checked(request('solo_con_stock'))>
                        <label class="form-check-label small" for="scs">Solo con stock</label>
                    </div>
                </div>
                <div class="col-md-2 d-flex gap-2 mt-3">
                    <button type="submit" name="buscar" value="1" class="btn btn-primary btn-sm btn-block mr-1">
                        <i class="fas fa-search mr-1"></i>Generar
                    </button>
                    <a href="{{ route('admin.reportes.inventario') }}" class="btn btn-outline-secondary btn-sm mr-1">
                        <i class="fas fa-times"></i>
                    </a>
                    @if($productos->isNotEmpty())
                    <button type="button" onclick="window.print()" class="btn btn-outline-dark btn-sm">
                        <i class="fas fa-print"></i>
                    </button>
                    @endif
                </div>
            </div>
        </form>
    </div>

    @if($productos->isEmpty() && !request()->anyFilled(['codigo_inicia', 'codigo_desde','codigo_hasta','cgrupo','buscar','solo_con_stock']))
        {{-- Estado inicial --}}
        <div class="text-center text-muted py-5">
            <i class="fas fa-chart-bar fa-4x mb-3 d-block" style="color:#c9a84c;opacity:.5"></i>
            <h5>Configure los filtros y presione <strong>Generar</strong></h5>
            <p class="small">Puede filtrar por rango de código, nombre, grupo o ver todos los productos.</p>
        </div>

    @elseif($productos->isEmpty())
        <div class="alert alert-warning">No se encontraron productos con los filtros indicados.</div>

    @else
        {{-- ── KPIs ── --}}
        <div class="row mb-3 no-print">
            <div class="col-6 col-md-2 mb-2">
                <div class="kpi-card" style="background:linear-gradient(135deg,#1e3a5f,#2d5282)">
                    <i class="fas fa-boxes"></i>
                    <div>
                        <div class="val">{{ number_format($totales['con_stock']) }}</div>
                        <div class="lbl">Productos con stock</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-2 mb-2">
                <div class="kpi-card" style="background:linear-gradient(135deg,#16a085,#1abc9c)">
                    <i class="fas fa-database"></i>
                    <div>
                        <div class="val">{{ number_format($totales['inventario_inicial'] ?? 0, 0) }}</div>
                        <div class="lbl">Inv. Inicial Total</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-2 mb-2">
                <div class="kpi-card" style="background:linear-gradient(135deg,#2d6a4f,#40916c)">
                    <i class="fas fa-dollar-sign"></i>
                    <div>
                        <div class="val">${{ number_format($totales['valor_usd'], 2) }}</div>
                        <div class="lbl">Valor total USD</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-2 mb-2">
                <div class="kpi-card" style="background:linear-gradient(135deg,#6b4c11,#c9a84c)">
                    <i class="fas fa-money-bill-wave"></i>
                    <div>
                        <div class="val">Bs {{ number_format($totales['valor_bs'], 0) }}</div>
                        <div class="lbl">Valor total Bs.</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-2 mb-2">
                <div class="kpi-card" style="background:linear-gradient(135deg,#c53030,#e53e3e)">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <div class="val">{{ number_format($totales['criticos']) }}</div>
                        <div class="lbl">Stock crítico</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Tabla principal ── --}}
        <div class="card shadow-sm border-0 mb-3">
            <div class="rep-header d-flex justify-content-between align-items-center">
                <div>
                    <h4><i class="fas fa-table mr-2"></i>Inventario por Producto</h4>
                    <div class="subtitle">
                        {{ $productos->count() }} productos ·
                        Total unidades: {{ number_format($totales['general']) }}
                    </div>
                </div>
                <div class="text-right print-title-inline small" style="opacity:.8">
                    Generado {{ now()->format('d/m/Y H:i') }}
                </div>
            </div>

            <div class="rep-table-wrap">
                <table class="rep-table">
                    <thead>
                        <tr>
                            <th style="min-width:90px">Código</th>
                            <th style="min-width:200px">Descripción</th>
                            <th class="num" style="min-width:90px">P.Divisa $</th>
                            <th class="num" style="min-width:90px">P.Bs.</th>
                            <th class="num" style="min-width:80px">Inv. Inicial</th>
                            @foreach($depositos as $dep)
                                <th class="dep-header" style="min-width:70px" title="{{ $dep->DDEPOS }}">
                                    {{ Str::limit($dep->DDEPOS, 8) }}
                                </th>
                            @endforeach
                            <th class="num dep-header" style="min-width:70px">TOTAL</th>
                            <th class="num" style="min-width:80px">% Venta</th>
                            <th class="dep-header" style="min-width:90px">BULTOS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($productos as $prod)
                        <tr class="{{ $prod->stock_critico ? 'stock-critico' : '' }}">
                            <td>
                                <span class="prod-code">{{ $prod->CODIGO }}</span>
                                @if($prod->stock_critico)
                                    <span class="badge-critico ml-1">MIN</span>
                                @endif
                            </td>
                            <td class="small">
                                {{ $prod->DESCR }}
                                @if($prod->CGRUPO)
                                    <span class="badge badge-secondary badge-sm ml-1" style="font-size:.65rem" title="{{ $prod->CGRUPO }}">
                                        {{ $prod->DGRUPO ?? $prod->CGRUPO }}
                                    </span>
                                @endif
                            </td>
                            <td class="num price-usd">{{ number_format((float)$prod->BASE1, 2) }}</td>
                            <td class="num price-bs">{{ number_format((float)$prod->BASE2, 2) }}</td>
                            <td class="num inv-inicial">
                                @php $invInicial = $prod->inventario_inicial ?? 0; @endphp
                                {{ $invInicial > 0 ? number_format($invInicial, 0) : '—' }}
                            </td>
                            @foreach($depositos as $dep)
                                @php $s = $prod->stocks[$dep->CDEPOS] ?? 0; @endphp
                                <td class="num {{ $s == 0 ? 'num-zero' : '' }}">
                                    {{ $s > 0 ? number_format($s, 0) : '—' }}
                                </td>
                            @endforeach
                            <td class="num font-weight-bold">{{ number_format($prod->total_stock, 0) }}</td>
                            <td class="num font-weight-bold">
                                @php
                                    $invInicial = $prod->inventario_inicial ?? 0;
                                    $existencias = $prod->total_stock;
                                    $porcentajeVenta = 0;
                                    
                                    if ($invInicial > 0) {
                                        $vendido = $invInicial - $existencias;
                                        $porcentajeVenta = ($vendido / $invInicial) * 100;
                                        if ($porcentajeVenta < 0) $porcentajeVenta = 0;
                                        if ($porcentajeVenta > 100) $porcentajeVenta = 100;
                                    } elseif ($existencias > 0) {
                                        $porcentajeVenta = 100;
                                    }
                                @endphp
                                @if($porcentajeVenta > 0)
                                    <span class="text-success">{{ number_format($porcentajeVenta, 1) }}%</span>
                                @elseif($porcentajeVenta == 0)
                                    <span class="text-muted">0%</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="num font-weight-bold">
                                @php
                                    $invInicial = $prod->inventario_inicial ?? 0;
                                    $existencias = $prod->total_stock;
                                    $porcentajeStock = 0;
                                    
                                    if ($invInicial > 0) {
                                        $porcentajeStock = ($existencias / $invInicial) * 100;
                                        if ($porcentajeStock < 0) $porcentajeStock = 0;
                                        if ($porcentajeStock > 100) $porcentajeStock = 100;
                                    } elseif ($existencias > 0) {
                                        $porcentajeStock = 100;
                                    }
                                @endphp
                                @if($porcentajeStock > 0)
                                    <span class="text-success">{{ number_format($porcentajeStock, 1) }}%</span>
                                @elseif($porcentajeStock == 0)
                                    <span class="text-muted">0%</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="bultos-cell">
                                @if($prod->upb !== null && $prod->upb > 0)
                                    <span class="bultos-val">{{ number_format($prod->bultos_total, 0) }}</span>
                                    <span class="text-muted"> bts</span>
                                    @if($prod->unidades_sobrantes > 0)
                                        <br><span class="text-muted" style="font-size:.7rem">
                                            + {{ number_format($prod->unidades_sobrantes, 0) }} un.
                                        </span>
                                    @endif
                                @else
                                    <span class="bultos-na">{{ $prod->upb === null ? 'N/C' : '—' }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="font-weight-bold">TOTALES</td>
                            <td class="num inv-inicial">{{ number_format($totales['inventario_inicial'] ?? 0, 0) }}</td>
                            @foreach($depositos as $dep)
                                <td class="num">{{ number_format($totales['por_deposito'][$dep->CDEPOS] ?? 0, 0) }}</td>
                            @endforeach
                            <td class="num">{{ number_format($totales['general'], 0) }}</td>
                            <td class="num font-weight-bold">
                                @php
                                    $totalInvInicial = $totales['inventario_inicial'] ?? 0;
                                    $totalExistencias = $totales['general'];
                                    $totalPorcentajeVenta = 0;
                                    
                                    if ($totalInvInicial > 0) {
                                        $vendido = $totalInvInicial - $totalExistencias;
                                        $totalPorcentajeVenta = ($vendido / $totalInvInicial) * 100;
                                        if ($totalPorcentajeVenta < 0) $totalPorcentajeVenta = 0;
                                        if ($totalPorcentajeVenta > 100) $totalPorcentajeVenta = 100;
                                    } elseif ($totalExistencias > 0) {
                                        $totalPorcentajeVenta = 100;
                                    }
                                @endphp
                                @if($totalPorcentajeVenta > 0)
                                    <span class="text-success">{{ number_format($totalPorcentajeVenta, 1) }}%</span>
                                @else
                                    <span class="text-muted">0%</span>
                                @endif
                            </td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>{{-- /wrap --}}
        </div>{{-- /card --}}

        {{-- ── Sección variantes por talla ── --}}
        @if(count($variantes) > 0)
        <div class="variantes-section no-print">
            <h5 class="font-weight-bold mb-3" style="color:#553c9a">
                <i class="fas fa-layer-group mr-2"></i>Resumen por Variantes de Talla
                <span class="badge badge-secondary ml-2">{{ count($variantes) }} grupos</span>
            </h5>

            @foreach($variantes as $vKey => $variante)
            <div class="variante-card">
                <div class="variante-header">
                    <span>
                        <i class="fas fa-tags mr-1"></i>{{ $variante['nombre_base'] }}
                    </span>
                    <span class="badge" style="background:rgba(255,255,255,.2)">
                        {{ $variante['dgrupo'] ?? $variante['cgrupo'] }}
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm variante-table mb-0">
                        <thead>
                            <tr>
                                <th>Talla</th>
                                <th>Código</th>
                                @foreach($depositos as $dep)
                                    <th class="text-center" title="{{ $dep->DDEPOS }}">{{ $dep->CDEPOS }}</th>
                                @endforeach
                                <th class="text-center">Total</th>
                                <th class="text-center">Bultos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalVariantePorDep = []; $totalVariante = 0; @endphp
                            @foreach($variante['tallas'] as $talla => $prod)
                            @php $totalVariante += $prod->total_stock; @endphp
                            <tr>
                                <td class="talla">{{ $talla }}</td>
                                <td><code class="small text-primary">{{ $prod->CODIGO }}</code></td>
                                @foreach($depositos as $dep)
                                    @php
                                        $s = $prod->stocks[$dep->CDEPOS] ?? 0;
                                        $totalVariantePorDep[$dep->CDEPOS] = ($totalVariantePorDep[$dep->CDEPOS] ?? 0) + $s;
                                    @endphp
                                    <td class="text-center {{ $s == 0 ? 'text-muted' : 'font-weight-bold' }}">
                                        {{ $s > 0 ? number_format($s, 0) : '—' }}
                                    </td>
                                @endforeach
                                <td class="text-center font-weight-bold">{{ number_format($prod->total_stock, 0) }}</td>
                                <td class="text-center" style="color:#553c9a;font-weight:600">
                                    @if($prod->upb !== null && $prod->upb > 0)
                                        {{ number_format($prod->bultos_total, 0) }} bts
                                    @else
                                        <span class="text-muted">N/C</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background:#f4f0ff;font-weight:700;color:#553c9a">
                                <td colspan="2">TOTAL</td>
                                @foreach($depositos as $dep)
                                    <td class="text-center">{{ number_format($totalVariantePorDep[$dep->CDEPOS] ?? 0, 0) }}</td>
                                @endforeach
                                <td class="text-center">{{ number_format($totalVariante, 0) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            @endforeach
        </div>
        @endif

    @endif {{-- /productos --}}

</div>
@endsection
