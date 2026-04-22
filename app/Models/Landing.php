<?php

namespace App\Models;

use App\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Auth;

class Landing extends Model
{
    protected $fillable = [
        'company_id', 'theme_id', 'status', 'name', 'slug', 'company_name', 'main_logo', 'footer_logo', 'email', 'phone', 'whatsapp', 'float_whatsapp', 'float_whatsapp_text', 'schedule', 'instagram', 'facebook', 'twitter', 'title', 'slogan', 'video', 'address', 'map_location', 'primary_color', 'secondary_color', 'gallery_path'
    ];
     
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (! $model->company_id) {
                $model->company_id = Auth::user()->company_id;
            }
            if (! $model->status) {
                $model->status = 'A';
            }
        });
    }  

    public function company() 
    {
	   return $this->belongsTo(Company::class, 'company_id');
	}

    public function theme() 
    {
       return $this->belongsTo(Theme::class, 'theme_id');
    }

	public function sections() 
    {
	   return $this->hasMany(LandingSections::class, 'landing_id');
	}

    public function setNameAttribute(string $name): void
    {
        $this->attributes['name'] = $name;
        $this->generateUniqueSlug();
    }

    private function generateUniqueSlug(): void
    {
        $slug = Str::slug($this->name);
        $originalSlug = $slug;
        $slugNumber = null;

        if (preg_match('/-(\d+)$/', $slug, $matches)) {
            $slugNumber = $matches[1];
            $slug = Str::replaceLast("-$slugNumber", '', $slug);
        }

        $existingSlugs = $this->getExistingSlugs($slug);
        if (!in_array($slug, $existingSlugs)) {
            $this->attributes['slug'] = $slug . ($slugNumber ? "-$slugNumber" : '');
            return;
        }

        $i = $slugNumber ? ($slugNumber + 1) : 1;
        $uniqueSlugFound = false;
        while (!$uniqueSlugFound) {
            $newSlug = $slug . '-' . $i;
            if (!in_array($newSlug, $existingSlugs)) {
                $this->attributes['slug'] = $newSlug;
                $uniqueSlugFound = true;
            }
            $i++;
        }

        $this->attributes['slug'] = $originalSlug . '-' . mt_rand(1000, 9999);
    }

    private function getExistingSlugs(string $slug): array
    {
        return self::where('slug', 'LIKE', $slug . '%')
            ->where('id', '!=', $this->id ?? null)
            ->pluck('slug')
            ->toArray();
    }

    public function getData(){
        return $this->orderBy('name')->get();
    }

    public function getReportConfig(){
        return [
            'title' => 'Listado de Landings', 
            'company' => Auth::user()->company
        ];
    }
	
}
