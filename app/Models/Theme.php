<?php
declare(strict_types=1);

namespace App\Models;

use App\User;
use Auth;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Theme extends Model
{
    protected $fillable = [
        'name', 'slug', 'template'
    ];       

    public function landings() {
	   return $this->hasMany(Landing::class, 'landing_id');
	}

    public function setTemplateAttribute(string $template): void
    {
        $this->attributes['template'] = $template;
        $this->generateUniqueSlug();
    }

    private function generateUniqueSlug(): void
    {
        $slug = Str::slug($this->template);
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
            'title' => 'Listado de Temas', 
            'company' => Auth::user()->company
        ];
    }
	
}
