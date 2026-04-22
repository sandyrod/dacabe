@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Gastos')

@section('titulo_header', 'Modificar Gastos')
@section('subtitulo_header', 'Gastos')


@section('content')
	<div class="container-fluid">
 		
 		@include('layouts.partials.errors')

	{!! Form::open(['route' =>  ['expenses.update', $expense->id], 'method' => 'PUT', 'id' => 'form']) !!}
		<input type="hidden" name="expense_id" id="expense_id" value="{{$expense->id}}">
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
