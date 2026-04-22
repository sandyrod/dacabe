@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Status')

@section('titulo_header', 'Crear Status')
@section('subtitulo_header', 'Status')


@section('content')
	<div class="container-fluid">
 		@include('layouts.partials.errors')

	{!! Form::open(['route' =>  ['company-status.store'], 'method' => 'POST', 'id' => 'form']) !!}
		
		<div class="card card-primary card-outline">
			
			<div class="card-body">
				@include('company_status.form')
			</div>

			@include('company_status.footer')
		</div>
	{!! Form::close() !!}

	</div>

@endsection


@section('js')
	<script src="{!! asset('theme/plugins/inputmask/jquery.inputmask.bundle.min.js')!!}"></script>
	<script>
		$(document).ready(function(){
			
			$('.btn').on('click', function() {
			    var $this = $(this);
			 	$this.button('loading');
			    setTimeout(function() {
			       $this.button('reset');
			   }, 8000);
			});

			set_active_sidebar_menu  ('Seguridad', 'Empresas');

		}); 
	</script>
	
@endsection
