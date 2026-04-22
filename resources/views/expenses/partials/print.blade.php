@extends('layouts.partials.print')

@section('titulo', $report_data['title'])

@section('content')

  <div class="row">
    <div class="col-12 table-responsive">
      <table class="table table-striped">
        <thead>
        <tr>
          <th>Fecha</th>
          <th>Grupo</th>
          <th>Nombre</th>
          <th>Descripción</th>
          <th>Bs.</th>
          <th>Divisa</th>
          <th>Tasa</th>
        </tr>
        </thead>
        <tbody>
        
        @foreach($expenses as $item)
            <tr>
              <td>{{ formatoFechaDMASimple($item->date_at) }}</td>
              <td>{{@$item->expense_group->name}}</td>
              <td>{{$item->name}}</td>
              <td>{{$item->description}}</td>
              <td>{{$item->amount}}</td>
              <td>{{$item->dollar_amount}}</td>
              <td>{{$item->rate}}</td>
            </tr>
        @endforeach
        
        </tbody>
      </table>
    </div>
  </div>      
        
@endsection
