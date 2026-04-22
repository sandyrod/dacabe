<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

use App\Http\Requests\CommandRequest;
use App\Models\Command;
use App\Traits\CommandTrait;

use App\Http\Resources\CommandResource;

use Illuminate\Support\Facades\Response;

class CommandsController extends Controller
{
    use CommandTrait;

    private $permission;
    private $module;
    private $paginate;

    public function __construct()
    {
        $this->middleware('auth');
        $this->permission = 'command';
        $this->module = 'commands';
        $this->paginate = 10;
    }

    public function index(Request $request)
    {
        if ( ! hasPermission($this->permission) )
            abort(403);
     
        $command = new Command();
    	$commands = $command->getData();
     
        if (requestAjaxOrJson($request, $commands)) 
            return $this->getJsonOrDatatableResponse($request, $commands);

        return view($this->module.'.index');
    }

    public function store(CommandRequest $request)
    {
        if ( ! hasPermission('create-'.$this->permission) )
            abort(403);

        $command = Command::create($request->all());

        if (requestAjaxOrJson($request))
            return new CommandResource($command);
       
        return redirect()->route($this->module.'.index')
                ->with('info', 'El registro '.$command->command.' ha sido creado satisfactoriamente');
    }

    public function create()
    {
        if ( ! hasPermission('create-'.$this->permission) )
            abort(403);

        $route = $this->module.'.index';
        return view($this->module.'.create', compact(['route']));
    }

    public function update(CommandRequest $request, Command $command)
    {
        if ( ! hasPermission('edit-'.$this->permission) )
            abort(403);
        
        $command->fill($request->all())->save();

        if (requestAjaxOrJson($request))
            return new CommandResource($command);
       
        return redirect()->route($this->module.'.index')
                ->with('info', 'El registro '.$command->command.' ha sido modificado satisfactoriamente');
    }

    public function edit(Command $command)
    {
        if ( ! hasPermission('edit-'.$this->permission) )
            abort(403);

        $route = $this->module.'.index';
        return view($this->module.'.edit', compact(['command', 'route']));
    }

    public function destroy(Command $command)
    {
        if ( ! hasPermission('delete-'.$this->permission) )
            abort(403);

        $name = $command->name;
        $command->delete();

        return Response::json([
            'type' => 'success',
            'title' => 'Registro Eliminado!',
            'text' => 'El registro '.$name . ' ha sido eliminado satisfactoriamente.'
        ], 200);
    }

    public function show(Command $command)
    {
        if ( ! hasPermission($this->permission) )
            return notPermissionResponse($module);

        return  new CommandResource($command);
    }

    public function showPdfList()
    {
        $command = new Command();
        $commands = $command->getData();

        $today = Carbon::now()->format('d/m/Y');

        $pdf = \PDF::loadView($this->module.'.partials.print', compact('commands', 'today'));
 
        return $pdf->download('lista_comandos.pdf');
    }

     public function showPrintList()
    {
        $command = new Command();
        $commands = $command->getData();
        $print = 1;
        
        $report_data = $command->getReportConfig();

        return view($this->module.'.partials.print', compact(['commands', 'print', 'report_data']));
    }


}
