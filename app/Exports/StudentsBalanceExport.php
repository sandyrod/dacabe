<?php

namespace App\Exports;

use DB;
use App\Models\Representative;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;


class StudentsBalanceExport implements FromQuery, WithHeadings
{
    use Exportable;

    public function headings(): array
    {
        return [
            'Grado',
            'Nombre del Niño',
            'Deuda Pendiente'
        ];
    }


    public function prepareRows($rows)
    {
        return $rows->transform(function ($student) {
            $student->name = $student->name . ' ' . $student->last_name;
            $mother_model = DB::table('students_representatives')->where('student_id', $student->student_id)->where('relationship_id', 1)->first();
            /*
            if ($mother_model) {
                $mother = DB::table('motherView')->where('id', $mother_model->person_id)->first();
                if ($mother) {
                    $student->mother_name = $mother->name . ' ' . $mother->last_name;                    
                }
            }
            */
            if ($mother_model) {
                $representative = (new Representative)->where('person_id', $mother_model->person_id)->first();
                if ($representative) {
                    $balance = DB::table('transactions')
                        ->where('representative_id', $representative->id)
                        ->where('transaction_type', 'cxc')
                        ->sum('dollar_amount') 
                        - 
                        DB::table('transactions')
                        ->where('representative_id', $representative->id)
                        ->where('transaction_type', 'in')
                        ->sum('dollar_amount');
                        
                    $student->last_name = $balance && ($balance > 0 || $balance < 0) ? $balance : 0;
                } else {
                    $student->last_name = 'CHEQUEAR';
                }
            } else {
                $student->last_name = 'REVISAR';                
            }
            $student->student_id = '';                

            return $student;
        });        
    }

    public function query()
    {
        return (new Representative)->getStudentBalanceDataToExcel();
    }

}
