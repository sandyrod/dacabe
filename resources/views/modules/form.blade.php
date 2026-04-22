<div class="row">
	<div class="col-md-12">
		<div class="form-group">
			{{ Form::label('name', 'Módulo', ['class' => 'control-label']) }}
			{!! Form::text('name', @$module->name, ['class' => 'form-control ', 'placeholder' => 'Nombre del módulo' ,'required', 'id' => 'name']) !!}
		</div>
	</div>
	
	<div class="col-md-12">
		<div class="form-group">
			{{ Form::label('description', 'Descripción', ['class' => 'control-label']) }}
			{!! Form::text('description', @$module->description, ['class' => 'form-control ', 'placeholder' => 'Descripción' , 'id' => 'description']) !!}
		</div>
	</div>
</div>


<div class="row">
	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('url', 'Url Botón', ['class' => 'control-label']) }}
			{!! Form::text('url', @$module->url, ['class' => 'form-control ', 'placeholder' => 'Url del Botón módulo' , 'id' => 'url']) !!}
		</div>
	</div>
	
	<div class="col-md-3">
		<div class="form-group">
			{{ Form::label('button_text', 'Texto Botón', ['class' => 'control-label']) }}
			{!! Form::text('button_text', @$module->button_text, ['class' => 'form-control ', 'placeholder' => 'Texto para el Botón' , 'id' => 'button_text']) !!}
		</div>
	</div>
	<div class="col-md-3">
		<div class="form-group">
			{{ Form::label('icon', 'Icono Botón', ['class' => 'control-label']) }}
			{!! Form::text('icon', @$module->icon, ['class' => 'form-control ', 'placeholder' => 'fa fa-user' , 'id' => 'icon']) !!}
		</div>
	</div>
</div>
