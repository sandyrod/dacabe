<div class="row">
	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('command', 'Comando', ['class' => 'control-label']) }}
			{!! Form::text('command', @$command->command, ['class' => 'form-control ', 'placeholder' => 'Nombre del comando' ,'required', 'id' => 'command']) !!}
		</div>
	</div>
	
	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('command_response', 'Respuesta', ['class' => 'control-label']) }}
			{!! Form::text('command_response', @$command->command_response, ['class' => 'form-control ', 'placeholder' => 'Respuesta del comando' ,'required', 'id' => 'command_response']) !!}
		</div>
	</div>
</div>
