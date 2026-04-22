@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - 403 Acceso Restringido')

@section('titulo_header', '403')
@section('subtitulo_header', 'Acceso Restringido')

@section('content')
   
  <section class="content">
      <div class="error-page">
          <h2 class="headline"> 403</h2>

          <div class="error-content">
             <h3>
                <i class="fas fa-exclamation-triangle text-danger"></i> Acceso Restringido
              </h3>

              <p>
                  Podemos ayudarle a encontrar lo que busca.
                o si prefiere <a class="btn btn-primary" href="{{ route('inicio') }}">Regresar al Inicio</a>
              </p>
          
        </div>
      </div>
    </section>

  </div>
  

@endsection
