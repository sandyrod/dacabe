@extends('layouts.app')

@section('titulo', 'Dashboard Gerencial')

@section('styles')
<style>
    .manager-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .manager-card-header {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        /* Deep Blue Gradient */
        color: white;
        padding: 1.5rem;
        text-align: center;
        position: relative;
    }

    .company-logo {
        /*width: 80px;*/
        height: 80px;
        object-fit: contain;
        /* Prevent distortion */
        background: white;
        border-radius: 10px;
        padding: 5px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        margin-bottom: 10px;
        display: inline-block;
        /* Ensure centering works */
    }

    .company-name {
        font-size: 1.5rem;
        font-weight: 700;
        margin-top: 0.5rem;
        margin-bottom: 0;
        text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
    }

    .manager-accordion .card {
        border: none;
        margin-bottom: 0.8rem;
        border-radius: 10px !important;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        background: #fff;
        transition: all 0.3s ease;
    }

    .manager-accordion .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .manager-accordion .card-header {
        background: white;
        border-bottom: 0;
        padding: 0;
        border-radius: 10px !important;
        /* Fix rounded corners */
        overflow: hidden;
    }

    .manager-accordion .btn-header {
        text-align: left;
        color: #555;
        font-weight: 600;
        font-size: 1.1rem;
        padding: 1.2rem;
        width: 100%;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: all 0.3s;
        background: #f8f9fa;
        border-left: 5px solid transparent;
    }

    .manager-accordion .btn-header.collapsed {
        background: #fff;
    }

    .manager-accordion .btn-header:not(.collapsed) {
        color: #fff;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .manager-accordion .btn-header[aria-controls="collapseConfig"]:not(.collapsed) {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        /* Purple/Blue */
    }

    .manager-accordion .btn-header[aria-controls="collapseInventario"]:not(.collapsed) {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        /* Green/Teal */
    }

    .manager-accordion .btn-header[aria-controls="collapsePedidos"]:not(.collapsed) {
        background: linear-gradient(135deg, #F2994A 0%, #F2C94C 100%);
        /* Orange/Yellow */
    }

    .manager-accordion .btn-header[aria-controls="collapsePedidos"]:not(.collapsed) span,
    .manager-accordion .btn-header[aria-controls="collapsePedidos"]:not(.collapsed) i {
        color: #fff;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    }

    .manager-accordion .btn-header[aria-controls="collapseVendedores"]:not(.collapsed) {
        background: linear-gradient(135deg, #2980b9 0%, #6dd5fa 100%);
        /* Blue */
    }

    .manager-accordion .btn-header[aria-controls="collapsePagos"]:not(.collapsed) {
        background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
        /* Red */
    }

    .manager-accordion .btn-header[aria-controls="collapseMaestros"]:not(.collapsed) {
        background: linear-gradient(135deg, #373B44 0%, #4286f4 100%);
        /* Dark Blue/Grey */
    }

    .manager-accordion .btn-header[aria-controls="collapseReport"]:not(.collapsed) {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        /* Pink/Red */
    }

    .manager-accordion .btn-header:hover {
        background-color: #f1f1f1;
        padding-left: 1.4rem;
    }

    .manager-accordion .btn-header:not(.collapsed):hover {
        padding-left: 1.2rem;
        /* Reset padding for active state to avoid jumpiness with gradient */
        opacity: 0.95;
    }

    .manager-accordion .btn-header i.icon-main {
        font-size: 1.3rem;
        margin-right: 15px;
        width: 30px;
        text-align: center;
        opacity: 0.8;
    }

    .manager-accordion .btn-header i.fa-chevron-down {
        transition: transform 0.3s;
        font-size: 0.8rem;
    }

    .manager-accordion .btn-header:not(.collapsed) i.fa-chevron-down {
        transform: rotate(180deg);
    }

    .manager-accordion .card-body {
        padding: 0;
        background-color: #fff;
        border-top: 1px solid #f0f0f0;
    }

    .manager-accordion .list-group-item {
        border: none;
        padding: 0.9rem 1.5rem;
        transition: background-color 0.2s;
        color: #666;
        font-size: 0.95rem;
        border-bottom: 1px solid #f9f9f9;
    }

    .manager-accordion .list-group-item:last-child {
        border-bottom: none;
    }

    .manager-accordion .list-group-item:hover {
        background-color: #f8f9fa;
        color: #333;
        font-weight: 500;
    }

    .manager-accordion .list-group-item i {
        color: #ccc;
        margin-right: 10px;
        width: 20px;
        text-align: center;
        transition: color 0.2s;
    }

    .manager-accordion .list-group-item:hover i {
        color: #007bff;
        /* Primary color */
    }

    .manager-accordion .label-report-new {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        background: rgba(245, 201, 4, 0.32);
        color: #b45309;
        border-radius: 999px;
        font-size: 0.8rem;
        padding: 0.18rem 0.65rem;
        margin-left: 0.75rem;
        font-weight: 700;
        box-shadow: 0 2px 8px rgba(197, 64, 64, 0.12);
    }

    .manager-accordion .list-group-item.report-highlight {
        background: linear-gradient(135deg, #fff8e1 0%, #fff1c2 100%);
        border-left: 4px solid #f59e0b;
        color: #2d3748;
        font-weight: 700;
    }

    .manager-accordion .list-group-item.report-highlight i {
        color: #b45309;
    }

    /* STATS CARDS */
    .stat-card {
        border-radius: 12px;
        border: none;
        overflow: hidden;
        position: relative;
        margin-bottom: 20px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s, box-shadow 0.3s;
        height: 120px;
        /* Fixed height */
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .stat-card .inner {
        padding: 1.2rem;
        z-index: 2;
        position: relative;
        color: white;
    }

    .stat-card .icon {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 3.5rem;
        color: rgba(255, 255, 255, 0.2);
        z-index: 1;
        transition: all 0.3s;
    }

    .stat-card:hover .icon {
        transform: translateY(-50%) scale(1.1);
        color: rgba(255, 255, 255, 0.3);
    }

    .stat-card h3 {
        font-size: 2.2rem;
        font-weight: 700;
        margin: 0;
        line-height: 1;
    }

    .stat-card p {
        font-size: 1rem;
        margin: 5px 0 0;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 500;
    }

    /* Gradients for Stats */
    .bg-gradient-warning {
        background: linear-gradient(45deg, #f09819 0%, #edde5d 100%);
    }

    .bg-gradient-success {
        background: linear-gradient(45deg, #11998e 0%, #38ef7d 100%);
    }

    .bg-gradient-info {
        background: linear-gradient(45deg, #2980b9 0%, #6dd5fa 100%);
    }

    .bg-gradient-danger {
        background: linear-gradient(45deg, #cb2d3e 0%, #ef473a 100%);
    }
@endsection

@section('content')

    <!-- Encabezado con Indicadores -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-5 g-3 stats-container mb-4">

        <!-- Primera Fila -->
        <!-- Client Sync Indicator -->
        <div class="col mb-3">
            <a href="{{ url('vendedores/sincronizar-clientes') }}" class="text-decoration-none text-reset h-100 w-100 d-block">
                <div class="card manager-card h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="card-body">
                        <h6 class="text-white mb-2"><i class="fas fa-sync-alt mr-1"></i> Sinc. Clientes</h6>
                        <div class="d-flex justify-content-between align-items-end mb-1">
                            <h3 class="mb-0 text-white" id="pedidos-sin-cruce-count">{{ $pedidosSinCruceCount }}</h3>
                            <small class="text-white font-weight-bold">
                                <i class="fas fa-exclamation-triangle"></i> Pendientes
                            </small>
                        </div>
                        <div class="progress" style="height: 8px; border-radius: 4px;">
                            <div class="progress-bar bg-gradient-light" role="progressbar"
                                style="width: 100%; background: rgba(255,255,255,0.3);"
                                aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="mt-2">
                            <small class="text-white-50">
                                <i class="fas fa-info-circle"></i> 
                                Pedidos sin cruce con clientes
                            </small>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Pedidos Indicator -->
        <div class="col mb-3">
            <a href="{{ url('pedidos') }}" class="text-decoration-none text-reset h-100 w-100 d-block">
                <div class="card manager-card h-100">
                    <div class="card-body">
                        <h6 class="text-muted mb-2"><i class="fas fa-shopping-bag mr-1"></i> Pedidos</h6>
                        <div class="d-flex justify-content-between align-items-end mb-1">
                            <h3 class="mb-0">{{ $totalOrdersCount }}</h3>
                            <small class="text-success font-weight-bold">
                                {{ $totalOrdersCount > 0 ? round(($approvedOrdersCount / $totalOrdersCount) * 100) : 0 }}% Aprobados
                            </small>
                        </div>
                        <div class="progress" style="height: 10px; border-radius: 5px;">
                            <div class="progress-bar bg-gradient-success" role="progressbar"
                                style="width: {{ $totalOrdersCount > 0 ? ($approvedOrdersCount / $totalOrdersCount) * 100 : 0 }}%"
                                aria-valuenow="{{ $approvedOrdersCount }}" aria-valuemin="0" aria-valuemax="{{ $totalOrdersCount }}"></div>
                            <div class="progress-bar bg-warning" role="progressbar"
                                style="width: {{ $totalOrdersCount > 0 ? ($pendingOrdersCount / $totalOrdersCount) * 100 : 0 }}%"
                                aria-valuenow="{{ $pendingOrdersCount }}" aria-valuemin="0" aria-valuemax="{{ $totalOrdersCount }}"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <small class="text-muted">{{ $approvedOrdersCount }} Aprobados</small>
                            <small class="text-muted">{{ $pendingOrdersCount }} Pendientes</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Segunda Fila -->
        <!-- Pagos Indicator -->
        <div class="col mb-3">
            <a href="{{ url('admin/pagos/aprobar') }}" class="text-decoration-none text-reset h-100 w-100 d-block">
                <div class="card manager-card h-100">
                    <div class="card-body">
                        <h6 class="text-muted mb-2"><i class="fas fa-money-bill-wave mr-1"></i> Pagos Recibidos</h6>
                        <div class="d-flex justify-content-between align-items-end mb-1">
                            <h3 class="mb-0">{{ $totalPaymentsCount }}</h3>
                            <small class="text-info font-weight-bold">
                                {{ $totalPaymentsCount > 0 ? round(($approvedPaymentsCount / $totalPaymentsCount) * 100) : 0 }}% Procesados
                            </small>
                        </div>
                        <div class="progress" style="height: 10px; border-radius: 5px;">
                            <div class="progress-bar bg-gradient-info" role="progressbar"
                                style="width: {{ $totalPaymentsCount > 0 ? ($approvedPaymentsCount / $totalPaymentsCount) * 100 : 0 }}%"
                                aria-valuenow="{{ $approvedPaymentsCount }}" aria-valuemin="0" aria-valuemax="{{ $totalPaymentsCount }}"></div>
                            <div class="progress-bar bg-warning" role="progressbar"
                                style="width: {{ $totalPaymentsCount > 0 ? ($pendingPaymentsCount / $totalPaymentsCount) * 100 : 0 }}%"
                                aria-valuenow="{{ $pendingPaymentsCount }}" aria-valuemin="0" aria-valuemax="{{ $totalPaymentsCount }}"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <small class="text-muted">{{ $approvedPaymentsCount }} Aprobados</small>
                            <small class="text-muted">{{ $pendingPaymentsCount }} Pendientes</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Comisiones Indicator -->
        <div class="col mb-3">
            <a href="{{ url('comisiones') }}" class="text-decoration-none text-reset h-100 w-100 d-block">
                <div class="card manager-card h-100">
                    <div class="card-body">
                        <h6 class="text-muted mb-2"><i class="fas fa-chart-pie mr-1"></i> Comisiones</h6>
                        <div class="d-flex justify-content-between align-items-end mb-1">
                            <h4 class="mb-0">${{ number_format($totalCommissions, 2) }}</h4>
                            <small class="text-primary font-weight-bold">
                                {{ $totalCommissions > 0 ? round(($paidCommissions / $totalCommissions) * 100) : 0 }}% Pagadas
                            </small>
                        </div>
                        <div class="progress" style="height: 10px; border-radius: 5px;">
                            <div class="progress-bar bg-primary" role="progressbar"
                                style="width: {{ $totalCommissions > 0 ? ($paidCommissions / $totalCommissions) * 100 : 0 }}%"
                                aria-valuenow="{{ $paidCommissions }}" aria-valuemin="0" aria-valuemax="{{ $totalCommissions }}"></div>
                            <div class="progress-bar bg-secondary" role="progressbar"
                                style="width: {{ $totalCommissions > 0 ? ($pendingCommissions / $totalCommissions) * 100 : 0 }}%"
                                aria-valuenow="{{ $pendingCommissions }}" aria-valuemin="0" aria-valuemax="{{ $totalCommissions }}"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <small class="text-muted">${{ number_format($paidCommissions, 2) }} Pagadas</small>
                            <small class="text-muted">${{ number_format($pendingCommissions, 2) }} Pendientes</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Retenciones Indicator -->
        <div class="col mb-3">
            <a href="{{ route('admin.retenciones.index') }}" class="text-decoration-none text-white h-100 w-100 d-block">
                <div class="card manager-card h-100" style="background: linear-gradient(135deg, #9a3412 0%, #c2410c 50%, #ea580c 100%);">
                    <div class="card-body d-flex flex-column justify-content-between" style="color:#fff;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-file-invoice mr-1"></i> Retenciones IVA</h6>
                            <i class="fas fa-file-invoice fa-2x" style="opacity:.35;"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-end gap-2 mb-1">
                                <h3 class="mb-0 mr-2 fw-bold">{{ $retencionesPendientesCount }}</h3>
                                <p class="mb-0 small" style="opacity:.85;">comprobante{{ $retencionesPendientesCount != 1 ? 's' : '' }} pendiente{{ $retencionesPendientesCount != 1 ? 's' : '' }}</p>
                            </div>
                            <small style="opacity:.8; font-size:12px;">
                                Bs. {{ number_format($retencionesPendientesBs, 2, ',', '.') }} por validar
                            </small>
                        </div>
                        @if($retencionesPendientesCount > 0)
                        <div class="mt-2">
                            <span style="background:rgba(255,255,255,.2); border-radius:20px; padding:3px 10px; font-size:11px; font-weight:700; letter-spacing:.5px;">
                                <i class="fas fa-exclamation-circle mr-1"></i> REQUIERE ATENCIÓN
                            </span>
                        </div>
                        @endif
                    </div>
                </div>
            </a>
        </div>

        <!-- Productos Indicator -->
        <div class="col mb-3 d-none">
            <a href="{{ url('order-inven') }}" class="text-decoration-none text-white h-100 w-100 d-block">
                <div class="card manager-card bg-gradient-danger text-white h-100">
                    <div class="card-body d-flex flex-column justify-content-center">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0"><i class="fas fa-box-open mr-1"></i> Productos</h6>
                            <i class="fas fa-box-open fa-2x opacity-50"></i>
                        </div>
                        <div class="d-flex align-items-end">
                            <h3 class="mb-0 mr-2">{{ $productsCount }}</h3>
                            <p class="mb-0 small opacity-75">Productos Totales</p>
                        </div>
                        <div class="progress mt-3" style="height: 5px; opacity: 0.5;">
                            <div class="progress-bar bg-white" role="progressbar" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

    </div>

    <!-- Menu Acordeon Centrado -->
    <div class="row justify-content-center mt-3">
        <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
            <div class="card manager-card">
                <div class="manager-card-header">
                    <i class="fas fa-clock"></i> Nuevos Pedidos
                </div>
                <div class="card-body p-4 bg-light">

                    @php
                    $impuesto = DB::connection('company')->table('IMPUEST')->where('DIMPUEST', 'IVA')->value('PORCEN');
                    $orders = (new \App\Models\Pedido)->getPendingOrders();
                    $groupedOrders = $orders->groupBy('seller_code')->map(function ($orders) {
                    return $orders->groupBy('id'); // Agrupar por cliente (RIF)
                    });
                    @endphp

                    <div class="modal" id="loadingModal" tabindex="-1" role="dialog" aria-labelledby="loadingModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content text-center">
                                <div class="modal-body">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="sr-only">Procesando...</span>
                                    </div>
                                    <p class="mt-2">Procesando...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if($groupedOrders->isEmpty())
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-5x text-muted mb-3"></i>
                        <h4 class="text-muted">No hay pedidos pendientes</h4>
                        <p class="text-secondary">No se han encontrado nuevas órdenes para procesar en este momento.</p>
                    </div>
                    @else
                    @foreach($groupedOrders as $clients)
                    @php
                    // Obtener el nombre del vendedor basado en el user_id
                    //$seller = \App\User::find($userId); // Ajusta según tu modelo de usuario

                    $clientsCollection = collect($clients);
                    $firstClient = $clientsCollection->first();
                    @endphp
                    <div class="card card-info collapsed-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                {{ @$firstClient[0]->seller_code }}
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0" style="display: none;">
                            <div class="client-info container pt-4">
                                <small class="text-danger"><i class="fa fa-info-circle"></i> Para modificar la cantidad o precio de un producto solo debe seleccionarlo e ingresar los valores deseados</small>
                            </div>
                            @foreach($clients as $rif => $orders)
                            @php
                            // Convertir a colección para poder usar first()
                            $ordersCollection = collect($orders);
                            // Obtener el primer pedido para acceder a la descripción
                            $firstOrder = $ordersCollection->first();
                            @endphp

                            <div class="client-info container pt-4" id="div_{{@$firstOrder->id}}">
                                @if (@$firstOrder->rif_foto)
                                <a href="#" onclick="event.preventDefault(); showRif('{{ $firstOrder->rif_foto }}')">
                                    <span class="text-danger hint--top" aria-label="Click para ver foto del RIF">
                                        <i class="fa fa-camera"></i>
                                    </span>
                                </a>
                                @endif
                                <a href="#" onclick="event.preventDefault(); changeClient('{{ $firstOrder }}')">
                                    <strong class="text-primary hint--top" aria-label="Click para cambiar el cliente">Cliente: {{ $firstOrder->descripcion ?? 'No disponible' }} (RIF: {{ $firstOrder->rif }})</strong>
                                </a>
                                <br>
                                @if (@$firstOrder->telefono)
                                <i class="fa fa-phone"></i> {{ $firstOrder->telefono }}
                                @endif
                                @if (@$firstOrder->email)
                                <i class="fa fa-envelope pl-3"></i> {{ $firstOrder->email }}
                                @endif

                                @if (@$firstOrder->cdepos)
                                @php
                                $depos = (new \App\Models\Deposito)->where('CDEPOS', $firstOrder->cdepos)->first();
                                @endphp
                                @if (@$depos)
                                <i class="fa fa-warehouse pl-3"></i> {{ $depos->DDEPOS }}
                                @endif
                                @endif
                                <span class="float-right">
                                    <span class="badge badge-info">
                                        <i class="fa fa-calendar"> {{formatoFechaDMA(@$firstOrder->created_at)}}</i>
                                    </span>
                                </span>
                                @if(@$firstOrder->descuento)
                                <div class="row pt-2">
                                    <div class="col-md-12 text-danger">Descuento por Pago en Divisa: <b>
                                            {{@$firstOrder->descuento}}%</b></div>
                                </div>
                                @endif
                                <hr>
                                <ul class="products-list product-list-in-card pl-2 pr-2">
                                    @php
                                    $total = 0;
                                    $n = 0;
                                    $iva = 0;
                                    @endphp
                                    @foreach($ordersCollection as $order)
                                    @php
                                    $total += ($order->precio_dolar * $order->cantidad);
                                    $n += $order->cantidad;
                                    $estilo = $order->pago == 'Bs' ? 'primary' : 'success';
                                    $pago = $order->pago == 'Bs' ? 'Bs' : '$';
                                    //$simbolo = '$/' . $pago;
                                    $simbolo = 'Ref';
                                    $iva_bs = $order->iva_bs;
                                    $iva += $order->iva>0 && $order->factura=='SI' ? ($order->precio_dolar * $order->cantidad)*($order->iva/100) : 0;
                                    @endphp
                                    <li class="item">
                                        <a href="#" onclick="event.preventDefault(); changeProduct('{{ $order->id }}', '{{ $order->codigo_inven }}', '{{ $order->cantidad }}','{{ $order->precio_dolar }}', '{{ $order->pago }}')" style="color: unset !important">
                                            <div class="product-info">
                                                <span class="product-title text-default"> {{ $order->codigo_inven }} - {!! $order->inven_descr !!}
                                                    <span class="badge badge-{{$estilo}} float-right" id="p_tot_{{$order->id}}_{{$order->codigo_inven}}">{{ $simbolo }} {{ number_format($order->precio_dolar * $order->cantidad, 2, ',', '.') }}</span>
                                                </span>
                                                <span class="product-description">
                                                    <p id="p_product_{{$order->id}}_{{$order->codigo_inven}}">
                                                        Cant.: {{$order->cantidad}}
                                                        <span class="pl-4"> Monto: {{$simbolo}} {{ number_format($order->precio_dolar, 2, ',', '.') }}</span>
                                                    </p>
                                                </span>
                                            </div>
                                        </a>
                                    </li>
                                    @endforeach
                                </ul>
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 w-100">
                                        <div class="">
                                            <span class="text-small w-100" id="n_{{$order->id}}">
                                                <b>Items:</b> {{@$n}}
                                            </span>

                                            <br>
                                            <?php
                                            $base = $total;
                                            //$porc_iva = $impuesto ? $impuesto : 0; 
                                            //$iva= @$total*($porc_iva/100); 
                                            $retencion = @$firstOrder->cliageret ? ($iva * (@$firstOrder->porc_retencion / 100)) : 0;
                                            $total = $base + ($iva - $retencion);
                                            $retencion_bs = ($iva_bs * @$firstOrder->porc_retencion / 100);
                                            ?>
                                            <b>IVA Bs:</b>
                                            <span
                                                id="iva_{{ $order->id }}_{{ $order->codigo_inven }}">
                                                {{ number_format($iva_bs, 2, ',', '.') }}
                                            </span>
                                            <br>
                                            @if ($order->factura=='SI')
                                            <span class="badge badge-info">Factura</span>
                                            @else
                                            <span class="badge badge-warning">Nota</span>
                                            @endif

                                        </div>

                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
                                        <div class="">
                                            <span class="text-small" id="iva_{{$order->id}}">
                                                @if (@$firstOrder->porc_retencion && $firstOrder->porc_retencion > 0)
                                                <b>% Retenc.</b> {{ @$firstOrder->porc_retencion }}%
                                                <br>
                                                (Ret. Bs.:
                                                <span
                                                    id="retencion_{{ $order->id }}_{{ $order->codigo_inven }}">
                                                    {{ number_format($retencion_bs, 2, ',', '.') }}
                                                </span>
                                                )
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                        <div class="container">
                                            <div class="alert alert-danger alert-dismissible w-100">
                                                <i class="icon fa fa-calculator"></i>
                                                <b id="gran_total_{{$order->id}}">
                                                    TOTAL: Ref {{number_format($total, 2, ',', '.')}}
                                                </b>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-3 pt-2">
                                        <a href="{{ route('admin.pedidos_editor.edit', @$firstOrder->id) }}" class="btn btn-outline-primary btn-block pt-2 hint--top" aria-label="Editar Pedido"><i class="fa fa-edit"></i> Editar Pedido</a>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-3 pt-2">

                                        <a id="btn_APROBADO_{{@$firstOrder->id}}" href="javascript:void(0)" onclick="event.preventDefault(); approveOrCancelOrder('{{@$firstOrder->id}}', 'APROBADO')" class="btn btn-outline-success btn-block pt-2 hint--top" aria-label="Aprobar Pedido"><i class="fa fa-check"></i> Aprobar</a>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-3 pt-2">
                                        <a id="btn_RECHAZADO_{{@$firstOrder->id}}" href="javascript:void(0)" onclick="event.preventDefault(); approveOrCancelOrder('{{@$firstOrder->id}}', 'RECHAZADO')" class="btn btn-outline-danger btn-block pt-2 hint--top" aria-label="Rechazar Pedido"><i class="fa fa-trash"></i> Rechazar</a>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-3 pt-2">
                                        <a target="_blank" href="{{url('print-order/' . @$firstOrder->id)}}" class="btn btn-outline-info btn-block pt-2 hint--top" aria-label="Imprimir Pedido"><i class="fa fa-print"></i> Imprimir</a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="card-footer text-center" style="display: none;">
                        </div>
                    </div>
                    @endforeach
                    @endif


                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">

            <div class="card manager-card">
                <div class="manager-card-header">
                    @if (Auth::user()->company->logo)
                    <img src="{{ asset('storage/logos/' . Auth::user()->company->logo) }}" alt="Logo"
                        class="company-logo">
                    @else
                    <img src="{{ asset('imgs/favicon.png') }}" alt="Logo" class="company-logo">
                    @endif
                    <h2 class="company-name text-default">{{ Auth::user()->company->name }}</h2>
                    <p class="mb-0 text-white-50"><small>Panel Administrativo</small></p>
                </div>

                <div class="card-body p-4 bg-light">
                    <div class="accordion manager-accordion" id="accordionManager">



                        <!-- INVENTARIO -->
                        <div class="card">
                            <div class="card-header" id="headingInventario">
                                <button class="btn btn-header collapsed" type="button" data-toggle="collapse"
                                    data-target="#collapseInventario" aria-expanded="false"
                                    aria-controls="collapseInventario">
                                    <span>
                                        <i class="fas fa-boxes icon-main"></i> Inventario
                                        <span class="label-report-new d-none"><i class="fas fa-bell"></i></span>
                                    </span>
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </div>
                            <div id="collapseInventario" class="collapse" aria-labelledby="headingInventario"
                                data-parent="#accordionManager">
                                <div class="card-body">
                                    <div class="list-group list-group-flush">
                                        <a href="{{ url('order-inven') }}"
                                            class="list-group-item list-group-item-action">
                                            <i class="fas fa-tag"></i> Productos
                                        </a>
                                        <a href="{{ url('admin/producto-bultos') }}"
                                            class="list-group-item list-group-item-action">
                                            <i class="fas fa-box-open"></i> Definir Bultos
                                        </a>
                                        <a href="{{ url('modificar-precios') }}"
                                            class="list-group-item list-group-item-action">
                                            <i class="fas fa-tags"></i> Modificar Precios
                                        </a>
                                        <a href="{{ url('productos-futuros') }}"
                                            class="list-group-item list-group-item-action">
                                            <i class="fas fa-clock"></i> Productos por Llegar
                                        </a>
                                        <a href="{{ url('marketing') }}"
                                            class="list-group-item list-group-item-action">
                                            <i class="fas fa-bullhorn"></i> Marketing
                                        </a>
                                        <a href="{{ url('admin/productos/movimiento') }}"
                                            class="list-group-item list-group-item-action">
                                            <i class="fas fa-exchange-alt"></i> Movimientos de producto
                                        </a>
                                        <a href="{{ url('inventario-inicial') }}"
                                            class="list-group-item list-group-item-action">
                                            <i class="fas fa-warehouse"></i> Inventario Inicial
                                        </a>
                                        <a href="{{ url('/admin/productos/auditoria') }}"
                                            class="list-group-item list-group-item-action">
                                            <i class="fas fa-clipboard-check"></i> Auditoria de productos
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        

                        <!-- PEDIDOS -->
                        <div class="card">
                            <div class="card-header" id="headingPedidos">
                                <button class="btn btn-header collapsed" type="button" data-toggle="collapse"
                                    data-target="#collapsePedidos" aria-expanded="false"
                                    aria-controls="collapsePedidos">
                                    <span><i class="fas fa-shopping-bag icon-main"></i> Pedidos
                                    <span class="label-report-new"><i class="fas fa-bell"></i></span></span>
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </div>
                            <div id="collapsePedidos" class="collapse" aria-labelledby="headingPedidos"
                                data-parent="#accordionManager">
                                <div class="card-body">
                                    <div class="list-group list-group-flush">
                                        <a href="{{ url('admin/pedidos-gestion') }}"
                                            class="list-group-item list-group-item-action">
                                            <i class="fas fa-check"></i> Gestión de Pedidos (nuevo)
                                        </a>
                                        <a href="{{ route('admin.pedidos_editor.index') }}"
                                            class="list-group-item list-group-item-action report-highlight">
                                            <i class="fas fa-edit"></i> Editor de Pedidos
                                        </a>
                                        <a href="{{ url('pedidos') }}"
                                            class="list-group-item list-group-item-action">
                                            <i class="fas fa-clipboard-list"></i> Gestión de Pedidos
                                        </a>
                                        <a href="{{ url('despachos') }}"
                                            class="list-group-item list-group-item-action">
                                            <i class="fas fa-truck"></i> Despachos
                                        </a>
                                        <a href="{{ url('admin/pedidos-iva-modificar') }}"
                                            class="list-group-item list-group-item-action">
                                            <i class="fas fa-calculator"></i> Ajustar Montos
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PAGOS -->
                        <div class="card">
                            <div class="card-header" id="headingPagos">
                                <button class="btn btn-header collapsed" type="button" data-toggle="collapse"
                                    data-target="#collapsePagos" aria-expanded="false" aria-controls="collapsePagos">
                                    <span>
                                        <i class="fas fa-money-bill-wave icon-main"></i> Pagos
                                        <span class="label-report-new"><i class="fas fa-bell"></i></span>
                                    </span>
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </div>
                            <div id="collapsePagos" class="collapse" aria-labelledby="headingPagos"
                                data-parent="#accordionManager">
                                <div class="card-body">
                                    <div class="list-group list-group-flush">
                                        <a href="{{ url('admin/pagos/aprobar') }}"
                                            class="list-group-item list-group-item-action">
                                            <i class="fas fa-check-double"></i> Recibidos
                                        </a>
                                        <a href="{{ url('admin/pagos') }}"
                                            class="list-group-item list-group-item-action">
                                            <i class="fas fa-file-invoice-dollar"></i> Reporte de Pagos
                                        </a>
                                        <a href="{{ url('admin/cuentas-por-cobrar') }}"
                                            class="list-group-item list-group-item-action report-highlight">
                                            <i class="fas fa-balance-scale"></i> Cuentas por Cobrar
                                        </a>
                                        <a href="{{ url('comisiones') }}"
                                            class="list-group-item list-group-item-action">
                                            <i class="fas fa-chart-pie"></i> Pago de Comisiones
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- VENDEDORES -->
                        <div class="card">
                            <div class="card-header" id="headingVendedores">
                                <button class="btn btn-header collapsed" type="button" data-toggle="collapse"
                                    data-target="#collapseVendedores" aria-expanded="false"
                                    aria-controls="collapseVendedores">
                                    <span>
                                        <i class="fas fa-users icon-main"></i> Vendedores
                                        <span class="label-report-new d-none"><i class="fas fa-bell"></i></span>
                                    </span>
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </div>
                            <div id="collapseVendedores" class="collapse" aria-labelledby="headingVendedores"
                                data-parent="#accordionManager">
                                <div class="card-body">
                                    <div class="list-group list-group-flush">
                                        <a href="{{ url('vendedores') }}"
                                            class="list-group-item list-group-item-action">
                                            <i class="fas fa-user-edit"></i> Gestión
                                        </a>
                                        <a href="{{ url('/comisiones/estado-cuenta') }}"
                                            class="list-group-item list-group-item-action">
                                            <i class="fas fa-calculator"></i> Estado de Cuenta
                                        </a>
                                        <a href="{{ url('descuentos') }}"
                                            class="list-group-item list-group-item-action">
                                            <i class="fas fa-percent"></i> Descuentos
                                        </a>
                                        <a href="{{ url('/admin/cliente-vendedor') }}"
                                            class="list-group-item list-group-item-action report-highlight">
                                            <i class="fas fa-address-card"></i> Clientes Asociados
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- MAESTROS -->
                        <div class="card">
                            <div class="card-header" id="headingMaestros">
                                <button class="btn btn-header collapsed" type="button" data-toggle="collapse"
                                    data-target="#collapseMaestros" aria-expanded="false"
                                    aria-controls="collapseMaestros">
                                    <span><i class="fas fa-database icon-main"></i> Maestros</span>
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </div>
                            <div id="collapseMaestros" class="collapse" aria-labelledby="headingMaestros"
                                data-parent="#accordionManager">
                                <div class="card-body">
                                    <div class="list-group list-group-flush">
                                        <a href="{{ url('pago_destinos') }}"
                                            class="list-group-item list-group-item-action">
                                            <i class="fas fa-university"></i> Tipos de Pago/Bancos
                                        </a>
                                        <a href="{{ url('banks') }}" class="list-group-item list-group-item-action">
                                            <i class="fas fa-university"></i> Bancos Clientes
                                        </a>
                                        <a href="{{ url('order-clients') }}"
                                            class="list-group-item list-group-item-action">
                                            <i class="fas fa-address-book"></i> Clientes
                                        </a>
                                        <a href="{{ url('zonas') }}" class="list-group-item list-group-item-action">
                                            <i class="fas fa-map-marker-alt"></i> Zonas
                                        </a>
                                        <a href="{{ url('tipprod') }}"
                                            class="list-group-item list-group-item-action">
                                            <i class="fas fa-layer-group"></i> Tipos de Producto
                                        </a>
                                        <a href="{{ url('dpto') }}" class="list-group-item list-group-item-action">
                                            <i class="fas fa-building"></i> Departamentos
                                        </a>
                                        <a href="{{ url('unimed') }}" class="list-group-item list-group-item-action">
                                            <i class="fas fa-ruler"></i> Unidades de Medida
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- REPORTES -->
                        <div class="card">
                            <div class="card-header" id="headingReport">
                                <button class="btn btn-header collapsed" type="button" data-toggle="collapse"
                                    data-target="#collapseReport" aria-expanded="false" aria-controls="collapseReport">
                                    <span>
                                        <i class="fas fa-chart-line icon-main"></i> Reportes
                                        <span class="label-report-new d-none"><i class="fas fa-bell"></i></span>
                                    </span>
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </div>

                            <div id="collapseReport" class="collapse" aria-labelledby="headingReport"
                                data-parent="#accordionManager">
                                <div class="card-body">
                                    <div class="list-group list-group-flush">
                                        <a href="{{ url('admin/reportes/inventario-deposito') }}"
                                            class="list-group-item list-group-item-action">
                                            <i class="fas fa-warehouse"></i> Inventario por Depósito
                                        </a>
                                        <a href="{{ url('admin/reportes/inventario') }}"
                                            class="list-group-item list-group-item-action">
                                            <i class="fas fa-tags"></i> Inventario General
                                        </a>
                                        <a href="{{ url('admin/reportes/inventario-dashboard') }}"
                                            class="list-group-item list-group-item-action">
                                            <i class="fas fa-chart-bar"></i> Gráficas de Inventario
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CONFIGURACION -->
                        <div class="card">
                            <div class="card-header" id="headingConfig">
                                <button class="btn btn-header collapsed" type="button" data-toggle="collapse"
                                    data-target="#collapseConfig" aria-expanded="false" aria-controls="collapseConfig">
                                    <span><i class="fas fa-cogs icon-main"></i> Configuración</span>
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </div>

                            <div id="collapseConfig" class="collapse" aria-labelledby="headingConfig"
                                data-parent="#accordionManager">
                                <div class="card-body">
                                    <div class="list-group list-group-flush">
                                        <a href="{{ url('descuento-global') }}"
                                            class="list-group-item list-group-item-action">
                                            <i class="fas fa-percentage"></i> Descuento Global
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
        

    </div>
</div>

@push('scripts')
<script>
// Cargar contador de pedidos sin cruce dinámicamente
$(document).ready(function() {
    $.ajax({
        url: '{{ route("sincronizar.clientes.get.pedidos.sin.cruce") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const count = response.pedidos_sin_cruce ? response.pedidos_sin_cruce.length : 0;
                $('#pedidos-sin-cruce-count').text(count);
                
                // Actualizar la barra de progreso
                const progressBar = $('#pedidos-sin-cruce-count').closest('.manager-card').find('.progress-bar');
                if (count > 0) {
                    progressBar.css('width', '100%');
                    progressBar.css('background', 'rgba(255,255,255,0.3)');
                } else {
                    progressBar.css('width', '0%');
                    progressBar.css('background', 'rgba(255,255,255,0.1)');
                }
            }
        },
        error: function() {
            console.error('Error al cargar contador de pedidos sin cruce');
        }
    });
});
</script>
@endpush

@include('layouts.partials.order_modal')
@include('layouts.partials.seller_modal')
@include('layouts.partials.order_functions')
@endsection