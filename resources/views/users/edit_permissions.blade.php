@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Permisos de usuario')

@section('titulo_header', 'Permisos de usuario')
@section('subtitulo_header', 'Permisos')

@section('styles')
    <link rel="stylesheet" href="{{ asset('theme/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection


@section('content')
    <div class="container">
        
        @include('layouts.partials.info')
        @include('layouts.partials.errors')

        <div class="row">
            <div class="col-md-3">
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile text-center">
                    
                        <img class="img-responsive img-circle" src="{{ asset('imgs/users/'.$user->photo) }}" width="200px"   id="profileAvatar"  alt="{{$user->name}}">
                        

                        <h3 class="profile-username text-center">{{$user->name}}</h3>
                        
                        <p class="text-center">
                            <a href="#" id="btn_estatus" onclick="change_user_estatus();" class="hint--left" aria-label="Click para cambiar">
                                <small class="badge badge-success">
                                    Activo
                                </small>
                            </a>
                        </p>
                        
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                {!! Form::open(['route' =>  ['users.permissions', $user->id], 'method' => 'POST', 'id' => 'form']) !!}
                    <input type="hidden" name="user_id" id="user_id" value="{{$user->id}}">
                    <div class="card card-primary card-outline">
                        
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                      <label>Roles</label>
                                      <select class="select2" multiple="multiple"  name="roles[]" data-placeholder="Seleccione Roles" style="width: 100%;">
                                        @foreach ($roles as $role) 
                                            @if ($role->assigned)
                                                <option selected="selected" value="{{$role->id}}">{{ $role->display_name }}</option>
                                            @else    
                                                <option value="{{$role->id}}">{{ $role->display_name }}</option>
                                            @endif
                                        @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                {{ Form::label('permisos', 'Permisos', ['class' => 'control-label']) }}
                            </div>
                            <div class="row">
                                @foreach ($permissions as $permission)
                                    <div class="col-md-3 col-sm-6 col-12">
                                      <div class="custom-control custom-checkbox">
                                        <input
                                              type="checkbox"
                                              class="custom-control-input"
                                              name="permissions[]"
                                              id="chk{{$permission->id}}"
                                              value="{{$permission->id}}"
                                              {!! $permission->assigned ? 'checked' : '' !!}
                                            >
                                        <label for="chk{{$permission->id}}" class="custom-control-label">{{$permission->display_name ?? $permission->name}}</label>
                                        </div>
                                      </label>
                                    </div>
                                @endforeach
                            </div>

                        </div>

                        @include('users.footer')  
                    </div>
                {!! Form::close() !!}

            </div>
        </div>
    </div>
@endsection


@section('scripts')

    <script src="{{ asset('theme/plugins/select2/js/select2.full.min.js') }}"></script>

    <script>
        
        set_select2();

        set_active_sidebar_menu  ('Opciones', 'Mi Perfil');
    </script>

@endsection
