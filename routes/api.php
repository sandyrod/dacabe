<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
*/

Route::group([
    'prefix' => 'auth'
], function () {
    Route::post('login', 'AuthController@login');
    //Route::post('signup', 'AuthController@signup');
  
    Route::group([
      'middleware' => 'auth:api'
    ], function() {
        Route::get('logout', 'AuthController@logout');
        Route::get('user', 'AuthController@user');

        Route::apiResource('users', 'UsersController');
        Route::get('users-all', 'UsersController@getAll');
        Route::apiResource('roles', 'RolesController');
        Route::get('roles-all', 'RolesController@getAll');
        Route::apiResource('permissions', 'PermissionsController');
        Route::get('permissions-all', 'PermissionsController@getAll');
        //Route::apiResource('formalties-types', 'FormaltiesTypesController');
        Route::apiResource('clients', 'ClientsController');
        Route::get('clients-all', 'ClientsController@getAll');
        Route::apiResource('companies', 'CompaniesController');
        Route::apiResource('company-status', 'CompanyStatusController');
        Route::apiResource('modules', 'ModulesController');                
        Route::get('get-company-modules', 'ModulesController@getCompanyPermissions'); 
        Route::apiResource('commands', 'CommandsController');                
        Route::get('ftp-invoices', 'FtpInvoicesController@index');                
        Route::get('ftp-invoices/{id?}', 'FtpInvoicesController@show');                
        Route::post('ftp-invoices', 'FtpInvoicesController@update');                
        Route::get('get-farmax-indicators', 'FtpInvoicesController@getIndicators');                
        //Route::apiResource('grupo', 'GrupoController');                
        Route::post('inven', 'InvenController@store');
        Route::post('inven-masivo', 'InvenController@storeMasive');
        Route::post('moviventas', 'MoviventasController@storeMasive');
        Route::apiResource('notifications', 'NotificationsController');
    });
    
});

Route::get('get-faqs', 'FtpInvoicesController@getFaqs');  
