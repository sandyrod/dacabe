@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Sucursal')

@section('titulo_header', 'Modificar Sucursal')
@section('subtitulo_header', 'Sucursal')


@section('content')
	<div class="container-fluid">
 		
 		@include('layouts.partials.errors')

	{!! Form::open(['route' =>  ['branches.update', $branch->id], 'method' => 'PUT', 'id' => 'form']) !!}
		<input type="hidden" name="branch_id" id="branch_id" value="{{$branch->id}}">
		<div class="card card-primary card-outline">
			
			<div class="card-body">
				@include('branches.form')
			</div>

			@include('branches.footer')			
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
