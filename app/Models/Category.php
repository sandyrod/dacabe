<?php

namespace App\Models;

use App\Models\Company;
use Auth;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'category_post';
    
     protected $fillable = [
        'description', 'icon', 'company_id'
    ];

    public function companies()
    {
        return $this->hasMany(Companies::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($category) {
            $category->company_id = Auth::user()->company_id;
        });
    }

    public function getData(){
        return $this->get();
    }

    public function getReportConfig(){
        return [
            'title' => 'Listado de Categorias', 
            'company' => Auth::user()->company
        ];
    }

}
