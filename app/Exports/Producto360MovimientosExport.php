<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class Producto360MovimientosExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    private Collection $rows;

    public function __construct($rows)
    {
        $this->rows = collect($rows);
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'Pedido #',
            'Fecha',
            'Referencia',
            'Estatus',
            'Cliente',
            'Codcli',
            'Codigo Producto',
            'Descripcion Producto',
            'Unidades',
            'Precio USD',
            'Total USD',
        ];
    }

    public function map($row): array
    {
        $fecha = $row->fecha ? \Carbon\Carbon::parse($row->fecha)->format('d/m/Y') : '';

        return [
            $row->id,
            $fecha,
            $row->referencia,
            $row->estatus,
            $row->cliente,
            $row->codcli,
            $row->codigo_producto,
            $row->descripcion_producto,
            (float) $row->unidades,
            (float) $row->precio_usd,
            (float) $row->total_usd,
        ];
    }
}
