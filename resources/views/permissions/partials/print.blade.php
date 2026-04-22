@extends('layouts.partials.print')

@section('titulo', $report_data['title'])

@section('content')

  <div class="row">
    <div class="col-12 table-responsive">
      <table class="table table-striped">
        <thead>
        <tr>
          <th>Permiso</th>
          <th>Nombre</th>
          <th>Descripción</th>
        </tr>
        </thead>
        <tbody>
        
        @foreach($permissions as $permission)
            <tr>
              <td>{{$permission->name}}</td>
              <td>{{$permission->display_name}}</td>
              <td>{{$permission->description}}</td>
            </tr>
        @endforeach
        
        </tbody>
      </table>
    </div>
  </div>      
        
@endsection