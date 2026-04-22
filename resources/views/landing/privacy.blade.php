@extends('landing.layouts.app')

@section('titulo', trans('site.head.title') )

@section('subtitulo', trans('site.head.subtitle'))

@section('css')
@endsection

@section('content')

  
        <!-- Start Feautes -->
        <section class="Feautes section mt-5">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="section-title">
                            <h2>Políticas de privacidad</h2>
                            
                            <p>En nuestra página web, valoramos tu privacidad y nos comprometemos a proteger tus datos personales.

Recopilamos información únicamente para mejorar tu experiencia y no compartimos tus datos con terceros sin tu consentimiento.

Utilizamos cookies para personalizar contenido y analizar el tráfico, pero nunca recopilamos información sensible sin tu permiso.

Si tienes alguna pregunta sobre nuestra política de privacidad, no dudes en contactarnos.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--/ End Feautes -->
        
        <!-- Start Fun-facts -->
        

@endsection


@section('js')
    <script>
        let url = window.location.href;
        let menu = ['nosotros', 'servicios', 'calendario', 'contactanos'];
        

    </script>
@endsection

@section('script')

@endsection
