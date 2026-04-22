@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Cliente')

@section('titulo_header', 'Modificar Cliente')
@section('subtitulo_header', 'Cliente')


@section('content')
	<div class="container-fluid">
 		
 		@include('layouts.partials.errors')

	{!! Form::open(['route' =>  ['order-clients.update', $order_client->id], 'method' => 'PUT', 'id' => 'form']) !!}
		<input type="hidden" name="order_client_id" id="order_client_id" value="{{$order_client->id}}">
		<div class="card card-primary card-outline">
			
			<div class="card-body">
				@include('order_clients.form')
			</div>

			@include('order_clients.footer')			
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
