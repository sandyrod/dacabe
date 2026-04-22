<div class="row">
	<div class="col-md-2">
		<div class="form-group">
			{{ Form::label('branch_id', 'Grupo de Gasto', ['class' => 'control-label']) }}
			<select class="form-control select2" data-placeholder="Seleccione" style="width: 100%;" id="branch_id" name="branch_id">
				@foreach($branches as $item)
					@if($item->id == @$expense->branch_id)
						<option selected="selected" value="{{$item->id}}">{{$item->name}}</option>
					@else
						<option value="{{$item->id}}">{{$item->name}}</option>
					@endif
                @endforeach
            </select>
		</div>
	</div>
	<div class="col-md-4">
		<div class="form-group">
			{{ Form::label('name', 'Nombre', ['class' => 'control-label']) }}
			{!! Form::text('name', @$expense->name, ['class' => 'form-control ', 'placeholder' => 'Nombre' ,'required', 'id' => 'name']) !!}
		</div>
	</div>
	
	<div class="col-md-4">
		<div class="form-group">
			{{ Form::label('description', 'Descripción', ['class' => 'control-label']) }}
			{!! Form::text('description', @$expense->description, ['class' => 'form-control ', 'placeholder' => 'Descripción del gasto', 'id' => 'description']) !!}
		</div>
	</div>

	<div class="col-md-2">
		<div class="form-group">
			{{ Form::label('reference', 'Nro Referencia', ['class' => 'control-label']) }}
			{!! Form::text('reference', @$expense->reference, ['class' => 'form-control ', 'placeholder' => 'Nro Referencia', 'id' => 'reference']) !!}
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-3">
		<div class="form-group">
			{{ Form::label('expense_group_id', 'Grupo de Gasto', ['class' => 'control-label']) }}
			<select class="form-control select2" data-placeholder="Seleccione" style="width: 100%;" id="expense_group_id" name="expense_group_id">
				@foreach($expense_groups as $item)
					@if($item->id == @$expense->expense_group_id)
						<option selected="selected" value="{{$item->id}}">{{$item->name}}</option>
					@else
						<option value="{{$item->id}}">{{$item->name}}</option>
					@endif
                @endforeach
            </select>
		</div>
	</div>
	
	<div class="col-md-3">
		<div class="form-group">
			{{ Form::label('date_at', 'Fecha', ['class' => 'control-label']) }}
			{!! Form::date('date_at', @$expense->date_at, ['class' => 'form-control ', 'placeholder' => 'Fecha del gasto', 'id' => 'date_at']) !!}
		</div>
	</div>

	<div class="col-md-2">
		<div class="form-group">
			{{ Form::label('amount', 'Total Bs.', ['class' => 'control-label']) }}
			{!! Form::text('amount', @$expense->amount, ['class' => 'form-control ', 'placeholder' => 'Monto Bs', 'id' => 'amount']) !!}
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group">
			{{ Form::label('dollar_amount', 'Total Divisa', ['class' => 'control-label']) }}
			{!! Form::text('dollar_amount', @$expense->dollar_amount, ['class' => 'form-control ', 'placeholder' => 'Monto $', 'id' => 'dollar_amount']) !!}
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group">
			{{ Form::label('rate', 'Tasa', ['class' => 'control-label']) }}
			{!! Form::text('rate', @$expense->rate, ['class' => 'form-control ', 'placeholder' => 'Tasa del dia', 'id' => 'rate']) !!}
		</div>
	</div>
</div>
