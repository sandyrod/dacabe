<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <!-- Web Application Manifest -->
        <link rel="manifest" href="/manifest.json">

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title> @yield('titulo', trans('site.head.title'))</title>
        <!-- Tell the browser to be responsive to screen width -->
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <link href="{{asset('imgs/favicon.png')}}" rel="shortcut icon" type="image/x-icon" />

        <meta name="csrf-token" content="{{ csrf_token() }}" />
        
        <!-- Fonts -->
        <link rel="dns-prefetch" href="//fonts.gstatic.com">
        
        <!-- Styles -->
        <link rel="stylesheet" href="{{ asset('theme/dist/css/adminlte.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/fontawesome-free/css/all.min.css') }}">
        <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    
        <style>
            body {
                font-family: helvetica;
            }
        </style>
        
        @yield('styles')

        @livewireStyles

        @laravelPWA

    </head>

    <body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed text-helvetica">
        
        <div class="wrapper">
        
        
            <div class="content-wrapper">
                
        
                @yield('content')
            
            </div>
            
        
            <aside class="control-sidebar control-sidebar-dark">
                <!-- Control sidebar content goes here -->
            </aside>
        
        </div>


        <!-- jQuery -->
        <script src="{{ asset('theme/plugins/jquery/jquery.min.js') }}"></script>
        <!-- jQuery UI 1.11.4 -->
        <script src="{{ asset('theme/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
        <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->

        <script>
          $.widget.bridge('uibutton', $.ui.button)
        </script>

        <!-- Bootstrap 4 -->
        <script src="{{ asset('theme/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        
        
        <script src="{{ asset('js/functions.js') }}" ></script>


        @yield('scripts')  

        @livewireScripts

    </body>
</html>
