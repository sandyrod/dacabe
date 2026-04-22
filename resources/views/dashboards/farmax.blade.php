@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Panel de administración')

@section('titulo_header', 'Dashboard')
@section('subtitulo_header', 'Panel de administración')


@section('content')
 
    <!-- Main content -->
    <section class="content">
        <!-- Small boxes (Stat box) -->
      
      @php($perm = $permissions->where('name', 'Precios')->first())
      @if($perm)
        @include('orders.partials.modal_products')
        @include('orders.partials.modal_saved_orders')
        @include('orders.partials.modal_dispatched_orders')
        @include('orders.partials.modal_detail_order')
        <div class="row">
          <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
              <div class="inner">
                <h3 class="invoices-month-counter"><i class="fa fa-circle-o-notch fa-spinner fa-fw"></i></h3>

                <p class="invoices-month-text">Sin Conexión</p>
              </div>
              <div class="icon">
                <i class="fa fa-download"></i>
              </div>
              <a href="#" class="small-box-footer update-invoices-indicator">Descargar <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
              <div class="inner">
                <h3><i class="fa fa-circle-o-notch fa-spinner fa-fw"></i><sup style="font-size: 20px"></sup></h3>

                <p>Facturas transferidas</p>
              </div>
              <div class="icon">
                <i class="fa fa-award"></i>
              </div>
              <a href="#" class="small-box-footer">Más detalles <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-danger">
              <div class="inner">
                <h3><i class="fa fa-circle-o-notch fa-spinner fa-fw"></i></h3>

                <p>Productos por enlazar</p>
              </div>
              <div class="icon">
                <i class="fa fa-barcode"></i>
              </div>
              <a href="#" class="small-box-footer">Ver detalles <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-warning">
              <div class="inner">
                <h3><i class="fa fa-circle-o-notch fa-spinner fa-fw"></i></h3>

                <p>Pagos realizados</p>
              </div>
              <div class="icon">
                <i class="fas fa-handshake"></i>
              </div>
              <a href="#" class="small-box-footer">Reportar Pago <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
        </div>
        
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
      @else
        <div class="row">
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-info">
              <div class="inner">
                <h3>150</h3>

                <p>New Orders</p>
              </div>
              <div class="icon">
                <i class="ion ion-bag"></i>
              </div>
              <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-success">
              <div class="inner">
                <h3>53<sup style="font-size: 20px">%</sup></h3>

                <p>Bounce Rate</p>
              </div>
              <div class="icon">
                <i class="ion ion-stats-bars"></i>
              </div>
              <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-warning">
              <div class="inner">
                <h3>44</h3>

                <p>User Registrations</p>
              </div>
              <div class="icon">
                <i class="ion ion-person-add"></i>
              </div>
              <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-danger">
              <div class="inner">
                <h3>65</h3>

                <p>Unique Visitors</p>
              </div>
              <div class="icon">
                <i class="ion ion-pie-graph"></i>
              </div>
              <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
        </div>
      @endif
        
         @ability('admin,owner', 'permission')
          <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
              
            </div>
          </div>
          @endability
        
    </section>
    <!-- /.content -->
  </div>
  
@endsection

