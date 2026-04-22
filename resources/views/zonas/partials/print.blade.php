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
        
        @foreach($zonas as $zona)
            <tr>
              <td>{{$zona->nombre}}</td>
              <td>{{$zona->descripcion}}</td>
            </tr>
        @endforeach
        
        </tbody>
      </table>
    </div>
  </div>      
        
@endsection
