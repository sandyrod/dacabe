<div class="row">
	<div class="col-md-4">
		<div class="form-group">
			{{ Form::label('company_id', 'Farmacia', ['class' => 'control-label']) }}
			<select class="form-control select2" data-placeholder="Seleccione" style="width: 100%;" id="company_id" name="company_id">
				@foreach($companies as $item)
					<option value="{{$item->id}}">{{$item->name}}</option>
                @endforeach
            </select>
		</div>
	</div>
	
	<div class="col-md-4">
		<div class="form-group">
			{{ Form::label('drugstore_id', 'Drogueria', ['class' => 'control-label']) }}
			<select class="form-control select2" data-placeholder="Seleccione" style="width: 100%;" id="drugstore_id" name="drugstore_id">
				@foreach($drugstores as $item)
					<option value="{{$item->id}}">{{$item->name}}</option>
                @endforeach
            </select>
		</div>
	</div>

	<div class="col-md-4">
		<div class="form-group">
			{{ Form::label('server', 'Servidor', ['class' => 'control-label']) }}
			{!! Form::text('server', @$ftp->server, ['class' => 'form-control ', 'placeholder' => '200.35.81.85' ,'required', 'id' => 'server']) !!}
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-3">
		<div class="form-group">
			{{ Form::label('username', 'Usuario Ftp', ['class' => 'control-label']) }}
			{!! Form::text('username', @$ftp->username, ['class' => 'form-control ', 'placeholder' => 'Nombre' ,'required', 'id' => 'username']) !!}
		</div>
	</div>
	
	<div class="col-md-3">
		<div class="form-group">
			{{ Form::label('password', 'Clave Ftp', ['class' => 'control-label']) }}
			{!! Form::text('password', @$ftp->password, ['class' => 'form-control ', 'placeholder' => 'Clave Ftp' ,'required', 'id' => 'password']) !!}
		</div>
	</div>

	<div class="col-md-3">
		<div class="form-group">
			{{ Form::label('remote_dir', 'Dir. Remoto', ['class' => 'control-label']) }}
			{!! Form::text('remote_dir', @$ftp->remote_dir, ['class' => 'form-control ', 'placeholder' => 'Clientes/n130/factura/' ,'required', 'id' => 'remote_dir']) !!}
		</div>
	</div>

	<div class="col-md-3">
		<div class="form-group">
			{{ Form::label('local_dir', 'Dir. Local', ['class' => 'control-label']) }}
			{!! Form::text('local_dir', @$ftp->local_dir, ['class' => 'form-control ', 'placeholder' => 'redoma' ,'required', 'id' => 'local_dir']) !!}
		</div>
	</div>
</div>
