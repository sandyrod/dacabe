<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Traits\CategoryTrait;

use Illuminate\Support\Facades\Response;

class CategoriesController extends Controller
{
    use CategoryTrait;

    private $permission;
    private $module;

    public function __construct()
    {
        $this->middleware('auth');
        $this->permission = 'category';
        $this->module = 'categories';
    }

    public function index(Request $request)
    {
        if ( ! hasPermission($this->permission) )
            abort(403);
     
        $category = new Category();
    	$categories = $category->getData();
     
        return (requestAjaxOrJson($request, $categories)) ? $this->getJsonOrDatatableResponse($request, $categories) : view($this->module.'.index');
    }

    public function store(CategoryRequest $request)
    {
        if ( ! hasPermission('create-'.$this->permission) )
            abort(403);

        $category = Category::create($request->all());
       
        return redirect()->route($this->module.'.index')
                ->with('info', 'El registro '.$category->name.' ha sido creado satisfactoriamente');
    }

    public function create()
    {
        if ( ! hasPermission('create-'.$this->permission) )
            abort(403);

        $route = $this->module.'.index';
        return view($this->module.'.create', compact(['route']));
    }

    public function update(CategoryRequest $request, Category $category)
    {
        if ( ! hasPermission('edit-'.$this->permission) )
            abort(403);
        
        $category->fill($request->all())->save();
       
        return redirect()->route($this->module.'.index')
                ->with('info', 'El registro '.$category->name.' ha sido modificado satisfactoriamente');
    }

    public function edit(Category $category)
    {
        if ( ! hasPermission('edit-'.$this->permission) )
            abort(403);

        $route = $this->module.'.index';
        return view($this->module.'.edit', compact(['category', 'route']));
    }

    public function destroy(Category $category)
    {
        if ( ! hasPermission('delete-'.$this->permission) )
            abort(403);

        $name = $category->name;
        $category->delete();

        return Response::json([
            'type' => 'success',
            'title' => 'Registro Eliminado!',
            'text' => 'El registro '.$name . ' ha sido eliminado satisfactoriamente.'
        ], 200);
    }


    public function show(Category $category)
    {
        $route = 'inicio';
        return view($this->module.'.show', compact(['category', 'route']));
    }

    public function showPdfList()
    {
        $category = new Category();
        $categories = $category->getData();

        $today = Carbon::now()->format('d/m/Y');

        $pdf = \PDF::loadView($this->module.'.partials.print', compact('categories', 'today'));
 
        return $pdf->download('lista_usuarios.pdf');
    }

     public function showPrintList()
    {
        $category = new Category();
        $categories = $category->getData();
        $print = 1;
        
        $report_data = $category->getReportConfig();

        return view($this->module.'.partials.print', compact(['categories', 'print', 'report_data']));
    }


}
