@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Landing')

@section('titulo_header', 'Crear Landing')
@section('subtitulo_header', 'Landing')


@section('content')
	<div class="container-fluid">
 		@include('layouts.partials.errors')

	{!! Form::open(['route' =>  ['landings.store'], 'method' => 'POST', 'files' => true, 'id' => 'form']) !!}
		
		<div class="card card-primary card-outline">
			
			<div class="card-body">
				@include('landings.form')
			</div>

			@include('landings.footer')
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
