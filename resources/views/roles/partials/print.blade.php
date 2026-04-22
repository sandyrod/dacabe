@extends('layouts.partials.print')

@section('titulo', $report_data['title'])

@section('content')

  <div class="row">
    <div class="col-12 table-responsive">
      <table class="table table-striped">
        <thead>
        <tr>
          <th>Rol</th>
          <th>Nombre</th>
          <th>Descripción</th>
        </tr>
        </thead>
        <tbody>
        
        @foreach($roles as $role)
            <tr>
              <td>{{$role->name}}</td>
              <td>{{$role->display_name}}</td>
              <td>{{$role->description}}</td>
            </tr>
        @endforeach
        
        </tbody>
      </table>
    </div>
  </div>      
        
@endsection