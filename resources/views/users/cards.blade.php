@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Usuarios')

@section('titulo_header', 'Gestión de Usuarios')
@section('subtitulo_header', 'Usuarios')

@section('styles')
    <link rel="stylesheet" href="{{ asset('theme/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('theme/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <style>
    </style>
@endsection


@section('content')
    <div class="container">
        
        @include('layouts.partials.info')
     
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">

                        
                              <div class="row d-flex align-items-stretch">
                                
                                @foreach($users as $user)
                                <div class="col-12 col-sm-6 col-md-4 d-flex align-items-stretch">
                                  <div class="card bg-light">
                                    <div class="card-header text-muted border-bottom-0">
                                      
                                    </div>
                                    <div class="card-body pt-0">
                                      <div class="row">
                                        <div class="col-7">
                                          <h2 class="lead"><b>{{$user->name}} {{$user->last_name}}</b></h2>
                                          <p class="text-muted text-sm"><b>Roles: </b>
                                            @foreach($user->roles as $role)
                                              @if ($loop->last)
                                                {{$role->display_name}}
                                              @else
                                                {{$role->display_name}} /
                                              @endif
                                            @endforeach
                                          </p>
                                          <ul class="ml-4 mb-0 fa-ul text-muted">
                                            @if ($user->phone)
                                                <li class="small"><span class="fa-li"><i class="fas fa-lg fa-phone"></i></span> {{ $user->phone }}</li>
                                            @endif
                                            <li class="small"><span class="fa-li"><i class="fas fa-lg fa-envelope"></i></span> {{ $user->email }}</li>
                                          </ul>
                                        </div>
                                        <div class="col-5 text-center">
                                          <img src="{{ asset('imgs/users/'.$user->photo) }}" alt="" class="img-circle img-fluid">
                                        </div>
                                      </div>
                                    </div>
                                    
                                  </div>
                                </div>
                                @endforeach
                                
                                
                              </div>
                            </div>
                            
                            <div class="card-footer mx-auto">
                              <nav aria-label="Contacts Page Navigation">
                                {{$users->links()}}
                              </nav>
                            </div>

                          </div>

    </div>

    @include('users.partials.functions')
@endsection

@section('scripts')

    <script src="{{ asset('theme/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('theme/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('theme/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('theme/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    
    @yield('js_functions')
  
@endsection
