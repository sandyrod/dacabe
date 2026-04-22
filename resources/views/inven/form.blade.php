<div class="row">
	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('name', 'Nombre', ['class' => 'control-label']) }}
			{!! Form::text('name', @$company->name, ['class' => 'form-control ', 'placeholder' => 'Nombre de la empresa' ,'required', 'id' => 'name']) !!}
		</div>
	</div>
	
	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('code', 'RIF', ['class' => 'control-label']) }}
			{!! Form::text('code', @$company->code, ['class' => 'form-control ', 'placeholder' => 'RIF', 'required', 'id' => 'code']) !!}
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('email', 'Correo Electrónico', ['class' => 'control-label']) }}
			{!! Form::email('email', @$company->email, ['class' => 'form-control ', 'placeholder' => 'Correo Electrónico' , 'id' => 'email']) !!}
		</div>
	</div>
	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('phone', 'Teléfono', ['class' => 'control-label']) }}
			{!! Form::text('phone', @$company->phone, ['class' => 'form-control ', 'placeholder' => 'Teléfono' , 'id' => 'phone']) !!}
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<div class="form-group">
			{{ Form::label('location', 'Dirección', ['class' => 'control-label']) }}
			{!! Form::text('location', @$company->location, ['class' => 'form-control ', 'placeholder' => 'Dirección', 'id' => 'location']) !!}
		</div>
	</div>
</div>




