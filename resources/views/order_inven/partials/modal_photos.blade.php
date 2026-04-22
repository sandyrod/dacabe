<div class="modal fade" id="modal_photos">
	<div class="modal-dialog">
		<div class="modal-content">
	    	<div class="modal-header">
	      		<h4 class="modal-title">Fotos de Producto <span id="descr_prod"></span></h4>
	      		<button type="button" class="close" data-dismiss="modal" aria-label="Close">
	        		<span aria-hidden="true">&times;</span>
	      		</button>
	    	</div>
	    	<div class="modal-body">
	   		    <form id="uploadPhotoForm" enctype="multipart/form-data">

		    		<div class="row">
						<div class="col-12">
							<div class="form-group">
								{{ Form::label('photo', 'Foto', ['class' => 'control-label']) }}
					            <div class="col-md-12 col-sm-12 col-xs-12 hint--top" aria-label="Click para cambiar la imagen">
					                {{ Form::file('photo', ['class' => 'btn btn-outline-primary btn-block ']) }}
					            </div>
					            <input type="hidden" id="_token" name="_token" value="{{ csrf_token() }}">
								<input type="hidden" id="codigo_inven" name="codigo_inven" />
							</div>	
						</div> 

	    			</div> 
	    		
		      		<div class="row">
		      			<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 mt-2">
		      				<button type="button" class="btn btn-primary btn-block" id="btn_photo">
					            <i class="fa fa-check"></i> Agregar
					        </button>
		      			</div>
		      			<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 mt-2">
		      				<button type="button" class="btn btn-outline-danger btn-block" data-dismiss="modal" aria-label="Close">
				        		<i class="fa fa-times"></i> Cancelar
				      		</button>
		      			</div>
		      		</div>
		      		<div class="row inven-photos mt-3">
		      			
		      		</div>
	    		</form>
	    	</div>	    	
	    	
	  	</div>	  
	</div>	
</div>