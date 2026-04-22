<?php

namespace App\Exports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PagosExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $companyDb = DB::connection('company')->getDatabaseName();

        $query = DB::connection('company')->table(DB::raw($companyDb . '.pagos as pag'))
            ->join(DB::raw($companyDb . '.pagos_pedidos as pp'), 'pp.pago_id', '=', 'pag.id')
            ->join(DB::raw($companyDb . '.pedidos as p'), 'p.id', '=', 'pp.pedido_id')
            ->leftJoin(DB::raw($companyDb . '.vendedores as v'), 'v.id', '=', 'pag.seller_id')
            ->leftJoin(DB::raw(config('database.connections.mysql.database') . '.users as u'), 'pag.user_id', '=', 'u.id')
            ->leftJoin(DB::raw($companyDb . '.tpago as t'), 't.CPAGO', '=', 'pag.tpago_id')
            ->select(
                'pag.fecha as fecha_pago',
                'p.id as pedido_id',
                't.DPAGO as tipo_pago',
                'pag.referencia',
                DB::raw('pp.monto as monto'),
                'pag.rate as tasa',
                DB::raw('(pp.monto * pag.rate) as monto_bolivares'),
                'v.codigo as codigo_vendedor',
                'u.name as nombre_vendedor',
                'p.descripcion as cliente'
            );

        if (!empty($this->filters['vendedor'])) {
            $query->where('u.email', $this->filters['vendedor']);
        }

        if (!empty($this->filters['tipo_pago'])) {
            $query->where('pag.tpago_id', $this->filters['tipo_pago']);
        }

        if (!empty($this->filters['fecha_inicio'])) {
            $query->whereDate('pag.fecha', '>=', $this->filters['fecha_inicio']);
        }

        if (!empty($this->filters['fecha_fin'])) {
            $query->whereDate('pag.fecha', '<=', $this->filters['fecha_fin']);
        }
        
        if (!empty($this->filters['search'])) {
            $searchTerm = $this->filters['search'];
            $query->where(function($q) use ($searchTerm) {
                $q->where('p.id', 'like', "%{$searchTerm}%")
                  ->orWhere('pag.referencia', 'like', "%{$searchTerm}%")
                  ->orWhere('p.descripcion', 'like', "%{$searchTerm}%");
            });
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Fecha Pago',
            'Pedido ID',
            'Tipo de Pago',
            'Referencia',
            'Monto $',
            'Tasa',
            'Monto Bs.',
            'Cod. Vendedor',
            'Vendedor',
            'Cliente',
        ];
    }
}
