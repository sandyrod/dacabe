@extends('layouts.partials.print')

@section('titulo', $report_data['title'])

@section('content')

  <div class="row">
    <div class="col-12 table-responsive">
      <table class="table table-striped">
        <thead>
        <tr>
          <th>Nombre</th>
          <th>Slug</th>
          <th>Plantilla</th>
        </tr>
        </thead>
        <tbody>
        
        @foreach(@$themes as $theme)
            <tr>
              <td>{{$theme->name}}</td>
              <td>{{$theme->slug}}</td>
              <td>{{$theme->template}}</td>
            </tr>
        @endforeach
        
        </tbody>
      </table>
    </div>
  </div>      
        
@endsection
