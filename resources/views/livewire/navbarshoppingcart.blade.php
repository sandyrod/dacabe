<li class="nav-item dropdown ">
  <a class="nav-link cart" id="cart" data-toggle="dropdown" href="#" aria-expanded="true">
    <i class="fa fa-shopping-cart"></i>
    @if (@$count_carts) 
		  <span class="badge badge-warning navbar-badge cart-count">
		   	{{ @$count_carts }}
		  </span>
		@endif
  </a>
  <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right " style="left: inherit; right: 0px;">
   	@foreach(@$carts as $cart)
			<a href="#" class="dropdown-item">
		  	<div class="media">
		    	<div class="media-body">
		      	<p class="text-sm">
		        	<span class="badge badge-primary mr-2">
		        		{{$cart->cantidad}}
		        	</span>
		         	{{substr($cart->inven_descr, 0, 30)}}
		        </p>
		      </div>
		    </div>
		  </a>
      <div class="dropdown-divider"></div>
    @endforeach
          
   	<div class="dropdown-divider"></div>
   	<a href="{{url('view-cart')}}" class="dropdown-item dropdown-footer text-primary"><i class="fa fa-shopping-cart"></i> Ver Carrito</a>
  </div>
</li>
      