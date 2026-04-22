@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Usuarios')

@section('titulo_header', 'Crear Usuario')
@section('subtitulo_header', 'Usuarios')

@section('css')
	<link rel="stylesheet" href="{{ asset('theme/plugins/select2/select2.min.css') }}">
	<style>
		label {
			width: 100%!important;
			text-align: left;
		}
	</style>
@endsection


@section('content')
	<div class="container-fluid">
 		@include('layouts.partials.errors')

	{!! Form::open(['route' =>  ['users.store'], 'method' => 'POST', 'id' => 'form']) !!}
		
		<div class="card card-primary card-outline">
			
			<div class="card-body">
				@include('users.form')
			</div>

			@include('users.footer')
		</div>
	{!! Form::close() !!}

	</div>

@endsection


@section('js')
	<script src="{{ asset('theme/plugins/select2/select2.full.min.js') }}"></script>
	<script src="{{ asset('theme/plugins/select2/i18n/es.js') }}"></script>
	<script src="{!! asset('theme/plugins/inputmask/jquery.inputmask.bundle.min.js')!!}"></script>
	<script>
		$(document).ready(function(){
			$(".select2").select2({language:"es", disabled:true});
			
			$('.btn').on('click', function() {
			    var $this = $(this);
			 	$this.button('loading');
			    setTimeout(function() {
			       $this.button('reset');
			   }, 8000);
			});

			set_active_sidebar_menu  ('Seguridad', 'Usuarios');

		}); 
	</script>
	
@endsection
