@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Módulos')

@section('titulo_header', 'Crear Módulo')
@section('subtitulo_header', 'Módulos')


@section('content')
	<div class="container-fluid">
 		@include('layouts.partials.errors')

	{!! Form::open(['route' =>  ['modules.store'], 'method' => 'POST', 'id' => 'form']) !!}
		
		<div class="card card-primary card-outline">
			
			<div class="card-body">
				@include('modules.form')
			</div>

			@include('modules.footer')
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

			set_active_sidebar_menu  ('Configuración', 'Módulos');

		}); 
	</script>
	
@endsection
