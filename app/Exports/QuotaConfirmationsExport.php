<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;


//class OrdersExport implements FromCollection
class QuotaConfirmationsExport implements FromQuery, WithHeadings
{
    use Exportable;

    public function headings(): array
    {
        return [
            'Fecha Solic.',
            'Grado',
            'Nombre Aspirante',
            'Apellido Aspirante',
            'Cédula',
            'Fecha Ncto.',
            'Género',
            'Motivo del cambio',
            'Procedencia',
            'Nombre Madre',
            'Apellido Madre',
            'Cédula',
            'Email',
            'Teléfono',
            'Otro Teléfono',
            'Lugar de Trabajo',
            'Ocupación',
            'Dirección',
            'Nombre Padre',
            'Apellido Padre',
            'Cédula',
            'Email',
            'Teléfono',
            'Otro Teléfono',
            'Lugar de Trabajo',
            'Ocupación',
            'Dirección',
            'Personas con quien convive el niño (a)', 
            'Motivo', 
            'Religión que profesan los padres'
        ];
    }


    public function prepareRows($rows)
    {
        return $rows->transform(function ($user) {
        	/*
            $status = (new OrderStatus)->find($user->order_status_id);
            if ($status) {
                $user->order_status_id = $status->name;
            }
            */

            return $user;
        });
    }

    public function query()
    {
        /*
        return QuotaConfirmationDetail::query()
            ->select('quota_confirmations.created_at', 'levels.name as level', 'quota_request_details.name as asp_name', 'quota_request_details.last_name as asp_ape', 'quota_request_details.document as asp_doc', 'birthday', 'quota_request_details.gender', 'quota_request_details.observations', 'origin', 'quota_requests.name', 'quota_requests.last_name', 'quota_requests.document', 'quota_requests.email', 'quota_requests.phone', 'quota_requests.cell_phone', 'quota_requests.profession', 'quota_requests.work_at', 'quota_requests.address', 'quota_requests.father_name', 'quota_requests.father_last_name', 'quota_requests.father_document', 'quota_requests.father_email', 'quota_requests.father_phone', 'quota_requests.father_cell_phone', 'quota_requests.father_profession', 'quota_requests.father_work_at', 'quota_requests.father_address', 'quota_requests.other_representatives', 'quota_requests.other_representative_reasons', 'religion')
            ->join('quota_requests', 'quota_requests.id', '=', 'quota_request_details.quota_request_id')
            ->leftJoin('levels', 'levels.id', '=', 'quota_request_details.level_id')
            ->orderBy('quota_requests.created_at', 'DESC')
            ->orderBy('quota_request_details.created_at', 'DESC');
        */

        return (new Student)->getConfirmatiosToExcel();
    }

}
