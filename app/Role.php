<?php

namespace App;

use Laratrust\Models\LaratrustRole;

use Auth;
use DB;

class Role extends LaratrustRole
{
    public $guarded = [];

    public function getUserRoles($user_id){
    	$roles = $this->get(['id', 'name', 'display_name']);
    	foreach ($roles as $role)
    		$role->assigned = DB::table('role_user')->where('role_id', $role->id)->where('user_id', $user_id)->exists();
    	
    	return $roles;
    }

    public function getData(){
        return $this->get();
    }

    public function getReportConfig(){
        return [
            'title' => 'Listado de Roles', 
            'company' => Auth::user()->company
        ];
    }
}
