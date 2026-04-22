@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel') . ' - Pedidos')

@section('titulo_header', 'Gestión de Pedidos')
@section('subtitulo_header', 'Pedidos')

@section('styles')
    <link rel="stylesheet" href="{{ asset('theme/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/plugins/sweetalert2/sweetalert2.min.css') }}">
    <style>
    </style>
@endsection


@section('content')
    <div class="container-fluid">

        @include('layouts.partials.info')

        @php($pending_commission = (new \App\Models\ComisionVendedor)->getPendingCommissions())
          @php($orders = (new \App\Models\Pedido)->getMyLastOrders(6))

          @if($pending_commission->count() > 0)
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
              <i class="fas fa-exclamation-triangle"></i> Tienes nuevas comisiones recibidas. Debes confirmar la recepción del pago. 
              <a href="{{ url('comisiones-recibidas') }}" class="ml-3 text-white">
                    <i class="fas fa-check"></i> Confirmar Comisiones Recibidas
                  </a>
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
          @endif

        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">

                @include('order_inven.product_list')
                {{--
                @livewire('shoppingcart')
                --}}

            </div>
        </div>

    </div>
    @include('order_inven.partials.modal_information')
    @include('order_inven.partials.functions')
@endsection

@section('scripts')

    <script src="{{ asset('theme/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('theme/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('theme/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('theme/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('theme/plugins/sweetalert2/sweetalert2.min.js') }}"></script>

    @yield('js_functions')

@endsection
