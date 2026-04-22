@extends('layouts.partials.print')

@section('titulo', $report_data['title'])

@section('content')

  <div class="row">
    <div class="col-12 table-responsive">
      <table class="table table-striped">
        <thead>
        <tr>
          <th>Comando</th>
          <th>Respuesta</th>
        </tr>
        </thead>
        <tbody>
        
        @foreach($commands as $command)
            <tr>
              <td>{{$command->command}}</td>
              <td>{{$command->command_response}}</td>
            </tr>
        @endforeach
        
        </tbody>
      </table>
    </div>
  </div>      
        
@endsection
