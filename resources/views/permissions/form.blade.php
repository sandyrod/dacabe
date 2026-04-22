<div class="row">
	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('name', 'Opción', ['class' => 'control-label']) }}
			{!! Form::text('name', @$permission->name, ['class' => 'form-control ', 'placeholder' => 'Opción' ,'required', 'id' => 'name']) !!}
		</div>
	</div>
	
	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('display_name', 'Nombre', ['class' => 'control-label']) }}
			{!! Form::text('display_name', @$permission->display_name, ['class' => 'form-control ', 'placeholder' => 'Nombre', 'required', 'id' => 'display_name']) !!}
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<div class="form-group">
			{{ Form::label('description', 'Descripción - Módulo', ['class' => 'control-label']) }}
			{!! Form::text('description', @$permission->description, ['class' => 'form-control ', 'placeholder' => 'Descripción', 'id' => 'description']) !!}
		</div>
	</div>
</div>

