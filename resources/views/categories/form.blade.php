<div class="row">
	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('description', 'Categoria', ['class' => 'control-label']) }}
			{!! Form::text('description', @$category->description, ['class' => 'form-control ', 'placeholder' => 'Nombre de la categoria' ,'required', 'id' => 'description']) !!}
		</div>
	</div>
	
	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('icon', 'Icono', ['class' => 'control-label']) }}
			{!! Form::text('icon', @$category->icon, ['class' => 'form-control ', 'placeholder' => 'Ejemplo: fas fa-user', 'id' => 'icon']) !!}
		</div>
	</div>
</div>
