@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Mi Perfil')

@section('titulo_header', 'Usuario')
@section('subtitulo_header', 'Mi Perfil')

@section('styles')
    <style>
        
    </style>
@endsection


@section('content')
    <div class="container-fluid">
        
        @include('layouts.partials.info')
        @include('layouts.partials.errors')

        <div class="row">
            <div class="col-md-4">
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile text-center">
                    
                        <img class="img-responsive img-circle" src="{{ asset('imgs/users/'.$user->photo) }}" width="200px"   id="profileAvatar"  alt="{{$user->name}}">
                        

                        <h6 class="text-center"> resolución 200 x 200 </h6>

                        <h3 class="profile-username text-center">{{$user->name}}</h3>
                        
                        <p class="text-center">
                            <a href="#" id="btn_estatus" onclick="change_user_estatus();" class="hint--left" aria-label="Click para cambiar">
                                <small class="badge badge-success">
                                    Activo
                                </small>
                            </a>
                        </p>
                        
                        <div class="row text-center pt-2">
                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <a href="#" class="btn btn-outline-primary btn-block"><i class="fa fa-camera"></i> Cambiar Foto</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                {!! Form::open(['route' =>  ['users.update', $user->id], 'method' => 'PUT', 'id' => 'form']) !!}
                    <input type="hidden" name="user_id" id="user_id" value="{{$user->id}}">
                    <div class="card card-primary card-outline">
                        
                        <div class="card-body">
                            @include('users.form')
                        </div>

                        @include('users.footer')  
                    </div>
                {!! Form::close() !!}

            </div>
        </div>
    </div>
@endsection


@section('scripts')

    <script>
        
        set_active_sidebar_menu  ('Opciones', 'Mi Perfil');
    </script>

@endsection
