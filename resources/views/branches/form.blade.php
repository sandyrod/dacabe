<div class="row">
	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('name', 'Nombre', ['class' => 'control-label']) }}
			{!! Form::text('name', @$branch->name, ['class' => 'form-control ', 'placeholder' => 'Nombre' ,'required', 'id' => 'name']) !!}
		</div>
	</div>
	
	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('address', 'Dirección', ['class' => 'control-label']) }}
			{!! Form::text('address', @$branch->address, ['class' => 'form-control ', 'placeholder' => 'Dirección', 'id' => 'address']) !!}
		</div>
	</div>
</div>
