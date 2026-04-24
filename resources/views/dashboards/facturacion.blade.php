@extends('layouts.app')

@section('titulo', 'Panel de Facturación')

@section('styles')
<style>
    .manager-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .manager-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 32px rgba(0, 0, 0, 0.15);
    }

    .manager-card-header {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white;
        padding: 1.5rem;
        text-align: center;
    }

    .stat-number {
        font-size: 2.2rem;
        font-weight: 700;
        margin: 0;
        line-height: 1;
    }

    .stat-label {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        opacity: 0.85;
        font-weight: 500;
    }

    .progress {
        height: 8px;
        border-radius: 4px;
        background: rgba(255,255,255,0.2);
    }

    .progress-bar {
        border-radius: 4px;
        background: rgba(255,255,255,0.5) !important;
    }

    .action-card {
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: pointer;
    }

    .action-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .action-icon {
        margin-bottom: 0.5rem;
    }

    .action-icon i {
        transition: transform 0.2s;
    }

    .action-card:hover .action-icon i {
        transform: scale(1.1);
    }

    .action-card-premium {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 20px;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
    }

    .action-card-premium::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
        transform: rotate(45deg);
        transition: all 0.6s;
        opacity: 0;
    }

    .action-card-premium:hover::before {
        animation: shimmer 0.6s ease-in-out;
    }

    @keyframes shimmer {
        0% { transform: translateX(-100%) translateY(-100%); opacity: 0; }
        50% { opacity: 1; }
        100% { transform: translateX(100%) translateY(100%); opacity: 0; }
    }

    .action-card-premium:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 20px 40px rgba(102, 126, 234, 0.3);
    }

    .readonly-card {
        background: linear-gradient(135deg, #e0e0e0 0%, #f5f5f5 100%);
        border: 2px dashed #ccc;
        position: relative;
        overflow: hidden;
    }

    .readonly-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: repeating-linear-gradient(
            45deg,
            transparent,
            transparent 10px,
            rgba(255,255,255,0.1) 10px,
            rgba(255,255,255,0.1) 20px
        );
        pointer-events: none;
    }

    .readonly-icon {
        filter: grayscale(100%);
        opacity: 0.6;
    }

    .pulse-animation {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.05); opacity: 0.8; }
        100% { transform: scale(1); opacity: 1; }
    }

    .gradient-text {
        background: linear-gradient(45deg, #667eea, #764ba2);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-weight: 700;
    }

    .action-btn {
        border-radius: 12px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        position: relative;
        overflow: hidden;
        text-decoration: none;
        display: block;
    }

    .action-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }

    .action-btn:hover::before {
        left: 100%;
    }

    .btn-navy {
        background: linear-gradient(135deg, #1e3a8a 0%, #2c5282 50%, #1e3a8a 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(30, 58, 138, 0.3);
    }

    .btn-navy:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(30, 58, 138, 0.4);
        background: linear-gradient(135deg, #2c5282 0%, #1e3a8a 50%, #2c5282 100%);
    }

    .btn-emerald {
        background: linear-gradient(135deg, #10b981 0%, #059669 50%, #10b981 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
    }

    .btn-emerald:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
        background: linear-gradient(135deg, #059669 0%, #10b981 50%, #059669 100%);
    }

    .btn-royal {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 50%, #3b82f6 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
    }

    .btn-royal:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
        background: linear-gradient(135deg, #1d4ed8 0%, #3b82f6 50%, #1d4ed8 100%);
    }

    .btn-icon {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
    }

    .btn-title {
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .btn-subtitle {
        font-size: 0.75rem;
        opacity: 0.8;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card manager-card">
                <div class="manager-card-header">
                    <h4 class="mb-1 font-weight-bold">
                        <i class="fas fa-file-invoice mr-2"></i>
                        Panel de Facturación DACABE
                    </h4>
                    <p class="mb-0 small" style="opacity:.8;">
                        Gestión de pedidos, pagos, sincronización y retenciones
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Stat cards --}}
    <div class="row">

        {{-- Pedidos --}}
        <div class="col-12 col-sm-6 col-xl-3 mb-4">
            <a href="{{ url('admin/pedidos-gestion') }}" class="text-decoration-none">
                <div class="card manager-card h-100">
                    <div class="card-body" style="background: linear-gradient(135deg, #F2994A 0%, #F2C94C 100%);">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="text-white font-weight-bold mb-0">
                                <i class="fas fa-shopping-bag mr-1"></i> Pedidos
                            </h6>
                            <i class="fas fa-shopping-bag fa-2x" style="opacity:.2; color:#fff;"></i>
                        </div>
                        <div class="stat-number text-white mb-1">{{ $pedidosPendientesCount }}</div>
                        <div class="stat-label text-white">pendientes</div>
                        <div class="progress mt-3">
                            <div class="progress-bar" role="progressbar"
                                 style="width: {{ $pedidosPendientesCount > 0 ? 100 : 0 }}%"></div>
                        </div>
                        <div class="mt-2">
                            <small class="text-white" style="opacity:.8;">
                                <i class="fas fa-arrow-right mr-1"></i> Ir a gestión de pedidos
                            </small>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-12 col-sm-6 col-xl-3 mb-4">
            <a href="{{ route('admin.pedidos_editor.index') }}" class="text-decoration-none">
                <div class="card manager-card h-100">
                    <div class="card-body" style="background: linear-gradient(135deg, #7f5539 0%, #b08968 100%);">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="text-white font-weight-bold mb-0">
                                <i class="fas fa-edit mr-1"></i> Editor de Pedidos
                            </h6>
                            <i class="fas fa-pen-square fa-2x" style="opacity:.2; color:#fff;"></i>
                        </div>
                        <div class="stat-number text-white mb-1">CRUD</div>
                        <div class="stat-label text-white">crear y modificar líneas</div>
                        <div class="progress mt-3">
                            <div class="progress-bar" role="progressbar" style="width: 100%"></div>
                        </div>
                        <div class="mt-2">
                            <small class="text-white" style="opacity:.8;">
                                <i class="fas fa-arrow-right mr-1"></i> Ir al editor avanzado
                            </small>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Pagos --}}
        <div class="col-12 col-sm-6 col-xl-3 mb-4">
            <a href="{{ url('/admin/pedidos-iva-modificar') }}" class="text-decoration-none">
                <div class="card manager-card h-100">
                    <div class="card-body" style="background: linear-gradient(45deg, #2980b9 0%, #6dd5fa 100%);">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="text-white font-weight-bold mb-0">
                                <i class="fas fa-money-bill-wave mr-1"></i> Ajuste Montos Pedidos
                            </h6>
                            <i class="fas fa-money-bill-wave fa-2x" style="opacity:.2; color:#fff;"></i>
                        </div>
                        <div class="stat-number text-white mb-1">IVA</div>
                        <div class="stat-label text-white">Permite ajustar totales</div>
                        <div class="progress mt-3">
                            <div class="progress-bar" role="progressbar"
                                 style="width: {{ $pagosPendientesCount > 0 ? 100 : 0 }}%"></div>
                        </div>
                        <div class="mt-2">
                            <small class="text-white" style="opacity:.8;">
                                <i class="fas fa-arrow-right mr-1"></i> Ajustar Montos
                            </small>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Sinc. Clientes --}}
        <div class="col-12 col-sm-6 col-xl-3 mb-4">
            <a href="{{ url('vendedores/sincronizar-clientes') }}" class="text-decoration-none">
                <div class="card manager-card h-100">
                    <div class="card-body" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="text-white font-weight-bold mb-0">
                                <i class="fas fa-sync-alt mr-1"></i> Sinc. Clientes
                            </h6>
                            <i class="fas fa-sync-alt fa-2x" style="opacity:.2; color:#fff;"></i>
                        </div>
                        <div class="stat-number text-white mb-1">{{ $pedidosSinCruceCount }}</div>
                        <div class="stat-label text-white">
                            <i class="fas fa-exclamation-triangle mr-1"></i> pendientes
                        </div>
                        <div class="progress mt-3">
                            <div class="progress-bar" role="progressbar"
                                 style="width: {{ $pedidosSinCruceCount > 0 ? 100 : 0 }}%"></div>
                        </div>
                        <div class="mt-2">
                            <small class="text-white" style="opacity:.8;">
                                <i class="fas fa-arrow-right mr-1"></i> Sincronizar clientes
                            </small>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Retenciones --}}
        <div class="col-12 col-sm-6 col-xl-3 mb-4">
            <a href="{{ route('admin.retenciones.index') }}" class="text-decoration-none">
                <div class="card manager-card h-100">
                    <div class="card-body" style="background: linear-gradient(135deg, #9a3412 0%, #ea580c 100%);">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="text-white font-weight-bold mb-0">
                                <i class="fas fa-file-invoice mr-1"></i> Retenciones IVA
                            </h6>
                            <i class="fas fa-file-invoice fa-2x" style="opacity:.2; color:#fff;"></i>
                        </div>
                        <div class="stat-number text-white mb-1">{{ $retencionesPendientesCount }}</div>
                        <div class="stat-label text-white">
                            comprobante{{ $retencionesPendientesCount != 1 ? 's' : '' }} pendiente{{ $retencionesPendientesCount != 1 ? 's' : '' }}
                        </div>
                        <div class="progress mt-3">
                            <div class="progress-bar" role="progressbar"
                                 style="width: {{ $retencionesPendientesCount > 0 ? 100 : 0 }}%"></div>
                        </div>
                        <div class="mt-2">
                            <small class="text-white" style="opacity:.8;">
                                Bs. {{ number_format($retencionesPendientesBs, 2, ',', '.') }} por validar
                            </small>
                        </div>
                    </div>
                </div>
            </a>
        </div>

    </div>
