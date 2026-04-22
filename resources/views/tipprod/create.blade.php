@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Tipo de Producto')

@section('titulo_header', 'Crear Tipo de Producto')
@section('subtitulo_header', 'Tipo de Producto')


@section('content')
	<div class="container-fluid">
 		@include('layouts.partials.errors')

	{!! Form::open(['route' =>  ['tipprod.store'], 'method' => 'POST', 'id' => 'form']) !!}
		
		<div class="card card-primary card-outline">
			
			<div class="card-body">
				@include('tipprod.form')
			</div>

			@include('tipprod.footer')
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
