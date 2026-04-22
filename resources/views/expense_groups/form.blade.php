<div class="row">
	<div class="col-md-8">
		<div class="form-group">
			{{ Form::label('name', 'Nombre', ['class' => 'control-label']) }}
			{!! Form::text('name', @$expense_group->name, ['class' => 'form-control ', 'placeholder' => 'Nombre' ,'required', 'id' => 'name']) !!}
		</div>
	</div>
	
	<div class="col-md-4">
		<div class="form-group">
			{{ Form::label('show_order', 'Orden', ['class' => 'control-label']) }}
			{!! Form::number('show_order', @$expense_group->show_order, ['class' => 'form-control ', 'placeholder' => 'Orden para mostrar', 'id' => 'show_order']) !!}
		</div>
	</div>
</div>
