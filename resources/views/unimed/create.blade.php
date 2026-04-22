@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Unidad de Medida')

@section('titulo_header', 'Crear Unidad de Medida')
@section('subtitulo_header', 'Unidad de Medida')


@section('content')
	<div class="container-fluid">
 		@include('layouts.partials.errors')

	{!! Form::open(['route' =>  ['unimed.store'], 'method' => 'POST', 'id' => 'form']) !!}
		
		<div class="card card-primary card-outline">
			
			<div class="card-body">
				@include('unimed.form')
			</div>

			@include('unimed.footer')
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
