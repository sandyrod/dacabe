<div class="row">
	<div class="col-md-4">
		<div class="form-group">
			{{ Form::label('codigo', 'Código', ['class' => 'control-label']) }}
			{!! Form::text('codigo', @$producto_futuro->codigo, ['class' => 'form-control ', 'placeholder' => 'Ejemplo: 001', 'required', 'id' => 'codigo']) !!}
		</div>
	</div>

	<div class="col-md-8">
		<div class="form-group">
			{{ Form::label('descripcion', 'Descripción', ['class' => 'control-label']) }}
			{!! Form::text('descripcion', @$producto_futuro->descripcion, ['class' => 'form-control ', 'placeholder' => 'Descripción', 'required', 'id' => 'descripcion']) !!}
		</div>
	</div> 	
</div> 

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            {{ Form::label('foto', 'Foto', ['class' => 'control-label']) }}
            <div class="col-md-12 col-sm-12 col-xs-12 hint--top" aria-label="Click para cambiar la imagen">
                {{ Form::file('foto', ['class' => 'btn btn-outline-primary btn-block ']) }}
            </div>
        </div>
    </div>

    <div class="col-md-2">
        <img src="{{ asset('storage/products/' . @$producto_futuro->foto)}}" width="200" alt="Logo">
    </div>
    
    
</div>