<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

use App\Http\Requests\ExpenseGroupRequest;
use App\Models\ExpenseGroup;
use App\Traits\ExpenseGroupTrait;

use Illuminate\Support\Facades\Response;

class ExpenseGroupsController extends Controller
{
    use ExpenseGroupTrait;

    private $permission;
    private $module;

    public function __construct()
    {
        $this->middleware('auth');
        $this->permission = 'expense-groups';
        $this->module = 'expense_groups';
    }

    public function index(Request $request)
    {
        if (! hasExpensePermission()) {
            abort(403);
        }
     
        $expense_groups = (new ExpenseGroup)->getData();
     
        return (requestAjaxOrJson($request, $expense_groups)) ? $this->getJsonOrDatatableResponse($request, $expense_groups) : view($this->module.'.index');
    }

    public function store(ExpenseGroupRequest $request)
    {
        if (! hasExpensePermission()) 
            abort(403);

        $expense_group = ExpenseGroup::create($request->all());
       
        return redirect()->route($this->permission.'.index')
                ->with('info', 'El registro ha sido creado satisfactoriamente');
    }

    public function create()
    {
        if (! hasExpensePermission()) 
            abort(403);

        $route = $this->permission.'.index';
        return view($this->module.'.create', compact(['route']));
    }

    public function update(ExpenseGroupRequest $request, ExpenseGroup $expense_group)
    {
        if (! hasExpensePermission()) 
            abort(403);
        
        $expense_group->fill($request->all())->save();
       
        return redirect()->route($this->permission.'.index')
                ->with('info', 'El registro ha sido modificado satisfactoriamente');
    }

    public function edit(Request $request, ExpenseGroup $expense_group)
    {
        if (! hasExpensePermission()) 
            abort(403);

        //$expense_group = (new ExpenseGroup)->getData($code);
        $route = $this->permission.'.index';
        return view($this->module.'.edit', compact(['expense_group', 'route']));
    }

    public function destroy(Request $request, ExpenseGroup $expense_group)
    {
        if (! hasExpensePermission()) 
            abort(403);

        $expense_group->delete(); 

        return Response::json([
            'type' => 'success',
            'title' => 'Registro Eliminado!',
            'text' => 'El registro ha sido eliminado satisfactoriamente.'
        ], 200);
    }


    public function showPdfList()
    {
        $expense_groups = (new ExpenseGroup)->getData();

        $today = Carbon::now()->format('d/m/Y');

        $pdf = \PDF::loadView($this->module.'.partials.print', compact('expense_groups', 'today'));
 
        return $pdf->download('lista_unidades.pdf');
    }

     public function showPrintList()
    {
        $expense_group = new ExpenseGroup();
        $expense_groups = $expense_group->getData();
        $print = 1;
        
        $report_data = $expense_group->getReportConfig();

        return view($this->module.'.partials.print', compact(['expense_groups', 'print', 'report_data']));
    }


}
