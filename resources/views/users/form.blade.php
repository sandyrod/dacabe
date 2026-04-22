<div class="row">
	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('name', 'Nombre', ['class' => 'control-label']) }}
			{!! Form::text('name', @$user->name, ['class' => 'form-control ', 'placeholder' => 'Nombre del usuario' ,'required', 'id' => 'name']) !!}
		</div>
	</div>
	
	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('last_name', 'Apellido', ['class' => 'control-label']) }}
			{!! Form::text('last_name', @$user->last_name, ['class' => 'form-control ', 'placeholder' => 'Apellido', 'id' => 'last_name']) !!}
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-4">
		<div class="form-group">
			{{ Form::label('document', 'Cédula', ['class' => 'control-label']) }}
			{!! Form::text('document', @$user->document, ['class' => 'form-control ', 'placeholder' => 'Cédula o Código', 'id' => 'document']) !!}
		</div>
	</div>
	<div class="col-md-4">
		<div class="form-group">
			{{ Form::label('phone', 'Teléfono', ['class' => 'control-label']) }}
			{!! Form::text('phone', @$user->phone, ['class' => 'form-control ', 'placeholder' => 'Teléfono' , 'id' => 'phone']) !!}
		</div>
	</div>
	<div class="col-md-4">
		<div class="form-group">
			{{ Form::label('email', 'Correo Electrónico', ['class' => 'control-label']) }}
			@if ( isset($profile))
				{!! Form::email('email', @$user->email, ['class' => 'form-control ', 'placeholder' => 'Correo Electrónico' ,'required', 'id' => 'email']) !!}
			@else
				{!! Form::email('email', @$user->email, ['class' => 'form-control ', 'placeholder' => 'Correo Electrónico' ,'required', 'id' => 'email']) !!}
			@endif
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

@php ($dashboards = [
		['id'=>'general', 'name'=>'General'],
		['id'=>'gerencial', 'name'=>'Gerencial'],
		['id'=>'farmax', 'name'=>'Farmax'],
		['id'=>'precios', 'name'=>'Precios'],
		['id'=>'pedidos', 'name'=>'Pedidos'],
		['id'=>'gastos', 'name'=>'Gastos'],
		['id'=>'modulos', 'name'=>'Módulos'],
	])

@if (@$companies && (auth()->user()->isAdministrativerUser() || auth()->user()->isAdministrator()))
<div class="row">
		<div class="col-md-6">
			<div class="form-group">
				{{ Form::label('company_id', 'Empresa', ['class' => 'control-label']) }}
				<select class="form-control select2" data-placeholder="Seleccione" style="width: 100%;" id="company_id" name="company_id">
					@foreach($companies as $item)
						<option value="{{$item->id}}">{{$item->name}}</option>
	                @endforeach
	            </select>
			</div>
		</div>

	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('dashboard', 'Dashboard', ['class' => 'control-label']) }}
			<select class="form-control select2" data-placeholder="Seleccione" style="width: 100%;" id="dashboard" name="dashboard">
					@foreach($dashboards as $item)
						@php($selected = '')
						@if($item['id'] == @$user->dashboard)
							@php($selected = 'selected="selected"')
						@endif
						<option {{$selected}} value="{{$item['id']}}">{{$item['name']}}</option>
	                @endforeach
	            </select>
		</div>
	</div>
</div>

@endif


