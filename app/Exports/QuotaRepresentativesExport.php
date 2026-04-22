<?php

namespace App\Exports;

use DB;
use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;


//class OrdersExport implements FromCollection
class QuotaRepresentativesExport implements FromQuery, WithHeadings
{
    use Exportable;

    public function headings(): array
    {
        return [
            'Grado',
            'Cédula Representante',
            'Nombre Representante',            
            'Apellido Representante',            
            '',            
        ];
    }


    public function prepareRows($rows)
    {
        return $rows->transform(function ($student) {
            $student->mother_name = "";
            $student->mother_last_name = "";
            $student->mother_document = "";
            //$student->student_id = "";
            $mother_model = DB::table('students_representatives')->where('student_id', $student->student_id)->where('relationship_id', 1)->first();
            if ($mother_model) {
                $mother = DB::table('motherView')->where('id', $mother_model->person_id)->first();
                if ($mother) {
                    $student->mother_name = $mother->name;
                    $student->mother_last_name = $mother->last_name;
                    $student->mother_document = $mother->document;                    
                }
            }
        	$student->student_id = " ";

            return $student;
        });        
    }

    public function query()
    {
        return (new Student)->getRepresentativesToExcel();
    }

}
