@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel') . ' - Dashboard')

@section('titulo_header', 'Dashboard')
@section('subtitulo_header', 'Panel Principal')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{-- Aviso de pedidos vencidos para vendedores --}}
                    @if(isset($pedidosVencidos) && count($pedidosVencidos) > 0)
                        <div id="alerta-pedidos-vencidos-home" class="alert alert-warning alert-dismissible fade show" role="alert" style="background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%); border: none; border-left: 5px solid #f39c12;">
                            <button type="button" class="btn-close" onclick="cerrarAlertaPedidosVencidosHome()" aria-label="Close"></button>
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-exclamation-triangle fa-2x" style="color: #f39c12;"></i>
                                </div>
                                <div>
                                    <h5 class="alert-heading mb-2" style="color: #856404; font-weight: 700;">
                                        <i class="fas fa-clock me-2"></i>¡Tiene Pedidos Vencidos!
                                    </h5>
                                    <p class="mb-2" style="color: #856404;">
                                        Usted tiene <strong>{{ count($pedidosVencidos) }}</strong> pedido(s) que han superado el plazo de crédito establecido.
                                    </p>
                                    <div class="mb-0">
                                        <small class="text-muted">Pedidos afectados:</small>
                                        <ul class="mb-0 mt-2" style="max-height: 120px; overflow-y: auto;">
                                            @foreach($pedidosVencidos as $pedido)
                                                <li class="mb-1" style="color: #856404;">
                                                    <strong>#{{ str_pad($pedido->id, 5, '0', STR_PAD_LEFT) }}</strong> - 
                                                    {{ $pedido->descripcion }} 
                                                    <span class="badge bg-danger text-white ms-2">
                                                        Vencido hace {{ \Carbon\Carbon::parse($pedido->fecha)->diffInDays(\Carbon\Carbon::now()) }} días
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <div class="mt-3">
                                        <a href="{{ route('vendedores.pagos.clientes') }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-hand-holding-usd me-2"></i>Ir a Gestión de Pagos
                                        </a>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cerrarAlertaPedidosVencidosHome()">
                                            <i class="fas fa-times me-2"></i>Ocultar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <script>
                            function cerrarAlertaPedidosVencidosHome() {
                                var alerta = document.getElementById('alerta-pedidos-vencidos-home');
                                if (alerta) {
                                    alerta.classList.remove('show');
                                    setTimeout(function() {
                                        alerta.style.display = 'none';
                                    }, 150);
                                }
                            }
                        </script>
                    @endif

                    You are logged in!
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
