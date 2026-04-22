<?php

namespace App\Traits;

use App\Models\Landing;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use DataTables;

use App\Http\Resources\ThemeCollection;

trait LandingsTrait
{

    private function getJsonOrDatatableResponse(Request $request, $landing){      
        if ($request->datatable)
            return $this->getDatatableResponse($request, $landing);
        
        return new ThemeCollection(Theme::paginate($this->paginate));
    }

    private function getDatatableResponse($request, $data){
        return DataTables::of($data)
            ->addColumn('action', function ( $data ) {
                return  $this->getActionColumn($data);
            })
            ->addColumn('companyname', function ( $data ) {
                return  $this->getCompanyNameColumn($data);
            })
            ->addColumn('themename', function ( $data ) {
                return  $this->getThemeNameColumn($data);
            })
            ->rawColumns(['icon', 'action', 'companyname', 'themename'])
            ->make(true);
    }

    public static function getCompanyNameColumn($data)
    {
        return optional($data->company)->name;
    }

    public static function getThemeNameColumn($data)
    {
        return optional($data->theme)->name;
    }

    public static function getActionColumn($data)
    {
        $buttons = [];
        if (hasPermission('edit-landings'))
            $buttons[] = [
                'style' => 'primary btn-sm',
                'name' => 'edit',
                'hint' => 'Editar',
                'icon' => 'edit',
            ];

        if (hasPermission('delete-landings'))
            $buttons[] = [
                'style' => 'danger btn-sm',
                'name' => 'delete',
                'hint' => 'Eliminar',
                'icon' => 'trash',
            ];
        return getActionHtmlColumn ($data, $buttons);
    }

    private function isMySession($user_id){
        return $user_id == Auth::user()->id;
    }
    
}
