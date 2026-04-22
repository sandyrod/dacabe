<!DOCTYPE html>
<html class="no-js" lang="es">
<head>
  {{-- Title --}}
  <!-- Meta Tags -->
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="keywords" content="Inversiones DACABE">
        <meta name="description" content="">
        <meta name='copyright' content=''>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        
        <!-- Title -->
        <title>Inversiones Dacabe</title>
        
        <!-- Favicon -->
        <link href="{{asset('landing/img/favicon.png')}}" rel="shortcut icon" type="image/x-icon" />
        
        <!-- Google Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Poppins:200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i&display=swap" rel="stylesheet">

        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="{{ asset('landing/css/bootstrap.min.css')}}">
        <!-- Nice Select CSS -->
        <link rel="stylesheet" href="{{ asset('landing/css/nice-select.css')}}">
        <!-- Font Awesome CSS -->
        <link rel="stylesheet" href="{{ asset('landing/css/font-awesome.min.css')}}">
        <!-- icofont CSS -->
        <link rel="stylesheet" href="{{ asset('landing/css/icofont.css')}}">
        <!-- Slicknav -->
        <link rel="stylesheet" href="{{ asset('landing/css/slicknav.min.css')}}">
        <!-- Owl Carousel CSS -->
        <link rel="stylesheet" href="{{ asset('landing/css/owl-carousel.css')}}">
        <!-- Datepicker CSS -->
        <link rel="stylesheet" href="{{ asset('landing/css/datepicker.css')}}">
        <!-- Animate CSS -->
        <link rel="stylesheet" href="{{ asset('landing/css/animate.min.css')}}">
        <!-- Magnific Popup CSS -->
        <link rel="stylesheet" href="{{ asset('landing/css/magnific-popup.css')}}">
        
        <!-- Medipro CSS -->
        <link rel="stylesheet" href="{{ asset('landing/css/normalize.css')}}">
        <link rel="stylesheet" href="{{ asset('landing/style.css')}}">
        <link rel="stylesheet" href="{{ asset('landing/css/responsive.css')}}">
        

  {{-- CSS Plugins Specific--}}
  @yield('css')

  {{-- CSS Styles - Casos Partículares --}}
  @yield('style')

</head>
<body>

  {{-- Seccion - HEADER & Menu --}}
  @include('landing.layouts.partials.header')

  {{-- Seccion - CONTENT --}}
  @yield('content')

  {{-- Seccion - FOOTER --}}
  @include('landing.layouts.partials.footer')



  {{-- Using Script --}}

  <!-- jquery Min JS -->
        <script src="{{ asset('landing/js/jquery.min.js')}}"></script>
        <!-- jquery Migrate JS -->
        <script src="{{ asset('landing/js/jquery-migrate-3.0.0.js')}}"></script>
        <!-- jquery Ui JS -->
        <script src="{{ asset('landing/js/jquery-ui.min.js')}}"></script>
        <!-- Easing JS -->
        <script src="{{ asset('landing/js/easing.js')}}"></script>
        <!-- Color JS -->
        <script src="{{ asset('landing/js/colors.js')}}"></script>
        <!-- Popper JS -->
        <script src="{{ asset('landing/js/popper.min.js')}}"></script>
        <!-- Bootstrap Datepicker JS -->
        <script src="{{ asset('landing/js/bootstrap-datepicker.js')}}"></script>
        <!-- Jquery Nav JS -->
        <script src="{{ asset('landing/js/jquery.nav.js')}}"></script>
        <!-- Slicknav JS -->
        <script src="{{ asset('landing/js/slicknav.min.js')}}"></script>
        <!-- ScrollUp JS -->
        <script src="{{ asset('landing/js/jquery.scrollUp.min.js')}}"></script>
        <!-- Niceselect JS -->
        <script src="{{ asset('landing/js/niceselect.js')}}"></script>
        <!-- Tilt Jquery JS -->
        <script src="{{ asset('landing/js/tilt.jquery.min.js')}}"></script>
        <!-- Owl Carousel JS -->
        <script src="{{ asset('landing/js/owl-carousel.js')}}"></script>
        <!-- counterup JS -->
        <script src="{{ asset('landing/js/jquery.counterup.min.js')}}"></script>
        <!-- Steller JS -->
        <script src="{{ asset('landing/js/steller.js')}}"></script>
        <!-- Wow JS -->
        <script src="{{ asset('landing/js/wow.min.js')}}"></script>
        <!-- Magnific Popup JS -->
        <script src="{{ asset('landing/js/jquery.magnific-popup.min.js')}}"></script>
        <!-- Counter Up CDN JS -->
        <script src="http://cdnjs.cloudflare.com/ajax/libs/waypoints/2.0.3/waypoints.min.js"></script>
        <!-- Bootstrap JS -->
        <script src="{{ asset('landing/js/bootstrap.min.js')}}"></script>
        <!-- Main JS -->
        <script src="{{ asset('landing/js/main.js')}}"></script>
  
  {{-- JS Plugins Specific --}}
  @yield('js')


  <!--  SERVICE WORKER -->
        <script type="text/javascript">

        // Initialize the service worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/serviceworker.js', {
                scope: '.'
            }).then(function (registration) {
                // Registration was successful
                console.log('Laravel PWA: ServiceWorker registration successful with scope: ', registration.scope);
            }, function (err) {
                // registration failed :(
                console.log('Laravel PWA: ServiceWorker registration failed: ', err);
            });
        } else {
            console.log('Nothing...');
        }
    </script>

  {{-- JS Scripts - Casos Partículares --}}
  @yield('script')


  @if( !env('APP_DEBUG', 'true') )
    <script>
        $(document).ready(function(){ $(document).bind("contextmenu", function(e){ return false; }); });
        console.log = function() {};

    </script>
  @endif
</body>
</html>