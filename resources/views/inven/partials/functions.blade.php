@section('js_functions')
<script>
    
    const URL = '{{url('companies')}}';
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
                    {data: 'code', name: 'code'},
                    {data: 'name', name: 'name'},
                    {data: 'phone', name: 'phone'},
                    {data: 'status', name: 'status'},
                    {data: 'action', name: 'action'}                    
                ],
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

    let toggle_permission = (module_id, company_id, toggle, mode) => {
        let expired_at = '';
        let uses = '';
        let title = (mode) ? 'Fecha Vencimiento' : 'Número de Usos';
        let placeholder = (mode) ? 'Ingrese fecha' : 'Ingrese Nro de Usos';
        if (toggle){
            Swal.fire({
              title: title,
              icon: 'question',
              input: 'text',
              inputPlaceholder: placeholder,
              showLoaderOnConfirm: true,
              showCancelButton: true
            }).then(function (response) {
                if (response.value){
                    if (mode)
                        expired_at = response.value;
                    else
                        uses = response.value;
                    apply_permissions(module_id, company_id, expired_at, uses);
                }
            });
        } else
            apply_permissions(module_id, company_id);
    }

    let apply_permissions = (module_id, company_id, expired_at='', uses='') => {
        $('.modules').html('<span class="text-center" style="color:#5CBCFF;"><i class="fa fa-spinner fa-pulse fa-lg fa-5x"></i></span>');
        let url = 'companies/set-permissions';
        $.ajax({
            url : url, type : 'POST', data: { _token : TOKEN, module_id: module_id, company_id: company_id, expired_at: expired_at, uses: uses }, dataType : 'json',
            success : function(response){
                refresh_permissions(response);
            },
            error : function(xhr, status) {
                console.log('error: ', xhr, status);
                switch(xhr.status) {
                    case 401:
                        Swal.fire("Oops!", "Su sesión ha expirado.", "info");
                        setTimeout(function(){   location.reload();   }, 2000);
                        break;
                    case 404:
                        Swal.fire("Oops!", "Recurso no encontrado.", "error");
                        break;
                    default:
                        Swal.fire("Oops!", "¡El registro no pudo ser procesado!... Intente nuevamente.", "error");
                }
            },
            complete:function(response){}
        });
    }

    let refresh_permissions = (response) => {
        let cad = 'No hay datos para mostrar';
        if (response.permissions){
            cad = '';
            response.permissions.forEach(function(valor) {
                let style = 'danger';
                let info = '';
                let action = 'Activar Permiso';
                let toggle = 1;
                let buttons = '<span class="btn btn-sm btn-outline-success hint--top-left" aria-label="Activar por Fecha Venc." onclick="toggle_permission('+valor.id+', '+valor.company_id+', '+toggle+', true)"><i class="fa fa-calendar"></i></span> <span class="btn btn-sm btn-outline-primary hint--top-left" aria-label="Activar por Nro de Usos" onclick="toggle_permission('+valor.id+', '+valor.company_id+', '+toggle+', false)"><i class="fa fa-list-ol"></i></span>';
                if (valor.companies_modules_id!=null){
                    toggle = 0;
                    style = 'success';
                    action = 'Quitar Permiso';
                    let info_text = '';
                    if (valor.expired_at)
                        info_text = 'ACTIVO: Expira el ' + valor.expired_at;
                    if (valor.uses)
                        info_text += 'ACTIVO: Usos: ' + valor.uses;
                    if (info_text)
                        info = '<span class="hint--top-right" aria-label="'+info_text+'"><i class="fa fa-check-circle text-green"></i></span>';
                    
                    buttons = '<span class="btn btn-sm btn-outline-danger hint--top-left" aria-label="Eliminar Permiso en Módulo" onclick="toggle_permission('+valor.id+', '+valor.company_id+', '+toggle+')"><i class="fa fa-trash"></i></span>';
                } else
                    info = '<span class="hint--top-right" aria-label="INACTIVO"><i class="fa fa-times-circle text-red"></i></span>';
                
                let span = '<span class="float-right">'+ buttons + '</span>';
                cad += '<li class="nav-item"><a href="#" class="nav-link">'+info+' <span> '+valor.name+'</span>'+span+'</a></li>';
            });
        }
        $('.modules').html(cad);
    }

    
    
    jQuery(function($) {

        $("table#"+table_id).on('click', '.permission', function(){
            edit_permission($(this).data('iddata'), $(this).data('namedata'));
        });

        $("table#"+table_id).on('click', '.edit', function(){
            edit_record($(this).data('iddata'));
        });

        $("table#"+table_id).on('click', '.delete', function(){
            delete_record($(this).data('iddata'));
        });


        $("table#"+table_id).on('click', '.change', function(){
            change_status($(this).data('iddata'));
        });

        let edit_permission = (id, name) => {
            $('.modal-title').html(name);
            $('.modules').html('<span class="text-center" style="color:#5CBCFF;"><i class="fa fa-spinner fa-pulse fa-lg fa-5x"></i></span>');
            load_permissions(id);
            $('#company-modal').modal('toggle');
        };

        let load_permissions = (id) => {
            let url = 'companies/get-permissions/'+id;
            
            $.ajax({
                url : url, type : 'GET', data: { _token : TOKEN, id: id  }, dataType : 'json',
                success : function(response){
                    refresh_permissions(response);
                },
                error : function(xhr, status) {
                    console.log('error: ', xhr, status);
                    switch(xhr.status) {
                        case 401:
                            Swal.fire("Oops!", "Su sesión ha expirado.", "info");
                            setTimeout(function(){   location.reload();   }, 2000);
                            break;
                        case 404:
                            Swal.fire("Oops!", "Recurso no encontrado.", "error");
                            break;
                        default:
                            Swal.fire("Oops!", "¡El registro no pudo ser procesado!... Intente nuevamente.", "error");
                    }
                },
                complete:function(response){}
            });            
        };

        let edit_record = (id) => {
            Swal.fire({
                title: "Aviso!", text: "¿Desea editar los datos del registro?", icon: "info", showCancelButton: true, confirmButtonColor: '#1274D9', confirmButtonText: 'Si, editar!', cancelButtonText: "Cancelar"
            }).then(function (response) {
                if (response.value)
                    $(location).attr('href', URL+'/'+id+'/edit');
                
            });
        };
        
        let delete_record = (id) => {
            Swal.fire({
                title: "Atención", text: "Confirme que desea eliminar este registro.", icon: "warning", showCancelButton: true, confirmButtonColor: '#D62E21', confirmButtonText: 'Si, Eliminar!', cancelButtonText: "Cancelar", showLoaderOnConfirm: true,
            }).then(function (response) {
                let url = URL+'/'+id;
                if (response.value)
                    send_request_method(url, 'delete', id);
            });
        };
       
        let change_status = (id) => {
            //let opciones = {1:'Activo', 2:'Inactivo', 3:'Suspendido'}
            let opciones = @json($status);
            Swal.fire({
              title: 'Estatus del Cliente',
              icon: 'question',
              input: 'select',
              inputOptions: opciones,
              inputPlaceholder: 'Seleccione opción...',
              showLoaderOnConfirm: true,
              showCancelButton: true
            }).then(function (response) {
                if (response.value){
                    let url = 'companies/change-status/'+id;
                    send_request_method(url, 'post', response.value);
                }
            });

        };

        let send_request_method = (url, type, id) => {
            $.ajax({
                url : url, type : type, data: { _token : TOKEN, id: id  }, dataType : 'json',
                success : function(check){
                    refresh_table();
                    Swal.fire({title: check.title, text: check.text, icon: check.type});
                },
                error : function(xhr, status) {
                    console.log('error: ', xhr, status);
                    switch(xhr.status) {
                        case 401:
                            Swal.fire("Oops!", "Su sesión ha expirado.", "info");
                            setTimeout(function(){   location.reload();   }, 2000);
                            break;
                        case 404:
                            Swal.fire("Oops!", "Recurso no encontrado.", "error");
                            break;
                        default:
                            Swal.fire("Oops!", "¡El registro no pudo ser procesado!... Intente nuevamente.", "error");
                    }
                },
                complete:function(response){}
            });
        };  
        
           
    });

    set_active_sidebar_menu  ('Seguridad', 'Empresas');
  
</script>

@endsection
