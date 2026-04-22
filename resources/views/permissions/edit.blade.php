@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Opciones')

@section('titulo_header', 'Modificar Opción')
@section('subtitulo_header', 'Opciones')

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

	{!! Form::open(['route' =>  ['permissions.update', $permission->id], 'method' => 'PUT', 'id' => 'form']) !!}
		<input type="hidden" name="permission_id" id="permission_id" value="{{$permission->id}}">
		<div class="card card-primary card-outline">
			
			<div class="card-body">
				@include('permissions.form')
			</div>

			@include('permissions.footer')			
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

			set_active_sidebar_menu  ('Seguridad', 'Opciones');

		}); 
	</script>
	
@endsection
