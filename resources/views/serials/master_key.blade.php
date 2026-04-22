@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Maestra')

@section('titulo_header', 'Maestra SD')
@section('subtitulo_header', 'Maestra')

@section('styles')
    <link rel="stylesheet" href="{{ asset('theme/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/plugins/sweetalert2/sweetalert2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/plugins/toastr/toastr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/plugins/select2/css/select2.min.css') }}">
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
                                    
                                </div>
                                
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                           <div class="col-md-4 col-12">
                                <div class="form-group">
                                    <div class="input-group">
                                        {!! Form::text('serial1', @$master_key, ['class' => 'form-control ', 'readonly' => 'readonly', 'id' => 'master_key']) !!}                                        
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="card-footer">
                        
                    </div>
                </div>
            </div>
        </div>

    </div>

    
    @include('serials.partials.functions')
@endsection

@section('scripts')

    <script src="{{ asset('theme/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('theme/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('theme/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('theme/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('theme/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('theme/plugins/toastr/toastr.min.js') }}"></script>
    <script src="{{ asset('theme/plugins/select2/js/select2.full.min.js') }}"></script>

    @yield('js_functions')
  
@endsection
