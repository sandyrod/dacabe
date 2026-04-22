<div class="card-footer">
	<div class="form-group">
		<div class="row">
			<div class="col-md-4 col-sm-12 col-xs-12">
			</div>
			<div class="mt-2 col-md-2 col-sm-6 col-xs-6">
				<a href="{{route($route)}}" class="btn btn-outline-danger btn-block">
					<i class="fas fa-chevron-left"></i>  Regresar
				</a>
			</div>
			<div class="mt-2 col-md-2 col-sm-6 col-xs-6">
				<button type="submit" class="btn btn-primary btn-block" 
					data-form="form" data-loading-text="Guardando..." data-class="{{$validate}}">
					<i class="fas fa-save"></i>  Guardar 
				</button>
			</div>
		</div>
	</div>
</div>
