<?php
use Illuminate\Support\Facades\Log;
use App\User;
use App\Models\{Company, CompanyStatus, DescuentoGlobal};
use Carbon\Carbon;

function mainCompanyLogo()
{
    $companies = new Company();
    $company = $companies->getMainCompany();
    if ($company) {
        return getCompanyPathLogo($company->theme, $company->logo);
    }

    return getDefaultCompanyLogo();
}

function mainCompanyClientLogo()
{
    $company = (new Company)->find(auth()->user()->company_id);
    if ($company) {
        return asset('storage/logos/' . $company->logo);
    }

    return getDefaultCompanyLogo();
}

function myCompanyLogo()
{
    $company = (new Company)->getLogoCompany();
    if ($company) {
        return asset('imgs/users/' . $company->logo);
    }

    return getDefaultCompanyLogo();
}



function getCompanyPathLogo ($theme, $logo)
{
    return asset('landing/' . $theme . '/imgs/' . $logo);
}


function getDefaultCompanyLogo ()
{
    return asset('imgs/logos/logo.png');
}

function getUserFullName()
{
	if (Auth::user())
		return trim(Auth::user()->name . ' ' . Auth::user()->last_name);

    return 'Usuario';
}

function getUserFullNameAbrev()
{
    if (Auth::user())
        return strtok(Auth::user()->last_name, " ");
        //return trim(strtok(Auth::user()->name, " ") . ' ' . strtok(Auth::user()->last_name, " "));

    return 'Usuario';
}

function getUserPhoto()
{
	if (Auth::user()) {
		return getUserPathPhoto(Auth::user()->photo);
    }

    return asset('imgs/users/nofoto.jpg');
}

function getUserUrl()
{
	if (Auth::user())
		return route('users.show', Auth::user()->id);

    return '#';
}

function getUserPathPhoto ($photo)
{
    return asset('storage/users/' . $photo);
    //return asset('imgs/users/' . $photo);
}

function formatoFechaDMA ( $fecha ) {
    return \Carbon\Carbon::parse( $fecha )->format('d/m/Y H:i');
}

function formatoFechaDMASimple ( $fecha ) {
    return \Carbon\Carbon::parse( $fecha )->format('d/m/Y');
}

function formatoFecha ( $fecha ) {
    return \Carbon\Carbon::parse( $fecha )->diffForHumans();
}

function getActionHtmlColumn ($data, $buttons) {
    $html = '';
    foreach ($buttons as $button) {
        $html .= getHtmlStructureButton($data, $button);
    }
    return $html;
}

function getHtmlStructureButton ($data, $button) {
    return '<a href="#" data-iddata="'.$data->id.'" class="btn btn-'.$button['style'].' '.$button['name'].' hint--top" aria-label="'.$button['hint'].'"><i class="fa fa-'.$button['icon'].'"></i></a> ';
}

function getOrderActionHtmlColumn ($data, $buttons, $field) {
    $html = '';
    foreach ($buttons as $button) {
        $html .= getOrderHtmlStructureButton($data, $button, $field);
    }
    return $html;
}

function getOrderHtmlStructureButton ($data, $button, $field) {
    return '<a href="#" data-iddata="'.$data->$field.'" class="btn btn-'.$button['style'].' '.$button['name'].' hint--top" aria-label="'.$button['hint'].'"><i class="fa fa-'.$button['icon'].'"></i></a> ';
}

function hasPermission ($permission) {
    return ( Auth::user()->isAbleTo($permission) || Auth::user()->hasRole('admin_pedidos') || Auth::user()->hasRole('admin'));
    return (Auth::user() && (Auth::user()->hasRole('admin') || Auth::user()->isAbleTo($permission)));
}

function requestAjaxOrJson($request){
    return ($request->ajax() || $request->wantsJson());
}

function getNow()
{
    return Carbon::now()->format('d/m/Y h:i');
}

function getHumanDate($date)
{
    return Carbon::createFromTimeStamp(strtotime($date))->diffForHumans();
}

function getModuleStatus($status_id)
{
    $status = [
        1 => ['Activo', 'success'],
        2 => ['Inactivo', 'warning']
    ];

    return $status[$status_id] ?? [0 => ['No Indicado', 'default']];
}

function notPermissionResponse($request)
{
    if (requestAjaxOrJson($request))
        return response()->json(['message' => 'not authorized'], 403);
    
    abort(403);
}

function getActiveCompanyStatus()
{
    $active = CompanyStatus::where('name', 'Activo')->first();
    if (! $active)
        $active = CompanyStatus::create(['name' => 'Activo']);

    return $active->id;
}

function getUrlImage($data = null, $mode = '')
{
    if (! $data) {
        return '';
    }
    if ($mode) {
        return asset('storage/landings/'.@$data->$mode);        
    }
    return asset('storage/landings/'.@$data->main_logo);
} 

function hasOrderPermission () {
    return (Auth::user() && (Auth::user()->hasRole('admin') || Auth::user()->hasRole('admin_pedidos')));
}

function hasOrderClientPermission () {
    return (Auth::user() && (Auth::user()->hasRole('admin') || Auth::user()->hasRole('admin_pedidos') || Auth::user()->hasRole('facturacion_dacabe')));
}

function hasOnlyOrderClient () {
    return (Auth::user()->hasRole('facturacion_dacabe'));
}

function hasOnlyDispatch () {
    return (Auth::user()->hasRole('dacabe_despacho') || Auth::user()->email=='gabydespacho@gmail.com');
}

function hasExpensePermission () {
    return (Auth::user() && (Auth::user()->hasRole('admin') || Auth::user()->hasRole('admin_gastos')));
}

function getOrderStatusColor($status)
{
    if ($status == 'PENDIENTE') {
        return 'warning';
    }
    if ($status == 'APROBADO') {
        return 'success';
    }
    if ($status == 'RECHAZADO') {
        return 'danger';
    }    
    return 'primary';
}

function truncateToTwoDecimals($number) {
    $numberStr = (string)$number;
    
    $parts = explode('.', $numberStr);
    
    $integerPart = number_format((float)$parts[0], 0, ',', '.');
    
    if (isset($parts[1])) {
        $decimalPart = substr($parts[1], 0, 2);
        return $integerPart . ',' . $decimalPart;
    }
    
    return $integerPart;
}

function obtenerDescuentoGlobal () {
    $dcto = DescuentoGlobal::first();
    if (!$dcto){
        return null;
    }
    /*
    return ($dcto->porcentaje > 0 ? $dcto->porcentaje : 0);
    */
    
    return ($dcto->discount);
}

function verPrecio1 () {
    $dcto = DescuentoGlobal::first();
    if (!$dcto){
        return null;
    }
    /*
    return ($dcto->porcentaje > 0 ? $dcto->porcentaje : 0);
    */
    
    return ($dcto->show_precio1=='SI' ? 'SI' : 'NO');
}
