@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Roles')

@section('titulo_header', 'Modificar Rol')
@section('subtitulo_header', 'Roles')

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

	{!! Form::open(['route' =>  ['roles.update', $role->id], 'method' => 'PUT', 'id' => 'form']) !!}
		<input type="hidden" name="role_id" id="role_id" value="{{$role->id}}">
		<div class="card card-primary card-outline">
			
			<div class="card-body">
				@include('roles.form')
			</div>

			@include('roles.footer')			
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
