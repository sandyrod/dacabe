@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Empresas')

@section('titulo_header', 'Gestión de Empresas')
@section('subtitulo_header', 'Empresas')

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

                <div class="card card-primary card-outline" >
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-3 col-xs-12 col-sm-12">
                                 @if (hasPermission('create-inven'))
                                    <a href="{{route('inven.create')}}" class="card-title btn btn-primary btn-block hint--top" aria-label="Agregar nuevo registro">
                                        <i class="fa fa-plus-circle"> </i>  Agregar
                                    </a>
                                @endif
                            </div>
                            <div class="col-md-9 col-xs-12 col-sm-12 mt-1">
                                <div class="float-right">
                                    <div class="btn-group">
                                        <a href="{{ route('inven.print') }}" target="_blank" class="card-title text-blue btn btn-default hint--top" aria-label="Imprimir">
                                            <i class="fas fa-print"> </i>
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
                                    <th>Rif</th>
                                    <th>Nombre</th>
                                    <th>Teléfono</th>
                                    <th>Status</th>
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

    
    @include('inven.partials.functions')
@endsection

@section('scripts')

    <script src="{{ asset('theme/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('theme/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('theme/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('theme/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('theme/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
    
    @yield('js_functions')
  
@endsection
