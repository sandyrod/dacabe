<div class="row">
	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('name', 'Nombre', ['class' => 'control-label']) }}
			{!! Form::text('name', @$theme->name, ['class' => 'form-control ', 'placeholder' => 'Nombre del Tema' ,'required', 'id' => 'name']) !!}
		</div>
	</div>
	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('template', 'Nombre de Plantilla', ['class' => 'control-label']) }}
			{!! Form::text('template', @$theme->template, ['class' => 'form-control ', 'placeholder' => 'Nombre de la Plantilla de landing' ,'required', 'id' => 'template']) !!}
		</div>
	</div>
</div>
