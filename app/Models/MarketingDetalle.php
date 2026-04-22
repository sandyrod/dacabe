<?php

namespace App\Models;

use App\Models\Company;
use Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Model;

class MarketingDetalle extends Model
{
    protected $connection = 'company';
    protected $table = 'marketing_detalle';
    public $timestamps = false;
    
    public function marketing()
    {
        return $this->belongsTo(Marketing::class);
    }
     
    public function getData($id = null)
    {
        if ($id) {
            return $this->where('id', $id)->first();
        }
        return $this->get();
    }

    public function getReportConfig()
    {
        return [
            'title' => 'Listado de Campañas', 
            'company' => Auth::user()->company
        ];
    }

}
