@extends('layouts.partials.print')

@section('titulo', $report_data['title'])

@section('content')

  <div class="row">
    <div class="col-12 table-responsive">
      <table class="table table-striped">
        <thead>
        <tr>
          <th>Nombre</th>
          <th>Porcentaje</th>
        </tr>
        </thead>
        <tbody>
        
        @foreach($descuentos as $descuento)
            <tr>
              <td>{{$descuento->nombre}}</td>
              <td>{{$descuento->porcentaje}}</td>
            </tr>
        @endforeach
        
        </tbody>
      </table>
    </div>
  </div>      
        
@endsection
