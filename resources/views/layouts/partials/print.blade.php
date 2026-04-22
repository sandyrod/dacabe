<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>@yield('titulo', 'Reporte')</title>
        
        <link rel="stylesheet" href="{{ asset('theme/dist/css/adminlte.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/fontawesome-free/css/all.min.css') }}">        
 
    </head>

    <body class="hold-transition sidebar-mini layout-fixed">
        
        <div class="wrapper">

    <div class="container">
        
        <div class="invoice p-3 mb-3">
              
              <div class="row invoice-info">
                <div class="col-sm-4 invoice-col">
                  <img class="responsive" width="150" src="{{ asset('imgs/logos/'.$report_data['company']->logo) }}" alt="">
                </div>

                <div class="col-sm-4 invoice-col">
                  <address>
                    <strong>{{$report_data['company']->name}}</strong><br>
                    <small>
                      RIF: {{$report_data['company']->code}}<br>
                      Teléfono: {{$report_data['company']->phone}}<br>
                      Email: {{$report_data['company']->email}}
                    </small>
                  </address>
                </div>
        
                <div class="col-sm-4 invoice-col">
                  <strong>{{ getUserFullName() }}</strong><br>
                  <small>
                    <b>Email:</b> {{ Auth::user()->email }}<br>
                    <b>Fecha:</b> {{ getNow() }}<br>
                  </small>
                </div>
              </div>
              
              <div class="row pt-4">
                <div class="col-12 text-center">
                  <h4>
                    <i class="fas fa-list-alt"></i> @yield('titulo', 'Reporte')
                    
                  </h4>
                </div>
              </div>

              @yield('content')

              
             
            </div>
          </div>

    </div>

     </div>


    @if (isset($print))
        <script type="text/javascript"> 
          window.addEventListener("load", window.print());
        </script>
    @endif
        
      
    </body>
</html>
