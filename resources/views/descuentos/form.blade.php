<div class="row">
	<div class="col-md-4">
		<div class="form-group">
			{{ Form::label('nombre', 'Nombre', ['class' => 'control-label']) }}
			{!! Form::text('nombre', @$descuento->nombre, ['class' => 'form-control ', 'placeholder' => 'Nombre', 'required', 'id' => 'nombre']) !!}
		</div>
	</div>

	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('porcentaje', '% Descuento', ['class' => 'control-label']) }}
			{!! Form::number('porcentaje', @$descuento->porcentaje, ['class' => 'form-control ', 'placeholder' => '% Descuento', 'required', 'id' => 'porcentaje', 'step' => '0.01']) !!}
		</div>
	</div>	
	<div class="col-md-2">
		<div class="form-group">
			{{ Form::label('dias_despacho', 'Días Despacho', ['class' => 'control-label']) }}
			{!! Form::number('dias_despacho', @$descuento->dias_despacho, ['class' => 'form-control ', 'placeholder' => 'Días Despacho', 'required', 'id' => 'dias_despacho']) !!}
		</div>
	</div>
</div>
