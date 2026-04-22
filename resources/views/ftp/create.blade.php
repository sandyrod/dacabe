@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Farmacias')

@section('titulo_header', 'Crear Farmacias')
@section('subtitulo_header', 'Farmacias')


@section('content')
	<div class="container-fluid">
 		@include('layouts.partials.errors')

	{!! Form::open(['route' =>  ['ftp.store'], 'method' => 'POST', 'id' => 'form']) !!}
		
		<div class="card card-primary card-outline">
			
			<div class="card-body">
				@include('ftp.form')
			</div>

			@include('ftp.footer')
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

			set_active_sidebar_menu  ('Farmax', 'Conf. farmacias');

		}); 
	</script>
	
@endsection
