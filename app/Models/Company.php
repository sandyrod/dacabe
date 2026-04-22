<?php

namespace App\Models;

use App\User;
use App\Models\CompanyStatus;
use Auth;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'companies';
    
     protected $fillable = [
        'name', 'email', 'code', 'url', 'logo', 'phone', 'location', 'status', 'db_name'
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function company_status()
    {
        return $this->belongsTo(CompanyStatus::class);
    }

    public function ftps()
    {
        return $this->hasMany('App\Models\Ftp', 'company_id');
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($company) {
            $company->token = \Illuminate\Support\Str::random(16);
            $company->company_status_id = getActiveCompanyStatus();
            $company->theme = 'default';
        });
    }

    public function getMainCompany ()
    {
        return $this->select('id', 'name', 'code', 'email', 'phone', 'logo', 'theme')->where('main', 1)->orderby('id', 'DESC')->first();
    }

    public function getMyCompany ()
    {
        return $this->select('id', 'name', 'code', 'email', 'phone', 'logo', 'theme')->where('id', auth()->user()->company_id)->first();
    }

    public function getLogoCompany ()
    {
        return $this->select('id', 'logo', 'theme')->find(auth()->user()->id);
    }
        

    public function getData()
    {
        return $this->orderBy('name')->get();
    }

    public function getReportConfig(){
        return [
            'title' => 'Listado de Empresas', 
            'company' => Auth::user()->company
        ];
    }

}
