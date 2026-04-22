@extends('landing.layouts.app')

@section('titulo', trans('site.head.title') )

@section('subtitulo', trans('site.head.subtitle'))

@section('css') @endsection


@section('slider-header')
    <div class="about-header">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center">
                    <h2 class="heading-text"> más información </h2>
                </div>
            </div>
        </div>
    </div>
    <div class="header-menu-area">
        <!-- header-menu-area -->
        <div class="container">
            <div class="row header-menu">
                <div class="col-md-6 col-sm-6 col-xs-12 header-menu-left">
                    <ul class="list-inline">
                        <li ><a href="{{url('/')}}">Home ></a></li>
                        <li ><a href="{{url('/')}}">contactos ></a></li>
                        <li><span class="active"> más información </span></li>
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

    <section class="padding-large-top-bottom"><!-- resource-single -->
        <div class="resource-single">
            <div class="container">


                <div class="welcome-text text-center wow fadeInUp" data-wow-duration="0.5s" data-wow-delay=".45s">
                    <h2>Ejemplo de Captura </h2>
                    <p>
                        <!--
                        La suscripción de <b>{{$email or 'contacto'}}</b>, ha sido eliminada satisfactoriamente de nuestros registros.
                        por lo que <b>no</b> debe seguir recibiendo nuestros boletines. si por alguna razón esto no llegara a cumplirse,
                        por favor no dude en contactarnos. -->
                    </p>
                </div>



                <!-- welcome-text -->
                <div class="row resource-slider-section  text-left">
                    <div class="col-md-6 col-sm-12">
                        <div class="resource-slide wow fadeInLeft" data-wow-duration="0.5s" data-wow-delay=".25s">
                            <div class="slide-item">
                                <img src="{{asset('asset/landing/img/resource/resource-slide.png')}}" alt="Slider Image">
                            </div>
                            <div class="slide-item">
                                <img src="{{asset('asset/landing/img/resource/resource-tools.png')}}" alt="Slider Image">
                            </div>
                            <div class="slide-item">
                                <img src="{{asset('asset/landing/img/resource/resource-slide.png')}}" alt="Slider Image">
                            </div>
                            <div class="slide-item">
                                <img src="{{asset('asset/landing/img/resource/resource-tools.png')}}" alt="Slider Image">
                            </div>
                        </div>
                    </div>
                    <!-- col-md-6 -->
                    <div class="col-md-6 col-sm-12">
                        <div class="slide-details wow fadeInRight" data-wow-duration="0.5s" data-wow-delay=".25s">
                            <h3>Mejoras digitales impresionante.</h3>
                            <p>Recuerde que nuestros objetivos ha sido siempre brindar de apoyo tecnológico en el uso de las herramientas de marketing digital, por lo que cumplimos con: </p>
                            <ul>
                                <li><i class="fa fa-check-square-o"></i>  Identificar las necesidades de su público objetivo </li>
                                <li><i class="fa fa-check-square-o"></i>  Construir una página de destino para el formulario de suscripción </li>
                                <li><i class="fa fa-check-square-o"></i>  Proceso de suscripción fácil </li>
                                <li><i class="fa fa-check-square-o"></i>  Segmentación y temas de interés </li>
                            </ul>
                        </div>
                    </div>
                    <!-- col-md-6 -->
                </div>
            </div>
            <!-- container close-->
        </div>
        <!-- section close-->
    </section>
    <!-- section close-->

@endsection


@section('js')

@endsection

@section('script')

@endsection