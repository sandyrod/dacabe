<?php

namespace App\Exports;

use App\Models\QuotaRequest;
use App\Models\QuotaRequestDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;

use DB;
use \Carbon\Carbon;

//class OrdersExport implements FromCollection
class OrdersExport implements FromQuery, WithHeadings
{
    use Exportable;

    public function headings(): array
    {
        return [
            'Grado',
            'Nombre del Niño (a)',
            'F',
            'M',
            'Fecha de Nac.',
            'Edad',
            'Representante',
            'CI',
            'Teléfono',
            'Dirección',
            'Correo',
            'Otro Tlf.'
        ];
    }


    public function prepareRows($rows)
    {
        return $rows->transform(function ($item) {
        	/*
            $status = (new OrderStatus)->find($item->order_status_id);
            if ($status) {
                $item->order_status_id = $status->name;
            }
            */
            $item->age = Carbon::parse($item->birthday)->age;

            return $item;
        });
    }

    public function query()
    {
        /*
        return QuotaRequestDetail::query()
            ->select('quota_request_details.created_at', 'levels.name as level', 'quota_request_details.name as asp_name', 'quota_request_details.last_name as asp_ape', 'quota_request_details.document as asp_doc', 'birthday', 'quota_request_details.gender', 'quota_request_details.observations', 'origin', 'quota_requests.name', 'quota_requests.last_name', 'quota_requests.document', 'quota_requests.email', 'quota_requests.phone', 'quota_requests.cell_phone', 'quota_requests.profession', 'quota_requests.work_at', 'quota_requests.address', 'quota_requests.father_name', 'quota_requests.father_last_name', 'quota_requests.father_document', 'quota_requests.father_email', 'quota_requests.father_phone', 'quota_requests.father_cell_phone', 'quota_requests.father_profession', 'quota_requests.father_work_at', 'quota_requests.father_address', 'quota_requests.other_representatives', 'quota_requests.other_representative_reasons', 'religion')
            ->join('quota_requests', 'quota_requests.id', '=', 'quota_request_details.quota_request_id')
            ->leftJoin('levels', 'levels.id', '=', 'quota_request_details.level_id')
            ->where('quota_requests.quota_status', 'PENDIENTE')
            ->orderBy('quota_requests.created_at', 'DESC')
            ->orderBy('quota_request_details.created_at', 'DESC');
        */

        return QuotaRequestDetail::query()
                ->select('levels.name as level', DB::raw("CONCAT(quota_request_details.name, ' ', quota_request_details.last_name) as asp_name"), DB::raw('(CASE WHEN quota_request_details.gender = "FEMENINO" THEN "1" ELSE "" END) AS female'), DB::raw('(CASE WHEN quota_request_details.gender = "MASCULINO" THEN "1" ELSE "" END) AS male'), 'birthday', DB::raw("YEAR(CURRENT_TIMESTAMP) - YEAR(birthday) - (RIGHT(CURRENT_TIMESTAMP, 5) < RIGHT(birthday, 5)) as age"),  DB::raw("CONCAT(quota_requests.name, ' ' , quota_requests.last_name) as mother_name"), 'quota_requests.document as mother_document', 'quota_requests.phone as mother_phone', 'quota_requests.address as mother_address', 'quota_requests.email as mother_email', 'quota_requests.cell_phone as mother_cell_phone')
                ->join('quota_requests', 'quota_requests.id', '=', 'quota_request_details.quota_request_id')
                ->leftJoin('levels', 'levels.id', '=', 'quota_request_details.level_id')
                ->where('quota_requests.quota_status', 'PENDIENTE')
                ->orderBy('quota_requests.created_at', 'DESC')
                ->orderBy('quota_request_details.created_at', 'DESC');
    }

}
