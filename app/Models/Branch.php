<?php

namespace App\Models;

use Auth;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $table = 'branches';
    
     protected $fillable = [
        'company_id', 'name', 'address'
    ];

    public function companies()
    {
        return $this->belongsTo(Companies::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->company_id = Auth::user()->company_id;
        });
    }

    public function getData()
    {
        return $this->where('company_id', auth()->user()->company_id)->get();
    }

    public function getReportConfig(){
        return [
            'title' => 'Listado de Sucursales', 
            'company' => Auth::user()->company
        ];
    }

}
