<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;
use DB;

class Download extends Model
{
    
    public function company(){
        return $this->belongsTo('App\Models\Company', 'company_id');
    }

    public function getPendingFiles()
    {
        return $this->where('status', 0)->get();
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($table) {
            $table->company_id = Auth::user()->company_id;
            $table->created_at = now();
        });
        static::updating(function ($table) {
            $table->updated_at = now();
        });
    }

}

