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

        {!! Form::open(['route' =>  ['users.update', $user->id], 'method' => 'PUT', 'files' => true, 'id' => 'form']) !!}
        <div class="row">
            <div class="col-md-4">
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile text-center">
                    
                        <img class="img-responsive img-circle" src="{{ asset('storage/users/'.$user->photo) }}" width="200px"   id="profileAvatar"  alt="{{$user->name}}">
                        

                        <h6 class="text-center"> resolución sugerida 200x200 </h6>

                        <h3 class="profile-username text-center">{{$user->name}}</h3>
                        
                        <p class="text-center">
                            <a href="#" id="btn_estatus" onclick="change_user_estatus();" class="hint--left" aria-label="Click para cambiar">
                                <small class="badge badge-success">
                                    Activo
                                </small>
                            </a>
                        </p>
                        
                        <div class="row text-center pt-2">
                            <div class="col-md-12 col-sm-12 col-xs-12 hint--top" aria-label="Click para cambiar su foto">

                                {{ Form::file('file', ['class' => 'btn btn-outline-primary btn-block ']) }}

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                    <input type="hidden" name="user_id" id="user_id" value="{{$user->id}}">
                    <div class="card card-primary card-outline">
                        
                        <div class="card-body">
                            @include('users.form')
                        </div>

                        @include('users.footer')  
                    </div>

            </div>
        </div>
        {!! Form::close() !!}
    </div>
@endsection


@section('scripts')

    <script>
        
        set_active_sidebar_menu  ('Opciones', 'Mi Perfil');
    </script>

@endsection
