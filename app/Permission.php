<?php

namespace App;

use Laratrust\Models\LaratrustPermission;
use Auth;
use DB;

class Permission extends LaratrustPermission
{
    public $guarded = [];

    public function getRolePermissions($role_id){
    	$permissions = $this->get(['id', 'name', 'display_name']);
    	foreach ($permissions as $permission)
    		$permission->assigned = DB::table('permission_role')->where('permission_id', $permission->id)->where('role_id', $role_id)->exists();
    	
    	return $permissions;
    }

    public function getUserPermissions($user_id){
    	$permissions = $this->get(['id', 'name', 'display_name']);
    	foreach ($permissions as $permission)
    		$permission->assigned = DB::table('permission_user')->where('permission_id', $permission->id)->where('user_id', $user_id)->exists();
    	
    	return $permissions;
    }

    public function getData(){
        return $this->get();
    }

    public function getReportConfig(){
        return [
            'title' => 'Listado de Opciones', 
            'company' => Auth::user()->company
        ];
    }
}
