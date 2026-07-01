@section('js_functions')
<script>
    
    const URL = '{{url('vendedores')}}';
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
                    {data: 'name', name: 'name'},
                    {data: 'document', name: 'document'},
                    {data: 'email', name: 'email'},
                    {data: 'estatus', name: 'estatus'},
                    {data: 'zona', name: 'zona'},
                    {data: 'deposito', name: 'deposito'},
                    {data: 'recargo', name: 'recargo'},
                    {data: 'action', name: 'action'}                    
                ],
                createdRow: function (row, data) {
                    const estatusRaw = (data.estatus || '').toString().toUpperCase();
                    const isSuspendido = estatusRaw.includes('SUSPENDIDO');
                    if (isSuspendido) {
                        $(row).addClass('fila-vendedor-suspendido');
                    }
                },
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

        $("table#"+table_id).on('click', '.edit', function(){
            edit_record($(this).data('iddata'));
        });

        $("table#"+table_id).on('click', '.delete', function(){
            delete_record($(this).data('iddata'));
        });

        $("table#"+table_id).on('click', '.toggle-status', function(){
            toggle_status($(this).data('iddata'), $(this));
        });


        let edit_record = (id) => {
            Swal.fire({
                title: "Aviso!", 
                text: "¿Desea editar los datos del registro?", 
                icon: "info", 
                showCancelButton: true, 
                confirmButtonColor: '#1274D9', 
                confirmButtonText: 'Si, editar!', 
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed)
                    $(location).attr('href', URL+'/'+id+'/edit');
            });
        };
        
        let delete_record = (id) => {
            Swal.fire({
                title: "Atención", 
                text: "Confirme que desea eliminar este registro.", 
                icon: "warning", 
                showCancelButton: true, 
                confirmButtonColor: '#D62E21', 
                confirmButtonText: 'Si, Eliminar!', 
                cancelButtonText: "Cancelar",
                showLoaderOnConfirm: true
            }).then((result) => {
                if (result.isConfirmed) {
                    let url = URL+'/'+id;
                    send_request_method(url, 'delete', {id: id});
                }
            });
        };

        let toggle_status = (id, trigger) => {
            const isSuspendido = $(trigger).attr('aria-label') === 'Habilitar';
            const nextStatus = isSuspendido ? 'ACTIVO' : 'SUSPENDIDO';
            const actionText = isSuspendido ? 'habilitar' : 'suspender';

            Swal.fire({
                title: "Confirmación",
                text: `¿Desea ${actionText} este vendedor?`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: isSuspendido ? '#28a745' : '#e67e22',
                confirmButtonText: isSuspendido ? 'Si, habilitar' : 'Si, suspender',
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    const url = URL + '/' + id + '/estatus';
                    send_request_method(url, 'patch', { estatus: nextStatus });
                }
            });
        };

        let send_request_method = (url, type, payload = {}) => {
            $.ajax({
                url : url,
                type : type,
                data: Object.assign({ _token : TOKEN }, payload),
                dataType : 'json',
                success : function(check){
                    Swal.fire({
                        title: check.title, 
                        text: check.text, 
                        icon: check.type, 
                        html: true
                    });
                    refresh_table();
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

    //set_active_sidebar_menu  ('Web', 'Categorias');
  
</script>

@endsection
