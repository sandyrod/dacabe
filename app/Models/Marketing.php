<?php

namespace App\Models;

use App\Models\Company;
use Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Model;

class Marketing extends Model
{
    protected $connection = 'company';
    protected $table = 'marketing';
    
    protected $fillable = ['codigo', 'user_id', 'descripcion', 'estatus', 'mensaje', 'tipo', 'date_at', 'contacto', 'image'];
    
    
    public function marketing_detalle()
    {
        return $this->hasMany(MarketingDetalle::class);
    }

    public function marketing_detalle_pendiente()
    {
        return $this->hasMany(MarketingDetalle::class)->where('estatus', 'Pendiente');
    }
     
    public function getData($id = null)
    {
        if ($id) {
            return $this->where('id', $id)->first();
        }
        return $this->with('marketing_detalle')->get();
    }

    public function getReportConfig()
    {
        return [
            'title' => 'Listado de Campañas', 
            'company' => Auth::user()->company
        ];
    }

}
