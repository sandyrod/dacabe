@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Descuentos')

@section('titulo_header', 'Crear Descuentos')
@section('subtitulo_header', 'Descuentos')


@section('content')
	<div class="container-fluid">
 		@include('layouts.partials.errors')

	{!! Form::open(['route' =>  ['descuentos.store'], 'method' => 'POST', 'id' => 'form']) !!}
		
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
