        @include('orders.partials.modal_products')
        @include('orders.partials.modal_saved_orders')
        @include('orders.partials.modal_dispatched_orders')
        @include('orders.partials.modal_detail_order')
        
        
          <div class="row">
            <div class="col-md-8 col-sm-12 col-xs-12">
              <div class="card card-primary card-outline">
              <div class="card-header border-0">
                    <a class="btn btn-app text-success btn-save-order">
                      <i class="fas fa-save"></i> Guardar
                    </a>
                    <a class="btn btn-app btn-trash text-danger">   
                      <i class="fas fa-times"></i> Cancelar
                    </a>
                    <a class="btn btn-app text-primary btn-get-order">
                      @if (@$count_saveds)
                        <span class="badge bg-danger count_saveds">{{$count_saveds}}</span>
                      @endif
                      <i class="fas fa-download"></i> Recuperar
                    </a>
                    <a class="btn btn-app text-primary products">   
                      <i class="fas fa-barcode"></i> Productos
                    </a> 
                    <!--  
                    <a class="btn btn-app text-primary">   
                      <i class="fas fa-edit"></i> Pedido
                    </a>                     
                    <a class="btn btn-app text-danger" href="{{ url('inicio') }}">
                      <i class="fas fa-sign-out-alt"></i> Salir
                    </a>  
                    -->
              </div>
              <div class="card-body table-responsive p-0">
                <table class="table table-striped table-valign-middle">
                  <thead>
                  <tr>
                    <th>Producto</th>
                    <th>Cant.</th>
                    <th>Subtotal</th>
                    <th>Acción</th>
                  </tr>
                  </thead>
                  <tbody id="details">
                      <tr><td class="text-center" colspan="4"><a class="btn btn-primary text-white" onclick="showProductModal();"> <i class="fas fa-barcode"></i> Agregar Producto</a></td></tr>
                  </tbody>
                </table>

                <div class="row div_total">
                  <div class="col-12">
                    <div class="table-responsive">
                      <table class="table">
                        <tbody>
                          <tr class="bg-ligthgray">
                            <th style="width:100%" class="pull-right">Subtotal</th>
                            <td id="field_subtotal">0</td>
                          </tr>
                          <tr class="bg-ligthgray">
                            <th style="width:100%" class="pull-right">IVA</th>
                            <td id="field_iva">0</td>
                          </tr>
                          <tr class="bg-ligthgray text-primary">
                            <th style="width:100%" class="pull-right"><h2>TOTAL</h2></th>
                            <td><h2 id="field_total">0</h2></td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!--
            <div class="row mb-3">
              <div class="col-md-6 offset-md-3">
                <a href="#" class="btn btn-lg btn-outline-primary btn-block">Ver Todas las facturas</a>
              </div>
            </div>
        -->
          </div>

          <div class="col-md-4">
            <!-- Widget: user widget style 1 -->
            <div class="card card-widget widget-user">
              <!-- Add the bg color to the header using any of the bg-* classes -->
              <div class="widget-user-header company-status-color bg-warning">
                <h3 class="widget-user-username">{{ Auth::user()->company->name }}</h3>
                <h5 class="widget-user-desc company-status-name"><i class="fas fa-spinner"></i></h5>
              </div>
              <div class="widget-user-image">
                <img class="img-circle elevation-2" src="{{ mainCompanyLogo() }}" alt="Logo">
              </div>
              <div class="card-footer p-0">
                <ul class="nav flex-column permission-list">
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      <i class="fas fa-spinner"></i>
                    </a>
                  </li>                  
                </ul>
              </div>
            </div>
            <!-- /.widget-user -->

            <input type="hidden" id="invoice_id" />

            <!-- PRODUCT LIST -->
            <div class="card card-danger">
              <div class="card-header">
                <h3 class="card-title pending-title">Productos No Vinculados</h3>

                <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                  <button type="button" class="btn btn-tool" data-card-widget="remove">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
              <!-- /.card-header -->
              <div class="card-body p-0">
                <ul class="products-list product-list product-list-in-card pl-2 pr-2">
                  <li class="item">
                    <div>
                      <a href="javascript:void(0)" class="product-title"><i class="fa fa-tags"></i> CODIGO BARRA
                        <span class="btn btn-primary float-right"><i class="fa fa-edit"></i></span></a>
                      <span class="product-description">
                        PRODUCTO
                      </span>
                    </div>
                  </li>
                  
                </ul>
              </div>
              <!-- /.card-body -->
              <div class="card-footer text-center">
                <a href="#" class="uppercase quit-filter"></a>
              </div>
              <!-- /.card-footer -->
            </div>              
            </div>
          </div>
         @ability('admin,owner', 'permission')
          <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
              
            </div>
          </div>
          @endability
        
    </section>
    <!-- /.content -->
  </div>
  
