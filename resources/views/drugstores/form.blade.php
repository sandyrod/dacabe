<div class="row">
	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('name', 'Nombre', ['class' => 'control-label']) }}
			{!! Form::text('name', @$drugstore->name, ['class' => 'form-control ', 'placeholder' => 'Nombre' ,'required', 'id' => 'name']) !!}
		</div>
	</div>
	
	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('url', 'URL', ['class' => 'control-label']) }}
			{!! Form::text('url', @$drugstore->url, ['class' => 'form-control ', 'placeholder' => '200.35.81.85' ,'required', 'id' => 'url']) !!}
		</div>
	</div>
</div>
