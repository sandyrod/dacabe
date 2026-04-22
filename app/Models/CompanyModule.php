<?php

namespace App\Models;

use Auth;
use App\User;
use Illuminate\Database\Eloquent\Model;
use DB;

class CompanyModule extends Model
{
    protected $table = 'companies_modules';

    protected $fillable = [
        'user_id', 'module_id', 'expired_at', 'company_id'
    ];
    
	protected static function boot()
    {
        parent::boot();
        static::creating(function ($module) {
            if (! $module->user_id)
            	$module->user_id = Auth::user()->id;
        });
    }

    public function company() 
    {
       return $this->belongsTo(Company::class);
    }

    public function user() 
    {
       return $this->belongsTo(User::class);
    }

    public function module() 
    {
       return $this->belongsTo(Module::class);
    }
    
    public function getData() {
    	return $this->get();
    }

    public function getReportConfig(){
        return [
            'title' => 'Listado de Módulos', 
            'company' => Auth::user()->company
        ];
    }

    
}