</div>

{{-- Action Buttons --}}
<div class="row mt-5">
    <div class="col-12">
        <div class="text-center mb-4">
            <h5 class="gradient-text d-inline-block">
                <i class="fas fa-sparkles mr-2"></i>
                Acciones Rápidas
            </h5>
        </div>
        <div class="row justify-content-center">
            {{-- Pagos Recibidos --}}
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-3">
                <a href="{{ url('admin/pagos/aprobar') }}" class="action-btn btn-navy p-3 text-center">
                    <i class="fas fa-hand-holding-usd btn-icon"></i>
                    <div class="btn-title">Pagos Recibidos</div>
                    <div class="btn-subtitle">Gestionar aprobaciones</div>
                </a>
            </div>

            {{-- Reporte de Pagos --}}
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-3">
                <a href="{{ url('admin/pagos') }}" class="action-btn btn-emerald p-3 text-center">
                    <i class="fas fa-file-invoice-dollar btn-icon"></i>
                    <div class="btn-title">Reporte de Pagos</div>
                    <div class="btn-subtitle">Análisis completo</div>
                </a>
            </div>

            {{-- Clientes Asociados a Vendedores --}}
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-3">
                <a href="{{ url('admin/cliente-vendedor') }}" class="action-btn btn-royal p-3 text-center">
                    <i class="fas fa-users btn-icon"></i>
                    <div class="btn-title">Clientes Asociados</div>
                    <div class="btn-subtitle">Gestión de vendedores</div>
                </a>
            </div>

            {{-- Cuentas por Cobrar --}}
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-3">
                <a href="{{ url('admin/cuentas-por-cobrar') }}" class="action-btn btn-navy p-3 text-center">
                    <i class="fas fa-balance-scale btn-icon"></i>
                    <div class="btn-title">Cuentas por Cobrar</div>
                    <div class="btn-subtitle">Cartera pendiente</div>
                </a>
            </div>

            <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-3">
                <a href="{{ route('admin.pedidos_editor.index') }}" class="action-btn btn-emerald p-3 text-center">
                    <i class="fas fa-edit btn-icon"></i>
                    <div class="btn-title">Editor de Pedidos</div>
                    <div class="btn-subtitle">Crear y modificar</div>
                </a>
            </div>

            {{-- Nuevo: Estadísticas --}}
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-3 d-none">
                <a href="{{ url('admin/pagos/estadisticas') }}" class="action-btn btn-royal p-3 text-center">
                    <i class="fas fa-chart-line btn-icon"></i>
                    <div class="btn-title">Estadísticas</div>
                    <div class="btn-subtitle">Métricas clave</div>
                </a>
            </div>

            {{-- Nuevo: Historial --}}
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-3 d-none">
                <a href="{{ url('admin/pagos/historial') }}" class="action-btn btn-navy p-3 text-center">
                    <i class="fas fa-history btn-icon"></i>
                    <div class="btn-title">Historial</div>
                    <div class="btn-subtitle">Registro completo</div>
                </a>
            </div>
        </div>
    </div>
</div>

</div>
@endsection
