@extends('layouts.partials.print')

@section('titulo', $report_data['title'])

@section('content')

  <div class="row">
    <div class="col-12 table-responsive">
      <table class="table table-striped">
        <thead>
        <tr>
          <th>Cédula</th>
          <th>Nombres</th>
          <th>Apellidos</th>
          <th>Email</th>
          <th>Teléfono</th>
        </tr>
        </thead>
        <tbody>
        
        @foreach($users as $user)
            <tr>
              <td>{{$user->code}}</td>
              <td>{{$user->name}}</td>
              <td>{{$user->last_name}}</td>
              <td>{{$user->email}}</td>
              <td>{{$user->phone}}</td>
            </tr>
        @endforeach
        
        </tbody>
      </table>
    </div>
  </div>      
        
@endsection