<?php

namespace App\Models;

use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Model;

class OrderBanco extends Model
{
    protected $connection = 'company';
    protected $table = 'BANCOS';
    public $timestamps = false;

    protected $fillable = [
        'CODIGO', 'NOMBRE'
    ];

    public function getData($codigo = null)
    {
        if ($codigo) {
            return $this->where('CODIGO', $codigo)->orderBy('NOMBRE')->first();
        }
        return $this->orderBy('CODIGO', 'DESC')->get();
    }


    public function getReportConfig()
    {
        return [
            'title' => 'Listado de Bancos', 
            'company' => Auth::user()->company
        ];
    }

}
