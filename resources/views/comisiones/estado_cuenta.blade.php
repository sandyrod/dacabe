@extends('layouts.app')

@section('titulo', 'Estado de Cuenta — Comisiones')

@section('styles')
<style>
    .balance-positive { background: linear-gradient(135deg, #11998e, #38ef7d); }
    .balance-negative { background: linear-gradient(135deg, #f093fb, #f5576c); }
    .balance-zero     { background: linear-gradient(135deg, #a8edea, #fed6e3); }
    .stat-ec { border-radius:14px; color:#fff; padding:1.25rem 1.5rem; box-shadow:0 6px 20px rgba(0,0,0,.12); }
    .vendor-row:hover { background:#f0f4ff !important; cursor:pointer; }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header" style="background:linear-gradient(135deg,#1e3c72,#2a5298);">
            <h4 class="mb-0 text-white font-weight-bold">
                <i class="fas fa-balance-scale mr-2"></i>Estado de Cuenta — Comisiones de Vendedores
            </h4>
        </div>
        <div class="card-body">

            {{-- Resumen global --}}
            <div class="row mb-4">
                @php
                    $totalDeuda    = $vendedores->where('saldo', '>', 0)->sum('saldo');
                    $totalCredito  = $vendedores->where('saldo', '<', 0)->sum('saldo');
                    $conDeuda      = $vendedores->where('saldo', '>', 0)->count();
                    $conCredito    = $vendedores->where('saldo', '<', 0)->count();
                @endphp
                <div class="col-md-4 mb-3">
                    <div class="stat-ec balance-positive">
                        <div class="small text-uppercase font-weight-bold mb-1" style="opacity:.8">Admin debe al vendedor</div>
                        <div class="h3 font-weight-bold mb-0">${{ number_format($totalDeuda, 2) }}</div>
                        <div class="small mt-1" style="opacity:.8">{{ $conDeuda }} vendedor(es)</div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="stat-ec balance-negative">
                        <div class="small text-uppercase font-weight-bold mb-1" style="opacity:.8">Crédito del admin (sobrepago)</div>
                        <div class="h3 font-weight-bold mb-0">${{ number_format(abs($totalCredito), 2) }}</div>
                        <div class="small mt-1" style="opacity:.8">{{ $conCredito }} vendedor(es)</div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="stat-ec balance-zero" style="color:#333;">
                        <div class="small text-uppercase font-weight-bold mb-1" style="opacity:.7">Vendedores registrados</div>
                        <div class="h3 font-weight-bold mb-0">{{ $vendedores->count() }}</div>
                        <div class="small mt-1" style="opacity:.7">Con movimientos</div>
                    </div>
                </div>
            </div>

            {{-- Tabla de vendedores --}}
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead style="background:linear-gradient(135deg,#1e3c72,#2a5298);color:#fff;">
                        <tr>
                            <th>Vendedor</th>
                            <th class="text-right">Total Devengado</th>
                            <th class="text-right">Total Pagado</th>
                            <th class="text-right">Saldo Actual</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vendedores as $v)
                        @php
                            $saldo = (float) $v->saldo;
                            $badgeClass = $saldo > 0.01 ? 'success' : ($saldo < -0.01 ? 'danger' : 'secondary');
                            $badgeLabel = $saldo > 0.01 ? 'Admin debe al vendedor' : ($saldo < -0.01 ? 'Admin tiene crédito' : 'Saldo en cero');
                        @endphp
                        <tr class="vendor-row">
                            <td>
                                <div class="font-weight-bold">{{ $v->nombre_vendedor ?? $v->correo_vendedor }}</div>
                                <small class="text-muted">{{ $v->correo_vendedor }}</small>
                            </td>
                            <td class="text-right">${{ number_format($v->total_devengado, 2) }}</td>
                            <td class="text-right">${{ number_format($v->total_pagado, 2) }}</td>
                            <td class="text-right font-weight-bold {{ $saldo > 0.01 ? 'text-success' : ($saldo < -0.01 ? 'text-danger' : 'text-muted') }}">
                                ${{ number_format(abs($saldo), 2) }}
                                @if($saldo < -0.01)
                                    <i class="fas fa-arrow-down ml-1" title="Crédito admin"></i>
                                @elseif($saldo > 0.01)
                                    <i class="fas fa-arrow-up ml-1" title="Admin debe"></i>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge badge-{{ $badgeClass }} px-2 py-1">{{ $badgeLabel }}</span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('comisiones.estado_cuenta_vendedor', urlencode($v->correo_vendedor)) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-list-alt mr-1"></i>Ver Detalles
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                No hay movimientos registrados aún.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
@endsection
