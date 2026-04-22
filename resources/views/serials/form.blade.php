<div class="row">
	<div class="col-md-10 col-sm-8 col-xs-8">
		<div class="form-group">
		{!! Form::select('company_id', @$companies,  @$serial->company_id, ['class'=>'form-control select2', 'style'=>'width: 100%', 'id'=>'company_id' ]) !!}
		</div>	        
	</div>
	<div class="col-md-2 col-sm-4 col-xs-4">
    	<span class="form-group-text">
			<a href="#" class="btn btn-outline-primary btn-block" id="btn_new"><i class="fas fa-plus-square"></i> </a>	
    	</span>
    </div>
	        
</div>
<!--
<div class="row">
	<div class="col-md-12">
		<div class="form-group">
			{{ Form::label('description', 'Descripción', ['class' => 'control-label']) }}
			{!! Form::text('description', @$serial->description, ['class' => 'form-control ', 'placeholder' => 'Descripción', 'required', 'id' => 'description']) !!}
		</div>
	</div>
</div>
-->
<div class="row">
	<div class="col-md-12">
		<div class="form-group">
			{{ Form::label('serial', 'Serial', ['class' => 'control-label']) }}

			<span class="hide" style="color:white;">
				<span id="hserial1" class="hide"></span>
				<span id="hserial2" class="hide"></span>
				<span id="hserial3" class="hide"></span>
			</span>
			<div class="input-group">
				{!! Form::text('serial', @$serial->serial, ['class' => 'form-control ', 'placeholder' => 'Serial', 'required', 'id' => 'serial']) !!}
				 <span class="form-group-append">
                     <button type="button" class="btn btn-success btn-flat save hint--left" aria-label="Generar Serial"><i class="fa fa-check"></i></button>
                   </span>
            </div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-4">
		<div class="form-group">
			<div class="input-group">
				{!! Form::text('serial1', '', ['class' => 'form-control ', 'placeholder' => 'Cód. Desbloqueo', 'readonly' => 'readonly', 'id' => 'serial1']) !!}
				<div class="form-group-append">
	            	<span class="form-group-text hint--top" aria-label="Copiar">
						<a href="#" class="btn btn-outline-info mt-10" id="btn_serial1"><i class="fas fa-copy"></i></a>	
	            	</span>
	            </div>
	        </div>
		</div>
	</div>
	<div class="col-md-4">
		<div class="form-group">
			<div class="input-group">
				{!! Form::text('serial2', '', ['class' => 'form-control ', 'placeholder' => 'Cód. Desbloqueo', 'readonly' => 'readonly', 'id' => 'serial2']) !!}
				<div class="form-group-append">
	            	<span class="form-group-text hint--top" aria-label="Copiar">
						<a href="#" class="btn btn-outline-info mt-10" id="btn_serial2"><i class="fas fa-copy"></i></a>	
	            	</span>
	            </div>
	        </div>
		</div>
	</div>
	<div class="col-md-4">
		<div class="form-group">
			<div class="input-group">
				{!! Form::text('serial3', '', ['class' => 'form-control ', 'placeholder' => 'Cód. Desbloqueo', 'readonly' => 'readonly', 'id' => 'serial3']) !!}
				<div class="form-group-append">
	            	<span class="form-group-text hint--top" aria-label="Copiar">
						<a href="#" class="btn btn-outline-info mt-10" id="btn_serial3"><i class="fas fa-copy"></i></a>	
	            	</span>
	            </div>
	        </div>
		</div>
	</div>
</div>




