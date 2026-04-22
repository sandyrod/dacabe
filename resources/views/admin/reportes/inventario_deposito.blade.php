@extends('layouts.app')

@section('titulo', 'Inventario por Depósito')
@section('titulo_header', 'Análisis de Inventario por Depósito')
@section('subtitulo_header', 'Stock de productos en una ubicación específica')

@section('styles')
<style>
:root { --rep-navy:#1e3a5f; --rep-gold:#c9a84c; }
.rep-header { background:linear-gradient(135deg,#1b4f72,#2980b9); color:#fff; border-radius:.75rem .75rem 0 0; padding:1.25rem 1.5rem; }
.rep-header h4 { font-weight:700; margin:0; }
.rep-header .subtitle { font-size:.82rem; opacity:.75; }
.filter-card { border:1px solid #d1d9e6; border-radius:.5rem; background:#fff; }
.kpi-card { border-radius:.6rem; padding:.9rem 1.1rem; color:#fff; display:flex; align-items:center; gap:.9rem; }
.kpi-card i { font-size:1.9rem; opacity:.7; }
.kpi-card .val { font-size:1.4rem; font-weight:700; line-height:1.1; }
.kpi-card .lbl { font-size:.72rem; opacity:.85; text-transform:uppercase; letter-spacing:.5px; }
.rep-table { font-size:.8rem; border-collapse:separate; border-spacing:0; width:100%; }
.rep-table thead th { background:var(--rep-navy); color:#fff; position:sticky; top:0; z-index:2; padding:.55rem .7rem; white-space:nowrap; font-weight:600; border-right:1px solid rgba(255,255,255,.12); }
.rep-table tbody td { padding:.4rem .7rem; border-bottom:1px solid #eef0f4; vertical-align:middle; }
.rep-table tbody tr:nth-child(even) { background:#fafbfc; }
.rep-table tbody tr:hover { background:rgba(25,118,210,.04); }
.rep-table tfoot td { background:var(--rep-navy); color:#fff; font-weight:700; padding:.6rem .7rem; position:sticky; bottom:0; }
.stock-critico td { background:#fff5f5 !important; }
.badge-critico { background:#e53e3e; color:#fff; font-size:.65rem; padding:2px 5px; border-radius:3px; }
.prod-code { font-family:'SFMono-Regular',Consolas,monospace; font-size:.78rem; font-weight:700; color:var(--rep-navy); }
.num { text-align:right; font-variant-numeric:tabular-nums; }
.num-zero { color:#ccc; }
.price-usd { color:#2d6a4f; font-weight:600; }
.price-bs  { color:#5c4033; }
.inv-inicial { 
    color: #16a085; 
    font-weight: 600; 
    text-align: center;
}
.bultos-val { font-weight:700; color:#553c9a; }
.bultos-na  { color:#bbb; font-style:italic; }
/* Barra de stock visual */
.stock-bar-bg { background:#eef0f4; border-radius:4px; height:6px; width:80px; overflow:hidden; display:inline-block; vertical-align:middle; }
.stock-bar    { background:#1b4f72; border-radius:4px; height:6px; transition:width .3s; }
.stock-bar.ok   { background:#40916c; }
.stock-bar.warn { background:#e9c46a; }
.stock-bar.crit { background:#e53e3e; }
@media print {
    .no-print,.sidebar-wrapper,.main-header,.content-header { display:none !important; }
    body,.content-wrapper { margin:0 !important; padding:0 !important; background:#fff !important; }
    .rep-table thead th,.rep-table tfoot td,.kpi-card { -webkit-print-color-adjust:exact; print-color-adjust:exact; }
    .filter-card { display:none !important; }
    .print-title { display:block !important; font-size:18pt; font-weight:bold; text-align:center; margin-bottom:4pt; }
    .print-sub   { display:block !important; font-size:10pt; text-align:center; color:#555; margin-bottom:12pt; }
}
.print-title,.print-sub { display:none; }
</style>
@endsection

@section('content')
<div class="container-fluid">

    <div class="print-title">Análisis de Inventario por Depósito</div>
    <div class="print-sub">
        {{ $deposito ? $deposito->DDEPOS : 'Todos los depósitos' }} ·
        Generado el {{ now()->format('d/m/Y H:i') }}
    </div>

    {{-- ── Filtros ── --}}
    <div class="filter-card p-3 mb-3 no-print">
        <form method="GET" action="{{ route('admin.reportes.inventario.deposito') }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="small font-weight-bold text-muted mb-1">Depósito / Ubicación <span class="text-danger">*</span></label>
                    <select name="cdepos" class="form-control form-control-sm" required>
                        <option value="">— Seleccione depósito —</option>
                        @foreach($todosDepositos as $d)
                            <option value="{{ $d->CDEPOS }}" @selected(request('cdepos') == $d->CDEPOS)>
                                {{ $d->CDEPOS }} – {{ $d->DDEPOS }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small font-weight-bold text-muted mb-1">Código Inicia con</label>
                    <input type="text" name="codigo_inicia" value="{{ request('codigo_inicia') }}"
                           class="form-control form-control-sm text-uppercase" placeholder="Ej: SG3">
                </div>
                <div class="col-md-2">
                    <label class="small font-weight-bold text-muted mb-1">Código desde</label>
                    <input type="text" name="codigo_desde" value="{{ request('codigo_desde') }}"
                           class="form-control form-control-sm text-uppercase" placeholder="Ej: SG3.5AL">
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
                <div class="col-md-1 d-flex align-items-end">
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" name="solo_con_stock"
                               id="scs" value="1" @checked(request('solo_con_stock'))>
                        <label class="form-check-label small" for="scs">Con stock</label>
                    </div>
                </div>
                <div class="col-md-3 d-flex gap-2 mt-3">
                    <button type="submit" name="buscar" value="1" class="btn btn-primary btn-sm btn-block mr-1">
                        <i class="fas fa-search mr-1"></i>Generar
                    </button>
                    <a href="{{ route('admin.reportes.inventario.deposito') }}" class="btn btn-outline-secondary btn-sm mr-1">
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

    @if($productos->isEmpty() && !request()->filled('buscar'))
        <div class="text-center text-muted py-5">
            <i class="fas fa-warehouse fa-4x mb-3 d-block" style="color:#2980b9;opacity:.4"></i>
            <h5>Seleccione un depósito y presione <strong>Generar</strong></h5>
        </div>

    @elseif($productos->isEmpty())
        <div class="alert alert-warning">No se encontraron productos con los filtros indicados.</div>

    @else
        {{-- KPIs --}}
        <div class="row mb-3 no-print">
            <div class="col-6 col-md-2 mb-2">
                <div class="kpi-card" style="background:linear-gradient(135deg,#1b4f72,#2980b9)">
                    <i class="fas fa-warehouse"></i>
                    <div>
                        <div class="val">{{ $deposito ? $deposito->DDEPOS : 'Todos' }}</div>
                        <div class="lbl">Depósito</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-2 mb-2">
                <div class="kpi-card" style="background:linear-gradient(135deg,#155724,#28a745)">
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
                <div class="kpi-card" style="background:linear-gradient(135deg,#c53030,#e53e3e)">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <div class="val">{{ $totales['criticos'] }}</div>
                        <div class="lbl">Stock crítico</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="card shadow-sm border-0 mb-3">
            <div class="rep-header d-flex justify-content-between align-items-center">
                <div>
                    <h4>
                        <i class="fas fa-warehouse mr-2"></i>
                        {{ $deposito ? $deposito->DDEPOS : 'Todos los depósitos' }}
                    </h4>
                    <div class="subtitle">
                        {{ $productos->count() }} productos ·
                        Total: {{ number_format($totales['general']) }} unidades
                    </div>
                </div>
                <span class="small" style="opacity:.75">{{ now()->format('d/m/Y H:i') }}</span>
            </div>

            <div style="overflow-x:auto">
                <table class="rep-table">
                    <thead>
                        <tr>
                            <th style="min-width:90px">Código</th>
                            <th style="min-width:200px">Descripción</th>
                            <th>Grupo</th>
                            <th class="num" style="min-width:90px">P.Divisa $</th>
                            <th class="num" style="min-width:90px">P.Bs.</th>
                            <th class="num" style="min-width:80px">Inv. Inicial</th>
                            <th class="num" style="min-width:80px">Existencias</th>
                            <th class="num" style="min-width:100px">% Venta</th>
                            <th style="min-width:100px">Stock</th>
                            <th class="num" style="min-width:80px">Valor USD</th>
                            <th style="min-width:90px">Bultos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($productos as $prod)
                        @php
                            $stockDep  = $prod->stocks[$deposito->CDEPOS ?? ''] ?? $prod->total_stock;
                            $pctStock  = $prod->SMAX > 0 ? min(100, $stockDep / $prod->SMAX * 100) : null;
                            $barClass  = $prod->stock_critico ? 'crit' : ($pctStock < 40 ? 'warn' : 'ok');
                        @endphp
                        <tr class="{{ $prod->stock_critico ? 'stock-critico' : '' }}">
                            <td>
                                <span class="prod-code">{{ $prod->CODIGO }}</span>
                                @if($prod->stock_critico)
                                    <span class="badge-critico ml-1">MIN</span>
                                @endif
                            </td>
                            <td class="small">{{ $prod->DESCR }}</td>
                            <td>
                                <span class="badge badge-secondary" style="font-size:.65rem" title="{{ $prod->CGRUPO }}">
                                    {{ $prod->DGRUPO ?? $prod->CGRUPO }}
                                </span>
                            </td>
                            <td class="num price-usd">{{ number_format((float)$prod->BASE1, 2) }}</td>
                            <td class="num price-bs">{{ number_format((float)$prod->BASE2, 2) }}</td>
                            <td class="inv-inicial">
                                {{ $prod->inventario_inicial > 0 ? number_format($prod->inventario_inicial, 0) : '—' }}
                            </td>
                            <td class="num {{ $stockDep == 0 ? 'num-zero' : 'font-weight-bold' }}">
                                {{ $stockDep > 0 ? number_format($stockDep, 0) : '—' }}
                            </td>
                            <td class="num font-weight-bold">
                                @if($prod->porcentaje_venta > 0)
                                    <span class="text-success">{{ number_format($prod->porcentaje_venta, 1) }}%</span>
                                @elseif($prod->porcentaje_venta == 0)
                                    <span class="text-muted">0%</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($pctStock !== null)
                                    <div class="stock-bar-bg">
                                        <div class="stock-bar {{ $barClass }}" style="width:{{ $pctStock }}%"></div>
                                    </div>
                                    <span class="small text-muted ml-1">{{ number_format($pctStock, 0) }}%</span>
                                @else
                                    <span class="small text-muted">Sin tope</span>
                                @endif
                            </td>
                            <td class="num price-usd">
                                {{ number_format($stockDep * (float)$prod->BASE1, 2) }}
                            </td>
                            <td class="small" style="color:#553c9a">
                                @if($prod->upb !== null && $prod->upb > 0)
                                    <span class="bultos-val">{{ number_format($prod->bultos_total, 0) }}</span> bts
                                    @if($prod->unidades_sobrantes > 0)
                                        <span class="text-muted"> +{{ number_format($prod->unidades_sobrantes, 0) }} u</span>
                                    @endif
                                @else
                                    <span class="bultos-na">N/C</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="font-weight-bold">TOTALES</td>
                            <td class="num inv-inicial">{{ number_format($totales['inventario_inicial'] ?? 0, 0) }}</td>
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
                            <td class="num">${{ number_format($totales['valor_usd'], 2) }}</td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Variantes por talla --}}
        @if(count($variantes) > 0)
        <div class="no-print mb-4">
            <h5 class="font-weight-bold mb-3" style="color:#553c9a">
                <i class="fas fa-layer-group mr-2"></i>Variantes de Talla
            </h5>
            @foreach($variantes as $variante)
            @php $cdeposKey = $deposito->CDEPOS ?? null; @endphp
            <div style="border:1px solid #d1d9e6;border-radius:.6rem;margin-bottom:1rem;overflow:hidden">
                <div style="background:linear-gradient(90deg,#553c9a,#7b5ea7);color:#fff;padding:.5rem 1rem;font-weight:600;font-size:.85rem;display:flex;justify-content:space-between;align-items:center">
                    <span><i class="fas fa-tags mr-1"></i>{{ $variante['nombre_base'] }}</span>
                    <span style="font-weight:400;font-size:.78rem;opacity:.85">
                        {{ $variante['dgrupo'] ?? $variante['cgrupo'] }}
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0" style="font-size:.78rem">
                        <thead>
                            <tr style="background:#f4f0ff">
                                <th style="color:#553c9a">Talla</th>
                                <th style="color:#553c9a">Código</th>
                                <th class="text-center" style="color:#553c9a">Existencias</th>
                                <th class="text-center" style="color:#553c9a">Bultos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $tvTotal = 0; @endphp
                            @foreach($variante['tallas'] as $talla => $prod)
                            @php
                                $sv = $cdeposKey
                                    ? ($prod->stocks[$cdeposKey] ?? 0)
                                    : $prod->total_stock;
                                $tvTotal += $sv;
                            @endphp
                            <tr>
                                <td style="font-weight:700;color:#553c9a">{{ $talla }}</td>
                                <td><code class="small text-primary">{{ $prod->CODIGO }}</code></td>
                                <td class="text-center {{ $sv == 0 ? 'text-muted' : 'font-weight-bold' }}">
                                    {{ $sv > 0 ? number_format($sv, 0) : '—' }}
                                </td>
                                <td class="text-center" style="color:#553c9a;font-weight:600">
                                    @if($prod->upb !== null && $prod->upb > 0)
                                        {{ number_format(intdiv((int)$sv, $prod->upb), 0) }} bts
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
                                <td class="text-center">{{ number_format($tvTotal, 0) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            @endforeach
        </div>
        @endif

    @endif
</div>
@endsection
