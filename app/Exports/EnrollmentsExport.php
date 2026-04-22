<?php

namespace App\Exports;

use App\Models\QuotaConfirmation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;


//class OrdersExport implements FromCollection
class EnrollmentsExport implements FromQuery, WithHeadings
{
    use Exportable;

    public function headings(): array
    {
        return [
            'Etapa',
            'Grado',
            'Hembras',
            'Varones'            
        ];
    }


    public function query()
    {
        return (new QuotaConfirmation)->getEnrollmentsExports();
    }

}
