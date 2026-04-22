@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Comando')

@section('titulo_header', 'Modificar Comando')
@section('subtitulo_header', 'Comando')


@section('content')
	<div class="container-fluid">
 		
 		@include('layouts.partials.errors')

	{!! Form::open(['route' =>  ['commands.update', $command->id], 'method' => 'PUT', 'id' => 'form']) !!}
		<input type="hidden" name="command_id" id="command_id" value="{{$command->id}}">
		<div class="card card-primary card-outline">
			
			<div class="card-body">
				@include('commands.form')
			</div>

			@include('commands.footer')			
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

			set_active_sidebar_menu  ('Web', 'Comando');

		}); 
	</script>
	
@endsection
