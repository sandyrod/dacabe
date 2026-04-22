<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Route::group(['middleware' => 'httpCache:60'], function() {
//Route::get('/', 'LandingController@index');
Route::get('get-calendar', 'LandingController@getCalendarDetail');
//});


Route::get('privacy', 'LandingController@privacy');
Route::get('dacabe', 'LandingController@indexDacabe');
Route::get('lista-vendedores', 'LandingController@vendedores');
Route::get('vendedor/{codigo_vendedor?}', 'LandingController@vendedor');
Route::get('catalogo', 'LandingController@catalogoFull');
Route::get('lista-productos', 'LandingController@catalogoFullRecargo');
Route::get('catalogo/{vendedor_id?}', 'LandingController@catalogo');
Route::get('catalogo/{vendedor_id?}/{categoria?}', 'LandingController@catalogo');

Route::get('catalogo-productos/categoria/{categoria?}', 'LandingController@catalogoCategoria');
Route::get('catalogo-productos/{producto?}', 'LandingController@catalogoProducto');

if (env('APP_NAME') == 'SDCLOUD') {
    Route::get('/', 'LandingController@index');
} else {
    Route::get('/', function () {
        return view('auth.login');
    });
}

Route::get('login', function () {
    return view('auth.login');
});

Auth::routes(['register' => false]);

Route::group(['middleware' => 'auth'], function () {
    Route::get('/inicio', 'HomeController@index')->name('inicio');
    Route::get('/manager-dashboard', 'ManagerDashboardController@index')->name('manager.dashboard');
    Route::get('/facturacion-dashboard', 'FacturacionDashboardController@index')->name('facturacion.dashboard');



    Route::get('comisiones-recibidas', ['as' => 'comisiones.recibidas', 'uses' => 'Admin\ComisionVendedorController@comisionesRecibidas']);
    Route::get('comisiones/todos-ids', 'Admin\ComisionVendedorController@getTodosIds')->name('comisiones.todos_ids');
    Route::get('comisiones/estado-cuenta', 'Admin\ComisionVendedorController@estadoCuenta')->name('comisiones.estado_cuenta');
    Route::get('comisiones/mi-estado-cuenta', 'Admin\ComisionVendedorController@miEstadoCuenta')->name('comisiones.mi_estado_cuenta');
    Route::get('comisiones/saldo-vendedor/{correo}', 'Admin\ComisionVendedorController@saldoVendedor')->name('comisiones.saldo_vendedor');
    Route::get('comisiones/estado-cuenta-vendedor/{correo}', 'Admin\ComisionVendedorController@estadoCuentaVendedor')->name('comisiones.estado_cuenta_vendedor');
    Route::resource('comisiones', 'Admin\ComisionVendedorController')->only(['index', 'update']);
    Route::get('comisiones/exportar', ['as' => 'comisiones.exportar', 'uses' => 'Admin\ComisionVendedorController@exportar']);
    Route::post('comisiones/{pedidoId}/editar-monto', ['as' => 'comisiones.editar_monto', 'uses' => 'Admin\ComisionVendedorController@updateMontoComision']);
    Route::post('comisiones/{pedidoId}/aprobar', ['as' => 'comisiones.aprobar', 'uses' => 'Admin\ComisionVendedorController@aprobar']);
    Route::post('comisiones/{pedidoId}/rechazar', ['as' => 'comisiones.rechazar', 'uses' => 'Admin\ComisionVendedorController@rechazar']);
    Route::get('comisiones/{pedidoId}/detalles', ['as' => 'comisiones.detalles', 'uses' => 'Admin\ComisionVendedorController@getDetalles']);
    Route::get('comisiones/{pedidoId}/pago', ['as' => 'comisiones.pago', 'uses' => 'Admin\ComisionVendedorController@getDetallePago']);
    Route::get('comisiones/{pedidoId}/pago-comision', ['as' => 'comisiones.pago_comision', 'uses' => 'Admin\ComisionVendedorController@getComisionPagoDetalle']);
    
    // Inventario Inicial Routes
    Route::get('inventario-inicial', 'Admin\InventarioInicialController@index')->name('admin.inventario.inicial');
    Route::post('inventario-inicial', 'Admin\InventarioInicialController@store')->name('admin.inventario.inicial.store');
    Route::get('inventario-inicial/records', 'Admin\InventarioInicialController@getRecords')->name('admin.inventario.inicial.records');
    Route::get('inventario-inicial/stats', 'Admin\InventarioInicialController@getStats')->name('admin.inventario.inicial.stats');
    Route::delete('inventario-inicial/{id}', 'Admin\InventarioInicialController@destroy')->name('admin.inventario.inicial.destroy');
    Route::get('productos/search', 'Admin\InventarioInicialController@searchProductos')->name('admin.productos.search');
    Route::post('comisiones/{pedidoId}/confirmar-recibido', ['as' => 'comisiones.confirmar_recibido', 'uses' => 'Admin\ComisionVendedorController@confirmarRecibido']);
    Route::post('comisiones/confirmar-recibido-lote', ['as' => 'comisiones.confirmar_recibido_lote', 'uses' => 'Admin\ComisionVendedorController@confirmarRecibidoLote']);
    Route::get('comisiones/{identificador}/comisiones-grupo', ['as' => 'comisiones.grupo_detalle', 'uses' => 'Admin\ComisionVendedorController@getComisionesGrupo']);

    Route::get('comisiones/destinos', ['as' => 'comisiones.destinos', 'uses' => 'Admin\ComisionVendedorController@getPagoDestinos']);
    Route::get('comisiones/bancos', ['as' => 'comisiones.bancos', 'uses' => 'Admin\ComisionVendedorController@getPagoBancos']);
    Route::get('debug-banks', function() {
        $banks = DB::connection('mysql')->table('banks')->select('codigo', 'nombre')->orderBy('nombre')->get();
        return response()->json($banks);
    })->name('debug.banks');


    // Rutas para pagos de vendedores
    Route::match(['get', 'post'], 'vendedores/pagos/index', 'App\Http\Controllers\VendedorPagoController@index')
        ->name('vendedores.pagos.index');

    Route::get('vendedores/pagos/clientes', 'App\Http\Controllers\VendedorPagoController@clientes')
        ->name('vendedores.pagos.clientes');

    Route::get('vendedores/pagos/pedidos/{cliente}', 'App\Http\Controllers\VendedorPagoController@getPedidosCliente')
        ->name('vendedores.pagos.pedidos');

    Route::get('vendedores/pagos/pedidos/revision/{cliente}', 'App\Http\Controllers\VendedorPagoController@getPedidosClienteRevision')
        ->name('vendedores.pagos.pedidos.revision');

    // Rutas para gestión de pedidos sin cruce
    Route::get('vendedores/pagos/pedidos-sin-cruce', 'App\Http\Controllers\VendedorPagoController@getPedidosSinCruce')
        ->name('vendedores.pagos.pedidos.sin.cruce');

    Route::get('vendedores/pagos/buscar-cliente', 'App\Http\Controllers\VendedorPagoController@buscarClienteRelacion')
        ->name('vendedores.pagos.buscar.cliente');

    Route::get('vendedores/pagos/relacionar-cliente', 'App\Http\Controllers\VendedorPagoController@relacionarCliente')
        ->name('vendedores.pagos.relacionar.cliente');

    // Rutas para sincronización de clientes
    Route::get('vendedores/sincronizar-clientes', 'App\Http\Controllers\SincronizarClientesController@index')
        ->name('sincronizar.clientes.index');

    Route::get('sincronizar/clientes/get-pedidos-sin-cruce', 'App\Http\Controllers\SincronizarClientesController@getPedidosSinCruce')
        ->name('sincronizar.clientes.get.pedidos.sin.cruce');

    Route::get('sincronizar/clientes/buscar-cliente', 'App\Http\Controllers\SincronizarClientesController@buscarCliente')
        ->name('sincronizar.clientes.buscar.cliente');

    Route::post('sincronizar/clientes/relacionar-cliente', 'App\Http\Controllers\SincronizarClientesController@relacionarCliente')
        ->name('sincronizar.clientes.relacionar.cliente');

    Route::post('sincronizar/clientes/asignar/{pedidoId}', 'App\Http\Controllers\SincronizarClientesController@asignarCliente')
        ->name('sincronizar.clientes.asignar');

    Route::get('vendedores/pagos/pedidos/aprobadas/{cliente}', 'App\Http\Controllers\VendedorPagoController@getPedidosClientePagadas')
        ->name('vendedores.pagos.pedidos.aprobadas');

    Route::get('vendedores/pagos/pedidos-en-revision/{cliente}', 'App\Http\Controllers\VendedorPagoController@getPedidosEnRevision')
        ->name('vendedores.pagos.pedidos-en-revision');

    Route::get('vendedores/pagos/metodo/{cliente}', 'App\Http\Controllers\VendedorPagoController@metodoPago')
        ->name('vendedores.pagos.metodo');

    Route::post('vendedores/pagos/metodo/redirect', 'App\Http\Controllers\VendedorPagoController@redirectToIndex')
        ->name('vendedores.pagos.metodo.redirect');

    Route::post('vendedores/pagos/procesar/{cliente}', 'App\Http\Controllers\VendedorPagoController@procesarPago')
        ->name('vendedores.pagos.procesar');

    Route::get('vendedores/pagos/{pedido}', 'App\Http\Controllers\VendedorPagoController@show')
        ->name('vendedores.pagos.show');

    Route::post('vendedores/pagos', 'App\Http\Controllers\VendedorPagoController@store')
        ->name('vendedores.pagos.store');

    Route::post('vendedores/pagos/multiple', 'App\Http\Controllers\VendedorPagoController@storeMultiple')
        ->name('vendedores.pagos.storeMultiple');

    Route::post('vendedores/pagos/confirmar', 'App\Http\Controllers\VendedorPagoController@mostrarConfirmacion')
        ->name('vendedores.pagos.confirmar');

    Route::get('vendedores/pagos/print', 'App\Http\Controllers\VendedorPagoController@print')
        ->name('vendedores.pagos.print');

    Route::get('vendedores/pagos/comprobante/{pago}', 'App\Http\Controllers\VendedorPagoController@mostrarComprobante')
        ->name('vendedores.pagos.mostrarComprobante');

    Route::get('vendedores/pagos/comprobante-pedido/{pedido}', 'App\Http\Controllers\VendedorPagoController@mostrarComprobantePedido')
        ->name('vendedores.pagos.mostrarComprobantePedido');

    Route::post('vendedores/pedidos/{pedido}/subir-retencion', 'App\Http\Controllers\VendedorPagoController@subirComprobanteRetencion')
        ->name('vendedores.pedidos.subir.retencion');

    // NUEVAS RUTAS REPLICADAS (v2)
    Route::group(['prefix' => 'vendedores/pagos-v2', 'as' => 'vendedores.pagos_new.'], function () {
        Route::get('clientes', 'VendedorPagoReplicadoController@clientes')->name('clientes');
        Route::get('pedidos/{cliente}', 'VendedorPagoReplicadoController@getPedidosCliente')->name('pedidos');
        Route::get('pedidos/revision/{cliente}', 'VendedorPagoReplicadoController@getPedidosClienteRevision')->name('pedidos.revision');
        Route::get('pedidos/aprobadas/{cliente}', 'VendedorPagoReplicadoController@getPedidosClientePagadas')->name('pedidos.aprobadas');
        Route::get('metodo/{cliente}', 'VendedorPagoReplicadoController@metodoPago')->name('metodo');
        Route::post('index', 'VendedorPagoReplicadoController@index')->name('index');
        Route::post('store', 'VendedorPagoReplicadoController@store')->name('store');
    });

    // Rutas para pagos recibidos (PagosController)
    Route::get('pagos/pendientes', 'PagosController@pendientes')->name('pagos.pendientes');
    Route::post('pagos/{id}/cambiar-estatus', 'PagosController@cambiarEstatus')->name('pagos.cambiarEstatus');
    Route::get('pagos/{id}/detalle', 'PagosController@detalle')->name('pagos.detalle');
    Route::post('pagos/{id}/aprobar-retencion', 'PagosController@aprobarRetencion')->name('pagos.aprobarRetencion');
});

Route::get('users/card', ['as' => 'users.card', 'uses' => 'UsersController@showCardList']);
Route::get('users/print', ['as' => 'users.print', 'uses' => 'UsersController@showPrintList']);
Route::get('users/{user_id}/permissions', ['as' => 'users.permissions', 'uses' => 'UsersController@editPermissions']);
Route::post('users/permissions', ['as' => 'users.permissions', 'uses' => 'UsersController@updatePermissions']);
Route::resource('users', 'UsersController');

Route::post('descuento-global/update', ['as' => 'descuento-global.update', 'uses' => 'OrderInvenController@actualizarDescuentoGlobal']);
Route::get('descuento-global', ['as' => 'descuento-global', 'uses' => 'OrderInvenController@verDescuentoGlobal']);

Route::resource('roles', 'RolesController')->except(['show']);
Route::post('roles/set-active', ['as' => 'roles.setActiveRole', 'uses' => 'RolesController@setActiveRole']);
Route::get('roles/print', ['as' => 'roles.print', 'uses' => 'RolesController@showPrintList']);

Route::resource('permissions', 'PermissionsController')->except(['show']);
Route::get('permissions/print', ['as' => 'permissions.print', 'uses' => 'PermissionsController@showPrintList']);

Route::post('companies/change-status/{status}', 'CompaniesController@changeStatus');
Route::get('companies/print', ['as' => 'companies.print', 'uses' => 'CompaniesController@showPrintList']);
Route::resource('companies', 'CompaniesController');
Route::get('companies/get-permissions/{id}', 'CompaniesController@getPermissions');
Route::post('companies/set-permissions', 'CompaniesController@setPermissions');

Route::get('company-status/print', ['as' => 'company-status.print', 'uses' => 'CompanyStatusController@showPrintList']);
Route::resource('company-status', 'CompanyStatusController');

Route::get('categories/print', ['as' => 'categories.print', 'uses' => 'CategoriesController@showPrintList']);
Route::resource('categories', 'CategoriesController');
Route::resource('banks', 'BankController');

//Route::get('posts/print', ['as' => 'posts.print', 'uses' => 'PostsController@showPrintList']);
//Route::resource('posts','PostsController');

Route::get('modules/print', ['as' => 'modules.print', 'uses' => 'ModulesController@showPrintList']);
Route::resource('modules', 'ModulesController')->except(['show']);

Route::get('master-key', ['as' => 'serials.masterkey', 'uses' => 'SerialsController@masterKey']);
Route::get('serials/print', ['as' => 'serials.print', 'uses' => 'SerialsController@showPrintList']);
Route::resource('serials', 'SerialsController')->except(['show', 'destroy', 'update']);

Route::get('commands/print', ['as' => 'commands.print', 'uses' => 'CommandsController@showPrintList']);
Route::resource('commands', 'CommandsController')->except(['show']);

Route::get('drugstores/print', ['as' => 'drugstores.print', 'uses' => 'DrugstoresController@showPrintList']);
Route::resource('drugstores', 'DrugstoresController')->except(['show']);

Route::get('ftp/print', ['as' => 'ftp.print', 'uses' => 'FtpController@showPrintList']);
Route::resource('ftp', 'FtpController')->except(['show']);

Route::get('themes/print', ['as' => 'themes.print', 'uses' => 'ThemesController@showPrintList']);
Route::resource('themes', 'ThemesController')->except(['show']);

Route::get('landings/print', ['as' => 'landings.print', 'uses' => 'LandingsController@showPrintList']);
Route::resource('landings', 'LandingsController')->except(['show']);

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/dacabe', 'LandingController@dacabe')->name('dacabe');

Route::get('products', 'InvenController@getProductsSearch');
Route::get('get-products', 'InvenController@getProducts');

Route::get('get-company-indicators', 'CompaniesController@getCompanyIndicators');
Route::get('get-farmax-details', 'CompaniesController@getFarmaxDownloads');

Route::get('fabioss', 'PagesController@getFabios');
Route::get('menu/{landing?}', 'PagesController@getLandingMenu');

Route::get('andrea-valentina', 'PagesController@getAndrea');
Route::get('15-de-andrea', 'PagesController@getAndrea2');

Route::get('crew-anchor', 'PagesController@getCrewAnchor');


// PEDIDOS
Route::get('tasa-bcv', 'HomeController@getBcvRate');
Route::get('get-tasa-bcv', 'HomeController@getBcvRateNew');
Route::get('tasa-bcv/saved', 'HomeController@getLastRate');
Route::get('ultima-tasa-bcv', 'HomeController@getLastRate');
Route::post('store-tasa-bcv', 'HomeController@storeRate')->name('tasa-bcv.store');
Route::get('get-discount', 'HomeController@getLastDiscount');

Route::get('unimed/print', ['as' => 'unimed.print', 'uses' => 'UnimedController@showPrintList']);
Route::resource('unimed', 'UnimedController')->except(['show']);

Route::get('dpto/print', ['as' => 'dpto.print', 'uses' => 'DptoController@showPrintList']);
Route::resource('dpto', 'DptoController')->except(['show']);

Route::get('order-clients/print', ['as' => 'order-clients.print', 'uses' => 'OrderClientsController@showPrintList']);
Route::resource('order-clients', 'OrderClientsController')->except(['show']);

Route::get('tipprod/print', ['as' => 'tipprod.print', 'uses' => 'TipprodController@showPrintList']);
Route::resource('tipprod', 'TipprodController')->except(['show']);

Route::get('zonas/print', ['as' => 'zonas.print', 'uses' => 'ZonasController@showPrintList']);
Route::resource('zonas', 'ZonasController')->except(['show']);

Route::get('vendedores/print', ['as' => 'vendedores.print', 'uses' => 'VendedoresController@showPrintList']);
Route::resource('vendedores', 'VendedoresController')->except(['show']);

Route::get('administradores/print', ['as' => 'administradores.print', 'uses' => 'AdministradoresController@showPrintList']);
Route::resource('administradores', 'AdministradoresController')->except(['show']);

Route::get('productos-futuros/print', ['as' => 'productos-futuros.print', 'uses' => 'ProductosFuturosController@showPrintList']);
Route::resource('productos-futuros', 'ProductosFuturosController')->except(['show']);

Route::post('marketing/process-marketing/{id?}', ['as' => 'process-marketing', 'uses' => 'MarketingController@processMarketing']);
Route::resource('marketing', 'MarketingController');
Route::post('update-marketing-detail', ['as' => 'update-marketing-detail', 'uses' => 'MarketingController@updateMarketingDetail']);
Route::post('delete-marketing-detail', ['as' => 'delete-marketing-detail', 'uses' => 'MarketingController@deleteMarketingDetail']);

Route::post('guardar-descuento', ['as' => 'guardar-descuento', 'uses' => 'OrderInvenController@guardarDescuento']);

// Move the export route outside the auth group temporarily for testing
Route::get('order-inven/export-products', ['as' => 'order-inven.export-products', 'uses' => 'OrderInvenController@exportProducts']);

Route::group(['middleware' => 'auth'], function () {
    Route::post('get-order-inven', ['as' => 'get-order-inven', 'uses' => 'OrderInvenController@getProducts']);
    Route::get('order-inven/print', ['as' => 'order-inven.print', 'uses' => 'OrderInvenController@showPrintList']);
    Route::get('shoppingcart', ['as' => 'shoppingcart', 'uses' => 'OrderInvenController@shoppingcart']);
    Route::resource('order-inven', 'OrderInvenController')->except(['show']);

    Route::get('modificar-precios', 'OrderInvenPriceController@index')->name('order-inven.modify-prices');
    Route::post('update-product-price', 'OrderInvenPriceController@updatePrice')->name('order-inven.update-price');
    Route::post('batch-update-prices', 'OrderInvenPriceController@batchUpdate')->name('order-inven.batch-update');
});
Route::post('store-inven-photo', ['as' => 'store-inven-photo', 'uses' => 'OrderInvenController@storePhoto']);

Route::post('add-to-cart', ['as' => 'add-to-cart', 'uses' => 'PedidosController@addToCart']);
Route::get('vendedor/pagos/cliente/{rif}', ['as' => 'vendedor.pagos.cliente', 'uses' => 'VendedorPagoController@cliente']);
Route::post('vendedor/pagos/confirmar', ['as' => 'vendedor.pagos.confirmar', 'uses' => 'VendedorPagoController@mostrarConfirmacion']);
Route::post('vendedor/pagos/procesar/{rif}', ['as' => 'vendedores.pagos.procesar', 'uses' => 'VendedorPagoController@procesarPago']);
Route::get('drop-order', ['as' => 'drop-order', 'uses' => 'PedidosController@dropOrder']);
Route::get('view-cart', ['as' => 'view-cart', 'uses' => 'PedidosController@viewCart']);
Route::get('view-order/{order_id?}', ['as' => 'view-order', 'uses' => 'PedidosController@viewOrder']);
Route::post('modify-qty', ['as' => 'modify-qty', 'uses' => 'PedidosController@modifyQty']);
Route::post('delete-item', ['as' => 'delete-item', 'uses' => 'PedidosController@deleteItem']);
Route::post('save-order', ['as' => 'save-order', 'uses' => 'PedidosController@saveOrder']);
Route::post('update-order-products', ['as' => 'update-order-products', 'uses' => 'PedidosController@updateOrderProducts']);
Route::get('get-seller-balance/{seller_id?}', ['as' => 'get-seller-balance', 'uses' => 'PedidosController@getSellerBalance']);
Route::post('order-save-payment', ['as' => 'order-save-payment', 'uses' => 'PedidosController@orderSavePayment']);
Route::post('order-delete-payment', ['as' => 'order-delete-payment', 'uses' => 'PedidosController@orderDeletePayment']);
Route::get('print-order/{order_id?}', ['as' => 'print-order', 'uses' => 'PedidosController@printOrder']);
Route::get('orders-pending-by-seller/{seller_id}', ['as' => 'orders-pending-by-seller', 'uses' => 'PedidosController@getOrderPendingBySeller']);
Route::post('update-estatus-order', ['as' => 'update-estatus-order', 'uses' => 'PedidosController@updateEstatusOrder']);

Route::get('seller-balance-print/{seller_id}', ['as' => 'seller-balance-print', 'uses' => 'PedidosController@sellerBalancePrint']);

Route::get('pedidos/print', ['as' => 'pedidos.print', 'uses' => 'PedidosController@showPrintList']);
Route::resource('pedidos', 'PedidosController')->except(['show']);
Route::get('pedidos/pdf-mail/{id}', 'PedidosController@generateEmailPdf');
Route::get('pedidos/verify-client/{id}', 'PedidosController@verifyClient');
Route::get('pedidos/pdf/{id}', 'PedidosController@generatePdf');

// DESPACHOS
Route::get('despachos/print', ['as' => 'despachos.print', 'uses' => 'DespachosController@showPrintList']);
Route::resource('despachos', 'DespachosController')->except(['show']);
Route::post('despachos/dispatch', ['as' => 'despachos.dispatch', 'uses' => 'DespachosController@dispatchOrder']);

// GASTOS
Route::get('branches/print', ['as' => 'branches.print', 'uses' => 'BranchesController@showPrintList']);
Route::resource('branches', 'branchesController')->except(['show']);
Route::get('expense-groups/print', ['as' => 'expense-groups.print', 'uses' => 'ExpenseGroupsController@showPrintList']);
Route::resource('expense-groups', 'ExpenseGroupsController')->except(['show']);
Route::get('expenses/print', ['as' => 'expenses.print', 'uses' => 'ExpensesController@showPrintList']);
Route::resource('expenses', 'ExpensesController')->except(['show']);
Route::get('expense-statistics', ['as' => 'expense-statistics', 'uses' => 'ExpensesController@showStatistics']);
Route::post('get-expense-statistics', ['as' => 'get-expense-statistics', 'uses' => 'ExpensesController@getExpensesController']);

Route::resource('descuentos', 'DescuentosController')->except(['show']);
Route::get('descuentos/print', ['as' => 'descuentos.print', 'uses' => 'DescuentosController@showPrintList']);

Route::resource('pago_destinos', 'PagoDestinosController')->except(['show']);
Route::get('pago_destinos/print', ['as' => 'pago_destinos.print', 'uses' => 'PagoDestinosController@showPrintList']);

Route::get('consulta-cliente/{rif?}', ['as' => 'consulta-cliente', 'uses' => 'PedidosController@verifyClientSeniat']);
Route::post('consulta-cliente-seniat', ['as' => 'consulta-cliente-seniat', 'uses' => 'PedidosController@verifyClientSeniatNew']);

Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'role:admin|admin_pedidos|gerente|vendedor|facturacion_dacabe']], function () {
    Route::resource('pagos', 'Admin\PagoController')->only(['index']);
    Route::get('pagos/aprobar', ['as' => 'pagos.aprobar', 'uses' => 'Admin\PagoController@aprobar']);
    Route::get('cuentas-por-cobrar', ['as' => 'admin.cuentas_por_cobrar.index', 'uses' => 'Admin\CuentasPorCobrarController@index']);
    Route::get('pagos/export', ['as' => 'pagos.export', 'uses' => 'Admin\PagoController@export']);
    Route::get('pagos/trazabilidad/{pedido_id}', ['as' => 'pagos.trazabilidad', 'uses' => 'Admin\PagoController@trazabilidad']);
    Route::get('productos/movimiento', 'Admin\ProductMovementController@index')->name('admin.productos.movimiento');
    Route::get('productos/auditoria', 'Admin\ProductAuditController@index')->name('admin.productos.auditoria');
    Route::get('productos/auditoria/detalle/{codigo}', 'Admin\ProductAuditController@detalleProducto')->name('admin.productos.auditoria.detalle');
    Route::get('productos/auditoria/export', 'Admin\ProductAuditController@export')->name('admin.productos.auditoria.export');
    Route::get('pedidos/detalle-ajax/{pedidoId}', 'Admin\ProductAuditController@getDetallePedidoAjax')->name('admin.pedidos.detalle.ajax');
    Route::get('pedidos-gestion', 'Admin\PedidoGestionController@index')->name('admin.pedidos.gestion');
    Route::post('toggle-factura-order', 'Admin\PedidoGestionController@toggleFactura')->name('admin.pedidos.toggleFactura');
    Route::post('update-retention-order', 'Admin\PedidoGestionController@updateRetention')->name('admin.pedidos.updateRetention');
    Route::post('update-dias-credito-order', 'Admin\PedidoGestionController@updateDiasCredito')->name('admin.pedidos.updateDiasCredito');
    Route::post('anular-pedido', 'Admin\PedidoGestionController@anularPedido')->name('admin.pedidos.anular');
    Route::post('anular-pedido-sin-reserva', 'Admin\PedidoGestionController@anularPedidoSinReserva')->name('admin.pedidos.anular.sinreserva');

    // Ajustes de pedido (cargos / notas de crédito)
    Route::get('pedidos/{pedidoId}/ajustes', 'Admin\PedidoGestionController@getAjustes')->name('admin.pedidos.ajustes.index');
    Route::post('pedidos/{pedidoId}/ajustes', 'Admin\PedidoGestionController@storeAjuste')->name('admin.pedidos.ajustes.store');
    Route::delete('pedidos-ajustes/{ajusteId}', 'Admin\PedidoGestionController@destroyAjuste')->name('admin.pedidos.ajustes.destroy');

    Route::get('pedidos-iva-modificar', 'Admin\PedidoIvaController@index')->name('admin.pedidos.iva.modificar');
    Route::post('pedidos-iva-batch-update', 'Admin\PedidoIvaController@batchUpdate')->name('admin.pedidos.iva.batch-update');
    Route::get('pedidos-iva-calcular/{id}', 'Admin\PedidoIvaController@calcularIvaBase')->name('admin.pedidos.iva.calcular');

    // Gestión de comprobantes de retención
    Route::get('retenciones', 'Admin\RetencionController@index')->name('admin.retenciones.index');
    Route::get('retenciones/{pedido}/detalle', 'Admin\RetencionController@detalle')->name('admin.retenciones.detalle');
    Route::post('retenciones/{pedido}/aprobar', 'Admin\RetencionController@aprobar')->name('admin.retenciones.aprobar');

    // Configuración de bultos por producto
    Route::get('producto-bultos', 'Admin\ProductoBultoController@index')->name('admin.producto_bultos.index');
    Route::get('producto-bultos/buscar', 'Admin\ProductoBultoController@buscarProductos')->name('admin.producto_bultos.buscar');
    Route::post('producto-bultos', 'Admin\ProductoBultoController@store')->name('admin.producto_bultos.store');
    Route::put('producto-bultos/{id}', 'Admin\ProductoBultoController@update')->name('admin.producto_bultos.update');
    Route::delete('producto-bultos/{id}', 'Admin\ProductoBultoController@destroy')->name('admin.producto_bultos.destroy');

    // Reportes gerenciales de inventario
    Route::get('reportes/inventario', 'Admin\ReporteInventarioController@index')->name('admin.reportes.inventario');
    Route::get('reportes/inventario-deposito', 'Admin\ReporteInventarioController@porDeposito')->name('admin.reportes.inventario.deposito');
    Route::get('reportes/inventario-dashboard', 'Admin\ReporteInventarioController@dashboard')->name('admin.reportes.inventario.dashboard');

    // Gestión de asociaciones cliente-vendedor
    Route::resource('cliente-vendedor', 'Admin\ClienteVendedorController')->only(['index', 'store', 'destroy']);
});
