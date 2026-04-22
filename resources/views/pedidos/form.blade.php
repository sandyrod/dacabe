<div class="row">
	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('CDPTO', 'Código', ['class' => 'control-label']) }}
			{!! Form::text('CDPTO', @$dpto->CDPTO, ['class' => 'form-control ', 'placeholder' => 'Ejemplo: 001', 'required', 'id' => 'CDPTO']) !!}
		</div>
	</div>

	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('DDPTO', 'Descripción', ['class' => 'control-label']) }}
			{!! Form::text('DDPTO', @$dpto->DDPTO, ['class' => 'form-control ', 'placeholder' => 'Descripción', 'required', 'id' => 'DDPTO']) !!}
		</div>
	</div>	
</div>
