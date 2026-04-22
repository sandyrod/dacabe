@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Descuento Global')

@section('titulo_header', 'Modificar Descuento Global')
@section('subtitulo_header', 'Descuento Global')


@section('content')
	<div class="container-fluid">
 		
 		@if (@$info)
		    <div class="row">
		        <div class="col-md-12">
		            <div class="alert alert-success alert-dismissible">
		                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button>
		                {{ $info }}
		            </div>
		        </div>
		    </div>
		@endif

	{!! Form::open(['route' =>  ['descuento-global.update'], 'method' => 'POST', 'files' => true, 'id' => 'form']) !!}
		<div class="card card-primary card-outline">
			
			<div class="card-body">
				
				<div class="row">
					<div class="col-md-4">
						<div class="form-group">
							{{ Form::label('ref1', 'Ref 1', ['class' => 'control-label']) }}
							{!! Form::text('ref1', @$descuento_global->ref1, ['class' => 'form-control ', 'id' => 'ref1']) !!}
						</div>
					</div>

					<div class="col-md-4">
						<div class="form-group">
							{{ Form::label('ref2', 'Ref 2', ['class' => 'control-label']) }}
							{!! Form::text('ref2', @$descuento_global->ref2, ['class' => 'form-control ', 'id' => 'ref2']) !!}
						</div>
					</div>

					<div class="col-md-4">
						<div class="form-group">
							{{ Form::label('show_precio1', 'Mostrar Precio 1', ['class' => 'control-label']) }}
							<select class="form-control select2" data-placeholder="Seleccione" style="width: 100%;" id="show_precio1" name="show_precio1">
								@if($descuento_global->show_precio1 && $descuento_global->show_precio1=='SI')
									<option selected="selected" value="SI">SI</option>
									<option value="NO">NO</option>
								@else
									<option value="SI">SI</option>
									<option selected="selected" value="NO">NO</option>
								@endif
				            </select>
						</div>
					</div>
				</div>

			</div>

			<div class="card-footer">
				<div class="form-group">
					<div class="row">
						<div class="col-md-4 col-sm-12 col-xs-12">
						</div>
						<div class="mt-2 col-md-2 col-sm-6 col-xs-6">
							<a href="{{route('inicio')}}" class="btn btn-outline-danger btn-block">
								<i class="fas fa-chevron-left"></i>  Regresar
							</a>
						</div>
						<div class="mt-2 col-md-2 col-sm-6 col-xs-6">
							<button type="submit" class="btn btn-primary btn-block" 
								data-form="form" data-loading-text="Guardando...">
								<i class="fas fa-save"></i>  Guardar 
							</button>
						</div>
					</div>
				</div>
			</div>
		
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
