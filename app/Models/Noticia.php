<?php

namespace App\Models;

use App\Models\Company;
use App\Models\Category;
use Auth;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Noticia extends Model
{
    protected $fillable = [
        'title', 'resume', 'content', 'slug', 'image', 'start_at', 'end_at', 'views', 'likes', 'status', 'user_id', 'company_id', 'section', 'path_images'
    ];

    public function companies()
    {
        return $this->hasMany(Companies::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function categories() {
       return $this->belongsToMany(Category::class,'category_post');
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($post) {
            $post->company_id = Auth::user()->company_id;
            $post->user_id = Auth::user()->id;
            $post->section = $post->section ?? 'notices';
            $post->resume = $post->resume ?? $post->title;
            $post->image = $post->image ?? 'nofoto.jpg';
            $post->slug = Str::slug($post->title);
        });
    }

    public function getData(){
        return $this->with('categories')->where('section', 'notices')->get();
    }

    public function getEvents(){
        return $this->where('section', 'events')->get();
    }

    public function getGalleries(){
        return $this->where('section', 'galleries')->get();
    }

    public function getReportConfig(){
        return [
            'title' => 'Listado de Noticias', 
            'company' => Auth::user()->company
        ];
    }

}
