<div class="modal fade" id="modal_products">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
	    	<div class="modal-header">
	      		<h4 class="modal-title">Buscar Producto</h4>
	      		<button type="button" class="close" data-dismiss="modal" aria-label="Close">
	        		<span aria-hidden="true">&times;</span>
	      		</button>
	    	</div>
	    	<div class="modal-body">
	      		@include('products.form')
	    	</div>
	    	
	    	<div class="modal-footer">
	    		<div class="div_search_by_group">
	    			Buscar
	    			<input type="text" class="form input_search_by_group" placeholder="Buscar">
	    		</div>
	    		<button type="button" class="btn btn-success d-none select-product">Seleccionar</button>
	    		<button type="button" class="btn btn-outline-danger" data-dismiss="modal" aria-label="Close">Cerrar</button>
	    	</div>
	  	</div>	  
	</div>	
</div>