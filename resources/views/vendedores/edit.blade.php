@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Vendedor')

@section('titulo_header', 'Modificar Vendedor')
@section('subtitulo_header', 'Vendedor')

@section('content')
	<div class="container-fluid">
 		
 		@include('layouts.partials.errors')

	{!! Form::open(['route' =>  ['vendedores.update', $vendedor->id], 'method' => 'PUT', 'id' => 'form']) !!}
		<input type="hidden" name="vendedor_id" id="vendedor_id" value="{{$vendedor->id}}">
		<input type="hidden" name="user_id" id="user_id" value="{{$vendedor->user->id}}">
		<div class="card card-primary card-outline">
			
			<div class="card-body">
				@include('vendedores.form')
			</div>

			@include('vendedores.footer')			
		</div>
	{!! Form::close() !!}

	</div>

@endsection


@section('scripts')
	<script>
		$(document).ready(function(){
			set_select2();
			
			$('.btn').on('click', function() {
			    var $this = $(this);
			 	$this.button('loading');
			    setTimeout(function() {
			       $this.button('reset');
			   }, 8000);
			});

		}); 
	</script>
	
@endsection
