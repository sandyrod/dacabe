@extends('layouts.partials.print')

@section('titulo', $report_data['title'])

@section('content')

  <div class="row">
    <div class="col-12 table-responsive">
      <table class="table table-striped">
        <thead>
        <tr>
          <th>Nombre</th>
          <th>Url</th>
        </tr>
        </thead>
        <tbody>
        
        @foreach($drugstores as $drugstore)
            <tr>
              <td>{{$drugstore->name}}</td>
              <td>{{$drugstore->url}}</td>
            </tr>
        @endforeach
        
        </tbody>
      </table>
    </div>
  </div>      
        
@endsection
