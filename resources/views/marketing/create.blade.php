@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Marketing')

@section('titulo_header', 'Crear Marketing')
@section('subtitulo_header', 'Marketing')


@section('content')
	<div class="container-fluid">
 		@include('layouts.partials.errors')

	{!! Form::open(['route' =>  ['marketing.store'], 'method' => 'POST', 'files' => true, 'id' => 'form']) !!}
		
		<div class="card card-primary card-outline">
			
			<div class="card-body">
				@include('marketing.form')
			</div>

			@include('marketing.footer')
		</div>
	{!! Form::close() !!}

	</div>

@endsection


@section('scripts')
	<script>
		$(document).ready(function() {
			$('.select2').select2({
		        placeholder: "Seleccione",
		        allowClear: true
		    });
			
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
