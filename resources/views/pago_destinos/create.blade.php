@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Tipos de pago recibidos')

@section('titulo_header', 'Crear Tipos de pago recibidos')
@section('subtitulo_header', 'Tipos de pago recibidos')


@section('content')
	<div class="container-fluid">
 		@include('layouts.partials.errors')

	{!! Form::open(['route' =>  ['pago_destinos.store'], 'method' => 'POST', 'id' => 'form']) !!}
		
		<div class="card card-primary card-outline">
			
			<div class="card-body">
				@include('pago_destinos.form')
			</div>

			@include('pago_destinos.footer')
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
