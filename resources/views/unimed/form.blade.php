<div class="row">
	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('CUNIMED', 'Código', ['class' => 'control-label']) }}
			{!! Form::text('CUNIMED', @$unimed->CUNIMED, ['class' => 'form-control ', 'placeholder' => 'Ejemplo: 001', 'required', 'id' => 'CUNIMED']) !!}
		</div>
	</div>

	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('DUNIMED', 'Descripción', ['class' => 'control-label']) }}
			{!! Form::text('DUNIMED', @$unimed->DUNIMED, ['class' => 'form-control ', 'placeholder' => 'Descripción', 'required', 'id' => 'DUNIMED']) !!}
		</div>
	</div>	
</div>
