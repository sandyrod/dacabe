@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Departamento')

@section('titulo_header', 'Modificar Departamento')
@section('subtitulo_header', 'Departamento')


@section('content')
	<div class="container-fluid">
 		
 		@include('layouts.partials.errors')

	{!! Form::open(['route' =>  ['dpto.update', $dpto->CDPTO], 'method' => 'PUT', 'id' => 'form']) !!}
		<input type="hidden" name="CDPTO" id="CDPTO" value="{{$dpto->CDPTO}}">
		<div class="card card-primary card-outline">
			
			<div class="card-body">
				@include('dpto.form')
			</div>

			@include('dpto.footer')			
		</div>
	{!! Form::close() !!}

	</div>

@endsection


@section('js')
	<script>
		$(document).ready(function(){
			
			$('.btn').on('click', function() {
			    var $this = $(this);
			 	$this.button('loading');
			    setTimeout(function() {
			       $this.button('reset');
			   }, 8000);
			});

		}); 
	</script>
	
@endsection
