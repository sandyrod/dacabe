@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Grupos de Gasto')

@section('titulo_header', 'Crear Grupos de Gasto')
@section('subtitulo_header', 'Grupos de Gasto')


@section('content')
	<div class="container-fluid">
 		@include('layouts.partials.errors')

	{!! Form::open(['route' =>  ['expense-groups.store'], 'method' => 'POST', 'id' => 'form']) !!}
		
		<div class="card card-primary card-outline">
			
			<div class="card-body">
				@include('expense_groups.form')
			</div>

			@include('expense_groups.footer')
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
