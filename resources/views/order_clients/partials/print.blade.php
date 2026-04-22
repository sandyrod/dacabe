@extends('layouts.partials.print')

@section('titulo', $report_data['title'])

@section('content')

  <div class="row">
    <div class="col-12 table-responsive">
      <table class="table table-striped">
        <thead>
        <tr>
          <th width="12%">RIF</th>
          <th>Nombres</th>
          <th>Tlf.</th>
          <th>Email</th>
        </tr>
        </thead>
        <tbody>
        
        @foreach($order_clients as $order_client)
            <tr>
              <td>{{$order_client->RIF}}</td>
              <td>{{$order_client->NOMBRE}}</td>
              <td>{{$order_client->TELEFONO}}</td>
              <td>{{$order_client->EMAIL}}</td>
            </tr>
        @endforeach
        
        </tbody>
      </table>
    </div>
  </div>      
        
@endsection
