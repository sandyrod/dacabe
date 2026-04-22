@extends('landing.layouts.app')

@section('titulo', trans('site.head.title') )

@section('subtitulo', trans('site.head.subtitle'))

@section('css') @endsection


@section('slider-header')
    <!--
    <div class="about-header">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center">
                    <h2 class="heading-text"> Subscribe </h2>
                </div>
            </div>
        </div>
    </div>
    -->
    <div class="header-menu-area">
        <!-- header-menu-area -->
        <div class="container">
            <div class="row header-menu">
                <div class="col-md-6 col-sm-6 col-xs-12 header-menu-left">
                    <ul class="list-inline">
                        <li ><a href="{{url('/')}}">Home ></a></li>
                        <li ><a href="{{url('/')}}">contactos ></a></li>
                        <li><span class="active">subscribe</span></li>
                    </ul>
                    <!-- .menu -->
                </div>
                <!-- col-md-6 -->
                <div class="col-md-6 col-sm-6 col-xs-12 text-right  header-social-icon">
                    <ul class="list-inline">
                        <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                        <li><a href="#"><i class="fa fa-twitter"></i></a></li>
                        <li><a href="#"><i class="fa fa-google-plus"></i></a></li>
                        <li><a href="#"><i class="fa fa-instagram"></i></a></li>
                    </ul>
                </div>
            </div>
            <!-- row -->
        </div>
        <!-- container -->
    </div>
    <!-- header-menu-area close -->
@endsection


@section('content')

    <section id="contactanos" class="dark-bg padding-large-top-bottom">
        <div class="contact-form-area ">
            <div class="container">
                <div class="row">
                    <div class="col-md-3 col-sm-3"></div>

                    <div class="col-md-6 col-sm-6">
                        <div class="welcome-text">
                            <h2>Formulario de Suscripción</h2>
                        </div>

                        <form method="POST" action="http://adcenter.dev/api/subscribe" accept-charset="UTF-8" id="form">
                            <input type="hidden" name="key" id="key" value="eyJpdiI6IlpvYW84VEkxZmtET25mbWpKakthdHc9PSIsInZhbHVlIjoiYU8zazdDNzhlRHpxbXJCa2pDRWJPdz09IiwibWFjIjoiZTRhMzMxZTg2OGM2ZWJlMjFlZjMyZDFlMzdhZTU5ZDY4MDI4YTA4NzcwZTY4MWVjYWFhMmI4ZTU4ODAxNGY1NSJ9">
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="text" name="nombre" id="nombre" placeholder="Nombre *" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" name="apellido" id="apellido" placeholder="Apellido *" class="form-control" required>
                                </div>
                            </div>

                            <input type="text" name="email" id="email" placeholder="Email*" class="form-control" required>

                            <div class="row cheakbox-button">
                                <div class="col-md-8">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" value="" checked>
                                            <span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
                                            Recibir consejos de marketing gratis
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="submit-button">
                                        <i class="fa fa-long-arrow-right"></i>
                                        <input type="submit" value="Suscribirme" class="btn btn-primary" >
                                    </div><!-- submit-button -->
                                </div>
                            </div>
                        </form>

                    </div>

                    <div class="col-md-3 col-sm-3"></div>
                </div>
            </div>
        </div>
    </section>

@endsection


@section('js')
    <script>
        $("body").find("input[type=submit]").on('click',function(e){
            e.preventDefault();
            //$("input[type='submit']").prop("disabled",true);
            $.ajax({
                url: $('#form').attr('action'),
                type: 'POST',
                data: $('#form').serialize(),
                dataType: 'json',
                success: function (check) {
                    setTimeout(function () {
                        swal({title: check.title, text: check.text, type: check.type, html: true});
                        //$('#form').each (function() { this.reset(); });
                        $("input[type='submit']").prop("disabled",false);
                    }, 100);
                },
                error: function (xhr, status) {
                    console.log('error en submit: '); console.log(xhr); console.log(status);
                    var errors = "";
                    $.each($.parseJSON(xhr.responseText), function (ind, elem) {
                        errors = errors + elem + "<br>";
                    });
                    swal("Oops!", "Ocurrió un error al intentar procesar la petición: <br><br>"+errors, "error");
                    $("input[type='submit']").prop("disabled",false);
                }
            });
        });
    </script>
@endsection
