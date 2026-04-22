@extends('layouts.app')

@section('titulo', 'Ledger — ' . $nombreVendedor)

@section('styles')
<style>
    .ledger-header { background:linear-gradient(135deg,#1e3c72,#2a5298); color:#fff; border-radius:14px 14px 0 0; padding:1.25rem 1.5rem; }
    .saldo-card { border-radius:12px; padding:1rem 1.5rem; color:#fff; }
    .tipo-devengada  { border-left: 4px solid #11998e; }
    .tipo-pago       { border-left: 4px solid #4e73df; }
    .tipo-aplicacion { border-left: 4px solid #f6c23e; }
    .balance-row { font-size:0.95rem; }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="ledger-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0 font-weight-bold">
                    <i class="fas fa-user-circle mr-2"></i>{{ $nombreVendedor }}
                </h4>
                <small style="opacity:.8;">{{ $correo }}</small>
            </div>
            <a href="{{ route('comisiones.estado_cuenta') }}" class="btn btn-sm btn-light">
                <i class="fas fa-arrow-left mr-1"></i>Volver
            </a>
        </div>
    </div>

    {{-- Saldo actual --}}
    @php
        $saldo = (float) $saldoActual;
        $esCredito  = $saldo < -0.001; // admin tiene crédito (sobrepagó)
        $esDeuda    = $saldo > 0.001;  // admin le debe al vendedor
        $gradiente  = $esDeuda ? 'linear-gradient(135deg,#11998e,#38ef7d)' : ($esCredito ? 'linear-gradient(135deg,#f093fb,#f5576c)' : 'linear-gradient(135deg,#a8edea,#fed6e3)');
        $labelSaldo = $esDeuda ? 'Admin debe al vendedor' : ($esCredito ? 'Admin tiene crédito (sobrepagó)' : 'Saldo en cero');
    @endphp
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="saldo-card shadow-sm" style="background:{{ $gradiente }}; {{ !$esCredito && !$esDeuda ? 'color:#333' : '' }}">
                <div class="small font-weight-bold text-uppercase mb-1" style="opacity:.8;">{{ $labelSaldo }}</div>
                <div class="h2 font-weight-bold mb-0">${{ number_format(abs($saldo), 2) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            @php
                $totalDev = $movimientos->where('tipo','comision_devengada')->sum('monto');
            @endphp
            <div class="saldo-card shadow-sm" style="background:linear-gradient(135deg,#667eea,#764ba2);">
                <div class="small font-weight-bold text-uppercase mb-1" style="opacity:.8;">Total Comisiones Devengadas</div>
                <div class="h2 font-weight-bold mb-0">${{ number_format($totalDev, 2) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            @php
                $totalPag = $movimientos->where('tipo','pago_comision')->sum('monto');
            @endphp
            <div class="saldo-card shadow-sm" style="background:linear-gradient(135deg,#f6d365,#fda085);">
                <div class="small font-weight-bold text-uppercase mb-1" style="opacity:.8;">Total Pagado al Vendedor</div>
                <div class="h2 font-weight-bold mb-0">${{ number_format($totalPag, 2) }}</div>
            </div>
        </div>
    </div>

    {{-- Ledger --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-light">
            <h5 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-stream mr-2 text-primary"></i>Movimientos del Ledger
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 balance-row">
                    <thead class="thead-light">
                        <tr>
                            <th width="140">Fecha</th>
                            <th width="130">Tipo</th>
                            <th>Concepto</th>
                            <th class="text-right" width="130">Monto Debido<br><small class="text-muted">(comisión real)</small></th>
                            <th class="text-right" width="130">Crédito Vendedor<br><small class="text-success">+ Admin debe</small></th>
                            <th class="text-right" width="130">Débito Vendedor<br><small class="text-danger">− Pagado/Ajuste</small></th>
                            <th class="text-right" width="130">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movimientos as $mov)
                        @php
                            $rowClass = $mov->tipo === 'comision_devengada' ? 'tipo-devengada'
                                      : ($mov->tipo === 'pago_comision'      ? 'tipo-pago'
                                      : 'tipo-aplicacion');
                            $saldoMov = (float) $mov->saldo_resultante;
                            $saldoColor = $saldoMov > 0.01 ? 'text-success font-weight-bold'
                                        : ($saldoMov < -0.01 ? 'text-danger font-weight-bold' : 'text-muted');
                        @endphp
                        <tr class="{{ $rowClass }}">
                            <td class="text-muted small">{{ \Carbon\Carbon::parse($mov->created_at)->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($mov->tipo === 'comision_devengada')
                                    <span class="badge badge-success">Comisión</span>
                                @elseif($mov->tipo === 'pago_comision')
                                    <span class="badge badge-primary">Pago</span>
                                @else
                                    <span class="badge badge-warning text-dark">Ajuste saldo</span>
                                @endif
                            </td>
                            <td>
                                <div>{{ $mov->concepto }}</div>
                                @if($mov->grupo_pago_id)
                                    <small class="text-muted"><i class="fas fa-tag mr-1"></i>{{ $mov->grupo_pago_id }}</small>
                                @endif
                                @if($mov->tipo === 'pago_comision' && $mov->monto_comision_original)
                                    <div class="small mt-1">
                                        <span class="text-muted">Comisión real: </span>
                                        <strong>${{ number_format($mov->monto_comision_original, 2) }}</strong>
                                        &nbsp;·&nbsp;
                                        <span class="text-muted">Pagado: </span>
                                        <strong>${{ number_format($mov->monto_pagado_real, 2) }}</strong>
                                        @php $dif = (float)$mov->monto_pagado_real - (float)$mov->monto_comision_original; @endphp
                                        @if(abs($dif) > 0.01)
                                            <span class="ml-1 badge {{ $dif > 0 ? 'badge-danger' : 'badge-success' }}">
                                                {{ $dif > 0 ? 'Sobrepago' : 'Pago parcial' }}: ${{ number_format(abs($dif), 2) }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="text-right text-muted">
                                @if($mov->tipo === 'pago_comision' && $mov->monto_comision_original)
                                    ${{ number_format($mov->monto_comision_original, 2) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-right text-success">
                                @if($mov->es_credito)
                                    +${{ number_format($mov->monto, 2) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-right text-danger">
                                @if(!$mov->es_credito)
                                    −${{ number_format($mov->monto, 2) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-right {{ $saldoColor }}">
                                @if($saldoMov < 0)
                                    <span title="Admin tiene crédito">−${{ number_format(abs($saldoMov), 2) }}</span>
                                @else
                                    ${{ number_format($saldoMov, 2) }}
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Sin movimientos registrados.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($movimientos->isNotEmpty())
                    <tfoot class="table-light font-weight-bold">
                        <tr>
                            <td colspan="4" class="text-right">Saldo Final:</td>
                            <td class="text-right text-success">${{ number_format($movimientos->where('es_credito', true)->sum('monto'), 2) }}</td>
                            <td class="text-right text-danger">−${{ number_format($movimientos->where('es_credito', false)->sum('monto'), 2) }}</td>
                            <td class="text-right {{ $saldo > 0.01 ? 'text-success' : ($saldo < -0.01 ? 'text-danger' : 'text-muted') }}">
                                ${{ number_format(abs($saldo), 2) }}
                                <small>({{ $saldo > 0.01 ? 'admin debe' : ($saldo < -0.01 ? 'crédito admin' : 'saldado') }})</small>
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
