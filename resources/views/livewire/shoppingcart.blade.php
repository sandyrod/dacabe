<div class="card card-primary card-outline" >
    <div class="card-header">
        <div class="row">
            <div class="col-md-3 col-xs-12 col-sm-12">
                 <h3 class="text-primary">Productos</h3>
            </div>
            <div class="col-md-9 col-xs-12 col-sm-12">
                <div class="float-right">
                    <div class="btn-group">
                        <a href="{{ url('shoppingcart') }}" class="card-title btn btn-primary hint--top" aria-label="Pedidos">
                            <i class="fa fa-shopping-cart"> </i>
                        </a>
                        <a href="{{ route('order-inven.print') }}" target="_blank" class="card-title text-blue btn btn-default hint--top" aria-label="Imprimir">
                            <i class="fas fa-print"> </i>
                        </a> 
                        <a href="{{ route('inicio') }}" class="card-title btn btn-outline-danger hint--top" aria-label="Inicio">
                            <i class="fas fa-arrow-left"> </i>
                        </a> 
                    </div>
                </div>
                
            </div>
        </div>
    </div>

    <div class="card-body">
         <div class="container">
	        <div class="row">
	            <div class="col-xs-12 col-sm-12 col-md-6 order-3 order-md-1 col-lg-6 pt-3">
	                <label><i class="fa fa-filter"></i> Mostrar: </label>
	                <select class="form-control select2" data-placeholder="Seleccione" id="cgrupo" name="cgrupo" wire:model="cgrupo" wire:change="refreshProducts()">
	                    <option value="TODOS">TODOS</option>
	                    @foreach($groups as $item)
	                        @if($item->CGRUPO == @$expense->branch_id)
	                            <option selected="selected" value="{{$item->id}}">{{$item->DGRUPO}}</option>
	                        @else
	                            <option value="{{$item->CGRUPO}}">{{$item->DGRUPO}}</option>
	                        @endif
	                    @endforeach
	                </select>
	            </div>
	            <div class="col-xs-12 col-sm-12 col-md-3 order-1 order-md-2 col-lg-3 pt-3">
	                <label for="descripcion"><i class="fa fa-address-card"></i> Cliente </label>
	                {!! Form::text('descripcion', @$company->descripcion, ['class' => 'form-control ', 'placeholder' => 'Nombre cliente', 'required', 'id' => 'descripcion']) !!}
	            </div>
	            <div class="col-xs-12 col-sm-12 col-md-3 order-2 order-md-3 col-lg-3 pt-3">
	                <label for="rif"> RIF </label>
	                {!! Form::text('rif', @$company->rif, ['class' => 'form-control ', 'placeholder' => 'RIF', 'required', 'id' => 'rif']) !!}
	            </div>
	        </div>
	    </div>
        <section>
          <div class="text-center container pt-4">
            
            <div class="row" id="product_lists">
                
                @if (! $order_invens) 
                	<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
                		
                	</div>
                	<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 w-100" style="text-align:left;">
                		<div class="alert alert-info alert-dismissible">
                			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                			<h5><i class="icon fas fa-info"></i> Atención!</h5>No se encontró existencia...
                		</div>
                	</div>
                @else
		            @foreach($order_invens as $inven)
		                @if ($inven->BASE1 > 0 && $inven->EUNIDAD && $inven->EUNIDAD > 0) 
		                    <div class="col-lg-3 col-md-12 mb-4 w-100">
		                    	<div class="card bg-product w-100" @click="Hello">
		                    		<div class="bg-image hover-zoom ripple ripple-surface ripple-surface-light" data-mdb-ripple-color="light">
		                    			<a href="#!">
		                    				<div class="image-container">
		                    					@if($inven->FOTO && $inven->FOTO != '')
		                        					<img class="card-img-top w-100" src="{{asset('storage/products/'.'/'.$inven->FOTO)}}" alt="Foto">
		                    					@else 
		                        					<img class="card-img-top w-100" src="{{asset('storage/products/nofoto.jpg') }}" alt="Foto">
		                        				@endif
		                    					<div class="overlay-pnf">
		                    						<div class="text">
		                    							<i class="fa fa-shopping-cart"></i> <br> CÓD: {{$inven->CODIGO}}
		                    						</div>
		                    					</div>
		                    				</div>
		                    			</a>
		                    		</div>
		                    		<div class="card-body">
		                    			<a href="" class="text-reset">
		                    				<h5 class="card-title mb-3 text-primary w-100">{{$inven->DESCR}}</h5>
		                    			</a><br>
		                    			<a href="" class="text-reset">
		                    				<small class="text-muted">{{$inven->DGRUPO}}</small>
		                    			</a>
		                    			<div class="row pt-3">
		                    				<div class="col-6">
		                    					<div class="d-flex justify-content-start align-items-end h-100">
		                    						<h5>
		                    							<span class="badge badge-sm bg-warning ms-2">{{$inven->CODIGO}} 
		                    								<i class="fa fa-shopping-cart"></i> {{$inven->EUNIDAD}}
		                    							</span>
		                    						</h5>
		                    					</div>
		                    				</div>
		                    				<div class="col-6">
		                    					<div class="d-flex justify-content-end align-items-end h-100 w-100">
		                    						<h5><span class="badge bg-primary ms-2">$ {{$inven->BASE1}}</span></h5>
		                    					</div>
		                    				</div>
		                    			</div>
		                    		</div>
		                    	</div>
		                    </div>
		                @endif
		            @endforeach

                @endif            


            </div>                            
          </div>
        </section>

    </div>

    <div class="card-footer">
        
    </div>
</div>