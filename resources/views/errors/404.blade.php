@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - 404 Recurso No Encontrado')

@section('titulo_header', '404')
@section('subtitulo_header', 'Recurso No Encontrado')

@section('content')
   
  <section class="content">
      <div class="error-page">
          <h2 class="headline"> 404</h2>

          <div class="error-content">
             <h3>
                <i class="fas fa-exclamation-triangle text-danger"></i> Página o Recurso No Encontrado
              </h3>

              <p>
                  Podemos ayudarle a encontrar lo que busca.
                o si prefiere <a class="btn btn-primary" href="{{ route('inicio') }}"><i class="fas fa-chevron-left"></i> Regresar al Inicio</a>
              </p>
          
        </div>
      </div>
    </section>

  </div>
  

@endsection
