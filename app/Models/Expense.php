<?php

namespace App\Models;

use Auth;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $table = 'expenses';
    
     protected $fillable = [
        'company_id', 'expense_group_id', 'reference', 'name', 'description', 'date_at', 'amount', 'dollar_amount', 'rate', 'branch_id'
    ];

    public function companies()
    {
        return $this->belongsTo(Companies::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function expense_group()
    {
        return $this->belongsTo(ExpenseGroup::class);
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
            'title' => 'Listado de Gastos', 
            'company' => Auth::user()->company
        ];
    }

}
