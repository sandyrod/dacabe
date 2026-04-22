<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title> @yield('titulo', trans('site.head.title'))</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link href="{{ asset('imgs/favicon.png') }}" rel="shortcut icon" type="image/x-icon" />

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('theme/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/fontawesome-free/css/all.min.css') }}">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">

    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">


    <link rel="stylesheet" href="{{ asset('theme/plugins/toastr/toastr.min.css') }}">

    <!-- Tempusdominus Bbootstrap 4 -->
    <link rel="stylesheet"
        href="{{ asset('theme/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
    <!-- iCheck -->
    <link rel="stylesheet" href="{{ asset('theme/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <!-- JQVMap -->
    <link rel="stylesheet" href="{{ asset('theme/plugins/jqvmap/jqvmap.min.css') }}">

    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="{{ asset('theme/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="{{ asset('theme/plugins/daterangepicker/daterangepicker.css') }}">
    <!-- summernote -->
    <link rel="stylesheet" href="{{ asset('theme/plugins/summernote/summernote-bs4.css') }}">
    <!-- Google Font: Source Sans Pro -->

    <!-- fullCalendar -->
    <link rel="stylesheet" href="{{ asset('theme/plugins/fullcalendar/main.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/plugins/fullcalendar-daygrid/main.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/plugins/fullcalendar-timegrid/main.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/plugins/fullcalendar-bootstrap/main.min.css') }}">

    <link rel="stylesheet" href="{{ asset('theme/plugins/hint/hint.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/plugins/sweetalert/sweetalert.min.css') }}">
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('theme/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <link href="{{ asset('theme/crew_anchor/lib/owlcarousel/assets/owl.carousel.min.css') }}" rel="stylesheet">


    @yield('styles')

    @livewireStyles

</head>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed sidebar-collapse">

    <div class="wrapper">

        @include('layouts.partials.navbar')
        @include('layouts.partials.sidebar')

        <div class="content-wrapper">

            @include('layouts.partials.content_header')

            @yield('content')

        </div>

        @include('layouts.partials.footer')

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

    <!-- jQuery Knob Chart -->
    <script src="{{ asset('theme/plugins/jquery-knob/jquery.knob.min.js') }}"></script>
    <!-- daterangepicker -->
    <script src="{{ asset('theme/plugins/moment/moment.min.js') }}"></script>
    <script src="{{ asset('theme/plugins/daterangepicker/daterangepicker.js') }}"></script>
    <!-- Tempusdominus Bootstrap 4 -->
    <script src="{{ asset('theme/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>
    <!-- Summernote -->
    <script src="{{ asset('theme/plugins/summernote/summernote-bs4.min.js') }}"></script>
    <!-- overlayScrollbars -->
    <script src="{{ asset('theme/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
    <script src="{{ asset('theme/plugins/sweetalert/sweetalert.min.js') }}"></script>
    <!-- AdminLTE App -->
    <script src="{{ asset('theme/dist/js/adminlte.js') }}"></script>

    <!-- fullCalendar 2.2.5 -->
    <script src="{{ asset('theme/plugins/moment/moment.min.js') }}"></script>
    <script src="{{ asset('theme/plugins/fullcalendar/main.min.js') }}"></script>
    <script src="{{ asset('theme/plugins/fullcalendar-daygrid/main.min.js') }}"></script>
    <script src="{{ asset('theme/plugins/fullcalendar-timegrid/main.min.js') }}"></script>
    <script src="{{ asset('theme/plugins/fullcalendar-interaction/main.min.js') }}"></script>
    <script src="{{ asset('theme/plugins/fullcalendar-bootstrap/main.min.js') }}"></script>

    <script src="{{ asset('theme/crew_anchor/lib/owlcarousel/owl.carousel.min.js') }}"></script>

    <script src="{{ asset('theme/plugins/select2/js/select2.full.min.js') }}"></script>

    <!-- Toastr -->
    <script src="{{ asset('theme/plugins/toastr/toastr.min.js') }}"></script>

    <script src="{{ asset('js/functions.js') }}"></script>

    <script type="text/javascript">
        $(function() {
            $('[data-toggle="tooltip"]').tooltip()
        })
    </script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if(session('prompt_bcv_rate'))
    <script>
        $(document).ready(function() {
            Swal.fire({
                title: '<strong>Tasa BCV del Día</strong>',
                icon: 'info',
                html: 'Por favor, establezca el valor de la tasa oficial para el día de hoy.',
                input: 'number',
                inputAttributes: {
                    min: 50,
                    max: 5000,
                    step: 0.01,
                    required: true
                },
                inputLabel: 'Monto en Bs.',
                inputPlaceholder: 'Ingresa la tasa (ej: 431.50)',
                showCancelButton: false,
                confirmButtonText: '<i class="fas fa-save"></i> Guardar Tasa',
                confirmButtonColor: '#3085d6',
                allowOutsideClick: false,
                allowEscapeKey: false,
                footer: '<small>La tasa debe estar entre 50.00 y 5000.00</small>',
                preConfirm: (value) => {
                    if (!value || value < 50 || value > 5000) {
                        Swal.showValidationMessage('El valor debe estar entre 50.00 y 5000.00');
                        return false;
                    }
                    return value;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Procesando...',
                        didOpen: () => {
                            Swal.showLoading()
                        },
                        allowOutsideClick: false
                    });

                    $.ajax({
                        url: '{{ route("tasa-bcv.store") }}',
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            rate: result.value.toString().replace(',', '.')
                        },
                        success: function(response) {
                            console.log('Tasa guardada:', response);
                            Swal.fire({
                                title: '¡Genial!',
                                text: response.message,
                                icon: 'success'
                            });
                        },
                        error: function(xhr) {
                            console.error('Error al guardar tasa:', xhr);
                            let msg = 'No se pudo guardar la tasa.';
                            if (xhr.status === 422) {
                                msg = 'Error de validación: El valor debe estar entre 50 y 5000.';
                                if (xhr.responseJSON && xhr.responseJSON.errors) {
                                    msg += '\nDetalles: ' + JSON.stringify(xhr.responseJSON.errors);
                                }
                            } else if (xhr.status === 419) {
                                msg = 'La sesión ha expirado (CSRF). Por favor, recarga la página.';
                            } else if (xhr.status === 500) {
                                msg = 'Error interno del servidor (500). Consulte los logs de Laravel.';
                            } else {
                                msg = 'Error ' + xhr.status + ': ' + (xhr.responseJSON ? xhr.responseJSON.message : xhr.statusText);
                            }
                            
                            // Alert de depuración para el usuario si es necesario
                            // alert(msg); 
                            
                            Swal.fire('Error', msg, 'error');
                        }
                    });
                }
            });
        });
    </script>
    @endif

    <script>
        function promptUpdateTasa() {
            Swal.fire({
                title: '<strong>Actualizar Tasa BCV</strong>',
                icon: 'info',
                html: 'Ingrese el nuevo valor de la tasa para hoy.',
                input: 'number',
                inputAttributes: {
                    min: 50,
                    max: 5000,
                    step: 0.01,
                    required: true
                },
                inputLabel: 'Monto en Bs.',
                inputPlaceholder: 'Ingresa la tasa (ej: 431.50)',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-save"></i> Guardar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#3085d6',
                preConfirm: (value) => {
                    if (!value || value < 50 || value > 5000) {
                        Swal.showValidationMessage('El valor debe estar entre 50 y 5000');
                        return false;
                    }
                    return value;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Procesando...',
                        didOpen: () => { Swal.showLoading() },
                        allowOutsideClick: false
                    });

                    $.ajax({
                        url: '{{ route("tasa-bcv.store") }}',
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            rate: result.value.toString().replace(',', '.')
                        },
                        success: function(response) {
                            Swal.fire('¡Éxito!', response.message, 'success').then(() => {
                                window.location.reload();
                            });
                        },
                        error: function(xhr) {
                            let msg = 'No se pudo actualizar la tasa.';
                            if (xhr.status === 422) {
                                msg = 'Valor inválido. Debe estar entre 50 y 5000.';
                            }
                            Swal.fire('Error', msg, 'error');
                        }
                    });
                }
            });
        }
    </script>
    @yield('scripts')

    @livewireScripts

</body>

</html>
