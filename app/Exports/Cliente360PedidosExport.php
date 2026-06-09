<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class Cliente360PedidosExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
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
            'Cliente',
            'Codcli',
            'Estatus',
            'Unidades',
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
            $row->descripcion,
            $row->codcli,
            $row->estatus,
            (float) $row->total_unidades,
            (float) $row->total_usd,
        ];
    }
}
