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
          <th>Precio 1</th>
          <th>Precio 2</th>
        </tr>
        </thead>
        <tbody>
        
        @foreach($order_invens as $item)
            <tr>
              <td>{{$item->CODIGO}}</td>
              <td>{{$item->DESCR}}</td>
              <td>{{truncateToTwoDecimals($item->BASE1)}}</td>
              <td>{{truncateToTwoDecimals($item->BASE2)}}</td>
            </tr>
        @endforeach
        
        </tbody>
      </table>
    </div>
  </div>      
        
@endsection
