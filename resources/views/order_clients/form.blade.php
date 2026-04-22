<div class="row">
	<div class="col-md-8">
		<div class="form-group">
			{{ Form::label('name', 'Nombre/Razón Social', ['class' => 'control-label']) }}
			{!! Form::text('name', @$order_client->name, ['class' => 'form-control ', 'placeholder' => 'Nombre', 'required', 'id' => 'name']) !!}
		</div>
	</div>	
	
	<div class="col-md-2">
		<div class="form-group">
			{{ Form::label('rif', 'RIF', ['class' => 'control-label']) }}
			{!! Form::text('rif', @$order_client->rif, ['class' => 'form-control ', 'placeholder' => 'RIF', 'required', 'id' => 'rif']) !!}
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group">
			{{ Form::label('seller_code', 'Cód. Vend.', ['class' => 'control-label']) }}
			{!! Form::text('seller_code', @$order_client->seller_code, ['class' => 'form-control ', 'placeholder' => 'Código Vendedor', 'id' => 'seller_code']) !!}
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-8">
		<div class="form-group">
			{{ Form::label('address', 'Dirección', ['class' => 'control-label']) }}
			{!! Form::text('address', @$order_client->address, ['class' => 'form-control ', 'placeholder' => 'Direccción', 'required', 'id' => 'address']) !!}
		</div>
	</div>	
	<div class="col-md-2">
		<div class="form-group">
			{{ Form::label('phone', 'Teléfono', ['class' => 'control-label']) }}
			{!! Form::text('phone', @$order_client->phone, ['class' => 'form-control ', 'placeholder' => 'Teléfono', 'id' => 'phone']) !!}
		</div>
	</div>	
	
	<div class="col-md-2">
		<div class="form-group">
			{{ Form::label('cellphone', 'Celular', ['class' => 'control-label']) }}
			{!! Form::text('cellphone', @$order_client->cellphone, ['class' => 'form-control ', 'placeholder' => 'Celular', 'id' => 'cellphone']) !!}
		</div>
	</div>
</div>
