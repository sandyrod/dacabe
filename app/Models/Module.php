<?php

namespace App\Models;

use Auth;
use App\User;
use Illuminate\Database\Eloquent\Model;
use DB;

class Module extends Model
{
    protected $fillable = [
        'user_id', 'name', 'description', 'status', 'icon', 'button_text', 'url'
    ];
    
	protected static function boot()
    {
        parent::boot();
        static::creating(function ($module) {
            if (! $module->user_id)
            	$module->user_id = Auth::user()->id;
        });
    }

    public function user() {
	   return $this->belongsTo(User::class,'user_id');
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

    public function getPermissions ($company_id)
    {
        $data = $this->select('name', 'description', 'id')->where('status', 1)->get();        

        $permissions = $data->map(function($item, $key) use ($company_id) {
            $modules = DB::table('companies_modules')->select('companies_modules.id as companies_modules_id', 'expired_at', 'uses')
                ->where('module_id', $item->id)
                ->where('company_id', $company_id)
                ->first();

            if ($modules)
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'company_id' => $company_id,
                    'companies_modules_id' => $modules->companies_modules_id,
                    'expired_at' => $modules->expired_at,
                    'uses' => $modules->uses
                ];
          
            return [
                'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'company_id' => $company_id,
                    'companies_modules_id' => null,
                    'expired_at' => null,
                    'uses' => null
            ];
        });


        return $permissions;
    }

    public function updatePermissions($module_id, $company_id, $expired_at, $uses)
    {
        $module = DB::table('companies_modules')->select('companies_modules.id')
                ->where('module_id', $module_id)
                ->where('company_id', $company_id)
                ->first();

        if ($module){
            $module = DB::table('companies_modules')
                ->where('module_id', $module_id)
                ->where('company_id', $company_id)
                ->delete();
        
            return $this->getPermissions ($company_id);
        }

        $new = DB::table('companies_modules')->insert([
            'user_id' => Auth::user()->id, 
            'module_id' => $module_id,
            'company_id' => $company_id,
            'uses' => $uses,
            'expired_at' => $expired_at
        ]);
        return $this->getPermissions ($company_id);

    }
}
