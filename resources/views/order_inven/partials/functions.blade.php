@section('js_functions')
    <script>
        let rate = 0;
        const DESCUENTO = {{ @$descuento }};
        const URL_DESCUENTO = '{{ url('guardar-descuento') }}';
        const URL_RATE = '{{ url('ultima-tasa-bcv') }}';
        const URL = '{{ url('order-inven') }}';
        const URL_PHOTO = '{{ url('store-inven-photo') }}';
        const URL_PRODUCTS = '{{ url('get-order-inven') }}';
        const URL_SEARCH_CLIENT = '{{ url('search-client') }}';
        const URL_SEARCH_CLIENT_SENIAT = '{{ url('consulta-cliente-seniat') }}';
        const URL_ADDCART = '{{ url('add-to-cart') }}';
        const URL_MODIFY_QTY = '{{ url('modify-qty') }}';
        const URL_DELETE_ITEM = '{{ url('delete-item') }}';
        const URL_SAVE_ORDER = '{{ url('save-order') }}';
        const URL_BACK = '{{ url('inicio') }}';
        const TOKEN = '{{ csrf_token() }}';
        let table_id = 'dynamic-table';
        const clients = @json(@$clients);
        let all_inven = null;

        let init_datatable = () => {
            return {
                language: {
                    url: '{{ asset('theme/plugins/datatables/venezuela.json') }}'
                },
                responsive: true,
                "processing": true,
                "serverSide": true,
                "autoWidth": false,
                searchDelay: 1000,
                ajax: {
                    "url": URL,
                    data: function(d) {
                        d.datatable = 1;
                    }
                },
                columns: [{
                        data: 'DESCR',
                        name: 'DESCR'
                    },
                    {
                        data: 'CODIGO',
                        name: 'CODIGO'
                    },
                    {
                        data: 'caracteristicas',
                        name: 'caracteristicas'
                    },
                    {
                        data: 'action',
                        name: 'action'
                    }
                ],
                bAutoWidth: false,
            };
        };

        let assignDepos = () => {
            $('#CDEPOS').val($('#CCDEPOS').val());
            //loadProducts();
        };

        let table = $('#' + table_id).DataTable(init_datatable());

        let refresh_table = () => {
            table.draw();
        };

        $('#busqueda').on('keyup', function() {
            table.search(this.value).draw();
        });

        let minusProduct = (item_id) => {
            modifyQuantity('minus', item_id)
            /*
            let qty = parseInt($('#qty_' + item_id).val());
            if (qty > 1) {
                qty = $('#qty_' + item_id).val(qty - 1);
            } else {
                Swal.fire("Oops!", "No puede disminuir la cantidad.", "error");
            } 
            */
        }

        let plusProduct = (item_id) => {
            modifyQuantity('plus', item_id)
            /*
            let qty = parseInt($('#qty_' + item_id).val()) + 1;
            $('#qty_' + item_id).val(qty);
            */
        }

        let saveQuantity = (item_id) => {
            qty = $('#qty_' + item_id).val();
            if (qty > 0) {
                modifyQuantity('set', item_id, qty);
            } else {
                Swal.fire("Oopsss!", "La Cantidad debe ser mayor a cero...", "error");
            }
        }

        let formatNumberInside = (number) => {
            let fixedNum = number.toFixed(2);
            let parts = fixedNum.split(".");
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            if (parts[1]) {
                return parts.join(",");
            }
            return parts[0];
        };

        /*
        let formatNumberInside = (number) => {
            if (number !== undefined && number !== null) {
                let numStr = number.toString();
                let parts = numStr.split(".");
                
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                
                if (parts[1]) {
                    parts[1] = parts[1].substring(0, 2);
                    return parts.join(",");
                }
                
                return parts[0];
            }
        };
        */

        let modifyQuantity = (operation, item_id, qty = 1) => {
            $.ajax({
                url: URL_MODIFY_QTY,
                type: 'post',
                data: {
                    _token: TOKEN,
                    operation: operation,
                    item_id: item_id,
                    qty: qty
                },
                dataType: 'json',
                success: function(response) {
                    $('#qty_' + item_id).val(response.qty);
                    //Swal.fire("Genial!", "Cantidad actualizada...", "success");
                    //Swal.fire({title: response.title, text: response.text, type: response.type, html:true});
                    Livewire.emit('updateCarts')

                    if (response && response.data) {
                        let total = response.data.reduce((acumulador, producto) => {
                            return acumulador + (producto.cantidad * producto.precio);
                        }, 0);

                        let subtotal = 0;
                        response.data.forEach((item) => {
                            if (item.id == item_id) {
                                subtotal = (item.cantidad * item.precio);
                            }
                        });

                        let n = response.data.reduce((acumulador, producto) => {
                            return acumulador + (producto.cantidad);
                        }, 0);

                        console.log(subtotal)

                        $('#subtotal_' + item_id).html(formatNumberInside(subtotal))
                        $('#n').html('Items: ' + formatNumberInside(n))
                        $('#grand_total').html('TOTAL $ ' + formatNumberInside(total))
                    }

                    const btn_qty = document.getElementById('btn_qty_' + item_id);
                    btn_qty.classList.add('animate');
                    btn_qty.classList.remove('btn-primary');
                    btn_qty.classList.add('btn-success');

                    setTimeout(() => {
                        btn_qty.classList.remove('animate');
                        btn_qty.classList.remove('btn-success');
                        btn_qty.classList.add('btn-primary');
                    }, 500);

                },
                error: function(xhr, status) {
                    console.log('error: ', xhr, status);
                    switch (xhr.status) {
                        case 401:
                            Swal.fire("Oops!", "Su sesión ha expirado.", "info");
                            setTimeout(function() {}, 2000);
                            break;
                        case 404:
                            Swal.fire("Oops!", "Recurso no encontrado.", "error");
                            break;
                        default:
                            Swal.fire("Oops!", "¡El registro no pudo ser procesado!... Intente nuevamente.",
                                "error");
                    }
                },
                complete: function(response) {}
            });
        };

        let deleteProduct = (item_id) => {
            $.ajax({
                url: URL_DELETE_ITEM,
                type: 'post',
                data: {
                    _token: TOKEN,
                    item_id: item_id
                },
                dataType: 'json',
                success: function(response) {
                    //Swal.fire({title: response.title, text: response.text, type: response.type, html:true});
                    if (response.type == 'success') {
                        $('#row_' + item_id).fadeOut(500, function() {
                            $(this).remove();
                        });
                        if (response && response.data) {
                            let total = response.data.reduce((acumulador, producto) => {
                                return acumulador + (producto.cantidad * producto.precio);
                            }, 0);

                            let n = response.data.reduce((acumulador, producto) => {
                                return acumulador + (producto.cantidad);
                            }, 0);


                            $('#n').html('Items: ' + formatNumberInside(n))
                            $('#grand_total').html('TOTAL $ ' + formatNumberInside(total))
                        }
                    } else {
                        Swal.fire("Oops!", "No se pudo eliminar.", "error");
                    }
                    Livewire.emit('updateCarts')
                },
                error: function(xhr, status) {
                    console.log('error: ', xhr, status);
                    switch (xhr.status) {
                        case 401:
                            Swal.fire("Oops!", "Su sesión ha expirado.", "info");
                            setTimeout(function() {}, 2000);
                            break;
                        case 404:
                            Swal.fire("Oops!", "Recurso no encontrado.", "error");
                            break;
                        default:
                            Swal.fire("Oops!", "¡El registro no pudo ser procesado!... Intente nuevamente.",
                                "error");
                    }
                },
                complete: function(response) {}
            });
        }
        /*
        $('#rif').on('blur', function() {
            let rif = $(this).val();
            searchByRif(rif);
        });
        */

        $('#btn_new').on('click', function() {
            $('#search_client').val(null);
            $('#search_client').trigger('change');
            $('#rif').val('');
            $('#descripcion').val('');
            $('#telefono').val('');
            $('#email').val('');
            $('#rif').attr('readonly', false);
            $('#descripcion').attr('readonly', false);
            $('#telefono').attr('readonly', false);
            $('#email').attr('readonly', false);
            $('#div_foto_rif').show();
        });

        $('#btn_search').on('click', function() {
            let rif = $('#rif').val();
            if (rif) {
                // Mostrar modal y cargar el contenido de la URL SENIAT
                // Si la modal no existe, créala dinámicamente
                if ($('#modal_seniat').length === 0) {
                    $('body').append(`
                    <div class="modal fade" id="modal_seniat" tabindex="-1" role="dialog" aria-labelledby="modalSeniatLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalSeniatLabel">Información SENIAT</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="text-center"><i class="fa fa-spinner fa-spin"></i> Cargando información...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
                }
                $('#modal_seniat .modal-body').html(
                    '<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Cargando información...</div>'
                );
                $('#modal_seniat').modal('show');
                $.ajax({
                    url: URL_SEARCH_CLIENT_SENIAT,
                    type: 'POST',
                    data: {
                        _token: TOKEN,
                        rif: rif
                    },
                    success: function(response) {
                        console.log(response);
                        // Mostrar el contenido HTML recibido en la modal
                        $('#modal_seniat .modal-body').html(response.html);
                    },
                    error: function(xhr, status, error) {
                        $('#modal_seniat .modal-body').html(
                            '<div class="alert alert-danger">No se pudo cargar la información del SENIAT.</div>'
                        );
                    }
                });
            }
        });

        let searchByRif = (rif) => {
            if (rif) {
                $('#descripcion').val('');
                $.ajax({
                    url: URL_SEARCH_CLIENT,
                    type: 'POST',
                    data: {
                        _token: TOKEN,
                        rif: rif
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.data) {
                            $('#descripcion').val(response.data.NOMBRE);
                            $('#telefono').val(response.data.TELEFONO);
                            $('#email').val(response.data.EMAIL);
                            $('#descripcion').attr('readonly', true);
                            if (response.data.TELEFONO && response.data.TELEFONO != '') {
                                $('#telefono').attr('readonly', true);
                            } else {
                                $('#telefono').attr('readonly', false);
                            }
                            if (response.data.EMAIL && response.data.EMAIL != '') {
                                $('#email').attr('readonly', true);
                            } else {
                                $('#email').attr('readonly', false);
                            }
                            $('#div_foto_rif').hide();
                        } else {
                            Swal.fire("Oops!",
                                "RIF No encontrado! Si desea solicitar la creación de un nuevo Cliente debe completar toda la información a continuación...",
                                "info");
                            $('#descripcion').val('');
                            $('#telefono').val('');
                            $('#email').val('');
                            $('#descripcion').attr('readonly', false);
                            $('#telefono').attr('readonly', false);
                            $('#email').attr('readonly', false);
                            $('#div_foto_rif').show();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('error: ', xhr, status);
                    }
                });
            }
        };

        let saveInvoice = () => {
            let factura = window.lleva_factura;
            let porc_retencion = window.porc_retencion;
            let esClienteNuevo = !$('#search_client').val();
            
            $('#btn_send').prop('disabled', true);
            $('#btn_send').text('Procesando...');
            let descripcion = $('#descripcion').val();
            let rif = $('#rif').val();
            let codcli = $('#codcli').val();
            let telefono = $('#telefono').val();
            let email = $('#email').val();
            let observations = $('#observations').val();
            let conditions = $('#conditions').val();
            let cliageret = $('#cliageret').val();
            let photo = $('#photo')[0].files[0]; // Captura el archivo de imagen

            if (rif && descripcion && telefono) {
                let formData = new FormData(); // Crea un nuevo objeto FormData
                formData.append('_token', TOKEN); // Añade el token
                formData.append('rif', rif); // Añade el RIF
                formData.append('codcli', codcli); // Añade el CODCLI
                formData.append('descripcion', descripcion); // Añade la descripción
                formData.append('telefono', telefono); // Añade el teléfono
                formData.append('email', email); // Añade el email
                formData.append('observations', observations);
                formData.append('conditions', conditions);
                formData.append('factura', factura);
                formData.append('porc_retencion', porc_retencion);
                formData.append('cliageret', cliageret);
                formData.append('is_new_client', esClienteNuevo ? '1' : '0');
                if (photo) {
                    formData.append('photo', photo); // Añade la imagen
                }

                $.ajax({
                    url: URL_SAVE_ORDER,
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function(response) {
                        Livewire.emit('updateCarts');

                        let title = (response && response.alert_title) ? response.alert_title : ((response && response.title) ? response.title : 'Genial!');
                        let icon = (response && response.alert_icon) ? response.alert_icon : 'success';
                        let html = (response && response.alert_html) ? response.alert_html : ((response && response.text) ? response.text : 'Pedido Guardado');

                        Swal.fire({
                            title: title,
                            html: html,
                            icon: icon,
                            confirmButtonText: 'Aceptar',
                            allowOutsideClick: false,
                            allowEscapeKey: false
                        }).then((result) => {
                            if (!result || result.isConfirmed || result.value === true) {
                                $(location).attr('href', URL_BACK);
                            }
                        });
                    },
                    error: function(xhr, status) {
                        console.log('error: ', xhr, status);
                        switch (xhr.status) {
                            case 401:
                                Swal.fire("Oops!", "Su sesión ha expirado.", "info");
                                setTimeout(function() {}, 2000);
                                break;
                            case 404:
                                Swal.fire("Oops!", "Recurso no encontrado.", "error");
                                break;
                            default:
                                Swal.fire("Oops!",
                                    "¡El registro no pudo ser procesado!... Intente nuevamente.",
                                    "error");
                        }
                    },
                    complete: function() {
                        $('#btn_send').prop('disabled', false);
                        $('#btn_send').text('Enviar');
                    }
                });
            } else {
                Swal.fire("Oops!", "Debe indicar el RIF, Nombre y Teléfono del Cliente!", "error");
            }
        };

        $('#uploadForm').on('submit', function(e) {
            e.preventDefault(); // Evita el envío normal del formulario
            // Declarar la variable globalmente si no existe
            window.lleva_factura = '';
            window.porc_retencion = 0;

            let esClienteNuevo = !$('#search_client').val();
            let cliageret = parseInt($('#cliageret').val()) || 0;

            let pedirPorcentajeRetencion = () => {
                Swal.fire({
                    title: 'Porcentaje de Retención',
                    icon: 'question',
                    input: 'select',
                    inputOptions: {
                        '0':   '0% — Sin retención',
                        '75':  '75% — Retención parcial',
                        '100': '100% — Retención total'
                    },
                    inputValue: '75',
                    inputPlaceholder: 'Seleccione el porcentaje',
                    showCancelButton: true,
                    confirmButtonText: 'Guardar',
                    cancelButtonText: 'Cancelar',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    inputValidator: (value) => {
                        if (!['0', '75', '100'].includes(value)) {
                            return 'Seleccione un porcentaje válido';
                        }
                    }
                }).then((result) => {
                    window.porc_retencion = result.value !== undefined ? result.value : 0;
                    saveInvoice();
                });
            };

            Swal.fire({
                title: 'Lleva factura?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'SI',
                cancelButtonText: 'NO',
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then((result) => {
                if (result.value !== true) {
                    window.lleva_factura = 'NO';
                    saveInvoice();
                    return;
                }
                window.lleva_factura = 'SI';

                if (esClienteNuevo) {
                    // Cliente nuevo: preguntar si es Contribuyente Especial
                    Swal.fire({
                        title: 'El cliente es Contribuyente Especial?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'SI',
                        cancelButtonText: 'NO',
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then((result) => {
                        if (result.value === true) {
                            window.contribuyente_especial = 'SI';
                            pedirPorcentajeRetencion();
                        } else {
                            window.contribuyente_especial = 'NO';
                            saveInvoice();
                        }
                    });
                } else if (cliageret == 1) {
                    // Cliente existente con CLIAGERET=1: preguntar directamente el porcentaje
                    window.contribuyente_especial = 'SI';
                    pedirPorcentajeRetencion();
                } else {
                    // Cliente existente sin retención
                    window.contribuyente_especial = 'NO';
                    saveInvoice();
                }
            });

            
            
            
        });

        let saveOrder = () => {
            let descripcion = $('#descripcion').val();
            let rif = $('#rif').val();
            let telefono = $('#telefono').val();
            let email = $('#email').val();
            if (rif && descripcion && telefono) {
                $.ajax({
                    url: URL_SAVE_ORDER,
                    type: 'post',
                    data: {
                        _token: TOKEN,
                        rif: rif,
                        descripcion: descripcion,
                        telefono: telefono,
                        email: email
                    },
                    dataType: 'json',
                    success: function(check) {
                        Livewire.emit('updateCarts')
                        Swal.fire({
                            title: 'Genial!',
                            text: 'Pedido Guardado',
                            type: 'success'
                        });
                        setTimeout(function() {
                            $(location).attr('href', URL_BACK);
                        }, 5000);

                    },
                    error: function(xhr, status) {
                        console.log('error: ', xhr, status);
                        switch (xhr.status) {
                            case 401:
                                Swal.fire("Oops!", "Su sesión ha expirado.", "info");
                                setTimeout(function() {}, 2000);
                                break;
                            case 404:
                                Swal.fire("Oops!", "Recurso no encontrado.", "error");
                                break;
                            default:
                                Swal.fire("Oops!",
                                    "¡El registro no pudo ser procesado!... Intente nuevamente.",
                                    "error");
                        }
                    },
                    complete: function(response) {}
                });
            } else {
                Swal.fire("Oops!", "Debe indicar el RIF, Nombre y Teléfono del Cliente!", "error");
            }
        }

        let showProductDetails = (product_code) => {
            let product = all_inven.filter((item) => item.CODIGO == product_code);
            console.warn(product[0].fotos);
            console.warn(product[0].informacion);
            let fotos = product && product[0].fotos ? product[0].fotos : null;
            let informacion = product && product[0].informacion ? product[0].informacion : null;
            let detalle = informacion && informacion.detalle ? informacion.detalle : '';
            let observaciones = informacion && informacion.observaciones ? informacion.observaciones : '';
            let html = '';
            html += '<div class="card bg-product w-100">';
            html += '<div class="card-body">';
            html += '<span class="text-reset"><h5 class="card-title mb-1 text-primary w-100">' + detalle +
                '</h5></span>';
            html += '<span class="text-reset"><small class="text-muted">' + observaciones + '</small></span>';

            let photo = '{{ asset('storage/products/') }}';
            fotos.forEach((foto) => {
                html +=
                    '<div class="bg-image hover-zoom ripple ripple-surface ripple-surface-light mt-2" data-mdb-ripple-color="light">';
                html += '<a href="' + photo + '/' + foto.foto +
                    '" target="_blank"><div class="image-container">';
                html += '<img class="card-img-top w-100" src="' + photo + '/' + foto.foto + '" alt="Foto">';
                html += '</div></a></div>';
            });
            html += '</div></div>';
            $('#product_detail').html(html);
            $('#modal_information').modal('show');
        };


        let addProduct = (codigo, precio = 0, cantidad = 1, modo = '$', stock_minimo = 0) => {
            if (precio > 0) {
                Swal.fire({
                    title: 'Indique la cantidad',
                    icon: 'success',
                    input: 'number',
                    inputValue: 1,
                    preConfirm: (value) => {
                        if (parseInt(value) > parseInt(cantidad)) {
                            Swal.showValidationMessage(`El valor no puede ser mayor a ${cantidad}`);
                        }
                        if (parseInt(value) < parseInt(stock_minimo)) {
                            Swal.showValidationMessage(
                                `El valor no puede ser inferior a ${stock_minimo}. <br> Para pedidos por esa cantidad debe contactar a inversionesdacabeonline@gmail.com`
                            );
                        }
                        return value; // Retornar el valor para que se pueda usar en el then
                    },
                    inputPlaceholder: 'Cantidad',
                    showLoaderOnConfirm: true,
                    showCancelButton: true
                }).then(function(response) {
                    if (response.value && response.value > 0) {
                        qty = response.value ? response.value : 1;
                        addProductCallback(codigo, qty, precio, modo);
                    }
                });
            }
        };

        let addProductCallback = (codigo, cantidad, precio, modo) => {
            $.ajax({
                url: URL_ADDCART,
                type: 'post',
                data: {
                    _token: TOKEN,
                    codigo: codigo,
                    cantidad: cantidad,
                    precio,
                    precio,
                    modo: modo,
                    cdepos: $('#CDEPOS').val()
                },
                dataType: 'json',
                success: function(response) {
                    if (response.type == 'error') {
                        Swal.fire("Atención!", response.message, "error");
                    } else {
                        Swal.fire("Genial!", "Producto agregado al pedido...", "success");
                        const cartCount = document.querySelector('.cart-count');
                        //let count = parseInt(cartCount.textContent);
                        //cartCount.textContent = count + 1;

                        console.warn(response)
                        if (response.qty) {
                            $('#disp_' + codigo).html(response.qty);
                        }

                        // Animación
                        const cart = document.getElementById('cart');
                        cart.classList.add('animate');

                        setTimeout(() => {
                            cart.classList.remove('animate');
                        }, 300);

                        // Obtener la tarjeta del producto
                        const productCard = document.querySelector(`#div_${codigo}`);

                        // Agregar la clase shake
                        productCard.classList.add('shake');

                        // Eliminar la clase shake después de la animación
                        setTimeout(() => {
                            productCard.classList.remove('shake');
                        }, 1000);

                        Livewire.emit('updateCarts')
                    }
                },
                error: function(xhr, status) {
                    console.log('error: ', xhr, status);
                    switch (xhr.status) {
                        case 401:
                            Swal.fire("Oops!", "Su sesión ha expirado.", "info");
                            setTimeout(function() {}, 2000);
                            break;
                        case 404:
                            Swal.fire("Oops!", "Recurso no encontrado.", "error");
                            break;
                        default:
                            Swal.fire("Oops!", "¡El registro no pudo ser procesado!... Intente nuevamente.",
                                "error");
                    }
                },
                complete: function(response) {}
            });
        };

        jQuery(function($) {
            $(".select2").select2({
                language: "es"
            });

            $('#rif').on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    return false;
                }
            });
            $('#search_client').on('change', function() {
                let selectedClientId = $(this).val();
                let selectedClient = clients.find(client => client.RIF == selectedClientId);
                if (selectedClient) {
                    $('#rif').val(selectedClient.RIF);
                    console.log(selectedClient);
                    console.log(selectedClient.CODCLI);
                    $('#codcli').val(selectedClient.CODCLI);
                    $('#descripcion').val(selectedClient.NOMBRE);
                    $('#telefono').val(selectedClient.TELEFONO);
                    $('#email').val(selectedClient.EMAIL);
                    $('#porc_retencion').val(0);
                    if (selectedClient.RETENCION && selectedClient.RETENCION > 0) {
                        $('#porc_retencion').val(selectedClient.RETENCION);
                    }
                    $('#cliageret').val(selectedClient.CLIAGERET);
                    $('#rif').attr('readonly', true);
                    $('#descripcion').attr('readonly', true);
                    if (selectedClient.TELEFONO && selectedClient.TELEFONO != '') {
                        $('#telefono').attr('readonly', true);
                    } else {
                        $('#telefono').attr('readonly', false);
                    }
                    if (selectedClient.EMAIL && selectedClient.EMAIL != '') {
                        $('#email').attr('readonly', true);
                    } else {
                        $('#email').attr('readonly', false);
                    }
                    $('#div_foto_rif').hide();
                } else {
                    $('#descripcion').val('');
                    $('#telefono').val('');
                    $('#email').val('');
                    $('#cliageret').val('');
                    $('#porc_retencion').val('');
                    $('#descripcion').attr('readonly', false);
                    $('#telefono').attr('readonly', false);
                    $('#email').attr('readonly', false);
                    $('#div_foto_rif').show();
                }
            });

            $('#CGRUPO').on('change', function() {
                //loadProducts();
            });

            $('#btn_filter').on('click', function() {
                loadProducts();
            });
            
            $('#search').on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    loadProducts();
                }
            });
           
            $("table#" + table_id).on('click', '.edit', function() {
                edit_record($(this).data('iddata'));
            });

            $("table#" + table_id).on('click', '.photos', function() {
                edit_photos($(this).data('iddata'));
            });

            $("table#" + table_id).on('click', '.delete', function() {
                delete_record($(this).data('iddata'));
            });

            let edit_photos = (id) => {
                $('#descr_prod').html(id);
                $('#codigo_inven').val(id);
                let formData = new FormData();
                formData.append('_token', TOKEN);
                formData.append('product_id', id);
                formData.append('mode', 'get');

                $('.inven-photos').html(
                    '<div class="col-md-12"><div class="spinner-border" role="status"><span class="sr-only">Cargando...</span></div></div>'
                );
                $.ajax({
                    url: URL_PHOTO,
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        let html = '';
                        if (response.data) {
                            response.data.forEach((item) => {
                                html +=
                                    '<div class="col-md-6"><div class="card"><img class="w-100" src="' +
                                    '{{ asset('storage/products/') }}/' + item.foto +
                                    '" alt="Foto">';
                                html += '<div class="card-body"><a href="#" data-iddata="' +
                                    item.id +
                                    '" class="btn_delete_photo btn btn-danger btn-block">Eliminar</a></div></div></div>';
                            });
                        }
                        $('.inven-photos').html(html);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log(textStatus)
                    }
                });
                $('#modal_photos').modal('show');

            };

            $(document).ready(function() {

                // Handle export to Excel button click
                $(document).on('click', '#export-excel-btn', function(e) {
                    e.preventDefault();

                    // Get current filter values
                    const cdepos = $('#CDEPOS').val();
                    const cgrupo = $('#CGRUPO').val();
                    const search = $('#search').val();

                    // Get the base URL from the button's data attribute
                    const exportUrl = $(this).data('export-url') || '/order-inven/export-products';
                    const params = new URLSearchParams();

                    if (cdepos) params.append('cdepos', cdepos);
                    //if (cgrupo && cgrupo !== 'TODOS') params.append('cgrupo', cgrupo);
                    params.append('cgrupo', cgrupo);
                    if (search) params.append('search', search);

                    // Redirect to the export URL with filters
                    window.location.href = `${exportUrl}?${params.toString()}`;
                });

                $('#btn_photo').on('click', function() {
                    $('#uploadPhotoForm').submit(); // Disparar el evento de envío del formulario
                });

                $(document).on('click', '.btn_delete_photo', function() {
                    let product_id = $('#codigo_inven').val();
                    let TOKEN = $('#_token').val();
                    let formData = new FormData();
                    formData.append('_token', TOKEN);
                    formData.append('photo', photo);
                    formData.append('product_id', product_id);
                    formData.append('id', $(this).data('iddata'));
                    formData.append('mode', 'delete');
                    $('.inven-photos').html(
                        '<div class="col-md-12"><div class="spinner-border" role="status"><span class="sr-only">Cargando...</span></div></div>'
                    );
                    $.ajax({
                        url: URL_PHOTO,
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function(response) {
                            swal({
                                title: response.title,
                                text: response.text,
                                type: response.type,
                                html: true
                            });
                            let html = '';
                            if (response.data) {
                                response.data.forEach((item) => {
                                    html +=
                                        '<div class="col-md-6"><div class="card"><img class="w-100" src="' +
                                        '{{ asset('storage/products/') }}/' +
                                        item.foto + '" alt="Foto">';
                                    html +=
                                        '<div class="card-body"><a href="#" data-iddata="' +
                                        item.id +
                                        '" class="btn_delete_photo btn btn-danger btn-block">Eliminar</a></div></div></div>';
                                });
                            }
                            $('.inven-photos').html(html);
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.log(textStatus)
                        }
                    });
                });

                $('#uploadPhotoForm').on('submit', function(e) {
                    e.preventDefault(); // Evitar el envío normal del formulario
                    $('.inven-photos').html(
                        '<div class="col-md-12"><div class="spinner-border" role="status"><span class="sr-only">Cargando...</span></div></div>'
                    );
                    var formData = new FormData(this); // Crear un objeto FormData                
                    $.ajax({
                        url: URL_PHOTO,
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function(response) {
                            swal({
                                title: response.title,
                                text: response.text,
                                type: response.type,
                                html: true
                            });
                            let html = '';
                            if (response.data) {
                                response.data.forEach((item) => {
                                    html +=
                                        '<div class="col-md-6"><div class="card"><img class="w-100" src="' +
                                        '{{ asset('storage/products/') }}/' +
                                        item.foto + '" alt="Foto">';
                                    html +=
                                        '<div class="card-body"><a href="#" data-iddata="' +
                                        item.id +
                                        '" class="btn_delete_photo btn btn-danger btn-block">Eliminar</a></div></div></div>';
                                });
                            }
                            $('.inven-photos').html(html);
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.log(textStatus)
                        }
                    });
                });
            });


            let edit_record = (id) => {
                Swal.fire({
                    title: "Aviso!",
                    text: "¿Desea editar los datos del registro?",
                    type: "info",
                    showCancelButton: true,
                    confirmButtonColor: '#1274D9',
                    confirmButtonText: 'Si, editar!',
                    cancelButtonText: "Cancelar",
                    closeOnConfirm: false
                }, function(isConfirm) {
                    if (isConfirm)
                        $(location).attr('href', URL + '/' + id + '/edit');

                });
            };

            let delete_record = (id) => {
                Swal.fire({
                    title: "Atención",
                    text: "Confirme que desea eliminar este registro.",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: '#D62E21',
                    confirmButtonText: 'Si, Eliminar!',
                    cancelButtonText: "Cancelar",
                    closeOnConfirm: false,
                    showLoaderOnConfirm: true,
                }, function() {
                    let url = URL + '/' + id;
                    send_request_method(url, 'delete', id);

                });
            };

            let send_request_method = (url, type, id) => {
                $.ajax({
                    url: url,
                    type: type,
                    data: {
                        _token: TOKEN,
                        id: id
                    },
                    dataType: 'json',
                    success: function(check) {
                        Swal.fire({
                            title: check.title,
                            text: check.text,
                            type: check.type,
                            html: true
                        });
                        refresh_table();
                    },
                    error: function(xhr, status) {
                        console.log('error: ', xhr, status);
                        switch (xhr.status) {
                            case 401:
                                Swal.fire("Oops!", "Su sesión ha expirado.", "info");
                                setTimeout(function() {
                                    location.reload();
                                }, 2000);
                                break;
                            case 404:
                                Swal.fire("Oops!", "Recurso no encontrado.", "error");
                                break;
                            default:
                                Swal.fire("Oops!",
                                    "¡El registro no pudo ser procesado!... Intente nuevamente.",
                                    "error");
                        }
                    },
                    complete: function(response) {}
                });
            };

            let formatNumber = (number) => {
                /*
                return new Intl.NumberFormat('es-ES', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(number);
                */

                let fixedNum = number.toFixed(2);
                let parts = fixedNum.split(".");
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                if (parts[1]) {
                    return parts.join(",");
                }
                return parts[0];
            };


            let refreshProducts = (response) => {
                let dacabe_percent = response.dacabe_percent;
                $('#CDEPOS').val(response.cdepos);
                if ((!response.order) && response.depos && (response.depos.length) > 1) {
                    $('#depos_div').removeClass('d-none');
                    $('#depos_div2').addClass('d-none');
                } else {
                    $('#depos_div').addClass('d-none');
                    $('#depos_div2').removeClass('d-none');
                    let deposito = response.depos.filter((item) => item.CDEPOS == response.cdepos);
                    let dep_nombre = deposito.length ? deposito[0].deposito.DDEPOS : '';
                    let url_cart = '{{ url('view-cart') }}';
                    $('#depos_div2').html('<a href="' + url_cart +
                        '"><div class="alert alert-success" role="alert">Depósito: ' + dep_nombre +
                        '</div></a>');
                }
                if (!document.getElementById('CCDEPOS')) {
                    let html = '<label><i class="fa fa-box"></i> Deposito: </label>';
                    html +=
                        '<select onchange="assignDepos()" class="form-control select2" data-placeholder="Seleccione" id="CCDEPOS" name="CCDEPOS">';
                    response.depos.forEach((item) => {
                        html += '<option value="' + item.CDEPOS + '">' + item.deposito.DDEPOS +
                            '</option>';
                    });
                    html += '</select>';
                    $('#depos_div').append(html)
                }

                html = '';
                if (!response.data || response.data.length <= 0) {
                    html =
                        '<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3"></div><div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 w-100" style="text-align:left;"><div class="alert alert-info alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button><h5><i class="icon fas fa-info"></i> Atención!</h5>No se encontró existencia...</div></div>';
                }

                all_inven = response.data;
                let discount = response.discount;
                let ver_precio1 = response.show_precio1;
                let producto_nacional = null;
                if (response.productos_nacionales) {
                    producto_nacional = response.productos_nacionales.CGRUPO;
                }
                let producto_lamina = null;
                if (response.productos_laminas) {
                    producto_lamina = response.productos_laminas.CGRUPO;
                }
                let descuento = response.descuento;
                response.data.forEach((inven) => {
                    if (inven.BASE1 > 0 && inven.EUNIDAD && inven.EUNIDAD > 0 && inven.EUNIDAD >= inven
                        .SMIN) {
                        html +=
                            '<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 mb-4 w-100"><div class="card bg-product w-100 card-productos" id="div_' +
                            inven.CODIGO + '"><span class="cart-item"></span>';
                        html +=
                            '<div class="bg-image hover-zoom ripple ripple-surface ripple-surface-light overflow-visible" data-mdb-ripple-color="light">';
                        html += '<a href="#"><div class="image-container position-relative">';
                        if (inven.promocion?.promocion) {
                            html +=
                                '<div class="ribbon-wrapper ribbon-lg"><div class="ribbon bg-warning">Promocion</div></div>';
                        } else {
                            if (inven.promocion?.nuevo) {
                                html +=
                                    '<div class="ribbon-wrapper ribbon-lg"><div class="ribbon bg-success text-lg">Nuevo</div></div>';
                            } else {
                                if (inven.CSGRUPO == '003') {
                                    html +=
                                        '<div class="ribbon-wrapper ribbon-lg"><div class="ribbon bg-danger">PROMO 12%</div></div>';
                                }
                            }
                        }
                        if (inven.FOTO && inven.FOTO != '') {
                            let photo = '{{ asset('storage/products/') }}';
                            html +=
                                '<img class="card-img-top w-100"  style="height: 250px; object-fit: cover;" src="' +
                                photo + '/' + inven.FOTO + '" alt="Foto">';
                        } else {
                            html +=
                                '<img class="card-img-top w-100"  style="height: 250px; object-fit: cover;" src="{{ asset('storage/products/nofoto.jpg') }}" alt="Foto">';
                        }

                        // Cedano VALIDAR PRODUCTOS NACIONALES O NO PARA PRECIO2 

                        //let precio2 = inven.BASE1 * rate;
                        let precio2 = 0;
                        let precio1 = 0;
                        let recargo = response.recargo ? response.recargo : 0;
                        if (inven.BASE2 && inven.BASE2 > 0) {
                            precio2 = inven.BASE2 + ((inven.BASE2 * recargo) / 100);
                            precio1 = precio2;// + ((precio2 * recargo) / 100);
                        }
                        /*
                        //precio2 = inven.BASE1/(1-(dacabe_percent/100));
                        let porc = dacabe_percent>0?dacabe_percent:35;
                        precio2 = inven.BASE1+((inven.BASE1*porc/100));
                        */
                        discount = dacabe_percent;
                        /*
                        html += '<div class="overlay-pnf"><div class="text"><i class="fa fa-shopping-cart"></i> <br> CÓD: '+ inven.CODIGO + '</div></div></div></a></div>';
                        html += '<div class="card-body"><a href="" class="text-reset"><h5 class="card-title mb-3 text-primary">'+inven.DESCR+'</h5></a><br>';
                        html += '<a href="" class="text-reset"><small class="text-muted">'+inven.DGRUPO+'</small></a><div class="row pt-3"><div class="col-6">';
                        html += '<div class="d-flex justify-content-start align-items-end h-100"><h5><small class="text-muted"> '+inven.CODIGO+'</small> <br /><span class="badge badge-sm bg-warning mt-2 ms-2">';
                        html += '<i class="fa fa-shopping-cart"></i> Disp. '+inven.EUNIDAD+'</span></h5></div></div><div class="col-6 w-100">';
                        html += '<div class="d-flex justify-content-end align-items-end h-100 w-100"><h5><span class="btn btn-success w-100" onclick="event.preventDefault(); addProduct(\''+inven.CODIGO+'\')">$/$ '+formatNumber(inven.BASE1)+'</span></br><small class="btn btn-primary mt-2 w-100" onclick="event.preventDefault(); addProduct(\''+inven.CODIGO+'\')">$/Bs '+formatNumber(precio2)+'</small></h5>';
                        html += '</div></div></div></div></div>';
                        html += '</div></div>';
                        */

                        let reserva = inven.RESERVA > 0 ? parseInt(inven.RESERVA) : 0;
                        let stock_minimo = inven?.informacion && inven.informacion?.stock_minimo > 0 ?
                            parseInt(inven.informacion.stock_minimo) : 0;
                        let exento_indicador = inven.IMPUEST > 0 ? '' : '(E) ';
                        if (stock_minimo && stock_minimo > 0) {
                            html +=
                                '<span class="badge badge-warning position-absolute bottom-0 end-0 me-2 mb-2 z-10" style="position: absolute !important; bottom: 0 !important; right: 0 !important; margin-right: 0.5rem !important; margin-bottom: 0.5rem !important; z-index: 20 !important; background-color: #ffc107 !important; color: #000 !important;">Mín. ' +
                                stock_minimo + '</span>';
                        }
                        html += '</div></a></div>';
                        html +=
                            '<div class="card-body" style="padding: 0.75rem"><span class="text-reset"><h5 class="card-title mb-1 text-primary w-100">' +
                            exento_indicador + inven.DESCR + '</h5></span>';
                        //let moreButton = '<div class="row pt-1"><div class="col-12"><span onclick="showProductDetails(\''+inven.CODIGO+'\')" class="btn btn-info btn-block"><i class="fa fa-eye"></i> Más detalles...</span></div></div>';
                        let moreButton =
                            '<div class=""><span alt="Más detalles..." title="Ver más información" data-toggle="tooltip" data-placement="top" onclick="showProductDetails(\'' +
                            inven.CODIGO +
                            '\')" class="btn btn-outline-danger btn-sm" style="padding:.45rem .6rem;"><i class="fa fa-eye"></i></span></div>';

                        html += '<span class="text-reset"><small class="text-muted">' + inven.DGRUPO +
                            '</small><br><small class="text-info">' + inven.NOMBRE +
                            '</small></span><div class="row pt-1"><div class="col-6">';
                        //if (DESCUENTO && DESCUENTO >0){
                        if (ver_precio1 == 'SI') {
                            html +=
                                '<div class=" h-100"><h5><span class="badge badge-sm bg-warning mt-2 ms-2"> ' +
                                inven.CODIGO + '</span>';
                        } else {
                            discount = DESCUENTO;
                            html +=
                                '<div class=" h-100"><h5><span class="badge badge-sm bg-warning mt-2 ms-2"> ' +
                                inven.CODIGO + '</span> <br />';
                        }
                        html += '</h5></div></div><div class="col-6 w-100">';
                        html +=
                            '<div class="h-100 w-100"><h5><span class="badge badge-sm bg-warning mt-2 ms-2"><i class="fa fa-shopping-cart"></i> Disp.  <span id="disp_' +
                            inven.CODIGO + '">' + (parseInt(inven.EUNIDAD) - reserva) +
                            '</span></span></div></div></div>';

                        html +=
                            '<div class="row w-100" style="padding-top:-20px;"><div class="col-2">' +
                            moreButton +
                            '</div><div class="col-10 w-100"><small class="btn btn-danger w-100" onclick="event.preventDefault(); addProduct(\'' +
                            inven.CODIGO + '\', \'' + precio2 + '\', \'' + (inven.EUNIDAD - inven
                                .RESERVA) + '\', \'Bs\', ' + stock_minimo +
                            ')" title="Agregar al carrito"><i class="fa fa-shopping-cart"></i> Precio: Ref ' +
                            formatNumberInside(precio2) + '</small></div></div>';

                        let porc_desc = 0;
                        let class_dcto = 'mt-5';
                        let dcto_cash = 0;
                        let img_promo = '';
                        if (((!producto_nacional) || (inven.CGRUPO != producto_nacional))) {
                            dcto_cash = ((precio2 * (descuento * -1)) / 100);
                            porc_desc = descuento * (-1);
                            class_dcto = '';
                            html +=
                                '<div class="row pt-1 w-100"><div class="col-12"><span class="text-danger w-100"><small class="text-danger"> Para pagos en Divisa recibes</small><br><b>' +
                                descuento +
                                '%</b> de Descuento <i class="fa fa-arrow-down"></i></span></div></div>';
                        } else {
                            img_promo =
                                '<img class="mt-5" src="{{ asset('imgs/logos/productos_nacionales.jpg') }}" alt="Producto Nacional" title="Producto Nacional" style="width:100%; float:right;">';
                            precio1 = inven.BASE2;
                            precio2 = inven.BASE1;
                        }
                        let porcentaje_iva = inven.IMPUEST > 0 ? inven.IMPUEST / 100 : 0;
                        let iva_bs = inven.IMPUEST > 0 ? (precio1 * porcentaje_iva).toFixed(2) : 0;

                        html +=
                            '<div class="row w-100" style="padding-top:-20px;"><div class="col-6 w-100"><span style="" class="mt-1 btn btn-danger w-100"><small> - Dcto: 0,00<br><hr style="border-top:1px solid #fff; margin:4px 0;"><b>TOTAL+IVA: ' +
                            formatNumberInside(parseFloat(precio1) + parseFloat(iva_bs)) +
                            '</b></small></span></div><div class="col-6">';
                        let base_cash = precio2 - dcto_cash;
                        let iva_cash = inven.IMPUEST > 0 ? base_cash * (porcentaje_iva) : 0;
                        let total_cash = base_cash + iva_cash;
                        html +=
                            '<span class="btn btn-danger btn-block mt-1" style=""><small> - Dcto: ' +
                            formatNumberInside(dcto_cash) +
                            '<br><hr style="border-top:1px solid #fff; margin:4px 0;"><b>TOTAL+IVA: ' +
                            formatNumberInside(total_cash) + '</b></small></span>';
                        html += '</div></div>';
                        
                        // Mostrar u ocultar promociones por porcentaje
                        let mostrar_promociones = 'NO';

                        // INFORMACION DE TOTALES
                        if (((!producto_nacional && !producto_lamina) || (inven.CGRUPO !=
                                producto_nacional && inven.CGRUPO != producto_lamina))) {
                            //html += '<div class="row pt-2"><div class="col-12"><small class="text-success w-100">Los primeros 7 dias solo pagas <i class="fa fa-arrow-down"></i></small></div></div>';

                            let styleInfo = 'style="background-color: #E6EAED; color: #5188AE"';
                            let subtotales =
                                '<div class="row pt-2 w-100" style="text-align: left; !important;">';
                            //let porcentaje_pronto_pago = (inven.CSGRUPO == '003') ? 12 : 6;
                            /*let mensaje_promo = (inven.CSGRUPO == '003') ?
                            '<i class="fa fa-arrow-down"></i> Hasta el 30/09 solo pagas <i class="fa fa-arrow-down"></i>' :
                            '<i class="fa fa-arrow-down"></i> Los primeros 7 dias solo pagas <i class="fa fa-arrow-down"></i>';
                            */
                            let porcentaje_pronto_pago = 6;
                            let mensaje_promo =
                                '<i class="fa fa-arrow-down"></i> Los primeros 7 dias solo pagas <i class="fa fa-arrow-down"></i>';

                            let descu = (((!producto_nacional && !producto_lamina) || (inven.CGRUPO !=
                                    producto_nacional && inven.CGRUPO != producto_lamina))) ?
                                base_cash * porcentaje_pronto_pago / 100 : 0;
                            let base_cash2 = (base_cash - descu);
                            let iva = inven.IMPUEST > 0 ? (base_cash2) * porcentaje_iva : 0;
                            let total_gen = base_cash2 + iva;

                            let descu_bs = (((!producto_nacional && !producto_lamina) || (inven
                                .CGRUPO != producto_nacional && inven.CGRUPO !=
                                producto_lamina))) ? precio2 * porcentaje_pronto_pago / 100 : 0;
                            let base_bs = (precio2 - descu_bs);
                            let iva_bs = inven.IMPUEST > 0 ? (base_bs) * porcentaje_iva : 0;
                            let total_gen_bs = base_bs + iva_bs;
                            
                            if (mostrar_promociones == 'SI' ){
                                subtotales +=
                                    '<div class="col-12 d-flex align-items-center justify-content-center w-100"><small class="text-success w-100 pt-1 text-center">' +
                                    mensaje_promo + '</small></div>';
                                subtotales +=
                                    '<div class="col-6 d-flex align-items-center justify-content-center w-100" style="height: 100%;"><span class="btn btn-success btn-block" style=""><small>-Dcto: ' +
                                    formatNumberInside(descu_bs) +
                                    '<br class=""><hr style="border-top:1px solid #fff; margin:4px 0;"><b>TOTAL+IVA: ' +
                                    formatNumberInside(total_gen_bs) + '</b></small></span></div>';
                                subtotales +=
                                    '<div class="col-6"><span class="btn btn-success btn-block" style=""><small>-Dcto: ' +
                                    formatNumberInside(descu) +
                                    '<br class=""><hr style="border-top:1px solid #fff; margin:4px 0;"><b>TOTAL+IVA: ' +
                                    formatNumberInside(total_gen) + '</b></small></span>';
                                subtotales += '</div>';
                                html += subtotales;
                            }

                        } else {
                            html += img_promo;
                        }
                        // FIN INFORMACION DE TOTALES

                        html += '</div></div>';
                        html += '</div></div>';


                    }
                });
                $('#product_lists').html(html);
            };

            let loadLastRate = () => {
                //loadProducts();
                $.ajax({
                    url: URL_RATE,
                    type: 'get',
                    data: {
                        _token: TOKEN
                    },
                    dataType: 'json',
                    success: function(response) {
                        rate = response.data.rate
                        loadProducts();
                    },
                    error: function(xhr, status) {
                        console.log('error: ', xhr, status);

                    },
                    complete: function(response) {}
                });
            };

            let loadProducts = (cgrupo = 'TODOS') => {
                let html = '<div class="w-100 text-primary"><i class="spinner-border"></i></div>';
                $('#product_lists').html(html);
                let search = $('#search').val();

                $.ajax({
                    url: URL_PRODUCTS,
                    type: 'post',
                    data: {
                        _token: TOKEN,
                        cgrupo: $('#CGRUPO').val(),
                        cdepos: $('#CDEPOS').val(),
                        search: search
                    },
                    dataType: 'json',
                    success: function(response) {
                        refreshProducts(response);
                    },
                    error: function(xhr, status) {
                        console.log('error: ', xhr, status);
                        switch (xhr.status) {
                            case 401:
                                //Swal.fire("Oops!", "Su sesión ha expirado.", "info");
                                setTimeout(function() {}, 2000);
                                break;
                            case 404:
                                //Swal.fire("Oops!", "Recurso no encontrado.", "error");
                                break;
                            default:
                                //Swal.fire("Oops!", "¡El registro no pudo ser procesado!... Intente nuevamente.", "error");
                        }
                    },
                    complete: function(response) {}
                });
            };

            $('.btn-save-discount').on('click', function() {
                let discount = $('.general-discount').val();
                $.ajax({
                    url: URL_DESCUENTO,
                    type: 'post',
                    data: {
                        _token: TOKEN,
                        discount: discount
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.type == 'success') {
                            Swal.fire("Genial!", "Descuento global actualizado.", "success");
                        }
                    },
                    error: function(xhr, status) {
                        console.log('error: ', xhr, status);
                        switch (xhr.status) {
                            case 401:
                                Swal.fire("Oops!", "Su sesión ha expirado.", "info");
                                setTimeout(function() {}, 2000);
                                break;
                            case 404:
                                Swal.fire("Oops!", "Recurso no encontrado.", "error");
                                break;
                            default:
                                Swal.fire("Oops!",
                                    "¡El registro no pudo ser procesado!... Intente nuevamente.",
                                    "error");
                        }
                    },
                    complete: function(response) {}
                });
            });

            loadLastRate();

        });


        set_active_sidebar_menu('Web', 'Categorias');
    </script>
@endsection
