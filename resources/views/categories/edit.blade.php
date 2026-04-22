@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Categorias')

@section('titulo_header', 'Modificar Empresa')
@section('subtitulo_header', 'Categorias')


@section('content')
	<div class="container-fluid">
 		
 		@include('layouts.partials.errors')

	{!! Form::open(['route' =>  ['categories.update', $category->id], 'method' => 'PUT', 'id' => 'form']) !!}
		<input type="hidden" name="category_id" id="category_id" value="{{$category->id}}">
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
