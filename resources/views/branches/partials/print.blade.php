@extends('layouts.partials.print')

@section('titulo', $report_data['title'])

@section('content')

  <div class="row">
    <div class="col-12 table-responsive">
      <table class="table table-striped">
        <thead>
        <tr>
          <th>Nombre</th>
          <th>Dirección</th>
        </tr>
        </thead>
        <tbody>
        
        @foreach($branches as $item)
            <tr>
              <td>{{$item->name}}</td>
              <td>{{$item->address}}</td>
            </tr>
        @endforeach
        
        </tbody>
      </table>
    </div>
  </div>      
        
@endsection
