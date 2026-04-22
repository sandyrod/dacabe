@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Producto Futuro')

@section('titulo_header', 'Modificar Producto Futuro')
@section('subtitulo_header', 'Producto Futuro')


@section('content')
	<div class="container-fluid">
 		
 		@include('layouts.partials.errors')

	{!! Form::open(['route' =>  ['productos-futuros.update', $producto_futuro->codigo], 'method' => 'PUT', 'files' => true, 'id' => 'form']) !!}
		<input type="hidden" name="codigo" id="codigo" value="{{$producto_futuro->codigo}}">
		<div class="card card-primary card-outline">
			
			<div class="card-body">
				@include('productos_futuros.form')
			</div>

			@include('productos_futuros.footer')			
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

		}); 
	</script>
	
@endsection
