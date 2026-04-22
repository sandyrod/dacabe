<?php

namespace App\Exports;

use App\Models\ComisionVendedor;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ComisionesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $comisiones;

    public function __construct($comisiones)
    {
        $this->comisiones = $comisiones;
    }

    public function collection()
    {
        return $this->comisiones;
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Vendedor',
            'Email',
            'Código Producto',
            'Cantidad',
            'Monto Comisión',
            '% Comisión',
            'Estado'
        ];
    }

    public function map($comision): array
    {
        return [
            $comision->created_at->format('d/m/Y H:i'),
            $comision->nombre_vendedor,
            $comision->correo_vendedor,
            $comision->codigo_producto,
            $comision->cantidad,
            number_format($comision->monto_comision, 2),
            number_format($comision->porcentaje_comision, 2) . '%',
            ucfirst($comision->estatus_comision)
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para el encabezado
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9EAD3']
                ]
            ],
            // Estilo para las filas alternas
            'A2:H' . ($this->comisiones->count() + 1) => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ],
        ];
    }
}
