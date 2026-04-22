<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

use App\Http\Requests\NotificationRequest;
use App\User;
use App\Models\Module;
use App\Models\{Notification};
use App\Traits\NotificationTrait;

use App\Http\Resources\NotificationResource;
use Illuminate\Support\Facades\Response;

class NotificationsController extends Controller
{
    use NotificationTrait;

    private $permission;
    private $module;
    private $paginate;

    public function __construct()
    {
        $this->middleware('auth');
        $this->permission = 'notifications';
        $this->module = 'notifications';
        $this->paginate = 10;
    }


    public function index(Request $request)
    {
        //if ( ! hasPermission($this->permission) ) {
        //    return notPermissionResponse($request);
        //}
        
        //if (requestAjaxOrJson($request)) {
            return $this->getJsonOrDatatableResponse($request);
        //}
        
        return view($this->module.'.index');
    }

    public function store(NotificationRequest $request)
    {
        if ( ! hasPermission('create-'.$this->permission) ) {
            return notPermissionResponse($request);
        }
        $notification = Notification::create($request->all());
       
        //if (requestAjaxOrJson($request)) {
            return new NotificationResource($notification);
        //}

        return redirect()->route($this->module.'.index')
                ->with('info', 'El registro '.$notification->descr.' ha sido creado satisfactoriamente');
    }

    public function update(NotificationRequest $request, Notification $notification)
    {
        if ( ! hasPermission('edit-'.$this->permission) ) {
            return notPermissionResponse($request);
        }
        
        //$company->update($request->only(['name', 'email', 'code', 'url', 'logo', 'phone', 'location', 'status']));
        $notification->fill($request->all())->save();

        //if (requestAjaxOrJson($request))
            return new NotificationResource($notification);
       
        //return redirect()->route($this->module.'.index')
        //        ->with('info', 'El registro '.$notification->descr.' ha sido modificado satisfactoriamente');
    }

    public function destroy(Notification $notification)
    {
        if ( ! hasPermission('delete-'.$this->permission) ) {
            return notPermissionResponse($request);
        }

        $name = $notification->descr;
        $notification->delete();

        return Response::json([
            'type' => 'success',
            'title' => 'Registro Eliminado!',
            'text' => 'El registro '.$name . ' ha sido eliminado satisfactoriamente.'
        ], 200);
    }

    public function show(Notification $notification)
    {
        if ( ! hasPermission($this->permission) ) {
            return notPermissionResponse($notification);
        }
        return  new NotificationResource($notification);
    }

    public function create()
    {
        if ( ! hasPermission('create-'.$this->permission) ) {
            abort(403);
        }

        $route = $this->module.'.index';
        return view($this->module.'.create', compact(['route']));
    }

    public function edit(Notification $notification)
    {
        if ( ! hasPermission('edit-'.$this->permission) ) {
            abort(403);
        }

        $route = $this->module.'.index';
        return view($this->module.'.edit', compact(['notification', 'route']));
    }

    public function showPrintList()
    {
        if ( ! hasPermission('edit-'.$this->permission) ) {
            abort(403);
        }
        $notification = new Notification();
        $prods = $notification->getData();
        $print = 1;
        
        $report_data = $notification->getReportConfig();

        return view($this->module.'.partials.print', compact(['prods', 'print', 'report_data']));
    }

    
}
