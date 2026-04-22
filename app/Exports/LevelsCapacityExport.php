<?php

namespace App\Exports;

use DB;
use App\Models\{Level, Period, Student};
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;


class LevelsCapacityExport implements FromQuery, WithHeadings
{
    use Exportable;

    
    public function __construct()
    {
        
    }

    public function headings(): array
    {
        return [
            'Nivel/Grado',
            'Matrícula',
            'Matrícula Solicitada',
            'Matrícula Inscrita',
            'Cupos Disponibles'
        ];
    }


    public function prepareRows($rows)
    {
        return $rows->transform(function ($level) {
            $n = (new Student)->where('level_id', $level->id)->whereIn('student_condition', ['REGULAR', 'NUEVO'])->count();
            $level->solicitada = $n;
            $period = (new Period)->where('status', 'ACTIVO')->first();
            $m = (new Student)->where('period_id', $period->id)->where('level_id', $level->id)->whereIn('student_condition', ['REGULAR', 'NUEVO'])->count();
            $level->inscritos = $m;
            $level->disp = $level->capacity - $n;
            $level->id = ' ';

            return $level;
        });        
    }

    public function query()
    {
        return (new Level)->getCapacityData();
    }

}
