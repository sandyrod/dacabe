@extends('layouts.partials.print')

@section('titulo', $report_data['title'])

@section('content')

  <div class="row">
    <div class="col-12 table-responsive">
      <table class="table table-striped">
        <thead>
        <tr>
          <th>Nombre</th>
          <th>Email</th>
          <th>Zona</th>
          <th>Deposito</th>
        </tr>
        </thead>
        <tbody>
        
        @foreach($vendedores as $item)
            <tr>
              <td>{{$item->user->name}} {{$item->user->last_name}}</td>
              <td>{{$item->user->email}}</td>
              <td>{{@$item->zona->nombre}}</td>
              <td>{{@$item->deposito->DDEPOS}}</td>
            </tr>
        @endforeach
        
        </tbody>
      </table>
    </div>
  </div>      
        
@endsection
