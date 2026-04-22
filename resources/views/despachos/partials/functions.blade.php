@section('js_functions')
<script>
    
    const URL = '{{url('despachos')}}';
    const TOKEN = '{{csrf_token()}}';
    let table_id = 'dynamic-table';
    let init_datatable = () => {
        return {
                language: {  url: '{{asset('theme/plugins/datatables/venezuela.json')}}'  },
                responsive: true,
                "processing": true,
                "serverSide": true,
                "autoWidth": false,
                searchDelay: 1000,
                ajax: {
                    "url": URL,
                    data: function (d){
                        d.datatable=1;
                    }
                },
                columns: [
                    {data: 'nro', name: 'nro'},
                    {data: 'fecha', name: 'fecha'},
                    {data: 'cliente', name: 'cliente'},
                    {data: 'warehouse', name: 'warehouse'},
                    {data: 'observations', name: 'observations'},
                    {data: 'conditions', name: 'conditions'},
                    {data: 'fecha_despacho', name: 'fecha_despacho'},
                    {data: 'action', name: 'action'}                    
                ],
                order: [[0, 'desc']],
                bAutoWidth: false,
            };
    };
    
    let table = $('#'+table_id).DataTable( init_datatable() );
    
    let refresh_table = () => {
        table.draw();
    };

    $('#busqueda').on('keyup', function () {
        table.search( this.value ).draw(); 
    } );

    
    
    jQuery(function($) {

        $("table#"+table_id).on('click', '.cliente_verificado', function(e){
            e.preventDefault();
            edit_verified($(this).data('iddata'));
        });

        $("table#"+table_id).on('click', '.edit', function(){
            edit_record($(this).data('iddata'));
        });
        
        $("table#"+table_id).on('click', '.despachar', function(){
            dispatch_record($(this).data('iddata'));
        });

        $("table#"+table_id).on('click', '.print', function(){
            print_record($(this).data('iddata'));
        });

        $("table#"+table_id).on('click', '.delete', function(){
            update_record($(this).data('iddata'), 'RECHAZADO');
        });

        $("table#"+table_id).on('click', '.approve', function(){
            update_record($(this).data('iddata'), 'APROBADO');
        });

        $("table#"+table_id).on('click', '.view', function(){
            view_record($(this).data('iddata'));
        });

        $("table#"+table_id).on('click', '.email', function(){
            let id = $(this).data('iddata');
            swal({
            title: "Aviso!", text: "¿Desea enviar correo del pedido?", type: "info", showCancelButton: true, confirmButtonColor: '#1274D9', confirmButtonText: 'Si, Enviar correo!', cancelButtonText: "Cancelar", closeOnConfirm: false, showLoaderOnConfirm: true
        }, function(isConfirm){
            if (isConfirm) {
                send_email(id);              
            }
        }); 
        });

        $("table#"+table_id).on('click', '.pdf', function(){
            generate_pdf($(this).data('iddata'));
        });

        let edit_record = (id) => {
            swal({
                title: "Aviso!", text: "¿Desea editar los datos del registro?", type: "info", showCancelButton: true, confirmButtonColor: '#1274D9', confirmButtonText: 'Si, editar!', cancelButtonText: "Cancelar", closeOnConfirm: false
            }, function(isConfirm){
                if (isConfirm)
                    $(location).attr('href', URL+'/'+id+'/edit');
                
            });
        };

        let dispatch_record = (id) => {
            const today = new Date().toISOString().split('T')[0];
            Swal.fire({
                title: "Fecha de despacho",
                html: `<input type="date" id="fecha_despacho" class="form-control" style="width: 100%;" value="${today}">`,
                showCancelButton: true,
                confirmButtonColor: '#1274D9',
                confirmButtonText: 'Despachar',
                cancelButtonText: "Cancelar",
                preConfirm: () => {
                    const fecha = document.getElementById('fecha_despacho').value;
                    if (!fecha) {
                        Swal.showValidationMessage("Debe seleccionar una fecha de despacho.");
                    }
                    return fecha;
                }
            }).then((result) => {
                console.log('result: ', result);
                if (!result.value) return;
                let fecha = result.value;
                let url = "{{ url('despachos/dispatch') }}";
                $.ajax({
                    url: url,
                    type: 'post',
                    data: { _token: TOKEN, fecha_despacho: fecha, order_id: id },
                    dataType: 'json',
                    success: function(response) {
                        Swal.fire({title: response.message, icon: response.type, html:true});
                        refresh_table();
                    },
                    error: function(xhr, status) {
                        console.log('error: ', xhr, status);
                        switch(xhr.status) {
                            case 401:
                                Swal.fire("Oops!", "Su sesión ha expirado.", "info");
                                setTimeout(function(){ location.reload(); }, 2000);
                                break;
                            case 404:
                                Swal.fire("Oops!", "Recurso no encontrado.", "error");
                                break;
                            default:
                                Swal.fire("Oops!", "¡El registro no pudo ser procesado!... Intente nuevamente.", "error");
                        }
                    }
                });
            });
        };
        
        let print_record = (id) => {
            window.open('print-order/' + id)
        };
        
        let edit_verified = (id) => {
            let url = '{{url('pedidos/verify-client/')}}';
            $.ajax({
                url : url+'/'+id, type : 'get', 
                success : function(check) {
                    refresh_table();
                    swal({title: check.title, text: check.text, type: check.type, html:true}); 
                },
                error : function(xhr, status) {
                    console.log('error: ', xhr, status);
                    switch(xhr.status) {
                        case 401:
                            swal("Oops!", "Su sesión ha expirado.", "info");
                            setTimeout(function(){      }, 2000);
                            break;
                        case 404:
                            swal("Oops!", "Recurso no encontrado.", "error");
                            break;
                        default:
                            //swal({title: check.title, text: check.text, type: check.type, html:true});                    
                            swal("Oops!", "¡El registro no pudo ser procesado!... Intente nuevamente.", "error");
                    }
                },
                complete:function(check){}
            }); 
        };
        
        let send_email = (id) => {
            //window.open('pedidos/pdf-mail/' + id)
            let url = '{{url('pedidos/pdf-mail/')}}';
            $.ajax({
                url : url+'/'+id, type : 'get', 
                success : function(check) {
                    swal({title: check.title, text: check.text, type: check.type, html:true});                    
                },
                error : function(xhr, status) {
                    console.log('error: ', xhr, status);
                    switch(xhr.status) {
                        case 401:
                            swal("Oops!", "Su sesión ha expirado.", "info");
                            setTimeout(function(){      }, 2000);
                            break;
                        case 404:
                            swal("Oops!", "Recurso no encontrado.", "error");
                            break;
                        default:
                            //swal({title: check.title, text: check.text, type: check.type, html:true});                    
                            swal("Oops!", "¡El registro no pudo ser procesado!... Intente nuevamente.", "error");
                    }
                },
                complete:function(check){}
            }); 
        };
        
        let generate_pdf = (id) => {
            window.open('pedidos/pdf/' + id)
        };
        
        let view_record = (id) => {
            window.location.href = 'view-order/' + id;
        };
        
        let update_record = (id, estatus) => {
            let status = estatus=='APROBADO' ? 'Aprobar' : 'Rechazar';
            swal({
                title: "Atención", text: "Confirme que desea "+ status +" este registro.", type: "warning", showCancelButton: true, confirmButtonColor: '#0D78CA', confirmButtonText: 'Si, '+status+'!', cancelButtonText: "Cancelar", closeOnConfirm: false, showLoaderOnConfirm: true,
            }, function() {
                //let url = URL+'/'+id;
                //send_request_method(url, 'delete', id);
                const URL_UPDATE_ESTATUS_ORDER = '{{url('update-estatus-order')}}';
                $.ajax({
                    url : URL_UPDATE_ESTATUS_ORDER, type : 'post', data: { _token : TOKEN, order_id: id, estatus: estatus  }, dataType : 'json',
                    success : function(check) {
                        swal({title: 'Genial!', text: check.message, type: check.type, html:true});
                        refresh_table();
                    },
                    error : function(xhr, status) {
                        console.log('error: ', xhr, status);
                        switch(xhr.status) {
                            case 401:
                                //swal("Oops!", "Su sesión ha expirado.", "info");
                                setTimeout(function(){      }, 2000);
                                break;
                            case 404:
                                //swal("Oops!", "Recurso no encontrado.", "error");
                                break;
                            default:
                                //swal("Oops!", "¡El registro no pudo ser procesado!... Intente nuevamente.", "error");
                        }
                    },
                    complete:function(check){}
                }); 

            });
        };

        let send_request_method = (url, type, id) => {
            $.ajax({
                url : url, type : type, data: { _token : TOKEN, id: id  }, dataType : 'json',
                success : function(check){
                    swal({title: check.title, text: check.text, type: check.type, html:true});
                    refresh_table();
                },
                error : function(xhr, status) {
                    console.log('error: ', xhr, status);
                    switch(xhr.status) {
                        case 401:
                            swal("Oops!", "Su sesión ha expirado.", "info");
                            setTimeout(function(){   location.reload();   }, 2000);
                            break;
                        case 404:
                            swal("Oops!", "Recurso no encontrado.", "error");
                            break;
                        default:
                            swal("Oops!", "¡El registro no pudo ser procesado!... Intente nuevamente.", "error");
                    }
                },
                complete:function(response){}
            });
        };  
        
           
    });

    set_active_sidebar_menu  ('Web', 'Categorias');
  
</script>

@endsection
