<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use App\Http\Requests\VendedorRequest;
use App\Models\{Vendedor, Zona, Deposito};
use App\Traits\VendedorTrait;

use Illuminate\Support\Facades\Response;

class VendedoresController extends Controller
{
    use VendedorTrait;

    private $permission;
    private $module;

    public function __construct()
    {
        $this->middleware('auth');
        $this->permission = 'vendedores';
        $this->module = 'vendedores';
    }

    public function index(Request $request)
    {
        if ( ! hasPermission($this->permission) )
            abort(403);
     
        $vendedores = (new Vendedor)->getData();
        
        if (! requestAjaxOrJson($request, $vendedores)) {
            return view($this->module.'.index');
        }
        

        return $this->getJsonOrDatatableResponse($request, $vendedores);

    }

    public function store(VendedorRequest $request)
    {
        if ( ! hasPermission($this->permission) )
            abort(403);

        $vendedor = (new Vendedor)->createNew($request);
       
        return redirect()->route($this->permission.'.index')->with('info', 'El registro ha sido creado satisfactoriamente');
    }

    public function create()
    {
        if ( ! hasPermission($this->permission) ) {
            abort(403);
        }

        $zonas = (new Zona)->getData();
        $depositos = (new Deposito)->getData();

        $route = $this->permission.'.index';
        return view($this->module.'.create', compact(['route', 'zonas', 'depositos']));
    }

    public function update(VendedorRequest $request, $code)
    {
        if ( ! hasPermission($this->permission) )
            abort(403);
        
        $vendedor = (new Vendedor)->updateItem($code, $request);
       
        return redirect()->route($this->permission.'.index')
                ->with('info', 'El registro ha sido modificado satisfactoriamente');
    }

    public function edit(Request $request, $code)
    {
        if ( ! hasPermission($this->permission) )
            abort(403);

        $vendedor = (new Vendedor)->getData($code);        
        $route = $this->permission.'.index';
        $metaPeriodoActual = Carbon::now()->format('Y-m');
        $metaTableDisponible = Schema::connection('company')->hasTable('metas_vendedores_periodo');
        $metaMensual = null;
        $metasMensuales = collect();

        if ($metaTableDisponible) {
            $metasMensuales = DB::connection('company')
                ->table('metas_vendedores_periodo')
                ->where('vendedor_id', $vendedor->id)
                ->where('periodo_tipo', 'mes')
                ->orderByDesc('periodo_key')
                ->get()
                ->keyBy('periodo_key');

            $metaMensual = DB::connection('company')
                ->table('metas_vendedores_periodo')
                ->where('vendedor_id', $vendedor->id)
                ->where('periodo_tipo', 'mes')
                ->where('periodo_key', strtoupper($metaPeriodoActual))
                ->first();
        }

        $zonas = (new Zona)->getData();
        $depositos = (new Deposito)->getData();

        return view($this->module.'.edit', compact(['vendedor', 'route', 'zonas', 'depositos', 'metaTableDisponible', 'metaMensual', 'metaPeriodoActual', 'metasMensuales']));
    }

