@extends('layouts.partials.print')

@section('titulo', $report_data['title'])

@section('content')

  <div class="row">
    <div class="col-12 table-responsive">
      <table class="table table-striped">
        <thead>
        <tr>
          <th>Nombre</th>
          <th>Descripción</th>
        </tr>
        </thead>
        <tbody>
        
        @foreach($company_status as $company)
            <tr>
              <td>{{$company->name}}</td>
              <td>{{$company->description}}</td>
            </tr>
        @endforeach
        
        </tbody>
      </table>
    </div>
  </div>      
        
@endsection
