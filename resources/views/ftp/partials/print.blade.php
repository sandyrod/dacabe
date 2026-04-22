@extends('layouts.partials.print')

@section('titulo', $report_data['title'])

@section('content')

  <div class="row">
    <div class="col-12 table-responsive">
      <table class="table table-striped">
        <thead>
        <tr>
          <th>Servidor</th>
          <th>Usuario</th>
          <th>Dir. Remoto</th>
          <th>Dir. Local</th>
        </tr>
        </thead>
        <tbody>
        
        @foreach($ftps as $ftp)
            <tr>
              <td>{{$ftp->server}}</td>
              <td>{{$ftp->username}}</td>
              <td>{{$ftp->remote_dir}}</td>
              <td>{{$ftp->local_dir}}</td>
            </tr>
        @endforeach
        
        </tbody>
      </table>
    </div>
  </div>      
        
@endsection
