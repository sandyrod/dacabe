let set_active_sidebar_menu = (element, sub_element) => {
  $("ul.nav-sidebar li").each(function () {
    if (
      $(this).text().indexOf(element) > 0 ||
      $(this).text().indexOf(sub_element) > 0
    ) {
      $(this).children().addClass("active");
    } else $(this).children().removeClass("active");
  });
};

let set_select2 = () => {
  $(".select2").select2();

  $(".select2bs4").select2({
    theme: "bootstrap4",
  });
};

function extraerNombreArchivoComprobante(valor) {
  if (!valor) {
    return "";
  }

  return String(valor)
    .split("/")
    .filter(Boolean)
    .pop();
}

function obtenerNombreComprobante(comprobante) {
  if (!comprobante) {
    return "";
  }

  try {
    const parsed = JSON.parse(comprobante);
    if (Array.isArray(parsed) && parsed.length > 0) {
      const archivo = parsed[0];
      return extraerNombreArchivoComprobante(
        archivo.ruta || archivo.nombre_original || archivo.nombre || "",
      );
    }
  } catch (e) {
    // El comprobante no viene serializado como JSON.
  }

  return extraerNombreArchivoComprobante(comprobante);
}

function construirUrlComprobante(comprobante) {
  const nombreArchivo = obtenerNombreComprobante(comprobante);
  return nombreArchivo
    ? `/imgs/comprobantes/${encodeURIComponent(nombreArchivo)}`
    : "";
}

// Hacer global la función para los botones dinámicos
function cambiarEstatusPago(id, estatus) {
  if (!confirm("¿Está seguro de cambiar el estatus del pago?")) return;
  let URL_UPDATE_PAGOS = window.URL_BASE_PAGOS + "/" + id + "/cambiar-estatus";
  // Mostrar procesando en el botón
  var btns = $(`#tabla-pagos button[onclick*='cambiarEstatusPago(${id}']`);
  btns.prop("disabled", true).text("Procesando...");
  $.ajax({
    url: URL_UPDATE_PAGOS,
    type: "POST",
    data: { _token: window.CSRF_TOKEN, estatus: estatus },
    dataType: "json",
    success: function (resp) {
      cargarPagosPendientes();
    },
    error: function () {
      alert("Error al cambiar el estatus del pago");
      btns.prop("disabled", false).text(function (i, old) {
        return estatus === "APROBADO" ? "Aprobar" : "Rechazar";
      });
    },
  });
}

