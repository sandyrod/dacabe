@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Producto')

@section('titulo_header', 'Modificar Producto')
@section('subtitulo_header', 'Producto')


@section('content')
	<div class="container-fluid">
 		
 		@include('layouts.partials.errors')

	{!! Form::open(['route' =>  ['order-inven.update', $order_inven->CODIGO], 'method' => 'PUT', 'files' => true, 'id' => 'form']) !!}
		<input type="hidden" name="CODIGO" id="CODIGO" value="{{$order_inven->CODIGO}}">
		<div class="card card-primary card-outline">
			
			<div class="card-body">
				@include('order_inven.form')
			</div>

			@include('order_inven.footer')			
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

			// Logic for discount checkbox
			$('#enable_descuento').on('change', function() {
				if($(this).is(':checked')) {
					$('#descuento').prop('disabled', false);
				} else {
					$('#descuento').prop('disabled', true).val(0);
				}
			});

			// Logic for commission checkbox
			$('#enable_comision').on('change', function() {
				if($(this).is(':checked')) {
					$('#comision').prop('disabled', false);
				} else {
					$('#comision').prop('disabled', true).val(0);
				}
			});
		}); 
	</script>
	
@endsection
