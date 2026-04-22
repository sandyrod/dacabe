<?php

namespace App\Exports;

use App\Models\QuotaConfirmation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;


class EnrollmentsResumenExport implements FromQuery, WithHeadings
{
    use Exportable;

    public function headings(): array
    {
        return [
            'Etapa',
            'Hembras',
            'Varones'            
        ];
    }


    public function query()
    {
        return (new QuotaConfirmation)->getEnrollmentsResumenExports();
    }

}
