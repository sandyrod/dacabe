<div class="row">
	<div class="col-md-4">
		<div class="form-group">
			{{ Form::label('CODIGO', 'Código', ['class' => 'control-label']) }}
			{!! Form::text('CODIGO', @$order_inven->CODIGO, ['class' => 'form-control ', 'readonly', 'placeholder' => 'Ejemplo: 001', 'required', 'id' => 'CODIGO']) !!}
		</div>
	</div>

	<div class="col-md-8">
		<div class="form-group">
			{{ Form::label('DESCR', 'Descripción', ['class' => 'control-label']) }}
			{!! Form::text('DESCR', @$order_inven->DESCR, ['class' => 'form-control ', 'readonly', 'placeholder' => 'Descripción', 'required', 'id' => 'DESCR']) !!}
		</div>
	</div>	
</div>

<div class="row">
	<div class="col-md-4">
		<div class="form-group">
			{{ Form::label('SMIN', 'Mínimo para ocultar en pedidos', ['class' => 'control-label']) }}
			{!! Form::number('SMIN', @$order_inven->SMIN, ['class' => 'form-control ', 'placeholder' => '1', 'id' => 'SMIN']) !!}
		</div>
	</div>

	<div class="col-md-4">
		<div class="form-group">
			{{ Form::label('stock_minimo', 'Mínimo para venta', ['class' => 'control-label']) }}
			{!! Form::number('stock_minimo', @$order_inven->informacion->stock_minimo, ['class' => 'form-control ', 'placeholder' => '1', 'id' => 'stock_minimo']) !!}
		</div>
	</div>

	<div class="col-md-4">
		<div class="form-group">
            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
              <input type="checkbox" class="custom-control-input" id="nuevo" name="nuevo"
              	value="1" {{ isset($promocion) && $promocion->nuevo ? 'checked' : '' }}>
              <label class="custom-control-label" for="nuevo">Producto Nuevo</label>
            </div>
          </div>
          <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">                              
		        <input type="checkbox" class="custom-control-input" id="promocion" name="promocion" 
       					value="1" {{ isset($promocion) && $promocion->promocion ? 'checked' : '' }}>

              <label class="custom-control-label" for="promocion">Producto en Promoción</label>
            </div>
        </div>
	</div>	
	<div class="row">
		<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
			{{ Form::label('base1', 'Precio 1', ['class' => 'control-label']) }}
				{!! Form::text('base1', @$order_inven->BASE1, ['class' => 'form-control ', 'id' => 'base1']) !!}
		</div>
		<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
			{{ Form::label('base2', 'Precio 2', ['class' => 'control-label']) }}
			{!! Form::text('base2', @$order_inven->BASE2, ['class' => 'form-control ', 'id' => 'base2']) !!}
		</div>
		<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
			<div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
					<input type="checkbox" class="custom-control-input" id="apliccom" name="apliccom"
						value="1" {{ isset($order_inven) && isset($order_inven->informacion->comision) && $order_inven->informacion->comision>0 ? 'checked' : '' }}>
					<label class="custom-control-label" for="apliccom">Aplica % Comision</label>
				</div>
			{!! Form::number('comision', @$order_inven->informacion->comision ?? 0, ['class' => 'form-control', 'id' => 'comision', 'step' => '0.01']) !!}

			<script>
				document.addEventListener('DOMContentLoaded', function() {
					const apliccom = document.getElementById('apliccom');
					const comision = document.getElementById('comision');

					function toggleComision() {
						if (apliccom.checked) {
							comision.readOnly = false;
						} else {
							comision.value = 0;
							comision.readOnly = true;
						}
					}

					apliccom.addEventListener('change', toggleComision);

					// Inicializa el estado al cargar
					toggleComision();
				});
				</script>
		</div>

		<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
			<div class="form-group">
	            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
					
	              <input type="checkbox" class="custom-control-input" id="aplicdes" name="aplicdes"
	              	value="1" {{ isset($order_inven) && $order_inven->APLICDES==1 ? 'checked' : '' }}>
	              <label class="custom-control-label" for="aplicdes">Aplica Descuento</label>
	            </div>
          </div>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
			{{ Form::label('detalle', 'Detalle del producto', ['class' => 'control-label']) }}
				{!! Form::text('detalle', @$order_inven->informacion->detalle, ['class' => 'form-control ', 'placeholder' => 'Información adicional del producto', 'id' => 'detalle']) !!}
		</div>
		<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
			{{ Form::label('observaciones', 'Observaciones adicionales', ['class' => 'control-label']) }}
				{!! Form::text('observaciones', @$order_inven->informacion->observaciones, ['class' => 'form-control ', 'placeholder' => 'Observaciones', 'id' => 'observaciones']) !!}
		</div>
	</div>
</div>


<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            {{ Form::label('photo', 'Foto', ['class' => 'control-label']) }}
            <div class="col-md-12 col-sm-12 col-xs-12 hint--top" aria-label="Click para cambiar la imagen">
                {{ Form::file('photo', ['class' => 'btn btn-outline-primary btn-block ']) }}
            </div>
        </div>
    </div>

    <div class="col-md-2">
        <img src="{{ asset('storage/products/' . @$order_inven->FOTO)}}" width="200" alt="Logo">
    </div>
    
    
</div>