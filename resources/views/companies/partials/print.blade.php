@extends('layouts.partials.print')

@section('titulo', $report_data['title'])

@section('content')

  <div class="row">
    <div class="col-12 table-responsive">
      <table class="table table-striped">
        <thead>
        <tr>
          <th>RIF</th>
          <th>Nombre</th>
          <th>Email</th>
          <th>Teléfono</th>
        </tr>
        </thead>
        <tbody>
        
        @foreach($companies as $company)
            <tr>
              <td>{{$company->code}}</td>
              <td>{{$company->name}}</td>
              <td>{{$company->email}}</td>
              <td>{{$company->phone}}</td>
            </tr>
        @endforeach
        
        </tbody>
      </table>
    </div>
  </div>      
        
@endsection