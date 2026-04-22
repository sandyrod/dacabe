<?php

namespace App\Exports;

use DB;
use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;


class StudentListExport implements FromQuery, WithHeadings
{
    use Exportable;

    private $level_id;

    public function __construct(int $level_id)
    {
        $this->level_id = $level_id;
    }

    public function headings(): array
    {
        return [
            'Nombre del Niño (a)',
            'F',
            'M',
            'Fecha Ncto.',
            'Edad',
            'Representante',
            'CI',
            'Teléfono',
            'Dirección',
            'Correo'
        ];
    }


    public function prepareRows($rows)
    {
        return $rows->transform(function ($student) {
            $age = \Carbon\Carbon::parse(@$student->birthday)->diff(\Carbon\Carbon::now())->format('%y');        
        	$student->birthday = substr(formatoFechaDMA($student->birthday), 0, 10);
            $student->age = $age; 
            $student->mother_name = "";
            $student->mother_document = "";
            $student->mother_email = "";
            $student->cell_phone = "";
            $student->mother_address = "";
            $mother_model = DB::table('students_representatives')->where('student_id', $student->student_id)->where('relationship_id', 1)->first();
            if ($mother_model) {
                $mother = DB::table('motherView')->where('id', $mother_model->person_id)->first();
                if ($mother) {
                    $student->mother_name = $mother->name . ' ' . $mother->last_name;
                    $student->mother_document = $mother->document;
                    $student->cell_phone = $mother->cell_phone . '   ' . $mother->phone;
                    $student->mother_address = $mother->address;
                    $student->mother_email = $mother->email;
                }
            }

            return $student;
        });        
    }

    public function query()
    {
        return (new Student)->getClassListToExcel($this->level_id);
    }

}
