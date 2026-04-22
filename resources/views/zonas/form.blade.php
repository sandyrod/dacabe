<div class="row">
	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('nombre', 'Nombre', ['class' => 'control-label']) }}
			{!! Form::text('nombre', @$zona->nombre, ['class' => 'form-control ', 'placeholder' => 'Nombre', 'required', 'id' => 'nombre']) !!}
		</div>
	</div>

	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('descripcion', 'Descripción', ['class' => 'control-label']) }}
			{!! Form::text('descripcion', @$zona->descripcion, ['class' => 'form-control ', 'placeholder' => 'Descripción', 'id' => 'descripcion']) !!}
		</div>
	</div>	
</div>
