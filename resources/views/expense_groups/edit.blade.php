@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Grupos de Gastos')

@section('titulo_header', 'Modificar Grupos de Gastos')
@section('subtitulo_header', 'Grupos de Gastos')


@section('content')
	<div class="container-fluid">
 		
 		@include('layouts.partials.errors')

	{!! Form::open(['route' =>  ['expense-groups.update', $expense_group->id], 'method' => 'PUT', 'id' => 'form']) !!}
		<input type="hidden" name="expense_group_id" id="expense_group_id" value="{{$expense_group->id}}">
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
