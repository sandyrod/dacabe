<!DOCTYPE html>
<html class="no-js" lang="es">
<head>
  {{-- Title --}}
  <title> @yield('titulo', @$landing->company_name) | Menú</title>
  {{-- Favicon --}}
  <link href="{{asset('asset/landing/img/favicon.png')}}" rel="shortcut icon" type="image/x-icon" />
  {{-- Meta --}}
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="keywords" content="Aplicacion para pedidos, Lara, Yaracuy, venezuela" />
  <meta name="description" content="Aplicacion para pedidos Yaracuy, venezuela">
  <meta name="author" content="Developer">

    
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="{{ asset('theme/restaurant/assets/vendor/animate.css/animate.min.css') }}" rel="stylesheet">
  <link href="{{ asset('theme/restaurant/assets/vendor/aos/aos.css') }}" rel="stylesheet">
  <link href="{{ asset('theme/restaurant/assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('theme/restaurant/assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
  <link href="{{ asset('theme/restaurant/assets/vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet">
  <link href="{{ asset('theme/restaurant/assets/vendor/glightbox/css/glightbox.min.css') }}" rel="stylesheet">
  <link href="{{ asset('theme/restaurant/assets/vendor/swiper/swiper-bundle.min.css') }}" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="{{ asset('theme/restaurant/assets/css/style.css') }}" rel="stylesheet">

  
  
  {{-- CSS Plugins Specific--}}
  @yield('css')

  <script src="{{ asset('asset/landing/js/modernizr-2.8.3.min.js') }}"></script>

  {{-- CSS Styles - Casos Partículares --}}
  @yield('style')

</head>
<body>

  {{-- Seccion - CONTENT --}}
  @yield('content')

  
  

  <!-- Vendor JS Files -->
  <script src="{{ asset('theme/restaurant/assets/vendor/aos/aos.js') }}"></script>
  <script src="{{ asset('theme/restaurant/assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('theme/restaurant/assets/vendor/glightbox/js/glightbox.min.js') }}"></script>
  <script src="{{ asset('theme/restaurant/assets/vendor/isotope-layout/isotope.pkgd.min.js') }}"></script>
  <script src="{{ asset('theme/restaurant/assets/vendor/swiper/swiper-bundle.min.js') }}"></script>
  <script src="{{ asset('theme/restaurant/assets/vendor/php-email-form/validate.js') }}"></script>

  <!-- Template Main JS File -->
  <script src="{{ asset('theme/restaurant/assets/js/main.js') }}"></script>
  
  {{-- JS Plugins Specific --}}
  @yield('js')

  {{-- JS Page --}}

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