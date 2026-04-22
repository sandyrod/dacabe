@extends('layouts.partials.print')

@section('titulo', $report_data['title'])

@section('content')

  <div class="row">
    <div class="col-12 table-responsive">
      <table class="table table-striped">
        <thead>
        <tr>
          <th>Nombre</th>
          <th>Descripción</th>
        </tr>
        </thead>
        <tbody>
        
        @foreach($pago_destinos as $pago_destino)
            <tr>
              <td>{{$pago_destino->nombre}}</td>
              <td>{{$pago_destino->descripcion}}</td>
            </tr>
        @endforeach
        
        </tbody>
      </table>
    </div>
  </div>      
        
@endsection
