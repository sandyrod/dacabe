@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Descuentos')

@section('titulo_header', 'Modificar Descuentos')
@section('subtitulo_header', 'Descuentos')


@section('content')
	<div class="container-fluid">
 		
 		@include('layouts.partials.errors')

	{!! Form::open(['route' =>  ['descuentos.update', $descuento->id], 'method' => 'PUT', 'id' => 'form']) !!}
		<input type="hidden" name="id" id="id" value="{{$descuento->id}}">
		<div class="card card-primary card-outline">
			
			<div class="card-body">
				@include('descuentos.form')
			</div>

			@include('descuentos.footer')			
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
