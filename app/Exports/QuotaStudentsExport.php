<?php

namespace App\Exports;

use DB;
use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;


//class OrdersExport implements FromCollection
class QuotaStudentsExport implements FromQuery, WithHeadings
{
    use Exportable;

    public function headings(): array
    {
        return [
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
        return $rows->transform(function ($student) {
        	$student->birthday = formatoFechaDMA($student->birthday);
            $student->mother_name = "";
            $student->mother_last_name = "";
            $student->mother_document = "";
            $student->mother_email = "";
            $student->mother_phone = "";
            $student->mother_cell_phone = "";
            $student->mother_profession = "";
            $student->mother_works_at = "";
            $student->mother_address = "";
            $mother_model = DB::table('students_representatives')->where('student_id', $student->student_id)->where('relationship_id', 1)->first();
            if ($mother_model) {
                $mother = DB::table('motherView')->where('id', $mother_model->person_id)->first();
                if ($mother) {
                    $student->mother_name = $mother->name;
                    $student->mother_last_name = $mother->last_name;
                    $student->mother_document = $mother->document;
                    $student->mother_email = $mother->email;
                    $student->mother_phone = $mother->phone;
                    $student->mother_cell_phone = $mother->cell_phone;
                    $student->mother_profession = $mother->profession;
                    $student->mother_works_at = $mother->works_at;
                    $student->mother_address = $mother->address;
                }
            }

            $student->father_name = "";
            $student->father_last_name = "";
            $student->father_document = "";
            $student->father_email = "";
            $student->father_phone = "";
            $student->father_cell_phone = "";
            $student->father_profession = "";
            $student->father_works_at = "";
            $student->father_address = "";
            $father_model = DB::table('students_representatives')->where('student_id', $student->student_id)->where('relationship_id', 2)->first();
            if ($father_model) {
                $father = DB::table('fatherView')->where('id', $father_model->person_id)->first();
                if ($father) {
                    $student->father_name = $father->name;
                    $student->father_last_name = $father->last_name;
                    $student->father_document = $father->document;
                    $student->father_email = $father->email;
                    $student->father_phone = $father->phone;
                    $student->father_cell_phone = $father->cell_phone;
                    $student->father_profession = $father->profession;
                    $student->father_works_at = $father->works_at;
                    $student->father_address = $father->address;
                }
            }

            return $student;
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
