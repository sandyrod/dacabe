@extends('layouts.app')

@section('titulo', config('app.name') . ' — Comprobantes de Retención')
@section('titulo_header', 'Retenciones de IVA')
@section('subtitulo_header', 'Comprobantes pendientes de validación')

@section('styles')
<style>
    :root {
        --ret-primary:   #1e3a5f;
        --ret-accent:    #f97316;
        --ret-success:   #16a34a;
        --ret-warning:   #d97706;
        --ret-surface:   #f8fafc;
        --ret-border:    #e2e8f0;
    }

    .ret-page { background: var(--ret-surface); min-height: 100vh; }

    /* ── Stat cards ── */
    .stat-card {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 24px rgba(0,0,0,.08);
        transition: transform .2s, box-shadow .2s;
    }
    .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 32px rgba(0,0,0,.12); }
    .stat-card .stat-icon {
        width: 56px; height: 56px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 24px; color: #fff; flex-shrink: 0;
    }
    .stat-card .stat-value { font-size: 28px; font-weight: 800; line-height: 1; color: #1e293b; }
    .stat-card .stat-label { font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: .6px; color: #64748b; margin-top: 4px; }

    /* ── Filter bar ── */
    .filter-bar {
        background: #fff;
        border: 1px solid var(--ret-border);
        border-radius: 14px;
        padding: 20px 24px;
        margin-bottom: 24px;
        box-shadow: 0 2px 12px rgba(0,0,0,.05);
    }
    .filter-bar .form-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #64748b; margin-bottom: 6px; }
    .filter-bar .form-control,
    .filter-bar .form-select { border-radius: 8px; border-color: #e2e8f0; font-size: 14px; }

    /* ── Table ── */
    .ret-table-card {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 24px rgba(0,0,0,.07);
    }
    .ret-table-card .card-header {
        background: linear-gradient(135deg, var(--ret-primary) 0%, #2a5298 100%);
        padding: 18px 24px;
        border-bottom: none;
    }
    .ret-table thead th {
        background: #f1f5f9;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .6px;
        color: #475569;
        border-bottom: 2px solid #e2e8f0;
        padding: 12px 16px;
        white-space: nowrap;
    }
    .ret-table tbody tr { transition: background .15s; }
    .ret-table tbody tr:hover { background: #f8fafc; }
    .ret-table tbody td { padding: 14px 16px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-size: 14px; }

    /* ── Status badges ── */
    .badge-ret-pending {
        background: linear-gradient(135deg, #fff7ed, #ffedd5);
        color: #9a3412;
        border: 1px solid #fed7aa;
        font-size: 11px; font-weight: 700; border-radius: 20px; padding: 4px 10px;
    }
    .base-status-chip {
        display: inline-flex;
        align-items: flex-start;
        gap: 8px;
        min-width: 128px;
        text-align: left;
        border-radius: 12px;
        border: 1px solid transparent;
        padding: 7px 10px;
        font-size: 11px;
        font-weight: 700;
        line-height: 1.1;
    }
    .base-status-chip i {
        margin-top: 1px;
        font-size: 11px;
    }
    .base-status-content {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }
    .base-status-title {
        letter-spacing: .15px;
        white-space: nowrap;
    }
    .base-status-value {
        font-size: 12px;
        font-weight: 800;
    }
    .base-status-ok {
        background: linear-gradient(135deg, #ecfdf3, #dff8ea);
        color: #166534;
        border-color: #b7ebcb;
    }
    .base-status-ok .base-status-value {
        color: #15803d;
    }
    .base-status-pending {
        background: linear-gradient(135deg, #fff8ea, #ffefcc);
        color: #92400e;
        border-color: #f9d78d;
    }
    .base-status-pending .base-status-value {
        color: #b45309;
    }

    /* ── Action buttons ── */
    .btn-detail {
        border: 1.5px solid #3b82f6; color: #3b82f6; background: transparent;
        border-radius: 8px; padding: 5px 12px; font-size: 12px; font-weight: 600;
        transition: all .2s;
    }
    .btn-detail:hover { background: #3b82f6; color: #fff; }
    .btn-approve {
        border: 1.5px solid var(--ret-success); color: var(--ret-success); background: transparent;
        border-radius: 8px; padding: 5px 12px; font-size: 12px; font-weight: 600;
        transition: all .2s;
    }
    .btn-approve:hover { background: var(--ret-success); color: #fff; }

    /* ── Fila comprobante listo por validar ── */
    .row-comprobante-listo {
        background: linear-gradient(90deg, #dcfce7 0%, #f0fdf4 55%, #ffffff 100%) !important;
        border-left: 5px solid #16a34a;
        box-shadow: inset 0 0 0 1px rgba(22,163,74,.12);
    }
    .row-comprobante-listo:hover { background: linear-gradient(90deg, #bbf7d0 0%, #dcfce7 55%, #f8fafc 100%) !important; }
    .row-comprobante-listo td:first-child { padding-left: 11px; }
    .row-comprobante-listo td { border-bottom-color: #d1fae5 !important; }
    .comprobante-chip {
        display: inline-flex;
        align-items: flex-start;
        gap: 8px;
        min-width: 132px;
        text-align: left;
        border-radius: 12px;
        border: 1px solid transparent;
        padding: 7px 10px;
        font-size: 11px;
        font-weight: 700;
        line-height: 1.1;
    }
    .comprobante-chip i {
        margin-top: 1px;
        font-size: 11px;
    }
    .comprobante-content {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }
    .comprobante-title {
        letter-spacing: .15px;
        white-space: nowrap;
    }
    .comprobante-subtitle {
        font-size: 11px;
        font-weight: 700;
    }
    .comprobante-ok {
        background: linear-gradient(135deg, #ecfdf3, #dff8ea);
        color: #166534;
        border-color: #b7ebcb;
    }
    .comprobante-ok .comprobante-subtitle {
        color: #15803d;
    }
    .comprobante-missing {
        background: linear-gradient(135deg, #fff8ea, #ffefcc);
        color: #92400e;
        border-color: #f9d78d;
    }
    .comprobante-missing .comprobante-subtitle {
        color: #b45309;
    }
    .btn-comprobante-link {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        border: 1px solid #c7ddff;
        background: #eff6ff;
        color: #1d4ed8;
        border-radius: 8px;
        padding: 4px 8px;
        font-size: 11px;
        font-weight: 700;
        text-decoration: none;
        transition: all .2s;
    }
    .btn-comprobante-link:hover {
        border-color: #93c5fd;
        background: #dbeafe;
        color: #1e40af;
    }

    /* ── Modal ── */
    #modalDetalle .modal-header {
        background: linear-gradient(135deg, var(--ret-primary) 0%, #2a5298 100%);
        border-radius: 12px 12px 0 0;
    }
    #modalDetalle .modal-content { border-radius: 12px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,.2); }
    .det-section-title {
        font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .7px;
        color: #475569; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 16px;
    }
    .det-pill { display: inline-flex; align-items: center; gap: 6px; border-radius: 8px; padding: 8px 14px; font-size: 13px; }
    .det-table th { font-size: 11px; text-transform: uppercase; letter-spacing: .5px; color: #64748b; font-weight: 700; }
    .det-table td { font-size: 13px; }

    /* ── Pagination ── */
    .pagination {
        gap: 4px;
        margin-bottom: 0;
    }
    .pagination .page-item .page-link {
        min-width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        margin: 0;
        border: 1px solid #dbe5f1;
        background: #ffffff;
        color: #334155;
        font-size: 13px;
        font-weight: 700;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
        transition: all .2s ease;
    }
    .pagination .page-item .page-link:hover {
        border-color: #93c5fd;
        color: #1e40af;
        background: #eff6ff;
        transform: translateY(-1px);
    }
    .pagination .page-item.active .page-link {
        border-color: #1e3a8a;
        background: linear-gradient(135deg, #1d4ed8, #1e40af);
        color: #fff;
        box-shadow: 0 6px 14px rgba(29, 78, 216, 0.3);
    }
    .pagination .page-item.disabled .page-link {
        color: #94a3b8;
        background: #f8fafc;
        border-color: #e2e8f0;
        box-shadow: none;
        cursor: not-allowed;
    }

    /* ── Fallback para paginador Tailwind (links()) en layout AdminLTE ── */
    .ret-page nav[role="navigation"] {
        width: 100%;
        display: flex;
        justify-content: center;
    }
    .ret-page nav[role="navigation"] .hidden.sm\:flex-1.sm\:flex {
        display: flex !important;
        justify-content: center;
        width: 100%;
    }
    .ret-page nav[role="navigation"] .hidden.sm\:flex-1.sm\:flex > div:first-child {
        display: none;
    }
    .ret-page nav[role="navigation"] .relative.z-0.inline-flex {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: none;
    }
    .ret-page nav[role="navigation"] .relative.z-0.inline-flex > * {
        min-width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 12px;
        border-radius: 10px;
        border: 1px solid #dbe5f1;
        background: #fff;
        color: #334155;
        font-size: 13px;
        font-weight: 700;
        line-height: 1;
        text-decoration: none;
        transition: all .2s ease;
    }
    .ret-page nav[role="navigation"] .relative.z-0.inline-flex > a:hover {
        border-color: #93c5fd;
        color: #1e40af;
        background: #eff6ff;
        transform: translateY(-1px);
    }
    .ret-page nav[role="navigation"] .relative.z-0.inline-flex > span[aria-current="page"] {
        border-color: #1e3a8a;
        background: linear-gradient(135deg, #1d4ed8, #1e40af);
        color: #fff;
        box-shadow: 0 6px 14px rgba(29, 78, 216, 0.3);
    }
    .ret-page nav[role="navigation"] .relative.z-0.inline-flex > span[aria-disabled="true"] {
        color: #94a3b8;
        background: #f8fafc;
        border-color: #e2e8f0;
        box-shadow: none;
        cursor: not-allowed;
    }
    .ret-page nav[role="navigation"] svg {
        width: 1rem !important;
        height: 1rem !important;
        display: inline-block;
        vertical-align: middle;
    }

    @media (max-width: 768px) {
        .base-status-chip,
        .comprobante-chip {
            min-width: auto;
        }
        .pagination .page-item .page-link {
            min-width: 32px;
            height: 32px;
            padding: 0 8px;
            font-size: 12px;
        }
        .ret-page nav[role="navigation"] .relative.z-0.inline-flex {
            gap: 4px;
        }
        .ret-page nav[role="navigation"] .relative.z-0.inline-flex > * {
            min-width: 32px;
            height: 32px;
            padding: 0 8px;
            font-size: 12px;
        }
        .ret-page nav[role="navigation"] .sm\:hidden {
            display: none !important;
        }
    }
</style>
@endsection

@section('content')
<div class="ret-page">
<div class="container-fluid py-4 px-4">

    {{-- ═══════════════════ HEADER ═══════════════════ --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 style="font-size:24px; font-weight:800; color:#1e293b; margin:0;">
                <i class="fas fa-file-invoice me-2" style="color: var(--ret-accent);"></i>
                Comprobantes de Retención
            </h2>
            <p class="mb-0 mt-1" style="color:#64748b; font-size:14px;">
                Pedidos con IVA retenido pendiente de validación por comprobante
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.retenciones.index') }}" class="btn btn-sm" style="border:1.5px solid #e2e8f0; border-radius:8px; color:#475569; font-size:13px;">
                <i class="fas fa-sync-alt me-1"></i> Actualizar
            </a>
        </div>
    </div>

    {{-- ═══════════════════ STAT CARDS ═══════════════════ --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-4">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center gap-3 p-4">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f97316, #ea580c);">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <div>
                        <div class="stat-value">{{ number_format($stats['total_pedidos']) }}</div>
                        <div class="stat-label">Pedidos pendientes</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center gap-3 p-4">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div>
                        <div class="stat-value" style="font-size:22px;">Bs. {{ number_format($stats['total_retencion_bs'], 2, ',', '.') }}</div>
                        <div class="stat-label">Total retención pendiente</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center gap-3 p-4">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #16a34a, #15803d);">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <div>
                        <div class="stat-value">{{ number_format($stats['solo_iva_pendiente']) }}</div>
                        <div class="stat-label">Base ya pagada, solo IVA</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════ FILTROS ═══════════════════ --}}
    <div class="filter-bar">
        <form method="GET" action="{{ route('admin.retenciones.index') }}">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label">Vendedor</label>
                    <select name="vendedor" class="form-select">
                        <option value="">Todos los vendedores</option>
                        @foreach($vendedores as $v)
                            <option value="{{ $v->id }}" {{ request('vendedor') == $v->id ? 'selected' : '' }}>
                                {{ $v->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Cliente (nombre o RIF)</label>
                    <input type="text" name="cliente" class="form-control" placeholder="Buscar…" value="{{ request('cliente') }}">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Desde</label>
                    <input type="date" name="fecha_inicio" class="form-control" value="{{ request('fecha_inicio') }}">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Hasta</label>
                    <input type="date" name="fecha_fin" class="form-control" value="{{ request('fecha_fin') }}">
                </div>
                <div class="col-12 col-md-1">
                    <label class="form-label">Base</label>
                    <select name="estado_base" class="form-select">
                        <option value="">Todas</option>
                        <option value="pagada" {{ request('estado_base') === 'pagada' ? 'selected' : '' }}>Pagada</option>
                        <option value="pendiente" {{ request('estado_base') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                    </select>
                </div>
                <div class="col-12 col-md-1 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill" style="border-radius:8px; font-size:13px; font-weight:600;" title="Filtrar">
                        <i class="fas fa-search"></i>
                    </button>
                    <a href="{{ route('admin.retenciones.index') }}" class="btn flex-fill" style="border:1.5px solid #e2e8f0; border-radius:8px; font-size:13px; color:#475569;" title="Limpiar filtros">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- ═══════════════════ TABLA ═══════════════════ --}}
    <div class="card ret-table-card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <div style="width:40px; height:40px; border-radius:10px; background:rgba(255,255,255,.15); display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-list-alt" style="color:#fff; font-size:18px;"></i>
                </div>
                <div>
                    <h5 class="mb-0" style="color:#fff; font-weight:700;">Pedidos con retención pendiente</h5>
                    <small style="color:rgba(255,255,255,.7);">{{ $pedidos->total() }} resultado{{ $pedidos->total() != 1 ? 's' : '' }}</small>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            @if($pedidos->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-check-circle fa-3x mb-3" style="color:#16a34a;"></i>
                    <p class="mb-0 fw-semibold">Sin comprobantes pendientes</p>
                    <small>No hay pedidos con retención de IVA por validar.</small>
                </div>
            @else
            <div class="table-responsive">
                <table class="table ret-table mb-0">
                    <thead>
                        <tr>
                            <th>Pedido</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Vendedor</th>
                            <th class="text-end">Base ($)</th>
                            <th class="text-end">IVA total (Bs.)</th>
                            <th class="text-end">Retención (Bs.)</th>
                            <th class="text-center">Comprobante</th>
                            <th class="text-center">Estado base</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pedidos as $p)
                        <tr class="{{ $p->comprobante_retencion ? 'row-comprobante-listo' : '' }}">
                            <td>
                                <span class="fw-bold" style="color:#1e3a5f;">#{{ $p->id }}</span>
                                <br>
                                <small class="text-muted" style="font-size:11px;">Ret. {{ $p->porc_retencion }}%</small>
                            </td>
                            <td style="color:#475569;">{{ \Carbon\Carbon::parse($p->fecha)->format('d/m/Y') }}</td>
                            <td>
                                <div class="fw-semibold" style="color:#1e293b;">{{ $p->cliente }}</div>
                                <small class="text-muted">{{ $p->rif }}</small>
                            </td>
                            <td style="color:#475569;">{{ $p->vendedor_nombre ?? '—' }}</td>
                            <td class="text-end fw-bold" style="color:#1e3a5f;">${{ number_format($p->base, 2, ',', '.') }}</td>
                            <td class="text-end fw-bold" style="color:#7c3aed;">Bs. {{ number_format($p->iva_bs, 2, ',', '.') }}</td>
                            <td class="text-end">
                                <span style="font-weight:800; color:#ea580c;">Bs. {{ number_format($p->saldo_iva_bs, 2, ',', '.') }}</span>
                            </td>
                            <td class="text-center">
                                @if($p->comprobante_retencion)
                                    <div class="d-flex flex-column align-items-center gap-1">
                                        <span class="comprobante-chip comprobante-ok">
                                            <i class="fas fa-file-circle-check"></i>
                                            <span class="comprobante-content">
                                                <span class="comprobante-title">Comprobante</span>
                                                <span class="comprobante-subtitle">Cargado</span>
                                            </span>
                                        </span>
                                        <a href="{{ asset('storage/' . $p->comprobante_retencion) }}" target="_blank"
                                            class="btn-comprobante-link" title="Ver / descargar comprobante de retención">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                    </div>
                                @else
                                    <span class="comprobante-chip comprobante-missing">
                                        <i class="fas fa-file-circle-xmark"></i>
                                        <span class="comprobante-content">
                                            <span class="comprobante-title">Comprobante</span>
                                            <span class="comprobante-subtitle">Sin cargar</span>
                                        </span>
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if((float)$p->saldo_base <= 0.01)
                                    <span class="base-status-chip base-status-ok">
                                        <i class="fas fa-check-circle"></i>
                                        <span class="base-status-content">
                                            <span class="base-status-title">Base pagada</span>
                                            <span class="base-status-value">$0,00</span>
                                        </span>
                                    </span>
                                @else
                                    <span class="base-status-chip base-status-pending">
                                        <i class="fas fa-hourglass-half"></i>
                                        <span class="base-status-content">
                                            <span class="base-status-title">Saldo pendiente</span>
                                            <span class="base-status-value">${{ number_format($p->saldo_base, 2, ',', '.') }}</span>
                                        </span>
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <button type="button"
                                        class="btn-detail"
                                        data-id="{{ $p->id }}"
                                        data-cliente="{{ addslashes($p->cliente) }}"
                                        onclick="verDetalle(this.dataset.id, this.dataset.cliente)"
                                        title="Ver detalle del pago y comisiones">
                                        <i class="fas fa-eye me-1"></i> Detalle
                                    </button>
                                    @if($p->comprobante_retencion)
                                        <a href="{{ asset('storage/' . $p->comprobante_retencion) }}"
                                            target="_blank"
                                            class="btn-detail"
                                            title="Ver comprobante de retención">
                                            <i class="fas fa-eye me-1"></i> Comprobante
                                        </a>
                                    @endif
                                    <button type="button"
                                        class="btn-approve"
                                        data-id="{{ $p->id }}"
                                        data-cliente="{{ addslashes($p->cliente) }}"
                                        data-monto="{{ number_format($p->saldo_iva_bs, 2, ',', '.') }}"
                                        onclick="aprobarRetencion(this.dataset.id, this.dataset.cliente, this.dataset.monto)"
                                        title="Validar comprobante de retención recibido">
                                        <i class="fas fa-stamp me-1"></i> Validar
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($pedidos->hasPages())
            <div class="d-flex justify-content-center py-3">
                {{ $pedidos->links('pagination::bootstrap-4') }}
            </div>
            @endif
            @endif
        </div>
    </div>

</div>
</div>

{{-- ═══════════════════ MODAL DETALLE ═══════════════════ --}}
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-3">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:40px; height:40px; border-radius:10px; background:rgba(255,255,255,.15); display:flex; align-items:center; justify-content:center;">
                        <i class="fas fa-file-alt" style="color:#fff;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" style="color:#fff; font-weight:700;" id="modalDetalleTitulo">Detalle del pedido</h5>
                        <small style="color:rgba(255,255,255,.7);" id="modalDetalleSubtitulo"></small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="modalDetalleBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-3 text-muted">Cargando información…</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script>
const CSRF = '{{ csrf_token() }}';
const URL_DETALLE  = "{{ url('admin/retenciones') }}/";
const URL_APROBAR  = "{{ url('admin/retenciones') }}/";
const STORAGE_URL  = "{{ asset('storage') }}";

function formatBS(v) {
    return 'Bs. ' + parseFloat(v || 0).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function formatUSD(v) {
    return '$ ' + parseFloat(v || 0).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// ── Ver detalle ────────────────────────────────────────────────────────────
function verDetalle(pedidoId, cliente) {
    document.getElementById('modalDetalleTitulo').textContent = 'Pedido #' + pedidoId;
    document.getElementById('modalDetalleSubtitulo').textContent = cliente;
    document.getElementById('modalDetalleBody').innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-3 text-muted">Cargando información…</p>
        </div>`;

    const modal = new bootstrap.Modal(document.getElementById('modalDetalle'));
    modal.show();

    fetch(URL_DETALLE + pedidoId + '/detalle')
        .then(r => r.json())
        .then(data => renderDetalle(data))
        .catch(() => {
            document.getElementById('modalDetalleBody').innerHTML =
                '<div class="alert alert-danger">Error al cargar los datos del pedido.</div>';
        });
}

function renderDetalle(data) {
    const p = data.pedido;
    const pagos = data.pagos;
    const comisiones = data.comisiones;

    const ivaInicialBs = parseFloat(p.iva_bs || 0);
    const ivaNetoBs    = ivaInicialBs - parseFloat(p.retencion || 0);
    const retencionBs  = parseFloat(p.saldo_iva_bs || 0);

    // ── Resumen del pedido
    let html = `
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="det-pill" style="background:#f0f9ff; border:1px solid #bae6fd; width:100%; flex-direction:column; align-items:flex-start; gap:2px;">
                <span style="font-size:10px; text-transform:uppercase; font-weight:700; color:#0369a1; letter-spacing:.5px;">Base pedido</span>
                <span style="font-size:18px; font-weight:800; color:#0c4a6e;">${formatUSD(p.base)}</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="det-pill" style="background:#faf5ff; border:1px solid #e9d5ff; width:100%; flex-direction:column; align-items:flex-start; gap:2px;">
                <span style="font-size:10px; text-transform:uppercase; font-weight:700; color:#7c3aed; letter-spacing:.5px;">IVA total (Bs.)</span>
                <span style="font-size:18px; font-weight:800; color:#4c1d95;">${formatBS(p.iva_bs)}</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="det-pill" style="background:#fff7ed; border:1px solid #fed7aa; width:100%; flex-direction:column; align-items:flex-start; gap:2px;">
                <span style="font-size:10px; text-transform:uppercase; font-weight:700; color:#c2410c; letter-spacing:.5px;">Retención ${p.porc_retencion}% (pendiente)</span>
                <span style="font-size:18px; font-weight:800; color:#9a3412;">${formatBS(retencionBs)}</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="det-pill" style="background:${parseFloat(p.saldo_base) <= 0.01 ? '#f0fdf4' : '#fffbeb'}; border:1px solid ${parseFloat(p.saldo_base) <= 0.01 ? '#bbf7d0' : '#fde68a'}; width:100%; flex-direction:column; align-items:flex-start; gap:2px;">
                <span style="font-size:10px; text-transform:uppercase; font-weight:700; color:${parseFloat(p.saldo_base) <= 0.01 ? '#166534' : '#92400e'}; letter-spacing:.5px;">Saldo base</span>
                <span style="font-size:18px; font-weight:800; color:${parseFloat(p.saldo_base) <= 0.01 ? '#14532d' : '#78350f'};">
                    ${parseFloat(p.saldo_base) <= 0.01 ? '<i class="fas fa-check-circle me-1"></i>Pagada' : formatUSD(p.saldo_base)}
                </span>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div style="background:#f8fafc; border-radius:10px; padding:16px;">
                <div class="det-section-title"><i class="fas fa-info-circle me-2" style="color:#3b82f6;"></i>Datos del pedido</div>
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted" style="font-size:12px; width:40%;">Cliente</td><td class="fw-semibold">${p.cliente}</td></tr>
                    <tr><td class="text-muted" style="font-size:12px;">RIF</td><td>${p.rif}</td></tr>
                    <tr><td class="text-muted" style="font-size:12px;">Vendedor</td><td>${p.vendedor_nombre || '—'}</td></tr>
                    <tr><td class="text-muted" style="font-size:12px;">Fecha pedido</td><td>${p.fecha ? p.fecha.substring(0,10) : '—'}</td></tr>
                    <tr><td class="text-muted" style="font-size:12px;">Tasa cambio</td><td>${parseFloat(p.tasa || 0).toLocaleString('es-VE', {minimumFractionDigits:2})} Bs/$</td></tr>
                    <tr><td class="text-muted" style="font-size:12px;">IVA neto pagado</td><td class="text-success fw-bold">${formatBS(ivaNetoBs)}</td></tr>
                </table>
            </div>
        </div>
        <div class="col-md-6">
            <div style="background:#f8fafc; border-radius:10px; padding:16px; height:100%;">
                <div class="det-section-title"><i class="fas fa-info-circle me-2" style="color:#f97316;"></i>Información de retención</div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div style="text-align:center; background:#fff7ed; border-radius:8px; padding:12px; border:1px solid #fed7aa;">
                        <div style="font-size:11px; color:#9a3412; font-weight:700; text-transform:uppercase;">% Retención</div>
                        <div style="font-size:24px; font-weight:800; color:#ea580c;">${p.porc_retencion}%</div>
                    </div>
                    <div style="text-align:center; background:#fff7ed; border-radius:8px; padding:12px; border:1px solid #fed7aa;">
                        <div style="font-size:11px; color:#9a3412; font-weight:700; text-transform:uppercase;">Monto retenido</div>
                        <div style="font-size:18px; font-weight:800; color:#ea580c;">${formatBS(retencionBs)}</div>
                    </div>
                </div>
                <div style="margin-top:12px; padding:10px 14px; background:#fff; border-radius:8px; border-left:4px solid #f97316;">
                    <p style="font-size:12px; color:#64748b; margin:0; line-height:1.5;">
                        <i class="fas fa-info-circle me-1 text-warning"></i>
                        El comprobante de retención <strong>debe ser recibido y validado</strong> por el administrador.
                        Al validar, el saldo de IVA queda en cero y el pedido pasa a estado completamente pagado.
                    </p>
                </div>
            </div>
        </div>
    </div>`;

    // ── Historial de pagos
    html += `
    <div class="det-section-title mt-2"><i class="fas fa-receipt me-2" style="color:#8b5cf6;"></i>Historial de pagos</div>`;

    if (pagos.length === 0) {
        html += '<div class="alert alert-warning py-2">No hay pagos registrados para este pedido.</div>';
    } else {
        html += `
        <div class="table-responsive mb-4">
            <table class="table det-table table-sm table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#Pago</th><th>Fecha</th><th>Tipo</th><th>Referencia</th>
                        <th class="text-end">Monto ($)</th><th class="text-end">IVA (Bs.)</th>
                        <th class="text-end">Retención (Bs.)</th><th class="text-center">Estatus</th>
                    </tr>
                </thead>
                <tbody>`;
        pagos.forEach(pg => {
            const estatusBadge = pg.estatus === 'APROBADO'
                ? '<span class="badge bg-success" style="font-size:10px;">APROBADO</span>'
                : '<span class="badge bg-warning text-dark" style="font-size:10px;">' + (pg.estatus || 'PENDIENTE') + '</span>';
            html += `<tr>
                <td class="fw-bold">#${pg.pago_id}</td>
                <td>${pg.fecha ? pg.fecha.substring(0,10) : '—'}</td>
                <td>${pg.tipo_pago || pg.moneda_pago || '—'}</td>
                <td>${pg.referencia || '—'}</td>
                <td class="text-end">${formatUSD(pg.monto)}</td>
                <td class="text-end">${formatBS(pg.iva)}</td>
                <td class="text-end text-warning fw-bold">${formatBS(pg.retencion)}</td>
                <td class="text-center">${estatusBadge}</td>
            </tr>`;
        });
        html += '</tbody></table></div>';
    }

    
    // ── Comprobante de retención cargado por el vendedor
    if (p.comprobante_retencion) {
        const ext = p.comprobante_retencion.split('.').pop().toLowerCase();
        const esImagen = ['jpg','jpeg','png'].includes(ext);
        const urlComp  = STORAGE_URL + '/' + p.comprobante_retencion;

        html += `
        <div class="det-section-title mt-3"><i class="fas fa-file-invoice me-2" style="color:#f97316;"></i>Comprobante de retención cargado</div>
        <div style="text-align:center;padding:16px;background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;margin-bottom:8px;">`;

        if (esImagen) {
            html += `<img src="${urlComp}" alt="Comprobante" style="max-width:100%;max-height:320px;border-radius:8px;margin-bottom:12px;border:1px solid #fde68a;">`;
        }

        html += `
            <div class="d-flex justify-content-center gap-2 flex-wrap">
                <a href="${urlComp}" target="_blank" class="btn btn-warning btn-sm">
                    <i class="fas fa-eye me-1"></i>Ver comprobante
                </a>
                <a href="${urlComp}" download class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-download me-1"></i>Descargar
                </a>
            </div>
        </div>`;
    } else {
        html += `
        <div class="det-section-title mt-3"><i class="fas fa-file-invoice me-2" style="color:#94a3b8;"></i>Comprobante de retención</div>
        <div style="text-align:center;padding:16px;background:#f8fafc;border:1px dashed #cbd5e1;border-radius:10px;margin-bottom:8px;">
            <span style="font-size:13px;color:#64748b;"><i class="fas fa-clock me-1"></i>El vendedor aún no ha cargado el comprobante de retención.</span>
        </div>`;
    }

    document.getElementById('modalDetalleBody').innerHTML = html;
}

// ── Aprobar / validar retención ────────────────────────────────────────────
function aprobarRetencion(pedidoId, cliente, montoStr) {
    Swal.fire({
        title: 'Validar comprobante de retención',
        html: `
            <p style="color:#475569; margin-bottom:16px;">
                Estás a punto de marcar el comprobante de retención del pedido
                <strong>#${pedidoId}</strong> — <em>${cliente}</em> como recibido y válido.
            </p>
            <div style="background:#fff7ed; border:1px solid #fed7aa; border-radius:10px; padding:14px; margin-bottom:8px;">
                <div style="font-size:12px; color:#9a3412; font-weight:700; text-transform:uppercase; margin-bottom:4px;">IVA retenido a liquidar</div>
                <div style="font-size:22px; font-weight:800; color:#ea580c;">Bs. ${montoStr}</div>
            </div>
            <p style="font-size:13px; color:#64748b;">El saldo de IVA de este pedido quedará en <strong>cero</strong> y el pedido aparecerá como completamente pagado para el vendedor.</p>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-stamp me-2"></i>Sí, validar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#16a34a',
        cancelButtonColor: '#64748b',
        reverseButtons: true,
        focusCancel: true,
    }).then(result => {
        if (!result.isConfirmed) return;

        fetch(URL_APROBAR + pedidoId + '/aprobar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({}),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: '¡Validado!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonColor: '#16a34a',
                }).then(() => window.location.reload());
            } else {
                Swal.fire('Error', data.error || 'No se pudo procesar la solicitud.', 'error');
            }
        })
        .catch(() => Swal.fire('Error', 'Error de conexión. Intente nuevamente.', 'error'));
    });
}
</script>
@endsection
