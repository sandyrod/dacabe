<?php

namespace App\Models;

use Auth;

use Illuminate\Database\Eloquent\Model;

class ExpenseGroup extends Model
{
    protected $table = 'expense_groups';
    
     protected $fillable = [
        'name', 'show_order', 'company_id'
    ];

    public function companies()
    {
        return $this->belongsTo(Companies::class);
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
        return $this->get();
    }

    public function getReportConfig(){
        return [
            'title' => 'Listado de Grupos de Gasto', 
            'company' => Auth::user()->company
        ];
    }

}
