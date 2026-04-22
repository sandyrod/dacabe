@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Panel de administración')

@section('titulo_header', 'Panel de administración')
@section('subtitulo_header', 'Panel de administración')


@section('content')
 
    <!-- Main content -->
    <section class="content">
        <!-- Small boxes (Stat box) -->        
      @if (Auth::user()->dashboard)
        @include('dashboards.'.Auth::user()->dashboard)
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
    const GET_DISCOUNT = '{{url('get-discount')}}';

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

    let showRif = (rif_foto) => {
        let photo = '{{asset('storage/products/')}}' + '/' + rif_foto;
        //document.getElementById('modalImage').src = photo; // Carga la imagen en la modal
        //document.getElementById('myModal').style.display = "block"; 
        window.open(photo);
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
          let priceDollar = product[0].BASE1 && product[0].BASE1 > 0 ? '$ ' + product[0].BASE1 : '';
          
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

    const URL_UPDATE_ESTATUS_ORDER = '{{url('update-estatus-order')}}';

    let approveOrCancelOrder = (order_id, estatus) => {
        if (order_id && estatus) {
            $.ajax({
                url : URL_UPDATE_ESTATUS_ORDER, type : 'post', data: { _token : TOKEN, order_id: order_id, estatus: estatus  }, dataType : 'json',
                success : function(response) {
                    if (response.type == 'success') {
                      $('#div_' + order_id).fadeOut(700, function() {
                          $(this).remove();
                      });
                    } else {
                      Swal.fire("Oops!", "Pedido No Encontrado. Intente nueamente!", "info");
                    }
                },
                error : function(xhr, status) {
                    console.log('error: ', xhr, status);
                    switch(xhr.status) {
                        case 401:
                            //Swal.fire("Oops!", "Su sesión ha expirado. 401", "info");
                            setTimeout(function(){      }, 2000);
                            break;
                        case 404:
                            //Swal.fire("Oops!", "Recurso no encontrado. 404", "error");
                            break;
                        default:
                            //Swal.fire("Oops!", "¡El registro no pudo ser procesado!... Intente nuevamente.", "error");
                    }
                },
                complete:function(response){}
            }); 
        }
    }

    
      
  $(document).ready(function(){    
        $(".owl-carousel").owlCarousel({
          items: 1,
          loop: true,
          margin: 10,
          nav: true,
          navText: ['<i class="fas fa-chevron-left"></i>', '<i class="fas fa-chevron-right"></i>'], // Cambia el texto por iconos
          autoplay: true,
          autoplayTimeout: 4000,
          autoplayHoverPause: true,
          smartSpeed: 1000,
          dots: true,
          animateOut: 'fadeOut',
          animateIn: 'fadeIn',
        });

        let getRate = () => {
            $.ajax({
                url: GET_DISCOUNT, type: 'GET', dataType : 'json',
                success : function(response) {
                    if (response.data){
                        $('#info-discount').html(response.data+'%');
                        //$('.div-discount').removeClass('d-none');
                    }else{
                      $('#info-discount').html('0%');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('error: ', xhr, status);
                }
            }); 
        };
        
        $('#info-discount').html('<div class="spinner-border" role="status"><span class="sr-only">Cargando...</span></div>');
        setTimeout(() => {
            getRate();
        }, 4000);
        
    });



    set_active_sidebar_menu  ('Opciones', 'Inicio');
  
  </script>
@endsection
