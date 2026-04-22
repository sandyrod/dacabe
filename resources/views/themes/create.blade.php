@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Temas')

@section('titulo_header', 'Crear Temas')
@section('subtitulo_header', 'Temas')


@section('content')
	<div class="container-fluid">
 		@include('layouts.partials.errors')

	{!! Form::open(['route' =>  ['themes.store'], 'method' => 'POST', 'id' => 'form']) !!}
		
		<div class="card card-primary card-outline">
			
			<div class="card-body">
				@include('themes.form')
			</div>

			@include('themes.footer')
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

			set_active_sidebar_menu  ('Landings', 'Gestión');
		}); 
	</script>
	
@endsection
