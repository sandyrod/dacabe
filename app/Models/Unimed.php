<?php

namespace App\Models;

use App\Models\Company;
use Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Model;

class Unimed extends Model
{
    protected $connection = 'company';
    protected $table = 'UNIMED';
    public $timestamps = false;
    
     
    public function companies()
    {
        return $this->hasMany(Companies::class);
    }
    
    public function createNew($request)
    {
        return $this->insert([
            'CUNIMED' => $request->CUNIMED,
            'DUNIMED' => $request->DUNIMED,
            'TIPO' => $request->TIPO,
        ]);
    }

    public function deleteRecord($code)
    {
        return $this->where('CUNIMED', $code)->delete();
    }

    public function getData($code = null)
    {
        if ($code) {
            return $this->where('CUNIMED', $code)->first();
        }
        return $this->get();
    }

    public function updateItem($code, $request)
    {
        return $this->where('CUNIMED', $code)->update([
            'CUNIMED' => $request->CUNIMED,
            'DUNIMED' => $request->DUNIMED
        ]);
    }

    public function getReportConfig()
    {
        return [
            'title' => 'Listado de Unidades', 
            'company' => Auth::user()->company
        ];
    }

}
