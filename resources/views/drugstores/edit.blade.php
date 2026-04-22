@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Drogueria')

@section('titulo_header', 'Modificar Drogueria')
@section('subtitulo_header', 'Drogueria')


@section('content')
	<div class="container-fluid">
 		
 		@include('layouts.partials.errors')

	{!! Form::open(['route' =>  ['drugstores.update', $drugstore->id], 'method' => 'PUT', 'id' => 'form']) !!}
		<input type="hidden" name="drugstore_id" id="drugstore_id" value="{{$drugstore->id}}">
		<div class="card card-primary card-outline">
			
			<div class="card-body">
				@include('drugstores.form')
			</div>

			@include('drugstores.footer')			
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

			set_active_sidebar_menu  ('Farmax', 'Droguerias');

		}); 
	</script>
	
@endsection
