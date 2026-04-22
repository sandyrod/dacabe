@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel') . ' - Pagos del Vendedor')
@section('titulo_header', 'Mis Pagos')
@section('subtitulo_header', 'Pagos Registrados')

@section('styles')
    <link rel="stylesheet" href="{{ asset('theme/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <style>
        .espera-aprobacion {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
        }

        .espera-aprobacion .btn {
            opacity: 0.7;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tabla-pagos-vendedor" class="table table-striped table-responsive table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Fechaasdas</th>
                                <th>Descripción</th>
                                <th>Vendedor</th>
                                <th>Monto</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('theme/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('theme/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('theme/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('theme/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <script>
        const URL = '{{ url('vendedores/pagos') }}';
        const TOKEN = '{{ csrf_token() }}';
        const USER_ID = '{{ Auth::id() }}';

        let init_datatable = () => {
            return {
                language: {
                    url: '{{ asset('theme/plugins/datatables/venezuela.json') }}'
                },
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: URL,
                    data: function(d) {
                        d.datatable = 1;
                        d.user_id = USER_ID;
                    }
                },
                columns: [{
                        data: 'fecha',
                        name: 'fecha'
                    },
                    {
                        data: 'descripcion',
                        name: 'descripcion'
                    },
                    {
                        data: 'vendedor_nombre',
                        name: 'vendedor_nombre'
                    },
                    {
                        data: 'monto',
                        name: 'monto',
                        render: function(data, type, row) {
                            const currency = (row.moneda_pago === 'bs' || row.monto_bs > 0) ? 'Bs. ' : '$';
                            const amount = row.moneda_pago === 'bs' ? row.monto_bs : data;
                            return currency + number_format(amount, 2, ',', '.');
                        }
                    },
                    {
                        data: 'estatus',
                        name: 'estatus',
                        render: function(data) {
                            let badgeClass = 'badge-secondary';
                            if (data === 'PENDIENTE') {
                                badgeClass = 'badge-warning';
                            } else if (data === 'APROBADO') {
                                badgeClass = 'badge-success';
                            } else if (data === 'RECHAZADO') {
                                badgeClass = 'badge-danger';
                            }
                            return `<span class="badge ${badgeClass}">${data}</span>`;
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        render: function(data, type, row) {
                            let rowClass = '';
                            if (row.estatus === 'PENDIENTE') {
                                rowClass = 'espera-aprobacion';
                            }

                            return `
                            <div class="btn-group ${rowClass}" role="group" aria-label="Acciones">
                                <button type="button" class="btn btn-sm btn-info mr-2 ver-detalle" data-id="${row.id}">
                                    <i class="fas fa-eye"></i> Ver Detalle
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="eliminarPago(${row.id})">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </div>
                        `;
                        }
                    }
                ],
                rowCallback: function(row, data) {
                    if (data.estatus === 'PENDIENTE') {
                        $(row).addClass('espera-aprobacion');
                    }
                }
            };
        };

        let table = $('#tabla-pagos-vendedor').DataTable(init_datatable());

        // Función para formatear números
        function number_format(number, decimals, dec_point, thousands_sep) {
            number = (number + '').replace(/[^0-9+-Ee.]/g, '');
            var n = !isFinite(+number) ? 0 : +number,
                prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
                dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
                s = '',
                toFixedFix = function(n, prec) {
                    var k = Math.pow(10, prec);
                    return '' + Math.round(n * k) / k;
                };
            s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
            if (s[0].length > 3) {
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
            }
            if ((s[1] || '').length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1).join('0');
            }
            return s.join(dec);
        }

        // Evento para ver detalles
        $(document).on('click', '.ver-detalle', function() {
            verDetallePago($(this).data('id'));
        });

        // Función para eliminar un pago
        function eliminarPago(id) {
            Swal.fire({
                title: '¿Está seguro?',
                text: '¿Desea eliminar este pago?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: URL + '/' + id,
                        type: 'DELETE',
                        data: {
                            _token: TOKEN
                        },
                        success: function(response) {
                            Swal.fire(
                                'Eliminado',
                                'El pago ha sido eliminado.',
                                'success'
                            );
                            table.draw();
                        },
                        error: function(xhr) {
                            Swal.fire(
                                'Error',
                                'No se pudo eliminar el pago.',
                                'error'
                            );
                        }
                    });
                }
            });
        }
    </script>
@endsection
