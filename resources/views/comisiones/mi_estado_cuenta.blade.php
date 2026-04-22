@extends('layouts.app')

@section('titulo', 'Mi Estado de Cuenta')

@section('styles')
<style>
    .ledger-header { background:linear-gradient(135deg,#667eea,#764ba2); color:#fff; border-radius:14px 14px 0 0; padding:1.25rem 1.5rem; }
    .saldo-card { border-radius:12px; padding:1.1rem 1.4rem; color:#fff; box-shadow:0 6px 20px rgba(0,0,0,.12); }
    .tipo-devengada  { border-left: 4px solid #11998e; }
    .tipo-pago       { border-left: 4px solid #4e73df; }
    .tipo-aplicacion { border-left: 4px solid #f6c23e; }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">

    <div class="card shadow-sm border-0 mb-4">
        <div class="ledger-header">
            <h4 class="mb-0 font-weight-bold">
                <i class="fas fa-wallet mr-2"></i>Mi Estado de Cuenta — Comisiones
            </h4>
            <small style="opacity:.8;">{{ auth()->user()->name }} &middot; {{ auth()->user()->email }}</small>
        </div>
    </div>

    @php
        $saldo     = (float) $saldoActual;
        $esCredito = $saldo < -0.001;
        $esDeuda   = $saldo > 0.001;
        $gradiente = $esDeuda   ? 'linear-gradient(135deg,#11998e,#38ef7d)'
                   : ($esCredito ? 'linear-gradient(135deg,#f093fb,#f5576c)'
                   :               'linear-gradient(135deg,#a8edea,#fed6e3)');
    @endphp

    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="saldo-card" style="background:{{ $gradiente }}; {{ !$esDeuda && !$esCredito ? 'color:#333' : '' }}">
                @if($esDeuda)
                    <div class="small font-weight-bold text-uppercase mb-1" style="opacity:.8">La empresa te debe</div>
                    <div class="h2 font-weight-bold mb-0">${{ number_format($saldo, 2) }}</div>
                    <div class="small mt-1" style="opacity:.8">Pendiente de pago</div>
                @elseif($esCredito)
                    <div class="small font-weight-bold text-uppercase mb-1" style="opacity:.8">La empresa tiene saldo a favor</div>
                    <div class="h2 font-weight-bold mb-0">${{ number_format(abs($saldo), 2) }}</div>
                    <div class="small mt-1" style="opacity:.8">Será aplicado a futuras comisiones</div>
                @else
                    <div class="small font-weight-bold text-uppercase mb-1" style="opacity:.7;color:#333">Sin saldo pendiente</div>
                    <div class="h2 font-weight-bold mb-0" style="color:#333">$0.00</div>
                    <div class="small mt-1" style="opacity:.7;color:#333">Todo en regla</div>
                @endif
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="saldo-card" style="background:linear-gradient(135deg,#667eea,#764ba2);">
                <div class="small font-weight-bold text-uppercase mb-1" style="opacity:.8">Total Comisiones Ganadas</div>
                <div class="h2 font-weight-bold mb-0">${{ number_format($movimientos->where('tipo','comision_devengada')->sum('monto'), 2) }}</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="saldo-card" style="background:linear-gradient(135deg,#f6d365,#fda085);">
                <div class="small font-weight-bold text-uppercase mb-1" style="opacity:.8">Total Recibido</div>
                <div class="h2 font-weight-bold mb-0">${{ number_format($movimientos->where('tipo','pago_comision')->sum('monto'), 2) }}</div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-light">
            <h5 class="mb-0 font-weight-bold"><i class="fas fa-history mr-2 text-primary"></i>Historial de Movimientos</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th width="140">Fecha</th>
                            <th width="120">Tipo</th>
                            <th>Detalle</th>
                            <th class="text-right" width="130">Lo que te debían<br><small class="text-muted">Comisión real</small></th>
                            <th class="text-right" width="120">A tu favor<br><small class="text-success">+</small></th>
                            <th class="text-right" width="120">Pagado/Ajuste<br><small class="text-danger">−</small></th>
                            <th class="text-right" width="120">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movimientos as $mov)
                        @php
                            $rowClass = $mov->tipo === 'comision_devengada' ? 'tipo-devengada'
                                      : ($mov->tipo === 'pago_comision' ? 'tipo-pago' : 'tipo-aplicacion');
                            $saldoMov = (float) $mov->saldo_resultante;
                        @endphp
                        <tr class="{{ $rowClass }}">
                            <td class="small text-muted">{{ \Carbon\Carbon::parse($mov->created_at)->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($mov->tipo === 'comision_devengada')
                                    <span class="badge badge-success">Comisión</span>
                                @elseif($mov->tipo === 'pago_comision')
                                    <span class="badge badge-primary">Pago recibido</span>
                                @else
                                    <span class="badge badge-warning text-dark">Ajuste</span>
                                @endif
                            </td>
                            <td>
                                <div>{{ $mov->concepto }}</div>
                                @if($mov->tipo === 'pago_comision' && $mov->monto_comision_original)
                                    <div class="small text-muted mt-1">
                                        Comisión correspondida: <strong>${{ number_format($mov->monto_comision_original, 2) }}</strong>
                                        &nbsp;·&nbsp; Pago recibido: <strong>${{ number_format($mov->monto_pagado_real, 2) }}</strong>
                                        @php $dif = (float)$mov->monto_pagado_real - (float)$mov->monto_comision_original; @endphp
                                        @if(abs($dif) > 0.01)
                                            <span class="badge {{ $dif > 0 ? 'badge-danger' : 'badge-warning text-dark' }}">
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
                            <td class="text-right text-success">{{ $mov->es_credito ? '+$'.number_format($mov->monto,2) : '—' }}</td>
                            <td class="text-right text-danger">{{ !$mov->es_credito ? '−$'.number_format($mov->monto,2) : '—' }}</td>
                            <td class="text-right font-weight-bold {{ $saldoMov > 0.01 ? 'text-success' : ($saldoMov < -0.01 ? 'text-danger' : 'text-muted') }}">
                                @if($saldoMov < 0)
                                    <span title="La empresa tiene saldo a favor">−${{ number_format(abs($saldoMov), 2) }}</span>
                                @else
                                    ${{ number_format($saldoMov, 2) }}
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                No tienes movimientos registrados aún.
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
