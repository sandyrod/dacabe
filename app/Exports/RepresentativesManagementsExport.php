<?php

namespace App\Exports;

use DB;
use App\Models\Representative;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;


class RepresentativesManagementsExport implements FromQuery, WithHeadings
{
    use Exportable;

    public function headings(): array
    {
        return [
            'Grado',
            'Status para Boletas',
            'Status'            
        ];
    }


    public function prepareRows($rows)
    {
        return $rows->transform(function ($student) {
            //$student->mother_name = "";
            $student->name = $student->name . ' ' . $student->last_name;
            $student->last_name = '';
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
                        
                    $student->student_id = ($balance > 0) ? 'PENDIENTE' : 'SOLVENTE';                    
                } else {
                    $student->student_id = 'CHEQUEAR';
                }
            } else {
                $student->student_id = 'REVISAR';                
            }
            //$student->student_id = "";
            $student->debits = "";
            $student->credits = "";

            return $student;
        });        
    }

    public function query()
    {
        return (new Representative)->getManagementDataToExcel();
    }

}
