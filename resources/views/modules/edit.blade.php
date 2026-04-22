@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Módulos')

@section('titulo_header', 'Modificar Módulo')
@section('subtitulo_header', 'Módulos')


@section('content')
	<div class="container-fluid">
 		
 		@include('layouts.partials.errors')

	{!! Form::open(['route' =>  ['modules.update', $module->id], 'method' => 'PUT', 'id' => 'form']) !!}
		<input type="hidden" name="module_id" id="module_id" value="{{$module->id}}">
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