// Función para ver detalles del pago
function verDetallePago(id) {
  $.ajax({
    url: window.URL_BASE_PAGOS + "/" + id + "/detalle",
    type: "GET",
    data: { _token: window.CSRF_TOKEN },
    dataType: "json",
    success: function (response) {
      console.log("Detalles recibidos:", response);

      // Crear el modal si no existe
      if (!$("#modalDetallePago").length) {
        $("body").append(`
                    <div class="modal fade" id="modalDetallePago" tabindex="-1" role="dialog" aria-labelledby="modalDetallePagoLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">Detalle del Pago</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="card mb-4 border-0 shadow-sm">
                                        <div class="card-header bg-primary text-white">
                                            <h5 class="mb-0">Información del Pago</h5>
                                        </div>
                                        <div class="card-body" style="background-color: #f8f9fa;">
                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="mr-3">
                                                            <i class="fas fa-dollar-sign fa-2x text-success"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0 text-muted">Monto Ref</h6>
                                                            <h5 class="mb-0" id="pago_monto"></h5>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="mr-3">
                                                            <i class="fas fa-money-bill-wave fa-2x text-primary"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0 text-muted">Monto Bs</h6>
                                                            <h5 class="mb-0" id="pago_monto_bs"></h5>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="mr-3">
                                                            <i class="fas fa-exchange-alt fa-2x text-info"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0 text-muted">Tasa</h6>
                                                            <h5 class="mb-0" id="pago_tasa"></h5>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="mr-3">
                                                            <i class="fas fa-calendar-alt fa-2x text-danger"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0 text-muted">Fecha de Pago</h6>
                                                            <h5 class="mb-0" id="pago_fecha"></h5>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-4 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="mr-3">
                                                            <i class="fas fa-hashtag fa-2x text-warning"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0 text-muted">Nro Referencia</h6>
                                                            <h5 class="mb-0" id="pago_referencia"></h5>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="mr-3">
                                                            <i class="fas fa-credit-card fa-2x text-purple"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0 text-muted">Tipo de Pago</h6>
                                                            <h5 class="mb-0" id="pago_tipo"></h5>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="mr-3">
                                                            <i class="fas fa-university fa-2x text-secondary"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0 text-muted">Banco Origen del pago</h6>
                                                            <h5 class="mb-0" id="pago_banco"></h5>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="mr-3">
                                                            <i class="fas fa-university fa-2x text-primary"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0 text-muted">Pago Destino</h6>
                                                            <h5 class="mb-0" id="pago_banco_destino"></h5>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="mr-3">
                                                            <i class="fas fa-file-alt fa-2x text-info"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0 text-muted">Descripción</h6>
                                                            <h5 class="mb-0" id="pago_descripcion"></h5>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="mr-3">
                                                            <i class="fas fa-image fa-2x text-success"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0 text-muted">Comprobante</h6>
                                                            <h5 class="mb-0" id="pago_comprobante">
                                                                ${
                                                                  response.pago
                                                                    .comprobante
                                                                    ? `<a href="${construirUrlComprobante(response.pago.comprobante)}" target="_blank">
                                                                            <img src="${construirUrlComprobante(response.pago.comprobante)}" alt="Comprobante" style="max-width:80px;max-height:80px;border-radius:6px;border:1px solid #ccc;cursor:pointer;">
                                                                          </a>`
                                                                    : "No disponible"
                                                                }
                                                            </h5>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                            </div>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Cliente</th>
                                                    <th>Pedido</th>
                                                    <th>Fecha Pedido</th>
                                                    <th>Monto Pago</th>
                                                </tr>
                                            </thead>
                                            <tbody id="detallesPagoBody">
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-danger" data-dismiss="modal" aria-label="Close">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
      }

      // Cerrar cualquier modal abierto
      $(".modal").modal("hide");

      // Función para formatear números con separador de miles . y separador decimal ,
      function formatearMonto(valor) {
        if (valor === null || valor === undefined || isNaN(valor)) return "";
        return parseFloat(valor).toLocaleString("es-ES", {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2,
        });
      }

      // Función para formatear fecha
      function formatearFecha(fecha) {
        if (!fecha) return "";
        const date = new Date(fecha);
        return date.toLocaleDateString("es-ES", {
          day: "2-digit",
          month: "2-digit",
          year: "numeric",
        });
      }

      // Mostrar la información del pago
      $("#pago_monto").text(formatearMonto(response.pago.monto));
      $("#pago_monto_bs").text(formatearMonto(response.pago.monto_bs));
      $("#pago_tasa").text(formatearMonto(response.pago.rate));
      $("#pago_referencia").text(response.pago.referencia ?? "");
      $("#pago_fecha").text(formatearFecha(response.pago.created_at));
      $("#pago_banco").text(
        response.pago.banco?.NOMBRE ?? response.pago.banco_codigo ?? "",
      );
      $("#pago_banco_destino").text(response.pago.pago_destino?.nombre ?? "");
      $("#pago_tipo").text(
        response.pago?.tipo_pago
          ? response.pago.tipo_pago.DPAGO
          : "No especificado",
      );
      $("#pago_descripcion").text(response.pago.descripcion ?? "");

      let comprobanteHtml = "No disponible";
      if (response.pago.comprobante) {
        const fileName = obtenerNombreComprobante(response.pago.comprobante);
        const filePath = construirUrlComprobante(response.pago.comprobante);
        if (fileName.toLowerCase().endsWith(".pdf")) {
          comprobanteHtml = `<a href="${filePath}" target="_blank" title="${fileName}">
                        <i class="fas fa-file-pdf fa-3x text-danger"></i>
                    </a>`;
        } else {
          comprobanteHtml = `<a href="${filePath}" target="_blank">
                        <img src="${filePath}" alt="Comprobante" style="max-width:80px;max-height:80px;border-radius:6px;border:1px solid #ccc;cursor:pointer;">
                    </a>`;
        }
      }
      $("#pago_comprobante").html(comprobanteHtml);

      // Limpiar y llenar la tabla con los detalles
      var tbody = $("#detallesPagoBody");
      tbody.empty();
      response.detalles.forEach(function (detalle) {
        console.warn("DETALLE");
        console.warn(detalle);
        const montoPago = detalle.monto;
        tbody.append(`
                    <tr>
                        <td>${detalle.cliente ?? ""}</td>
                        <td>${detalle.id ?? ""}</td>
                        <td>${detalle.fecha_pedido ?? ""}</td>
                        <td>${montoPago.toLocaleString("es-ES", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                    </tr>
                `);
      });

      // Mostrar el modal
      $("#modalDetallePago")
        .modal({
          backdrop: "static",
          keyboard: true,
        })
        .modal("show");

      console.log("Modal mostrado con éxito");
    },
    error: function (xhr, status, error) {
      console.error("Error:", status, error);
      alert("Error al obtener los detalles del pago");
    },
  });
}

function cargarPagosPendientes() {
  $.ajax({
    url: window.URL_BASE_PAGOS + "/pendientes",
    type: "get",
    data: { _token: window.CSRF_TOKEN },
    dataType: "json",
    success: function (response) {
      var tbody = "";
      response.forEach(function (pago) {
        // Formatear la fecha a dd/mm/yyyy
        const fecha = new Date(pago.created_at);
        const fechaFormateada = formatoFechaDMASimple(fecha);

        // Formatear el monto con punto como separador de miles y coma como decimal
        const montoFormateado = number_format(pago.monto, 2, ",", ".");

        tbody += `<tr>
                    <td>${fechaFormateada}</td>
                    <td>${pago.descripcion || ""}</td>
                    <td>${pago.vendedor_nombre}</td>
                    <td>${montoFormateado}</td>
                    <td>${pago.tipo_pago ? pago.tipo_pago.DPAGO : "No especificado"}</td>
                    <td>
                        <div class="btn-group" role="group" aria-label="Acciones">
                            <button type="button" class="btn btn-sm btn-info mr-2" onclick="verDetallePago(${pago.id})">
                                <i class="fas fa-eye"></i> 
                            </button>
                            <button type="button" class="btn btn-sm btn-success mr-2" onclick="cambiarEstatusPago(${pago.id}, 'APROBADO')">
                                <i class="fas fa-check"></i> 
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="cambiarEstatusPago(${pago.id}, 'RECHAZADO')">
                                <i class="fas fa-times"></i> 
                            </button>
                        </div>
                    </td>
                </tr>`;
      });
      $("#tabla-pagos tbody").html();
    },
    error: function (xhr, status) {
      console.log("error: ", xhr, status);
    },
    complete: function (response) {},
  });
}

// Función para formatear números con punto como separador de miles y coma como decimal
function number_format(number, decimals, dec_point, thousands_sep) {
  number = (number + "").replace(/[^0-9+-Ee.]/g, "");
  var n = !isFinite(+number) ? 0 : +number,
    prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
    sep = typeof thousands_sep === "undefined" ? "," : thousands_sep,
    dec = typeof dec_point === "undefined" ? "." : dec_point,
    s = "",
    toFixedFix = function (n, prec) {
      var k = Math.pow(10, prec);
      return "" + Math.round(n * k) / k;
    };
  s = (prec ? toFixedFix(n, prec) : "" + Math.round(n)).split(".");
  if (s[0].length > 3) {
    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
  }
  if ((s[1] || "").length < prec) {
    s[1] = s[1] || "";
    s[1] += new Array(prec - s[1].length + 1).join("0");
  }
  return s.join(dec);
}

// Función para formatear fecha a dd/mm/yyyy
function formatoFechaDMASimple(date) {
  const options = { year: "numeric", month: "2-digit", day: "2-digit" };
  return new Date(date)
    .toLocaleDateString("es-ES", options)
    .replace(/\//g, "/");
}

jQuery(function ($) {
  // Handle export to Excel button click
  $(document).on("click", "#export-excel-btn2", function (e) {
    e.preventDefault();

    // Get current filter values
    const cdepos = $("#CDEPOS").val();
    const cgrupo = $("#CGRUPO").val();
    const search = $("#search").val();

    // Get the base URL from the button's data attribute
    const exportUrl =
      $(this).data("export-url") || "/order-inven/export-products";
    const params = new URLSearchParams();

    if (cdepos) params.append("cdepos", cdepos);
    //if (cgrupo && cgrupo !== 'TODOS') params.append('cgrupo', cgrupo);
    params.append("cgrupo", cgrupo);
    if (search) params.append("search", search);

    // Redirect to the export URL with filters
    window.location.href = `${exportUrl}?${params.toString()}`;
  });

  // Inicializar modales
  console.log("Inicializando modal");

  // Inicializar el modal cuando se muestre la pestaña de pagos
  $(document).on(
    "shown.bs.tab",
    'a[data-toggle="pill"][href="#custom-tabs-pagos"]',
    function (e) {
      console.log("Pestaña de pagos mostrada");

      // Inicializar el modal
      $("#modalDetallePago").modal({
        backdrop: "static",
        keyboard: true,
      });

      // Evento para limpiar campos cuando se cierre
      $("#modalDetallePago").on("hidden.bs.modal", function () {
        $("#montoPago").text("");
        $("#clientePago").text("");
        $("#fechaPago").text("");
      });
    },
  );

  const URL = '/get-tasa-bcv';
  const URLBCV = '/tasa-bcv/saved';
  const TOKEN = window.CSRF_TOKEN || "";
  const URL_PAGOS = "/pagos/pendientes";

  const getSavedRate = () => {
    $.ajax({
      url: URLBCV,
      type: "get",
      data: { _token: TOKEN },
      dataType: "json",
      success: function (response) {
        let rate = "";
        if (response.data) {
          let dollarLocale = Intl.NumberFormat("es-VE");
          rate =
            '<div class="btn btn btn-info bcv-rate"> <b> ' +
            dollarLocale.format(response.data.rate) +
            " | " +
            dollarLocale.format(response.data.rate2) +
            "</b></div>";
        }
        $(".bcv-rate").html(rate);
      },
      error: function (xhr, status) {
        console.log("error: ", xhr, status);
        $(".bcv-rate").html("");
      },
      complete: function (response) {},
    });
  };

  $.ajax({
    url: URL,
    type: "get",
    data: { _token: TOKEN },
    dataType: "json",
    success: function (response) {
      //if (response.data && (response.data.user.email == 'dacabe@gmail.com')){
      if (response.data) {
        let rate = "";
        let dollarLocale = Intl.NumberFormat("es-VE");
        if (response.data?.user?.email == "dacabe@gmail.com") {
          rate =
            '<div class="btn btn btn-info bcv-rate"> <b>  ' +
            dollarLocale.format(response.data.rate) +
            " | " +
            dollarLocale.format(response.data.rate2) +
            "</b></div>";
        } else {
          rate =
            '<div class="btn btn btn-info bcv-rate"> <b>  ' +
            dollarLocale.format(response.data.rate) +
            "</b></div>";
        }
        console.log("inside");
        $(".bcv-rate").html(rate);
      }
    },
    error: function (xhr, status) {
      cargarPagosPendientes();
    },
    complete: function (response) {},
  });

  // Cargar pagos pendientes al mostrar la pestaña "Pagos Recibidos"
  $(document).on(
    "shown.bs.tab",
    'a[data-toggle="pill"][href="#custom-tabs-pagos"]',
    function (e) {
      cargarPagosPendientes();
    },
  );

  // También cargar pagos pendientes si la pestaña está activa al cargar la página
  if (
    $("#custom-tabs-pagos").hasClass("active") ||
    $("#custom-tabs-pagos-tab").hasClass("active")
  ) {
    cargarPagosPendientes();
  }

  function cargarPagosPendientes() {
    $.ajax({
      url: URL_PAGOS,
      type: "get",
      data: { _token: TOKEN },
      dataType: "json",
      success: function (response) {
        var tbody = "";
        response.forEach(function (pago) {
          // Formatear la fecha a dd/mm/yyyy
          const fecha = new Date(pago.created_at);
          const fechaFormateada = formatoFechaDMASimple(fecha);

          // Formatear el monto con punto como separador de miles y coma como decimal
          const montoFormateado = number_format(pago.monto, 2, ",", ".");

          tbody += `<tr>
                            <td>${fechaFormateada}</td>
                            <td>${pago.descripcion || ""}</td>
                            <td>${pago.vendedor_nombre}</td>
                            <td>
                                <div>
                                    <span class="text-success font-weight-bold">$${montoFormateado}</span><br>
                                    <small class="text-muted">Tasa: <span class="badge badge-info">${number_format(pago.rate, 2, ",", ".")}</span></small><br>
                                    <small class="text-primary">Bs. ${number_format(pago.monto * pago.rate, 2, ",", ".")}</small>
                                </div>
                            </td>
                            <td><small>${pago.tipo_pago ? pago.tipo_pago.DPAGO : "No especificado"}</small></td>
                            <td>
                                <div class="btn-group" role="group" aria-label="Acciones">
                                    <button type="button" class="btn btn-sm btn-info mr-2" onclick="verDetallePago(${pago.id})">
                                        <i class="fas fa-eye"></i> 
                                    </button>
                                    <button type="button" class="btn btn-sm btn-success mr-2" onclick="cambiarEstatusPago(${pago.id}, 'APROBADO')">
                                        <i class="fas fa-check"></i> 
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="cambiarEstatusPago(${pago.id}, 'RECHAZADO')">
                                        <i class="fas fa-times"></i> 
                                    </button>
                                </div>
                            </td>
                        </tr>`;
        });
        $("#tabla-pagos tbody").html(tbody);
      },
      error: function (xhr, status) {
        console.log("error: ", xhr, status);
      },
      complete: function (response) {},
    });
  }
});
