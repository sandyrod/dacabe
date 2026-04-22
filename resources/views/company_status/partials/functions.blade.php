@section('js_functions')
<script>
    
    const URL = '{{url('company-status')}}';
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
                    {data: 'description', name: 'description'},
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

    
    
    jQuery(function($) {

        $("table#"+table_id).on('click', '.edit', function(){
            edit_record($(this).data('iddata'));
        });

        $("table#"+table_id).on('click', '.delete', function(){
            delete_record($(this).data('iddata'));
        });

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
