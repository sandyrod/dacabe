<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Model;

class Drugstore extends Model
{
	protected $fillable = ['code', 'name', 'url', 'address'];

	protected static function boot()
    {
        parent::boot();
        static::creating(function ($ftp) {
            if (! $ftp->url) {
                $ftp->url = '200.35.81.85';
            }
        });
    }

    public function getData (  )
    {
        return $this->get();
    }

    public function getReportConfig(){
        return [
            'title' => 'Listado de Droguerias', 
            'company' => Auth::user()->company
        ];
    }
}
