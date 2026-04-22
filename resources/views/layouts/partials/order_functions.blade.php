@section('scripts')
    <script>
        const TOKEN = '{{ csrf_token() }}';
        const URL = '{{ url('order-inven') }}';
        const URL_SAVE_PRODUCT = '{{ url('update-order-products') }}';
        const URL_GET_SELLER_BALANCE = '{{ url('get-seller-balance') }}';
        const URL_SAVE_PAYMENT = '{{ url('order-save-payment') }}';
        const URL_DELETE_PAYMENT = '{{ url('order-delete-payment') }}';

        let minusProduct = (item_id) => {
            modifyQuantity('minus', item_id)
        }

        let plusProduct = (item_id) => {
            modifyQuantity('plus', item_id)
        }

        let saveQuantity = (item_id) => {
            qty = $('#qty_' + item_id).val();
            if (qty > 0) {
                modifyQuantity('set', item_id, qty);
            } else {
                swal("Oopsss!", "La Cantidad debe ser mayor a cero...", "error");
            }
        }
        /*
        let formatNumberInside = (number) => {
            if (number) {
                let fixedNum = number.toFixed(2);
                let parts = fixedNum.split(".");
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                if (parts[1]) {
                    return parts.join(","); 
                }
                return parts[0]; 
            }
        };
        */

        let formatNumberInside = (number) => {
            if (number !== undefined && number !== null) {
                let numStr = number.toString();
                let parts = numStr.split(".");

                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");

                if (parts[1]) {
                    parts[1] = parts[1].substring(0, 2);
                    return parts.join(",");
                }

                return parts[0]; // Solo parte entera
            }
        };

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
                            swal("Oops!", "Su sesión ha expirado.", "info");
                            setTimeout(function() {}, 2000);
                            break;
                        case 404:
                            swal("Oops!", "Recurso no encontrado.", "error");
                            break;
                        default:
                            swal("Oops!", "¡El registro no pudo ser procesado!... Intente nuevamente.",
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
                    //swal({title: response.title, text: response.text, type: response.type, html:true});
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
                        swal("Oops!", "No se pudo eliminar.", "error");
                    }
                    Livewire.emit('updateCarts')
                },
                error: function(xhr, status) {
                    console.log('error: ', xhr, status);
                    switch (xhr.status) {
                        case 401:
                            swal("Oops!", "Su sesión ha expirado.", "info");
                            setTimeout(function() {}, 2000);
                            break;
                        case 404:
                            swal("Oops!", "Recurso no encontrado.", "error");
                            break;
                        default:
                            swal("Oops!", "¡El registro no pudo ser procesado!... Intente nuevamente.",
                                "error");
                    }
                },
                complete: function(response) {}
            });
        }

        let showRif = (rif_foto) => {
            let photo = "{{ asset('storage/products/') }}" + '/' + rif_foto;
            //document.getElementById('modalImage').src = photo; // Carga la imagen en la modal
            //document.getElementById('myModal').style.display = "block"; 
            window.open(photo);
        };

        let changeClient = (order) => {
            let data = JSON.parse(order);
            console.warn(data.rif)
        };

        let changeProduct = (order_id, codigo_inven, cantidad, precio_dolar, pago, action = 'show') => {
            //console.warn(pago);
            $('#modal_order_id').val(order_id);
            $('#modal_codigo_inven').val(codigo_inven);
            $('#modal_cantidad').val(cantidad);
            $('#modal_precio_dolar').val(precio_dolar);
            $('#modal_products').modal('show');
        };

        let deletePayment = (payment_id) => {
            $('#btn_delete_' + payment_id).html(
                '<div class="spinner-border" role="status"><span class="sr-only">Cargando...</span></div>');
            $.ajax({
                url: URL_DELETE_PAYMENT,
                type: 'post',
                data: {
                    _token: TOKEN,
                    payment_id: payment_id
                },
                dataType: 'json',
                success: function(response) {
                    if (response.type == 'success') {
                        $('#li_' + payment_id).fadeOut(500, function() {
                            $(this).remove();
                        });
                        $('#balance_' + response.data.id).html('SALDO $ ' + formatNumberInside(response.data
                            .saldo));
                        $('#seller_balance').html(formatNumberInside(response.data.saldo));
                    } else {
                        swal("Oops!", "No se pudo eliminar.", "error");
                    }
                    $('#btn_delete_' + payment_id).html('<i class="fa fa-trash"></i>');
                },
                error: function(xhr, status) {
                    console.log('error: ', xhr, status);
                    $('#btn_delete_' + payment_id).html('<i class="fa fa-trash"></i>');
                    switch (xhr.status) {
                        case 401:
                            swal("Oops!", "Su sesión ha expirado.", "info");
                            setTimeout(function() {}, 2000);
                            break;
                        case 404:
                            swal("Oops!", "Recurso no encontrado.", "error");
                            break;
                        default:
                            swal("Oops!", "¡El registro no pudo ser procesado!... Intente nuevamente.",
                                "error");
                    }
                },
                complete: function(response) {}
            });
        };

        let printSellerBalance = () => {
            let url_seller_print = '{{ url('seller-balance-print') }}/';
            let seller_id = $('#modal_seller_id').val();
            if (seller_id) {
                window.open(url_seller_print + seller_id)
            }
        };

        let showClientTransactions = (seller_id) => {
            $('#modal_seller_id').val(seller_id);
            $('#seller_name').html(
                '<div class="spinner-border" role="status"><span class="sr-only">Cargando...</span></div>');
            $('#seller_zone').html(
                '<div class="spinner-border" role="status"><span class="sr-only">Cargando...</span></div>');
            $('#seller_balance').html(
                '<div class="spinner-border" role="status"><span class="sr-only">Cargando...</span></div>');
            $('#li_payments').html(
                '<div class="spinner-border" role="status"><span class="sr-only">Cargando...</span></div>');
            $('#seller_orders').html('0');
            $('#seller_payments').html('0');
            $('#modal_sellers').modal('show');
            $.ajax({
                url: URL_GET_SELLER_BALANCE + '/' + seller_id,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    $('#seller_name').html('');
                    $('#seller_zone').html('');
                    $('#seller_balance').html(' 0,00');
                    $('#li_payments').html('');

                    if (response.data) {
                        $('#seller_name').html('<i class="fa fa-map-marker"></i> ' + response.data.zona
                            .nombre);
                        $('#seller_zone').html(response.data.user.name + ' ' + response.data.user
                            .last_name);
                        $('#seller_balance').html(formatNumberInside(response.data.saldo));
                        $('#seller_orders').html(formatNumberInside(response.data.ventas ?? 0));
                        $('#seller_payments').html(formatNumberInside(response.data.payments ?? 0));
                        let html = '';
                        response.data.pagos.forEach((item) => {
                            html += '<li class="item mt-3" id="li_' + item.id +
                                '"><div class="product-img">';
                            html +=
                                '<a class="btn btn-app text-danger" onclick="event.preventDefault(); deletePayment(' +
                                item.id +
                                ');" style="height: 47px; min-width:55px;" id="btn_delete_' + item
                                .id + '"><i class="fa fa-trash"></i> </a>';
                            html +=
                                '</div><div class="product-info"><a href="javascript:void()" class="product-title pl-3"><i class="fa fa-calendar"> </i> ' +
                                formatDate(item.fecha);
                            html += '<span class="badge badge-danger float-right">$ ' +
                                formatNumberInside(item.monto) +
                                '</span></a><span class="product-description pl-3">';
                            html += item.descripcion ? '<i class="fa fa-edit"></i> ' + item
                                .descripcion + '</span></div></li>' : '&nbsp;';

                        });
                        $('#li_payments').html(html);
                    }
                },
                error: function(xhr, status, error) {
                    $('#seller_name').html('');
                    $('#seller_zone').html('');
                    $('#seller_balance').html('0,00');
                    console.log('error: ', xhr, status);
                }
            });
        };

        function formatDate(dateString) {
            const parts = dateString.split('-');
            return `${parts[2]}/${parts[1]}/${parts[0]}`;
        }

        let showPaymentOrderList = (seller_id) => {
            // Limpiar el contenido y mostrar spinner
            $('#payment_order_list').html(
                '<div class="spinner-border" role="status"><span class="sr-only">Cargando...</span></div>');
            // Llamar al endpoint para obtener los pedidos pendientes del vendedor
            $.ajax({
                url: '{{ url('orders-pending-by-seller') }}/' + seller_id,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    let html = '';
                    if (response.data && response.data.length > 0) {
                        response.data.forEach(order => {
                            if (order.saldo_pendiente > 0) {
                                html += `
                                <div class="mb-2 input-group flex-column align-items-stretch">
                                    <div class="d-flex flex-wrap align-items-center mb-1">
                                        <span class="input-group-text flex-grow-1">Pedido #${order.id} - Total: ${formatNumberInside(order.total)}</span>
                                        <span class="badge badge-danger ml-2 input-group-text">Saldo: ${formatNumberInside(order.saldo_pendiente)}</span>
                                    </div>
                                    <div class="d-flex">
                                        <input type="number" min="0" step="0.01" class="form-control payment-order-input" name="payment_order_${order.id}" placeholder="Monto a abonar">
                                        <button type="button" class="btn btn-outline-primary set-max-payment ml-2" data-order-id="${order.id}" data-saldo="${order.saldo_pendiente}"><i class="fa fa-money-bill"></i></button>
                                    </div>
                                </div>
                            `;

                                setTimeout(() => {
                                    // Botón para asignar saldo pendiente automáticamente
                                    $('.set-max-payment').off('click').on('click',
                                        function() {
                                            let orderId = $(this).data('order-id');
                                            let saldoPendiente = parseFloat($(this)
                                                .data('saldo'));
                                            let montoPago = parseFloat($(
                                                '#modal_monto_pago').val());
                                            let totalAsignado = 0;

                                            // Sumar todos los pagos asignados excepto el actual
                                            $('#payment_order_list input[type="number"]')
                                                .each(function() {
                                                    if ($(this).attr('name') !==
                                                        'payment_order_' + orderId
                                                    ) {
                                                        let val = parseFloat($(this)
                                                            .val());
                                                        if (!isNaN(val) && val >
                                                            0) {
                                                            totalAsignado += val;
                                                        }
                                                    }
                                                });

                                            let maxDisponible = montoPago -
                                                totalAsignado;
                                            let montoAsignar = Math.min(saldoPendiente,
                                                maxDisponible);

                                            if (montoAsignar < 0) montoAsignar = 0;

                                            $('input[name="payment_order_' + orderId +
                                                '"]').val(montoAsignar).trigger(
                                                'input');
                                        });
                                }, 0);
                            }
                        });
                    } else {
                        html =
                            '<div class="alert alert-info">No hay pedidos pendientes para este vendedor.</div>';
                    }
                    $('#payment_order_list').html(html);

                    // Validación: la suma de los montos no debe exceder el monto total del pago
                    $('.payment-order-input').on('input', function() {
                        let total_payment_orders = 0;
                        $('#payment_order_list input[type="number"]').each(function() {
                            let val = parseFloat($(this).val());
                            if (!isNaN(val) && val > 0) {
                                total_payment_orders += val;
                            }
                        });
                        let monto_pago = parseFloat($('#modal_monto_pago').val());
                        if (!isNaN(monto_pago) && total_payment_orders > monto_pago) {
                            $(this).val('');
                            swal({
                                title: 'Oops!',
                                text: 'La suma de los montos asignados a los pedidos no puede ser mayor al monto total del pago.',
                                type: 'error'
                            });
                        }
                    });
                },
                error: function(xhr, status) {
                    $('#payment_order_list').html(
                        '<div class="alert alert-danger">Error al cargar los pedidos pendientes.</div>');
                    console.log('error: ', xhr, status);
                }
            });
        };

        // Invocar showPaymentOrderList al mostrar la modal de pagos
        $('#modal_sellers').on('shown.bs.modal', function() {
            let seller_id = $('#modal_seller_id').val();
            if (seller_id) {
                showPaymentOrderList(seller_id);
            }
        });

        let savePayment = () => {
            let seller_id = $('#modal_seller_id').val();
            let fecha = $('#modal_fecha_pago').val();
            let monto = parseFloat($('#modal_monto_pago').val());
            let descripcion = $('#modal_descripcion_pago').val();

            // Recolectar los montos de cada input de pago de pedidos
            let payment_orders = [];
            let total_payment_orders = 0;
            $('#payment_order_list input[type="number"]').each(function() {
                let val = parseFloat($(this).val());
                let order_id = $(this).attr('name').replace('payment_order_', '');
                if (!isNaN(val) && val > 0) {
                    payment_orders.push({
                        pedido_id: order_id,
                        monto: val
                    });
                    total_payment_orders += val;
                }
            });

            // Validar que la suma de los montos no sea mayor al monto ingresado
            // Mostrar la suma de los montos asignados en un lugar visible
            $('#total_payment_orders_sum').remove(); // Eliminar si ya existe
            $('#payment_order_list').after(
                '<div id="total_payment_orders_sum" class="alert alert-info mt-2">Total asignado a pedidos: $ ' +
                formatNumberInside(total_payment_orders) + '</div>');

            if (total_payment_orders > monto) {
                swal({
                    title: 'Oops!',
                    text: 'La suma de los montos asignados a los pedidos no puede ser mayor al monto total del pago.',
                    type: 'error'
                });
                return;
            }

            if (seller_id && fecha && monto > 0) {
                $('#btn_payment').html('<div class="spinner-border" role="status"><span class="sr-only"></span></div>');
                $.ajax({
                    url: URL_SAVE_PAYMENT,
                    type: 'POST',
                    data: {
                        _token: TOKEN,
                        seller_id: seller_id,
                        fecha: fecha,
                        monto: monto,
                        descripcion: descripcion,
                        payment_orders: payment_orders
                    },
                    dataType: 'json',
                    success: function(response) {
                        Swal.fire({
                            title: response.title,
                            text: response.text,
                            icon: response.type,
                            html: true
                        });
                        $('#modal_monto_pago').val(0);
                        $('#modal_descripcion_pago').val('');

                        html = '<li class="item mt-3" id="li_' + response.payment.id +
                            '"><div class="product-img">';
                        html +=
                            '<a class="btn btn-app text-danger" onclick="event.preventDefault(); deletePayment(' +
                            response.payment.id +
                            ');" style="height: 47px; min-width:55px;"><i class="fa fa-trash"></i> </a>';
                        html +=
                            '</div><div class="product-info"><a href="javascript:void()" class="product-title pl-3"><i class="fa fa-calendar"> </i> ' +
                            formatDate(response.payment.fecha);
                        html += '<span class="badge badge-danger float-right">$ ' + formatNumberInside(
                                parseFloat(response.payment.monto)) +
                            '</span></a><span class="product-description pl-3">';
                        html += '<i class="fa fa-edit"></i> ' + response.payment.descripcion +
                            '</span></div></li>';
                        $('#li_payments').prepend(html);

                        $('#balance_' + response.data.id).html('SALDO $ ' + formatNumberInside(response.data
                            .saldo));
                        $('#seller_balance').html(formatNumberInside(response.data.saldo));
                        $('#btn_payment').html('<i class="fa fa-check"></i> Registrar pago');
                        // ejecuta showPaymentOrderList ();
                        showClientTransactions(seller_id);
                        showPaymentOrderList(seller_id);
                    },
                    error: function(xhr, status, error) {
                        $('#btn_payment').html('<i class="fa fa-check"></i> Registrar pago');
                        console.log('error: ', xhr, status);
                    }
                });
            } else {
                swal({
                    title: 'Oops!',
                    text: 'Debe ingresar al menos la Fecha y Monto del pago, por favor verifique nuevamente!',
                    type: 'error'
                });
            }
        };

        let formatTotal = (monto) => {
            let tot = parseFloat(monto);
            let fixedNum = tot.toFixed(2);
            let parts = fixedNum.split(".");
            let prec = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            if (parts[1]) {
                return parts.join(",");
            }
            return prec;
        };

        let saveProduct = () => {
            let order_id = $('#modal_order_id').val();
            let codigo_inven = $('#modal_codigo_inven').val();
            let cantidad = $('#modal_cantidad').val();
            let cantidad_nota = $('#modal_cantidad_nota').val();
            let precio_dolar = $('#modal_precio_dolar').val();
            if (order_id && codigo_inven && cantidad > 0 && precio_dolar > 0) {
                $('#loadingModal').modal('show');
                $('#modal_products').modal('hide');
                $.ajax({
                    url: URL_SAVE_PRODUCT,
                    type: 'POST',
                    data: {
                        _token: TOKEN,
                        order_id: order_id,
                        codigo_inven: codigo_inven,
                        cantidad: cantidad,
                        cantidad_nota: cantidad_nota,
                        precio_dolar: precio_dolar
                    },
                    dataType: 'json',
                    success: function(response) {
                        Swal.fire({
                            title: response.title,
                            text: response.text,
                            icon: response.type,
                            html: true
                        });
                        $('#modal_order_id').val(null);
                        let simbolo = '$/' + response.data.pago;
                        let tot = formatTotal(parseFloat(response.data.precio_dolar));
                        let subtot_p = formatTotal(parseFloat(response.data.precio_dolar) * parseFloat(
                            response
                            .data.cantidad));
                        let tot_p = formatTotal(parseFloat(response.data.precio_dolar) * parseFloat(response
                            .data.cantidad));

                        let iva = formatTotal(parseFloat(tot_p) * parseFloat(response.data.iva / 100));
                        if (document.getElementById('iva_' + response.data.pedido_id + '_' + response.data
                                .codigo_inven)) {
                            document.getElementById('iva_' + response.data.pedido_id + '_' + response.data
                                    .codigo_inven).innerHTML =
                                simbolo + ' ' + iva;
                        }
                        let ret = response.retencion && response.retencion > 0 ? formatTotal(parseFloat(
                            iva) * parseFloat(response.retencion / 100)) : 0;
                        if (document.getElementById('retencion_' + response.data.pedido_id + '_' + response
                                .data.codigo_inven)) {
                            document.getElementById('retencion_' + response.data.pedido_id + '_' + response
                                    .data.codigo_inven).innerHTML =
                                simbolo + ' ' + ret;
                        }

                        let html = 'Cant.: ' + response.data.cantidad + '<span class="pl-4">  Monto: ' +
                            simbolo + ' ' + tot + '</span>';
                        let div = document.getElementById('p_product_' + response.data.pedido_id + '_' +
                            response.data.codigo_inven);
                        if (div) {
                            div.innerHTML = html;
                            console.warn(html)
                        } else {
                            console.log('no existe...');
                        }
                        document.getElementById('p_tot_' + response.data.pedido_id + '_' + response.data
                                .codigo_inven).innerHTML =
                            simbolo + ' ' + tot_p;

                        let totales = response.totales;
                        document.getElementById('n_' + response.data.pedido_id).innerHTML = 'Items: ' +
                            formatTotal(totales.total_cantidad);
                        document.getElementById('gran_total_' + response.data.pedido_id).innerHTML =
                            'TOTAL: ' + formatTotal(totales.total_precio + totales.total_iva);
                        console.log('#n_' + response.data.pedido_id)
                        console.log('#gran_total_' + response.data.pedido_id + '_' + response.data
                            .codigo_inven)
                        console.log(totales)

                        $('#modal_order_id').val(null);
                        $('#modal_codigo_inven').val(null);
                        $('#modal_cantidad').val(null);
                        $('#modal_precio_dolar').val(null);
                        $('#loadingModal').modal('hide');
                    },
                    error: function(xhr, status, error) {
                        $('#loadingModal').modal('hide');
                        console.log('error: ', xhr, status);
                    }
                });
            } else {
                swal({
                    title: 'Oops!',
                    text: 'Algunos valores no son permitidos, por favor verifique nuevamente!',
                    type: 'error'
                });
            }

        };

        $('#rif').on('blur', function() {
            let rif = $(this).val();
            searchByRif(rif);
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
                            swal({
                                title: 'Oops!',
                                text: 'RIF No encontrado! Si desea solcitar la creación de un nuevo Cliente debe completar toda la información a continuación...',
                                type: 'error'
                            });
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
                        swal({
                            title: 'Genial!',
                            text: 'Pedido Guardado',
                            type: 'success'
                        });
                        //setTimeout(function() {
                        $(location).attr('href', URL_BACK);
                        //}, 5000);

                    },
                    error: function(xhr, status) {
                        console.log('error: ', xhr, status);
                        switch (xhr.status) {
                            case 401:
                                swal("Oops!", "Su sesión ha expirado.", "info");
                                setTimeout(function() {}, 2000);
                                break;
                            case 404:
                                swal("Oops!", "Recurso no encontrado.", "error");
                                break;
                            default:
                                swal("Oops!", "¡El registro no pudo ser procesado!... Intente nuevamente.",
                                    "error");
                        }
                    },
                    complete: function(response) {}
                });
            } else {
                swal("Oops!", "Debe indicar el RIF, Nombre y Teléfono del Cliente!", "error");
            }
        }

        let addProduct = (codigo, precio = 0, modo = '$') => {
            if (precio > 0) {
                swal({
                    title: 'Indique la cantidad',
                    icon: 'success',
                    input: 'number',
                    inputPlaceholder: 'Cantidad',
                    showLoaderOnConfirm: true,
                    showCancelButton: true
                }).then(function(response) {
                    console.log(response.value)
                    qty = response.value ? response.value : 1;
                    addProductCallback(codigo, qty, precio, modo);
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
                    modo: modo
                },
                dataType: 'json',
                success: function(response) {
                    swal("Genial!", "Producto agregado al pedido...", "success");
                    const cartCount = document.querySelector('.cart-count');
                    //let count = parseInt(cartCount.textContent);
                    //cartCount.textContent = count + 1;

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
                },
                error: function(xhr, status) {
                    console.log('error: ', xhr, status);
                    switch (xhr.status) {
                        case 401:
                            swal("Oops!", "Su sesión ha expirado.", "info");
                            setTimeout(function() {}, 2000);
                            break;
                        case 404:
                            swal("Oops!", "Recurso no encontrado.", "error");
                            break;
                        default:
                            swal("Oops!", "¡El registro no pudo ser procesado!... Intente nuevamente.",
                                "error");
                    }
                },
                complete: function(response) {}
            });
        };

        const URL_UPDATE_ESTATUS_ORDER = '{{ url('update-estatus-order') }}';

        let approveOrCancelOrder = (order_id, estatus) => {
            if (order_id && estatus) {
                //$('#btn_'+estatus+'_'+order_id).html();
                $('#loadingModal').modal('show');
                $.ajax({
                    url: URL_UPDATE_ESTATUS_ORDER,
                    type: 'post',
                    data: {
                        _token: TOKEN,
                        order_id: order_id,
                        estatus: estatus
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.type == 'success') {
                            $('#div_' + order_id).fadeOut(700, function() {
                                $(this).remove();
                            });
                            //cedano
                            $('#totals_' + response.data.id).html('VENDIDO: $ ' + formatNumberInside(
                                response.data.totales));
                            $('#balance_' + response.data.id).html('SALDO:  ' + formatNumberInside(response
                                .data.saldo));
                        } else {
                            swal("Oops!", "Pedido No Encontrado. Intente nueamente!", "info");
                        }

                    },
                    error: function(xhr, status) {
                        console.log('error: ', xhr, status);
                        switch (xhr.status) {
                            case 401:
                                //swal("Oops!", "Su sesión ha expirado.", "info");
                                setTimeout(function() {}, 2000);
                                break;
                            case 404:
                                //swal("Oops!", "Recurso no encontrado.", "error");
                                break;
                            default:
                                //swal("Oops!", "¡El registro no pudo ser procesado!... Intente nuevamente.", "error");
                        }
                    },
                    complete: function(response) {
                        $('#loadingModal').modal('hide');
                    }
                });
            }
        }

        jQuery(function($) {

            let edit_record = (id) => {
                swal({
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
                swal({
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
                        swal({
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
                                swal("Oops!", "Su sesión ha expirado.", "info");
                                setTimeout(function() {
                                    location.reload();
                                }, 2000);
                                break;
                            case 404:
                                swal("Oops!", "Recurso no encontrado.", "error");
                                break;
                            default:
                                swal("Oops!",
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
                html = '';
                if (!response.data || response.data.length <= 0) {
                    html =
                        '<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3"></div><div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 w-100" style="text-align:left;"><div class="alert alert-info alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button><h5><i class="icon fas fa-info"></i> Atención!</h5>No se encontró existencia...</div></div>';
                }

                response.data.forEach((inven) => {
                    if (inven.BASE1 > 0 && inven.EUNIDAD && inven.EUNIDAD > 0) {
                        html +=
                            '<div class="col-lg-3 col-md-12 mb-4 w-100"><div class="card bg-product w-100" id="div_' +
                            inven.CODIGO + '"><span class="cart-item"></span>';
                        html +=
                            '<div class="bg-image hover-zoom ripple ripple-surface ripple-surface-light" data-mdb-ripple-color="light">';
                        html += '<a href="#"><div class="image-container">';
                        if (inven.promocion?.promocion) {
                            console.log(inven.promocion?.promocion)
                            html +=
                                '<div class="ribbon-wrapper ribbon-lg"><div class="ribbon bg-warning">Promocion</div></div>';
                        } else {
                            if (inven.promocion?.nuevo) {
                                html +=
                                    '<div class="ribbon-wrapper ribbon-lg"><div class="ribbon bg-success text-lg">Nuevo</div></div>';
                            }
                        }
                        let photo = "{{ asset('storage/products/') }}";
                        if (inven.FOTO && inven.FOTO != '') {
                            html += '<img class="card-img-top w-100" src="' + photo + '/' + inven.FOTO +
                                '" alt="Foto">';
                        } else {
                            html += '<img class="card-img-top w-100" src="' + photo +
                                '/nofoto.jpg" alt="Foto">';
                        }
                        //let precio2 = inven.BASE1 * rate;
                        /*
                        let precio2 = 0;
                        if (inven.BASE2 && inven.BASE2 > 0) {
                            precio2 = inven.BASE2;
                        }
                        */

                        // Cedano VALIDAR PRODUCTOS NACIONALES O NO PARA PRECIO2 
                        precio2 = inven.base1 + (inven.base1 * dacabe_percent / 100);
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


                        html += '</div></a></div>';
                        html +=
                            '<div class="card-body"><span class="text-reset"><h5 class="card-title mb-1 text-primary w-100">' +
                            inven.DESCR + '</h5></span>';
                        html += '<span class="text-reset"><small class="text-muted">' + inven.DGRUPO +
                            '</small></span><div class="row pt-1"><div class="col-6">';
                        html +=
                            '<div class=" h-100"><h5><span class="badge badge-sm bg-warning mt-2 ms-2"> ' +
                            inven.CODIGO +
                            '</span> <br /><span class="mt-3 btn btn-success w-100" onclick="event.preventDefault(); addProduct(\'' +
                            inven.CODIGO + '\', \'' + inven.BSAE1 + '\', \'$\')">$/$ ' + formatNumber(
                                inven.BASE1) + '</span>';
                        html += '</h5></div></div><div class="col-6 w-100">';
                        let reserva = inven.reserva > 0 ? parseInt(inven.reserva) : 0;
                        html +=
                            '<div class="h-100 w-100"><h5><span class="badge badge-sm bg-warning mt-2 ms-2"><i class="fa fa-shopping-cart"></i> Disp. <span id="disp_' +
                            inven.CODIGO + '">' + (parseInt(inven.EUNIDAD) - reserva) +
                            '</span></span></br><small class="btn btn-primary mt-3 w-100" onclick="event.preventDefault(); addProduct(\'' +
                            inven.CODIGO + '\', \'' + precio2 + '\', \'Bs\')">Ref ' + formatNumber(
                                precio2) + '</small></h5>';
                        html += '</div></div></div></div></div>';
                        html += '</div></div>';


                    }
                });


                $('#product_lists').html(html);
            };



        });
    </script>
@endsection
