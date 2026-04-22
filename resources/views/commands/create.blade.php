@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Comandos')

@section('titulo_header', 'Crear Comando')
@section('subtitulo_header', 'Comandos')


@section('content')
	<div class="container-fluid">
 		@include('layouts.partials.errors')

	{!! Form::open(['route' =>  ['commands.store'], 'method' => 'POST', 'id' => 'form']) !!}
		
		<div class="card card-primary card-outline">
			
			<div class="card-body">
				@include('commands.form')
			</div>

			@include('commands.footer')
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

			set_active_sidebar_menu  ('Web', 'Comandos');

		}); 
	</script>
	
@endsection
