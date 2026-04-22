@extends('layouts.partials.print')

@section('titulo', $report_data['title'])

@section('content')

  <div class="row">
    <div class="col-12 table-responsive">
      <table class="table table-striped">
        <thead>
        <tr>
          <th>Código</th>
          <th>Descripción</th>
        </tr>
        </thead>
        <tbody>
        
        @foreach($dptos as $dpto)
            <tr>
              <td>{{$dpto->CDPTO}}</td>
              <td>{{$dpto->DDPTO}}</td>
            </tr>
        @endforeach
        
        </tbody>
      </table>
    </div>
  </div>      
        
@endsection
