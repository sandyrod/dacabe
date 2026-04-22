@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Despachos')

@section('titulo_header', 'Gestión de Despachos')
@section('subtitulo_header', 'Despachos')

@section('styles')
    <link rel="stylesheet" href="{{ asset('theme/plugins/sweetalert2/sweetalert2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <style>
    </style>
@endsection


@section('content')
    <div class="container-fluid">
        
        @include('layouts.partials.info')
     
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">

                <div class="card card-primary card-outline" >
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-3 col-xs-12 col-sm-12">
                                 
                                    
                                
                            </div>
                            <div class="col-md-9 col-xs-12 col-sm-12">
                                <div class="float-right">
                                    <div class="btn-group">
                                        <a href="{{ route('inicio') }}" class="card-title btn btn-outline-danger hint--top" aria-label="Inicio">
                                            <i class="fas fa-arrow-left"> </i>
                                        </a> 
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="dynamic-table" class="table table-striped table-bordered table-hover" >
                                <thead>
                                <tr class="success">
                                    <th>Nro.</th>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>Vendedor/Depósito</th>
                                    <th>Observaciones</th>
                                    <th>Condiciones</th>
                                    <th>Fecha Despacho</th>
                                    <th>Acción</th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer">
                        
                    </div>
                </div>
            </div>
        </div>

    </div>

    @include('despachos.partials.functions')
@endsection

@section('scripts')
    <script src="{{ asset('theme/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('theme/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('theme/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('theme/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('theme/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
    
    @yield('js_functions')
  
@endsection
