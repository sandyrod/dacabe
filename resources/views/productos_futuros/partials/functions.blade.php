@section('js_functions')
<script>
    let rate = 0;
    const URL = '{{url('productos-futuros')}}';
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
                    {data: 'descripcion', name: 'descripcion'},
                    {data: 'codigo', name: 'codigo'},
                    {data: 'action', name: 'action'},
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
            swal({
                title: "Aviso!", text: "¿Desea editar los datos del registro?", type: "info", showCancelButton: true, confirmButtonColor: '#1274D9', confirmButtonText: 'Si, editar!', cancelButtonText: "Cancelar", closeOnConfirm: false
            }, function(isConfirm){
                if (isConfirm)
                    $(location).attr('href', URL+'/'+id+'/edit');
                
            });
        };
        
        let delete_record = (id) => {
            swal({
                title: "Atención", text: "Confirme que desea eliminar este registro.", type: "warning", showCancelButton: true, confirmButtonColor: '#D62E21', confirmButtonText: 'Si, Eliminar!', cancelButtonText: "Cancelar", closeOnConfirm: false, showLoaderOnConfirm: true,
            }, function() {
                let url = URL+'/'+id;
                send_request_method(url, 'delete', id);

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

    // set_active_sidebar_menu  ('Web', 'Categorias');
  
</script>

@endsection
