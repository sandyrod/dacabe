<?php

namespace App\Models;

use App\User;
use App\Models\Company;
use Auth;

use Illuminate\Database\Eloquent\Model;

class Serial extends Model
{
    protected $table = 'serials';
    
     protected $fillable = [
        'user_id', 'company_id', 'description', 'quantity'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($company) {
            $company->user_id = Auth::user()->id;
            $company->quantity = 1;
        });
    }

    public function getData(){
        return $this->orderBy('id', 'DESC')->get();
    }

    public function getReportConfig(){
        return [
            'title' => 'Listado de Seriales emitidos', 
            'company' => Auth::user()->company
        ];
    }

}
