<div class="row">
	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('name', 'Nombre', ['class' => 'control-label']) }}
			{!! Form::text('name', @$company_status->name, ['class' => 'form-control ', 'placeholder' => 'Nombre del Status' ,'required', 'id' => 'name']) !!}
		</div>
	</div>
	
	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('description', 'Descripción', ['class' => 'control-label']) }}
			{!! Form::text('description', @$company_status->description, ['class' => 'form-control ', 'placeholder' => 'Descripción', 'id' => 'description']) !!}
		</div>
	</div>
</div>
