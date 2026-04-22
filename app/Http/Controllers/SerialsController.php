<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

use App\Http\Requests\SerialRequest;
use App\User;
use App\Models\Serial;
use App\Models\Company;
use App\Traits\SerialTrait;

use App\Http\Resources\SerialResource;
use Illuminate\Support\Facades\Response;

class SerialsController extends Controller
{
    use SerialTrait;

    private $permission;
    private $module;
    private $paginate;

    public function __construct()
    {
        $this->middleware('auth');
        $this->permission = 'serial';
        $this->module = 'serials';
        $this->paginate = 10;
    }

    public function index(Request $request)
    {
        if ( ! hasPermission($this->permission) )
            return notPermissionResponse($request);
        
        if (requestAjaxOrJson($request))
            return $this->getJsonOrDatatableResponse($request);

        $companies = Company::orderBy('name')->pluck('name', 'id');
        
        return view($this->module.'.index', compact(['companies']));
    }

    public function store(SerialRequest $request)
    {
        if ( ! hasPermission('create-'.$this->permission) )
            return notPermissionResponse($request);

        $seriales = new Serial();
        $serial = $seriales->where('company_id', $request->company_id)->where('created_at', '>=', \Carbon\Carbon::now()->subHour())->first();
        if ($serial)
            $serial->quantity++;
        else
            $serial = new Serial(['company_id' => $request->company_id]);
        
        $serial->save();
       
        if (requestAjaxOrJson($request))
            return new SerialResource($serial);

        return redirect()->route($this->module.'.index')
                ->with('info', 'El registro '.$serial->name.' ha sido creado satisfactoriamente');
    }

    public function destroy(Serial $serial)
    {
        if ( ! hasPermission('delete-'.$this->permission) )
            return notPermissionResponse($request);

        $name = $serial->name;
        $serial->delete();

        return Response::json([
            'type' => 'success',
            'title' => 'Registro Eliminado!',
            'text' => 'El registro '.$name . ' ha sido eliminado satisfactoriamente.'
        ], 200);
    }


    public function showPrintList()
    {
        if ( ! hasPermission('edit-'.$this->permission) )
            abort(403);

        $serial = new Serial();
        $serials = $serial->getData();
        $print = 1;
        
        $report_data = $serial->getReportConfig();

        return view($this->module.'.partials.print', compact(['serials', 'print', 'report_data']));
    }

    public function masterKey()
    {
        if ( ! hasPermission($this->permission) )
            return notPermissionResponse($request);
        
        $master_key = $this->getMasterKey();
        
        return view($this->module.'.master_key', compact(['master_key']));
    }

    public function getMasterKey()
    {
        $values = [50,60,70,10,20,30,40];
        $day_week = \Carbon\Carbon::now()->dayOfWeek;
        $hour = \Carbon\Carbon::now()->format('H');
        $hour = str_pad($hour*2, 2, '0', STR_PAD_LEFT);
        $month = \Carbon\Carbon::now()->format('m');
        $day = \Carbon\Carbon::now()->format('d');
        $day = $day > 9 ? $day : substr($day, 1, 1) . '0';

        return $month . $values[$day_week] . $hour;
    }

}
