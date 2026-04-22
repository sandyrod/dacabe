@section('js_functions')
<script>
    
    const URL = '{{url('serials')}}';
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
                    {data: 'created_at', name: 'created_at'},
                    {data: 'company', name: 'company'},
                    {data: 'user', name: 'user'},
                    {data: 'quantity', name: 'quantity'},
                    {data: 'action', name: 'action'}                    
                ],
                bAutoWidth: false,
            };
    };

    set_select2();

    const Toast = Swal.mixin({
          toast: true,
          position: 'bottom-end',
          showConfirmButton: false,
          timer: 5000
    });
    
    let table = $('#'+table_id).DataTable( init_datatable() );
    
    let refresh_table = () => {
        table.draw();
    };

    $('#busqueda').on('keyup', function () {
        table.search( this.value ).draw(); 
    } );

    $('#btn_new').on('click', function () {
        add_company(); 
    });

    $('.save').on('click', function () {
        add_serial(); 
    });

    $('#btn_serial1').on('click', function () {
        copyToClipboard('hserial1'); 
    });

    $('#btn_serial2').on('click', function () {
        copyToClipboard('hserial2'); 
    });

    $('#btn_serial3').on('click', function () {
        copyToClipboard('hserial3'); 
    });

    let add_serial = () => {
        if ($('#serial').val()){
            let clave = generar_serial();
            $('#serial1').val( clave.substr(0,5) );   
            $('#hserial1').html( clave.substr(0,5) ); 
            $('#serial2').val( clave.substr(5,5) );   
            $('#hserial2').html( clave.substr(5,5) ); 
            $('#serial3').val( clave.substr(10,5) );  
            $('#hserial3').html( clave.substr(10,5) );    
            
            $.ajax({
               url :  "{{route('serials.store')}}", type : 'POST', data: { _token : TOKEN, company_id: $('#company_id').val() }, dataType : 'json',
                success : function(  response  ) {
                    refresh_table();
                    Toast.fire({
                        icon: 'success',
                        title: 'Serial generado con Éxito...'
                      });
                },
                error : function(xhr, status) {
                    err = xhr.responseJSON.errors.code[0] ? xhr.responseJSON.errors.code[0] : 'Error de Datos...'; 
                    Swal.fire("¡El registro no pudo ser procesado!", err, "error");
                }
            });
        } else {
            Swal.fire("¡Debe ingresar el Serial origen!", err, "error");
        }
    };

    let add_company = () => {
        Swal.fire({
          title: 'Nombre de la Empresa',
          icon: 'question',
          input: 'text',
          inputPlaceholder: 'Ingrese el Nombre de la Empresa',
          showLoaderOnConfirm: true,
          showCancelButton: true
        }).then(function (response) {
            if (response.value)
                get_code_company(response.value);
        });
    };

    let get_code_company = (name) => {
        Swal.fire({
          title: 'RIF de la Empresa',
          icon: 'question',
          input: 'text',
          inputPlaceholder: 'Min. 6 caracteres',
          showLoaderOnConfirm: true,
          showCancelButton: true
        }).then(function (response) {
            if (response.value)
                save_company(name, response.value);
        });
      
    };

    let save_company = (name, code) => {
        $.ajax({
            url :  "{{route('companies.store')}}", type : 'POST', data: { _token : TOKEN, name: name, code: code }, dataType : 'json',
            success : function(response){
                Toast.fire({
                    icon: 'success',
                    title: 'Empresa creada con Éxito.'
                  });

                var newOption = new Option(response.data.name, response.data.id, true, true);
                $('#company_id').append(newOption).trigger('change');
                $('#company_id option:selected').val(response.data.id);
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
                        err = xhr.responseJSON.errors.code[0] ? xhr.responseJSON.errors.code[0] : 'Error de Datos...'; 
                        Swal.fire("¡El registro no pudo ser procesado!", err, "error");
                }
            },
            complete:function(response){}
        });
        
    };

    let copyToClipboard = (element) => {
        let aux = document.createElement("input");
        aux.setAttribute("value", $('#'+element).html());
        document.body.appendChild(aux);
        aux.select();
        document.execCommand("copy");
        document.body.removeChild(aux);
        Toast.fire({icon: 'success', title: 'Texto copiado...'});
    };

    let desproteger = (cadena, semilla) => {                
        let codigo = "";
        if (semilla == "")
            semilla = 10;
        for (indice = 0; indice < cadena.length; indice++) {
            evalue = cadena.substr(indice, 1);
            transforme = evalue.charCodeAt(0);
            if (transforme >= 116 && transforme <= 121)
                transforme -= 25;
            transforme = transforme-(semilla+17);
            codigo += String.fromCharCode(transforme);
        }           
        return codigo;
    };

    let encripletra = (caracter, tabori, tabdes) => {                     
        if (caracter.match("^[a-zA-Z\(\)]+$")){
            caracter = caracter.toUpperCase();
            ArrAlfa = ["", "ABCDEFGHIJKLMNÑOPQRSTUVWXYZ", "OQDWLPISJXZNFHBEVGJUYKÑRMBA", "TGARYXCPWZUSJONMÑBHELQDKVFI"];
            return (ArrAlfa[tabdes].substr(ArrAlfa[tabori].indexOf(caracter),1));
        }

        return '';
    };
    
    let generar_serial = () => {
        let codigo = $('#serial').val();
        let clave = '';
        let des_sem = 3;
        let semilla = parseInt( desproteger ( codigo.substr(2,2),des_sem) );
        if ( Number.isNaN ( semilla ) )
            semilla = 0;
        semilla1 = parseInt( desproteger ( codigo.substr(0,2),semilla ) );
        if (Number.isNaN(semilla1))
            semilla1 = 0;
        let alfa = ((semilla1 % 2) == 0) ? 2 : 3;
           
        for (i=0; i<=codigo.length; i++)
            clave += encripletra (codigo.substr(i,1),1,alfa);

        return clave;
    }
        
    
    jQuery(function($) {

        $("table#"+table_id).on('click', '.delete', function(){
            delete_record($(this).data('iddata'));
        });

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

    set_active_sidebar_menu  ('SDCloud 2020', 'Seriales');
  
</script>

@endsection
