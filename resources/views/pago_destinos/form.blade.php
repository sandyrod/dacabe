<div class="row">
	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('nombre', 'Nombre', ['class' => 'control-label']) }}
			{!! Form::text('nombre', @$pago_destino->nombre, ['class' => 'form-control ', 'placeholder' => 'Nombre', 'required', 'id' => 'nombre']) !!}
		</div>
	</div>

	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('descripcion', 'Descripción', ['class' => 'control-label']) }}
			{!! Form::text('descripcion', @$pago_destino->descripcion, ['class' => 'form-control ', 'placeholder' => 'Descripción', 'id' => 'descripcion']) !!}
		</div>
	</div>	
</div>
