@extends('layouts.partials.print')

@section('titulo', $report_data['title'])

@section('content')

  <div class="row">
    <div class="col-12 table-responsive">
      <table class="table table-striped">
        <thead>
        <tr>
          <th>Fecha</th>
          <th>Empresa</th>
          <th>Usuario</th>
          <th>Cantidad</th>
        </tr>
        </thead>
        <tbody>
        
        @foreach($serials as $serial)
            <tr>
              <td>{{$serial->created_at}}</td>
              <td>{{$serial->company()->name}}</td>
              <td>{{$serial->user()->name}} {{$serial->user()->last_name}}</td>
              <td>{{$serial->quantity}}</td>
            </tr>
        @endforeach
        
        </tbody>
      </table>
    </div>
  </div>      
        
@endsection
