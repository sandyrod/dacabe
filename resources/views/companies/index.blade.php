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
                                 @if (hasPermission('create-company'))
                                    <a href="{{route('companies.create')}}" class="card-title btn btn-primary btn-block hint--top" aria-label="Agregar nuevo registro">
                                        <i class="fa fa-plus-circle"> </i>  Agregar
                                    </a>
                                @endif
                            </div>
                            <div class="col-md-9 col-xs-12 col-sm-12 mt-1">
                                <div class="float-right">
                                    <div class="btn-group">
                                        <a href="{{ url('company-status') }}" class="card-title text-blue btn btn-default hint--top" aria-label="Status de Empresas">
                                            <i class="fas fa-business-time"></i> Estatus
                                        </a>
                                        <a href="{{ route('companies.print') }}" target="_blank" class="card-title text-blue btn btn-default hint--top" aria-label="Imprimir">
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

    <div class="modal fade" id="company-modal">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">Permisos para Módulos</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning alert-dismissible">
                  <p>Click sobre el módulo para activar o inactivar</p>
                </div>
                @include('companies.modal_form')
            </div>
            <div class="modal-footer justify-content-between">
              <button type="button" class="btn btn-outline-danger" data-dismiss="modal">Cerrar</button>              
            </div>
          </div>
        </div>        
      </div>

    @include('companies.partials.functions')
@endsection

@section('scripts')

    <script src="{{ asset('theme/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('theme/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('theme/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('theme/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('theme/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
    
    @yield('js_functions')
  
@endsection