@section('scripts')
  <script>

    const URL_PRODUCT = '{{url('products')}}';
    const URL_SAVE_ORDER = '{{url('save-order')}}';
    const URL_STORE_ORDER = '{{url('dispatch')}}';
    const URL_GET_ORDER = '{{url('get-save-orders')}}';
    const URL_UPDATE_STATUS = '{{url('update-order-status')}}';
    const URL_GET_SD_ORDER = '{{url('get-sd-order')}}';
    const TOKEN = '{{csrf_token()}}';
    let products = [];
    var products_group;
    var temporal = [];
    var saved = [];
    var dispatched = [];

    let getProducts = () => {
        $.ajax({
            url : '{{url('get-products/')}}', type : 'get', dataType : 'json',
            success : function(response) {
              products = response;
            },
            error : function(xhr, status) {
                console.log('error: ', xhr, status);                  
            }
        });
    };

    let loadOrder = (nro) => {
      //if ($('#pedido').val() && $('#pedido').val() != nro) {
      if (temporal.length && $('#pedido').val() != '') {
        swal("Oops!", "No puede cargar otro Pedido mientras existan productos en el pedido actual!", "info");
      } else { 
        $('#pedido').val('P' + nro);
        searchOrder();          
      }

    };

    
    // PRODUCTOS
    getProducts();

    $("#select2_products").select2({
        minimumInputLength: 2,
        ajax: {
            url: URL_PRODUCT,
            dataType: 'json',
            type: "GET",
            quietMillis: 50,
            data: function (term) {
                return {
                    term: term
                };
            },
            processResults: function (data) {
                  return {
                    results: $.map(data, function (item) {
                        return {
                            text: item.codigo + ' | ' + item.descr,
                            id: item.id
                        }
                    })
                };
            }
        }
    });

    $('#select2_products').on("select2:select", function(e) { 
      let descr = '';
      let detail = '<tr><td></td><td></td><td></td><td></td><td></td></tr>';
      $(".select-product").addClass('d-none');
      $(".select-product").removeClass('d-block');
      if ($("#select2_products option:selected").text()) {
        let id = $("#select2_products").val();
        let product = products.filter((item)=> item.id == id);
        descr = '<i class="fa fa-shopping-cart"></i> ';
        if (product && product.length > 0) {
          descr += product[0].descr;
          let style = 'bg-danger';
          if (product[0].precio > 0) {
            $(".select-product").removeClass('d-none');
            $(".select-product").addClass('d-block');
            style = 'bg-success';
          }
          let priceDollar = product[0].pvpm1 && product[0].pvpm1 > 0 ? '$ ' + product[0].pvpm1 : '';
          
          //detail = '<tr class="'+style+'"><td>'+product[0].codigo+'</td><td>'+product[0].alterno+'</td><td><b>Bs. '+product[0].precio.toLocaleString('es-VE')+'</b></td><td><b>' +priceDollar.toLocaleString('es-VE')+ '</b></td><td>Bs. '+product[0].iva.toLocaleString('es-VE')+'</td></tr>';
          detail = '<tr class="'+style+'"><td>'+product[0].codigo+'</td><td>'+product[0].alterno+'</td><td><b>Bs. '+product[0].precio.toLocaleString('es-VE')+'</b></td><td><b>' +priceDollar.toLocaleString('es-VE')+ '</b></td><td>Bs. '+product[0].iva.toLocaleString('es-VE')+'</td></tr>';
        }
      }
      $(".product_detail_header").html(descr);
      $(".search_products").html(detail);
    });
    
    $(".products").on('click', function(){
      showProductModal();
    });

    $(".btn-trash").on('click', function(){
      //if (temporal.length) {
        swal({
            title: "Aviso!", text: "¿Desea Cancelar el pedido?", type: "info", showCancelButton: true, confirmButtonColor: '#D81A1A', confirmButtonText: 'Si, Cancelar!', cancelButtonText: "Cancelar", closeOnConfirm: true, showLoaderOnConfirm: true
        }, function(isConfirm){
            if (isConfirm) {
              $('#pedido').val('');
              temporal = [];
              refreshDetails();
            }
        });        
      //} else {
      //  swal({title: 'Atención!', text: 'No existen productos precargados...', type: 'error', html:true});
      //}
      
    });

    $(".btn-save-order").on('click', function(){
      if ($('#pedido').val() == '') {
        swal({title: 'Atención!', text: 'Debe seleccionar la habitación a la cual se cargará este pedido...', type: 'error', html:true});
        return false;
      }
      if (temporal.length) {
        swal({
            title: "Aviso!", text: "¿Desea guardar el pedido?", type: "info", showCancelButton: true, confirmButtonColor: '#1274D9', confirmButtonText: 'Si, Guardar!', cancelButtonText: "Cancelar", closeOnConfirm: false, showLoaderOnConfirm: true
        }, function(isConfirm){
            if (isConfirm) {
              let data = {
                rif: $('#client_rif').val(),
                client_id: $('#client_id').val(),
                client_name: $('#client_name').val(),
                cashier: CASHIER,
                cashier_name: CASHIER_NAME,
                products: temporal,
                nro: $('#pedido').val()
              };
              sendRequestSaveOrderCreate(URL_SAVE_ORDER, 'post', data, 'El pedido ha sido guardado...', 'print'); 
            }
        }); 
      } else {
        swal({title: 'Atención!', text: 'No existen productos precargados...', type: 'error', html:true});
      }      
    });

    $(".btn-get-order").on('click', function(){
      getSavedsOrders(URL_GET_ORDER, 'get');      
    });

    let storeOrder = () => {
      if (temporal.length > 0) {
        swal({
            title: "Aviso!", text: "¿Desea despachar el pedido?", type: "info", showCancelButton: true, confirmButtonColor: '#1274D9', confirmButtonText: 'Si, Despachar!', cancelButtonText: "Cancelar", closeOnConfirm: false, showLoaderOnConfirm: true
        }, function(isConfirm){
            if (isConfirm) {
              let data = {                
                dispatched_id: $('#dispatched_id').val(),
                rif: $('#client_rif').val(),
                client_id: $('#client_id').val(),
                pedido: $('#pedido').val(),
                client_name: $('#client_name').val(),
                cashier: CASHIER,
                cashier_name: CASHIER_NAME,
                products: temporal
              };
              sendRequestSaveOrderCreate(URL_STORE_ORDER, 'post', data, 'El pedido ha sido despachado...');
            }
        }); 
      } else {
        swal({title: 'Atención!', text: 'No existen productos precargados...', type: 'error', html:true});
      }
    };

    $(".btn-store-order").on('click', function(){
      if (! temporal.length) {
        swal({title: 'Atención!', text: 'No existen productos precargados...', type: 'error', html:true});
        return false;
      }

      if (! $('#pedido').val()) {
        swal({title: 'Atención!', text: 'Debe indicar la habitación a la cual se cargará el pedido...', type: 'error', html:true});
        return false;
      }

      if (! $('#client_id').val()) {
        swal({title: 'Atención!', text: 'El pedido no tiene Cliente seleccionado...', type: 'error', html:true});
        showClientModal(); 
      } else {
        storeOrder();
      }
    });

    let enableProductsSearch = (el) => {
      
      if (el == 'div_search_products') {
        $('.div_search_products').addClass('d-block');
        $('.div_search_products').removeClass('d-none');
        $('.div_search_products_groups').removeClass('d-block');
        $('.div_search_products_groups').addClass('d-none');
        $('.div_search_by_group').removeClass('d-block');
        $('.div_search_by_group').addClass('d-none');
        $('.select-product').removeClass('d-none');
        $('.select-product').addClass('d-block');
      } else {
        $('.div_search_products').removeClass('d-block');
        $('.div_search_products').addClass('d-none');
        $('.div_search_products_groups').addClass('d-block');
        $('.div_search_products_groups').removeClass('d-none');
        $('.div_search_by_group').addClass('d-block');
        $('.div_search_by_group').removeClass('d-none');
        $('.select-product').addClass('d-none');
        $('.select-product').removeClass('d-block');
      }
    };

    let showProductModal = (mode = 'div_search_products') => {
      enableProductsSearch(mode);
      $('#modal_products').modal('show');
    };

    let plusProduct = (product_code, mode = 'plus') => {
      prod = temporal.find((o, i) => {
        if (o.codigo == product_code) {
          let price = temporal[i].precio1 / temporal[i].cantidad;
          if (mode == 'plus') {
            temporal[i].cantidad += 1;            
            temporal[i].precio1 = (price * temporal[i].cantidad);
          } else {
            if(temporal[i].cantidad > 1) {
              temporal[i].cantidad -= 1;
              temporal[i].precio1 = (price * temporal[i].cantidad);
            } else {
              swal({title: 'Atención!', text: 'No se puede restar cantidad al producto...', type: 'error', html:true});
            }
          }
            return true;
        }
      });
      refreshDetails();
    };

    let addProduct = (product) => {
      let prod = false;
      prod = temporal.find((o, i) => {
        if (o.codigo == product.codigo) {
            let price = temporal[i].precio1 / temporal[i].cantidad;
            temporal[i].cantidad += 1;
            temporal[i].precio1 = (price * temporal[i].cantidad).toFixed(2);
            return true;
        }
      });
      if (! prod) {
        temporal.push({
          id: product.id,
          codigo: product.codigo,
          descr: product.descr,
          iva1: product.iva,
          cantidad: 1,
          precio1: product.precio - product.iva,
        }); 
      }
      refreshDetails();
    };

    let deleteProduct = (product_code) => {
      temporal = temporal.filter((item) => item.codigo != product_code);
      refreshDetails();
    };
  
    let refreshDetails = () => {
      //let html = $('#details').html();
      let html = '';
      let subtotal = 0;
      let iva = 0;
      let total = 0;
      temporal.forEach(item => {
        subtotal += parseFloat(item.precio1);
        iva += parseFloat(item.iva1 * item.cantidad);
        html += '<tr><td>'+item.descr+'</td><td>'+item.cantidad+'</td><td><b>'+item.precio1.toLocaleString('es-VE')+'</b></td><td>';
        html += '<a href="#" onclick="plusProduct(\''+item.codigo+'\', \'plus\')" class="btn mt-1 btn-sm btn-primary"><i class="fas fa-plus"></i></a>';
        html += '<a href="#" onclick="plusProduct(\''+item.codigo+'\', \'minus\')" class="ml-1 mt-1 btn btn-sm btn-warning"><i class="fas fa-minus"></i></a>';
        html += '<a href="#" onclick="deleteProduct(\''+item.codigo+'\')" class="ml-1 mt-1 btn btn-sm btn-danger"><i class="fas fa-trash"></i></a></td></tr>';
      });
      //if (! html || html == '') {
        html += '<tr><td class="text-center" colspan="4"><a class="btn btn-primary text-white" onclick="showProductModal();"> <i class="fas fa-barcode"></i> Agregar Producto</a></td></tr>';
      //}
      $('#details').html(html);

      if (temporal.length) {
        subtotal = parseFloat(subtotal);
        iva = parseFloat(iva);
        $('#field_subtotal').html((subtotal).toLocaleString('es-VE'));
        $('#field_iva').html((iva).toLocaleString('es-VE'));
        $('#field_total').html((subtotal + iva).toLocaleString('es-VE'));
        $('.div_total').removeClass('d-none');
      } else {
        $('#field_subtotal').html(0);
        $('#field_iva').html(0);
        $('#field_total').html(0);
        //$('.div_total').addClass('d-none');
      }
    };

  let updateDetails = (product_id) => {
      let html = $('#details').html();
      let product = products.filter((item)=> item.id == product_id);
      if (product && product.length > 0) {
        html += '<tr><td>'+product[0].descr+'</td><td>1</td><td><b>'+product[0].precio1+'</b></td><td>';
        html += '<a href="#" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i></a>';
        html += '<a href="#" class="ml-1 btn btn-sm btn-warning"><i class="fas fa-minus"></i></a>';
        html += '<a href="#" class="ml-1 btn btn-sm btn-danger"><i class="fas fa-trash"></i></a></td></tr>';
      }
      $('#details').html(html);
    };

    $(".select-product").on('click', function(){
      //$('#product_id').val($('#select2_products').val());
      //$('#client_info').html($("#input_client option:selected").text());
      let product_id = $('#select2_products').val();
      //updateDetails(product_id);
      let product = products.filter((item)=> item.id == product_id);      
      addProduct(product[0]);
      $('#modal_products').modal('hide');
    });

    let sendRequestSaveOrderCreate = (url, type, data, message = 'El pedido ha sido guardado...', print = null) => {
      $.ajax({
          url : url, type : type, data: { _token : TOKEN, data: data  }, dataType : 'json',
          success : function(response) { 
            $('#client_id').val(null);
            $('#client_info').html('Click para seleccionar cliente...');
            temporal = [];
            refreshDetails();
            if (response.count_saveds) {
              $('.count_saveds').html(response.count_saveds);
            }
            $('#dispatched_id').val('');
            $('#client_rif').val('');
            $('#client_name').val('');
            $('#client_phone').val('');
            $('#client_address').val('');
            
            if (print != null) {
              swal({
                  title: "Aviso!", text: message + " ¿Desea imprimir el pedido en espera?", type: "info", showCancelButton: true, confirmButtonText: 'Si, Imprimir!', cancelButtonText: "Cancelar", closeOnConfirm: true, showLoaderOnConfirm: true
              }, function(isConfirm){
                  if (isConfirm) {
                    printOrder (response.vtaespe.id);
                  }
                  location.reload();
              });
            } else {
              swal({title: 'Éxito!', text: message, type: 'success', html:true});
              location.reload();
            }
          },
          error : function(xhr, status) {
              console.log('error: ', xhr, status);
              switch(xhr.status) {
                  case 401:
                      swal("Oops!", "Su sesión ha expirado.", "info");
                      setTimeout(function(){   location.reload();   }, 2000);
                      break;
                  case 404:
                      swal("Oops!", "Falta información para guardar.", "error");
                      break;
                  default:
                      swal("Oops!", "¡El registro no pudo ser procesado!... Intente nuevamente.", "error");
              }
          },
          complete:function(response){}
      });
    };

    let getOrder = (order_id) => {
      let order = saved.filter((item) => item.id == order_id);
      if (order.length) {        
        order[0].vtaespd.forEach((prod)=> {
          let product = products.filter((item)=> item.codigo.trim() == prod.codigo.trim());
          if (product && product.length > 0) {
            temporal.push({
              id: product[0].id,
              codigo: product[0].codigo,
              descr: product[0].descr,
              iva1: prod.impuesto,
              cantidad: prod.cantidad,
              precio1: prod.total,
            });
          }
          $('#pedido').val(order[0].nro);
          if (order[0].codcli) {
            $('#client_id').val(order[0].codcli);
            $('#client_info').html(order[0].codcli + ' | ' + order[0].nombre);
          }
          updateOrderStatus(order[0].id);
        });
        refreshDetails();
      }        
      $('#modal_saved_orders').modal('hide');
    };

    let updateOrderStatus = (order_id) => {
      $.ajax({
          url : URL_UPDATE_STATUS, type : 'post', data: { _token : TOKEN, order_id: order_id  }, dataType : 'json',
          success : function(response) {
              if (response.count_saveds) {
                $('.count_saveds').html(response.count_saveds);
              }
          },
          error : function(xhr, status) {
              console.log('error: ', xhr, status);              
          },
          complete:function(response){}
      });
    };

    let getSavedsOrders = (url, type) => {
      $.ajax({
          url : url, type : type, dataType : 'json',
          success : function(response) {
            if (response.data.length) {
              saved = response.data;
              $('.div_saveds').html('<span>cargando...<span>');
              let html ='<table class="table table-striped table-valign-middle">';
              html += '<thead><tr><th>#</th><th>Cliente</th><th>Cant. | Descripción</th><th>Acción</th></tr></thead><tbody>';
              response.data.forEach((item) => {
                html += '<tr><td><b>'+item.nro+'</b></td><td><b>'+item.nombre+'</b></td>';
                html += '<td>';
                item.vtaespd.forEach((prod)=> {
                  html += '<small class="text-default mr-1"> '+prod.cantidad+'&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;'+prod.descr+'</small><br />';
                });
                html += '</td>';
                html += '<td><a href="#" onclick="getOrder(\''+item.id+'\')" class="btn btn-primary"><i class="fas fa-download"></i></a><a href="#" onclick="printOrder(\''+item.id+'\')" class="ml-1 btn btn-warning"><i class="fas fa-print"></i></a></td></tr>';

              });
              html += '</tbody></table>';

              $('#modal_saved_orders').modal('show');
              $('.div_saveds').html(html);
            } else {
              saved = [];
              swal("Oops!", "No hay pedidos en espera...", "info");
            }
          },
          error : function(xhr, status) {
              console.log('error: ', xhr, status);
              switch(xhr.status) {
                  case 401:
                      swal("Oops!", "Su sesión ha expirado.", "info");
                      setTimeout(function(){   location.reload();   }, 2000);
                      break;
                  case 404:
                      swal("Oops!", "Falta información para guardar.", "error");
                      break;
                  default:
                      swal("Oops!", "¡El registro no pudo ser procesado!... Intente nuevamente.", "error");
              }
          },
          complete:function(response){}
      });
    };

    let loadDetailOrder = (nro) => {
      $('#modal_dispatched_orders').modal('hide');
      let desp = dispatched.filter((item)=>item.nro == nro);
      let det = despachod.filter((item)=>item.nro == nro);

      let tit =  '';
      if (desp[0].nombre) {
        tit += '<i class="fa fa-user"></i> ' + desp[0].codcli + ' ' + desp[0].nombre;
      } else {
        tit += 'RESUMEN DEL PEDIDO'
      }
      $('.info_tit').html( tit);
      let html = '<b class="text-danger">#' + desp[0].nro.substring(1,4) + '</b><br><table class="table table-striped table-valign-middle">';
      html += '<thead><tr><th>Descripción</th><th>Cant.</th><th>Precio Bs</th><th>Total Bs</th><th>Total $</th></thead><tbody>';
      let tasa = 0;
      let totgen = 0;
      let totgendiv = 0;
      let divisa = '';
      det.forEach((item) => {
        html += '<tr><td>'+item.descr+'</td><td><span class="badge badge-success">'+item.cantidad+'</span></td><td><b>'+item.total+'</b></td>';
          tot = item.cantidad*item.total;
          totgen += tot;
          tasa = item.tasadeldia;
          if (tasa) {
            totdiv = tot / tasa;
            totgendiv += totdiv;
            divisa = '$ ' + totdiv.toLocaleString('es-VE');
          }
          html += '<td><b>'+tot.toLocaleString('es-VE')+'</b></td>';
          html += '<td class="text-success"><b>'+divisa.toLocaleString('es-VE')+'</b></td>';
        });
      html += '<tr class="bg-info"><td class="text-right" colspan="5">TOTAL Bs.: <b>'+totgen.toLocaleString('es-VE')+'</b></td></tr>';
      html += '<tr class="bg-success"><td class="text-right" colspan="5">TOTAL $: <b>'+totgendiv.toLocaleString('es-VE')+'</b></td></tr>';
      html += '</tbody></table>';

      $('.div_detail_order').html(html);

      $('#modal_detail_order').modal('show');
    };


    set_active_sidebar_menu  ('Opciones', 'Inicio');
  
  </script>
@endsection
