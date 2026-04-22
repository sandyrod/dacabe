<?php

namespace App\Models;

use App\Models\Company;
use Auth;

use Illuminate\Database\Eloquent\Model;

class CompanyStatus extends Model
{
    protected $table = 'company_status';
    
     protected $fillable = [
        'name', 'description'
    ];

    public function companies()
    {
        return $this->hasMany(Company::class, 'company_status_id');
    }

    public function getData(){
        return $this->get();
    }

    public function getReportConfig(){
        return [
            'title' => 'Listado de Estatus de Empresas', 
            'company' => Auth::user()->company
        ];
    }

}
