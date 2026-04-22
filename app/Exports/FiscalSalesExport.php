<?php

namespace App\Exports;

use DB;
use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;


class FiscalSalesExport implements FromQuery, WithHeadings
{
    use Exportable;

    private $month;

    public function __construct(int $month)
    {
        $this->month = $month;
    }

    public function headings(): array
    {
        return [
            'FECHA',
            'RIF',
            'NOMBRE/RAZON SOCIAL',
            'NRO COMPROBANTE RETENC. IVA',
            'NRO FACTURA',
            'NRO CONTROL',
            'NRO NOTA DEBITO',
            'NRO NOTA CREDITO',
            'TIPO DE TRANS.',
            'NRO FACT. AFECTADA',
            'TOTAL VENTA INCLUYE I.V.A.',
            'VENTAS INTERNAS NO GRAV(EXENTAS O EXONERADAS',
        ];
    }


    public function prepareRows($rows)
    {
        return $rows->transform(function ($data) {
            if (substr($data->rif, 0,1) >= '0' && substr($data->rif, 0,1) <= '9') {
                $data->rif = 'V-' .$data->rif;
            }
            $data->comprob = "";
            $data->ndd = "";
            $data->ndc = "";
            $data->trans = "FAC";
            $data->fact_afec = "";
            $data->date_at = \PhpOffice\PhpSpreadsheet\Shared\Date::dateTimeToExcel(\Carbon\Carbon::parse($data->date_at));
            $data->total = $data->total;
            $data->tot2 = $data->total;
            

            return $data;
        });        
    }

    public function query()
    {
        return (new Invoice)->select('date_at', 'rif', 'name', 'name as comprob', 'document_number', 'control_number', 'control_number as ndd', 'control_number as ndc', 'control_number as trans', 'control_number as fact_afec', 'total', 'total as tot2')->where('estatus', '!=', 'PENDIENTE')->whereMonth('date_at', $this->month);
    }

}
