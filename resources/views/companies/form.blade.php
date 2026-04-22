<div class="row">
	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('name', 'Nombre', ['class' => 'control-label']) }}
			{!! Form::text('name', @$company->name, ['class' => 'form-control ', 'placeholder' => 'Nombre de la empresa' ,'required', 'id' => 'name']) !!}
		</div>
	</div>
	
	<div class="col-md-3">
		<div class="form-group">
			{{ Form::label('code', 'RIF', ['class' => 'control-label']) }}
			{!! Form::text('code', @$company->code, ['class' => 'form-control ', 'placeholder' => 'RIF', 'required', 'id' => 'code']) !!}
		</div>
	</div>
	<div class="col-md-3">
		<div class="form-group">
			{{ Form::label('phone', 'Teléfono', ['class' => 'control-label']) }}
			{!! Form::text('phone', @$company->phone, ['class' => 'form-control ', 'placeholder' => 'Teléfono' , 'id' => 'phone']) !!}
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-4">
		<div class="form-group">
			{{ Form::label('email', 'Correo Electrónico', ['class' => 'control-label']) }}
			{!! Form::email('email', @$company->email, ['class' => 'form-control ', 'placeholder' => 'Correo Electrónico' , 'id' => 'email']) !!}
		</div>
	</div>
	
	<div class="col-md-8">
		<div class="form-group">
			{{ Form::label('location', 'Dirección', ['class' => 'control-label']) }}
			{!! Form::text('location', @$company->location, ['class' => 'form-control ', 'placeholder' => 'Dirección', 'id' => 'location']) !!}
		</div>
	</div>
</div>


<hr>

<div class="row">
	<div class="col-md-4">
		<div class="form-group">
			{{ Form::label('db_name', 'Nombre Base de Datos', ['class' => 'control-label']) }}
			{!! Form::text('db_name', @$company->db_name, ['class' => 'form-control ', 'placeholder' => 'Nombre Base de Datos', 'id' => 'db_name']) !!}
		</div>
	</div>
	<div class="col-md-8 col-sm-12 col-xs-12 hint--top" aria-label="Click para cambiar Logo">
        {{ Form::file('file', ['class' => 'btn btn-outline-primary btn-block ']) }}
    </div>
</div>
