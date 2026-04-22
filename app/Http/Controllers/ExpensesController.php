<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

use App\Http\Requests\ExpenseRequest;
use App\Models\{Expense, ExpenseGroup, Branch};
use App\Traits\ExpenseTrait;

use DB;
use Illuminate\Support\Facades\Response;

class ExpensesController extends Controller
{
    use ExpenseTrait;

    private $permission;
    private $module;

    public function __construct()
    {
        $this->middleware('auth');
        $this->permission = 'expenses';
        $this->module = 'expenses';
    }

    public function index(Request $request)
    {
        if (! hasExpensePermission()) {
            abort(403);
        }
     
        $expenses = (new Expense)->getData();
     
        return (requestAjaxOrJson($request, $expenses)) ? $this->getJsonOrDatatableResponse($request, $expenses) : view($this->module.'.index');
    }

    public function store(ExpenseRequest $request)
    {
        if (! hasExpensePermission()) 
            abort(403);

        $expense = Expense::create($request->all());
       
        return redirect()->route($this->permission.'.index')
                ->with('info', 'El registro ha sido creado satisfactoriamente');
    }

    public function showStatistics()
    {
        $route = 'inicio';
        return view($this->module.'.statistics', compact(['route']));
    }

    public function getExpensesController(Request $request)
    {
        $where = [];
        if ($request->start_at) {
            $where[] = ['date_at', '>=', $request->start_at];
        }
        if ($request->end_at) {
            $where[] = ['date_at', '<=', $request->end_at];
        }
        $data = (new Expense)
            ->select('expense_groups.name', DB::raw('SUM(amount) as amount'), DB::raw('SUM(dollar_amount) as dollar_amount'))
            ->join('expense_groups', 'expenses.expense_group_id', 'expense_groups.id')
            ->where($where)
            ->groupBy('expense_group_id')
            ->get();

        $route = 'inicio';
        
        return Response::json([
            'type' => 'success',
            'data' => $data
        ], 200);
    }

    public function create()
    {
        if (! hasExpensePermission()) 
            abort(403);

        $route = $this->permission.'.index';

        $expense_groups = (new ExpenseGroup)->orderBy('name')->select('name', 'id')->get();
        $branches = (new Branch)->orderBy('name')->select('name', 'id')->where('company_id', auth()->user()->company_id)->get();
        
        return view($this->module.'.create', compact(['route', 'expense_groups', 'branches']));
    }

    public function update(ExpenseRequest $request, Expense $expense)
    {
        if (! hasExpensePermission()) 
            abort(403);
        
        $expense->fill($request->all())->save();
       
        return redirect()->route($this->permission.'.index')
                ->with('info', 'El registro ha sido modificado satisfactoriamente');
    }

    public function edit(Request $request, Expense $expense)
    {
        if (! hasExpensePermission()) 
            abort(403);

        $expense_groups = (new ExpenseGroup)->orderBy('name')->select('name', 'id')->get();
        $branches = (new Branch)->orderBy('name')->select('name', 'id')->where('company_id', auth()->user()->company_id)->get();

        $route = $this->permission.'.index';
        return view($this->module.'.edit', compact(['expense', 'route', 'expense_groups', 'branches']));
    }

    public function destroy(Request $request, Expense $expense)
    {
        if (! hasExpensePermission()) 
            abort(403);

        $expense->delete(); 

        return Response::json([
            'type' => 'success',
            'title' => 'Registro Eliminado!',
            'text' => 'El registro ha sido eliminado satisfactoriamente.'
        ], 200);
    }


    public function showPdfList()
    {
        $expenses = (new Expense)->getData();

        $today = Carbon::now()->format('d/m/Y');

        $pdf = \PDF::loadView($this->module.'.partials.print', compact('expenses', 'today'));
 
        return $pdf->download('lista_unidades.pdf');
    }

     public function showPrintList()
    {
        $expense = new Expense();
        $expenses = $expense->getData();
        $print = 1;
        
        $report_data = $expense->getReportConfig();

        return view($this->module.'.partials.print', compact(['expenses', 'print', 'report_data']));
    }


}
