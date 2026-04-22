@extends('layouts.partials.print')

@section('titulo', $report_data['title'])

@section('content')

  <div class="row">
    <div class="col-12 table-responsive">
      <table class="table table-striped">
        <thead>
        <tr>
          <th>Módulo</th>
          <th>Descripción</th>
        </tr>
        </thead>
        <tbody>
        
        @foreach($modules as $module)
            <tr>
              <td>{{$module->name}}</td>
              <td>{{$module->description}}</td>
            </tr>
        @endforeach
        
        </tbody>
      </table>
    </div>
  </div>      
        
@endsection
