<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title> {{ config('app.name', 'Laravel') }} - Inicio de Sesión </title>
        <!-- Tell the browser to be responsive to screen width -->
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <link href="{{asset('imgs/favicon.png')}}" rel="shortcut icon" type="image/x-icon" />
        
        <!-- Fonts -->
        <link href='https://fonts.googleapis.com/css?family=Oswald' rel='stylesheet' type='text/css'>
        <link rel="dns-prefetch" href="//fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
  
        <!-- Styles -->
        <link rel="stylesheet" href="{{ asset('css/adminlte.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/fontawesome-free/css/all.min.css') }}">
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">

        <script src="{{ asset('js/app.js') }}" defer></script>
 
    </head>

    <body class="hold-transition login-page" style="background-image: url('{{asset('imgs/backgrounds/bg-img.jpg')}}');">
   
        <div class="login-box">
            <div class="login-box-body box box-info">
                <a href="{{ url('/') }}" class="text-center" title="{{ config('app.name', 'Laravel') }}">
                
                    <div class="login-logo">
                        <img src="{{ asset('imgs/logos/dacabe.png') }}" width="300" class="responsive" alt="{{ config('app.name', 'Laravel') }}" />
                    </div>
                </a>

                <span >
                    <h4 class="text-mobile w-100 text-center pb-3"><i class="fa fa-laptop"></i> Control de Negocio</h4>
                </span>

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    @if (count($errors) > 0)
                          <div class="alert alert-danger text-left">
                              <i class="fa fa-close"></i>
                              @foreach ($errors->all() as $error)
                              {{ $error }}
                              @endforeach
                          </div>
                    @endif

                    @if (Session::has('csrf'))
                        <div class="alert alert-warning">
                            <strong>Atención!</strong>   <br>  {{Session::get('csrf')}}
                        </div>
                    @endif

                    <div class="input-group form-group has-feedback">
                        <input type="text" autocomplete="{{ config('app.debug')?'on':'off'}}" class="form-control" name="email" value="{{ old('email') }}" placeholder="Email" oninput="setCustomValidity('')" oninvalid="this.setCustomValidity('Ingrese su Correo electrónico')" autofocus required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>
                  
                    <div class="input-group mb-3 form-group has-feedback">
                        <input type="password" autocomplete="off" class="form-control" name="password" placeholder="Clave de usuario" data-validate-length="15" oninput="setCustomValidity('')" oninvalid="this.setCustomValidity('Ingrese su Clave')" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>        
                    </div>

                    <div class="row">
                        <div class="col-md-6 col-sm-6 col-xs-6 d-none">
                            <button type="buttom" class="btn btn-default btn-block btn-flat">
                                Olvid&oacute; clave
                            </button>
                        </div>
        
                        <div class="col-md-12 pull-right w-100">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fa fa-check-square-o"></i> Aceptar
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </body>
</html>
