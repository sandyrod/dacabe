<?php

namespace App\Traits;

use App\User;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use DataTables;

trait UserTrait
{
    private function requestAjaxOrJson($request)
    {
        return ($request->ajax() || $request->wantsJson());
    }

    private function getJsonOrDatatableResponse(Request $request, $users)
    {      
        if ($request->datatable)
            return $this->getDatatableResponse($request, $users);
        
        //return $this->getJsonResponse($request, $users);
    }

    private function getDatatableResponse($request, $data)
    {
        return DataTables::of($data)
            ->addColumn('roles', function ( $data ) {
                return $this->getRolesColumn($data);
            })
            ->addColumn('company', function ( $data ) {
                return $this->getCompanyColumn($data);
            })
            ->addColumn('action', function ( $data ) {
                return  $this->getActionColumn($data);
            })
            ->rawColumns(['roles', 'company', 'action'])
            ->make(true);
    }

    private function getRolesColumn($data)
    {
        $user = User::find($data->id);
        $roles = $user->roles;
    
        if ($roles)
            return $this->getHtmlRoles($roles);

        return '';
    }
    
    private function getCompanyColumn($data)
    {
        return $data->company->name;    
    }

    private function getHtmlRoles($roles)
    {
        $html = '<span class="hint--top"';
        $tooltip = '';
        foreach ($roles as $role)
            $tooltip .= $role->display_name . ', ';

        return $html . ' aria-label="'.substr($tooltip, 0, strlen($tooltip) - 2).'"><i class="fa fa-user"></i></span>';
    }


    public static function getActionColumn($data)
    {
        $buttons = [];
        if (hasPermission('edit-user'))
            $buttons[] = [
                'style' => 'primary btn-sm',
                'name' => 'edit',
                'hint' => 'Editar',
                'icon' => 'edit',
            ];

        if (hasPermission('delete-user'))
            $buttons[] = [
                'style' => 'danger btn-sm',
                'name' => 'delete',
                'hint' => 'Eliminar',
                'icon' => 'trash',
            ];

        if (hasPermission('edit-user'))
            $buttons[] = [
                'style' => 'success btn-sm',
                'name' => 'permission',
                'hint' => 'Permisos',
                'icon' => 'lock',
            ];


        return getActionHtmlColumn ($data, $buttons);
    }

    private function redirectUserUpdate($user)
    {
        if ($this->isMySession($user->id))
            return redirect()->route('users.show', $user->id)
                ->with('info', 'Tu perfil ha sido modificado satisfactoriamente');

        return redirect()->route('users.index')
                ->with('info', 'El usuario '.$user->name.' ha sido modificado satisfactoriamente');
    }

    private function isMySession($user_id)
    {
        return $user_id == Auth::user()->id;
    }
    
}
