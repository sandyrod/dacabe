<?php

namespace App\Models;

use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Model;

class OrderTpago extends Model
{
    protected $connection = 'company';
    protected $table = 'TPAGO';
    public $timestamps = false;

    protected $fillable = [
        'CPAGO', 'DPAGO'
    ];

    public function getData($code = null)
    {
        if ($code) {
            return $this->where('CPAGO', $code)->orderBy('DPAGO')->first();
        }
        return $this->orderBy('CPAGO', 'DESC')->get();
    }


    public function getReportConfig()
    {
        return [
            'title' => 'Listado de Tipos de Pago', 
            'company' => Auth::user()->company
        ];
    }

}
