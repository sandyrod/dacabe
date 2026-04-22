<?php

namespace App\Models;

use App\Models\Company;
use Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Model;

class Promocion extends Model
{
    protected $connection = 'company';
    protected $table = 'promociones';
    //public $timestamps = false;
    
     
    public function getData($code = null)
    {
        if ($code) {
            return $this->where('codigo', $code)->orderBy('codigo')->first();
        }
        return $this->orderBy('codigo')->get();
    }

    public function producto()
    {
        return $this->belongsTo(OrderInven::class, 'codigo', 'CODIGO');
    }

    public function getReportConfig()
    {
        return [
            'title' => 'Listado de Promociones', 
            'company' => Auth::user()->company
        ];
    }

}
