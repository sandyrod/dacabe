@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Gasto')

@section('titulo_header', 'Crear Gasto')
@section('subtitulo_header', 'Gasto')


@section('content')
	<div class="container-fluid">
 		@include('layouts.partials.errors')

	{!! Form::open(['route' =>  ['expenses.store'], 'method' => 'POST', 'id' => 'form']) !!}
		
		<div class="card card-primary card-outline">
			
			<div class="card-body">
				@include('expenses.form')
			</div>

			@include('expenses.footer')
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
