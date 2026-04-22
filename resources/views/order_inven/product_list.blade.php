<div class="card card-primary card-outline" >
    <div class="card-header">
        <div class="row">
            <div class="col-md-3 col-xs-12 col-sm-12">
                 <h3 class="text-primary">Productos</h3>
            </div>
            <div class="col-md-9 col-xs-12 col-sm-12">
                <div class="float-right">
                    <div class="btn-group">
                        
                        <a href="{{ url('view-cart') }}" class="card-title btn btn-primary hint--top" aria-label="Ver Carrito">
                            <i class="fa fa-shopping-cart"> </i>
                        </a>
                        <a href="{{ route('order-inven.print') }}" target="_blank" class="card-title text-blue btn btn-default hint--top d-none" aria-label="Imprimir">
                            <i class="fas fa-print"> </i>
                        </a>
                        <a href="#" id="export-excel-btn" class="card-title btn btn-success hint--top" aria-label="Exportar a Excel" data-export-url="{{ route('order-inven.export-products') }}">
                            <i class="fas fa-file-excel"></i>
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
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
                <span id="depos_div">                    
                </span>
                <span id="depos_div2"> 
                </span>
                <input type="hidden" id="CDEPOS" />
            </div>
            <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
                <label><i class="fa fa-filter"></i> Mostrar: </label>
                <select class="form-control select2" data-placeholder="Seleccione" id="CGRUPO" name="CGRUPO">
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
            <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
                <label><i class="fa fa-search"></i> Buscar por Nombre o Código: </label>
                <input type="text" class="w-100 form-control" id="search" name="search" placeholder="Buscar término...">
            </div>
            <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
                <span id="btn_filter" class="btn btn-primary btn-block mt-4"><i class="fa fa-filter"></i> Filtrar</span>
            </div>
        </div>
        <section>
          <div class="text-center container py-5">
            
            <div class="row" id="product_lists">
                

            </div>                            
          </div>
        </section>

    </div>

    <div class="card-footer">
        
    </div>
</div>