    public function guardarMetaMensual(Request $request)
    {
        if ( ! hasPermission($this->permission) ) {
            abort(403);
        }

        if (! Schema::connection('company')->hasTable('metas_vendedores_periodo')) {
            return back()->withErrors(['No existe la tabla de metas manuales. Ejecuta el SQL de creacion y vuelve a intentar.']);
        }

        $validated = $request->validate([
            'vendedor_id' => ['required', 'integer', 'min:1', 'exists:company.vendedores,id'],
            'periodo_key' => ['required', 'date_format:Y-m'],
            'meta_ventas_usd' => ['nullable', 'numeric', 'min:0'],
            'meta_pedidos_aprobados' => ['nullable', 'integer', 'min:0'],
            'meta_pedidos_pagados' => ['nullable', 'integer', 'min:0'],
            'meta_logro_pedidos_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $todosNulos = is_null($validated['meta_ventas_usd'])
            && is_null($validated['meta_pedidos_aprobados'])
            && is_null($validated['meta_pedidos_pagados'])
            && is_null($validated['meta_logro_pedidos_pct']);

        if ($todosNulos) {
            return back()->withErrors(['Debes indicar al menos una meta para guardar.']);
        }

        $filtro = [
            'vendedor_id' => (int) $validated['vendedor_id'],
            'periodo_tipo' => 'mes',
            'periodo_key' => strtoupper(trim($validated['periodo_key'])),
        ];

        $payload = [
            'meta_ventas_usd' => is_null($validated['meta_ventas_usd']) ? null : round((float) $validated['meta_ventas_usd'], 2),
            'meta_pedidos_aprobados' => is_null($validated['meta_pedidos_aprobados']) ? null : (int) $validated['meta_pedidos_aprobados'],
            'meta_pedidos_pagados' => is_null($validated['meta_pedidos_pagados']) ? null : (int) $validated['meta_pedidos_pagados'],
            'meta_logro_pedidos_pct' => is_null($validated['meta_logro_pedidos_pct']) ? null : round((float) $validated['meta_logro_pedidos_pct'], 2),
            'actualizado_por' => (int) auth()->id(),
            'updated_at' => now(),
        ];

        $query = DB::connection('company')->table('metas_vendedores_periodo')->where($filtro);
        if ($query->exists()) {
            $query->update($payload);
        } else {
            DB::connection('company')->table('metas_vendedores_periodo')->insert(array_merge($filtro, $payload, [
                'created_at' => now(),
            ]));
        }

        return redirect()
            ->route('vendedores.edit', $validated['vendedor_id'])
            ->with('info', 'Meta mensual guardada correctamente.');
    }

    public function destroy(Request $request, $code)
    {
        if ( ! hasPermission($this->permission) )
            abort(403);

        $vendedor = (new Vendedor)->deleteRecord($code);

        return Response::json([
            'type' => 'success',
            'title' => 'Registro Eliminado!',
            'text' => 'El registro ha sido eliminado satisfactoriamente.'
        ], 200);
    }

    public function updateStatus(Request $request, $code)
    {
        if ( ! hasPermission($this->permission) ) {
            abort(403);
        }

        $request->validate([
            'estatus' => 'required|in:ACTIVO,SUSPENDIDO'
        ]);

        $vendedor = (new Vendedor)->find($code);
        if (! $vendedor) {
            return Response::json([
                'type' => 'error',
                'title' => 'Registro no encontrado',
                'text' => 'No fue posible localizar el vendedor indicado.'
            ], 404);
        }

        $estatus = strtoupper(trim((string) $request->estatus));
        $vendedor->estatus = $estatus === 'SUSPENDIDO' ? 'SUSPENDIDO' : null;
        $vendedor->save();

        return Response::json([
            'type' => 'success',
            'title' => $estatus === 'SUSPENDIDO' ? 'Vendedor suspendido' : 'Vendedor habilitado',
            'text' => $estatus === 'SUSPENDIDO'
                ? 'El vendedor ha sido suspendido correctamente.'
                : 'El vendedor ha sido habilitado correctamente.'
        ], 200);
    }


    public function showPdfList()
    {
        $vendedores = (new Vendedor)->getData();

        $today = Carbon::now()->format('d/m/Y');

        $pdf = \PDF::loadView($this->module.'.partials.print', compact('vendedores', 'today'));
 
        return $pdf->download('lista_unidades.pdf');
    }

    public function showPrintList()
    {
        $vendedor = new Vendedor();
        $vendedores = $vendedor->getData();
        $print = 1;
        
        $report_data = $vendedor->getReportConfig();

        return view($this->module.'.partials.print', compact(['vendedores', 'print', 'report_data']));
    }


}
