<div class="row">
	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('CTIPPROD', 'Código', ['class' => 'control-label']) }}
			{!! Form::text('CTIPPROD', @$tipprod->CTIPPROD, ['class' => 'form-control ', 'placeholder' => 'Ejemplo: 001', 'required', 'id' => 'CTIPPROD']) !!}
		</div>
	</div>

	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('DTIPPROD', 'Descripción', ['class' => 'control-label']) }}
			{!! Form::text('DTIPPROD', @$tipprod->DTIPPROD, ['class' => 'form-control ', 'placeholder' => 'Descripción', 'required', 'id' => 'DTIPPROD']) !!}
		</div>
	</div>	
</div>
