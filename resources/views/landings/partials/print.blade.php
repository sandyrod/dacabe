@extends('layouts.partials.print')

@section('titulo', $report_data['title'])

@section('content')

  <div class="row">
    <div class="col-12 table-responsive">
      <table class="table table-striped">
        <thead>
        <tr>
          <th>Nombre</th>
          <th>Empresa</th>
          <th>Tema</th>
        </tr>
        </thead>
        <tbody>
        
        @foreach(@$landings as $landing)
            <tr>
              <td>{{$landing->name}}</td>
              <td>{{$landing->company_id}}</td>
              <td>{{$landing->theme_id}}</td>
            </tr>
        @endforeach
        
        </tbody>
      </table>
    </div>
  </div>      
        
@endsection
