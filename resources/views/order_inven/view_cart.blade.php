@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Cargar Pedido')

@section('titulo_header', 'Cargar Pedido')
@section('subtitulo_header', 'Cargar Pedido')

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
     
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">

                @include('order_inven.details_cart')
                
            </div>
        </div>

    </div>

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
