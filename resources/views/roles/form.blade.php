<div class="row">
	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('name', 'Rol', ['class' => 'control-label']) }}
			{!! Form::text('name', @$role->name, ['class' => 'form-control ', 'placeholder' => 'Rol' ,'required', 'id' => 'name']) !!}
		</div>
	</div>
	
	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('display_name', 'Nombre', ['class' => 'control-label']) }}
			{!! Form::text('display_name', @$role->display_name, ['class' => 'form-control ', 'placeholder' => 'Nombre', 'id' => 'display_name']) !!}
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<div class="form-group">
			{{ Form::label('description', 'Descripción', ['class' => 'control-label']) }}
			{!! Form::text('description', @$role->description, ['class' => 'form-control ', 'placeholder' => 'Descripción', 'id' => 'description']) !!}
		</div>
	</div>
</div>


<div class="row">
	{{ Form::label('permisos', 'Permisos', ['class' => 'control-label']) }}
</div>
<div class="row">
    @foreach ($permissions as $permission)
    	<div class="col-md-3 col-sm-6 col-12">
	      <div class="custom-control custom-checkbox">
	        <input
		          type="checkbox"
		          class="custom-control-input"
		          name="permissions[]"
		          id="chk{{$permission->id}}"
		          value="{{$permission->id}}"
		          {!! $permission->assigned ? 'checked' : '' !!}
		        >
	        <label for="chk{{$permission->id}}" class="custom-control-label">{{$permission->display_name ?? $permission->name}}</label>
	    	</div>
	      </label>
	    </div>
    @endforeach
</div>
