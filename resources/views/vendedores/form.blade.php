<div class="row">
	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('name', 'Nombre', ['class' => 'control-label']) }}
			{!! Form::text('name', @$vendedor->user->name, ['class' => 'form-control ', 'placeholder' => 'Nombre', 'required', 'id' => 'name']) !!}
		</div>
	</div>

	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('last_name', 'Apellido', ['class' => 'control-label']) }}
			{!! Form::text('last_name', @$vendedor->user->last_name, ['class' => 'form-control ', 'placeholder' => 'Apellido', 'id' => 'last_name']) !!}
		</div>
	</div>	
</div>

<div class="row">
	<div class="col-md-4">
		<div class="form-group">
			{{ Form::label('email', 'Email', ['class' => 'control-label']) }}
			{!! Form::text('email', @$vendedor->email, ['class' => 'form-control ', 'placeholder' => 'Email', 'required', 'id' => 'email']) !!}
		</div>
	</div>	

	<div class="col-md-2">
		<div class="form-group">
			{{ Form::label('document', 'Rif/Cédula', ['class' => 'control-label']) }}
			{!! Form::text('document', @$vendedor->user->document, ['class' => 'form-control ', 'placeholder' => 'Rif o Cédula', 'id' => 'document']) !!}
		</div>
	</div>

	<div class="col-md-2">
		<div class="form-group">
			{{ Form::label('telefono', 'Teléfono', ['class' => 'control-label']) }}
			{!! Form::text('telefono', @$vendedor->telefono, ['class' => 'form-control ', 'placeholder' => 'Teléfono', 'id' => 'telefono']) !!}
		</div>
	</div>	
	<div class="col-md-2">
		<div class="form-group">
			{{ Form::label('codigo', 'Código', ['class' => 'control-label']) }}
			{!! Form::text('codigo', @$vendedor->codigo, ['class' => 'form-control ', 'placeholder' => 'Código', 'id' => 'codigo']) !!}
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group text-success">
			{{ Form::label('recargo', '% Recargo al precio', ['class' => 'control-label']) }}
			{!! Form::number('recargo', @$vendedor->recargo, ['class' => 'form-control', 'id' => 'recargo', 'step' => '0.01', 'min' => '0']) !!}
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('zona_id', 'Zona', ['class' => 'control-label']) }}
			<select class="form-control select2" data-placeholder="Seleccione" style="width: 100%;" id="zona_id" name="zona_id">
				@foreach(@$zonas as $item)
					@if($item->id == @$vendedor->zona_id)
						<option selected="selected" value="{{$item->id}}">{{$item->nombre}}</option>
					@else
						<option value="{{$item->id}}">{{$item->nombre}}</option>
					@endif
                @endforeach
            </select>
		</div>
	</div>

	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('CDEPOS', 'Depósito', ['class' => 'control-label']) }}
			<select class="form-control select2" data-placeholder="Seleccione" style="width: 100%;" id="CDEPOS" multiple="multiple"  name="depositos[]">
            	@foreach(@$depositos as $item)
					@if(@$vendedor->id && $vendedor->depositos->where('CDEPOS', $item->CDEPOS)->first())
						<option selected="selected" value="{{$item->CDEPOS}}">{{$item->DDEPOS}}</option>
					@else
						<option value="{{$item->CDEPOS}}">{{$item->DDEPOS}}</option>
					@endif
                @endforeach
            </select>            
		</div>
	</div>	
</div>

<div class="row">
	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('password', 'Contraseña', ['class' => 'control-label']) }}
			{!! Form::password('password', ['class' => 'form-control ', 'placeholder' => 'Contraseña', 'id' => 'password']) !!}
		</div>
	</div>
	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('password_confirmation', 'Confirmar contraseña', ['class' => 'control-label']) }}
			{!! Form::password('password_confirmation', ['class' => 'form-control ', 'placeholder' => 'Confirmar Contraseña', 'id' => 'password_confirmation']) !!}
		</div>
	</div>
</div>
