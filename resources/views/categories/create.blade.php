@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Categorias')

@section('titulo_header', 'Crear Empresa')
@section('subtitulo_header', 'Categorias')


@section('content')
	<div class="container-fluid">
 		@include('layouts.partials.errors')

	{!! Form::open(['route' =>  ['categories.store'], 'method' => 'POST', 'id' => 'form']) !!}
		
		<div class="card card-primary card-outline">
			
			<div class="card-body">
				@include('categories.form')
			</div>

			@include('categories.footer')
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

			set_active_sidebar_menu  ('Web', 'Categorias');

		}); 
	</script>
	
@endsection
