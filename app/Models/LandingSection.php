<?php

namespace App\Models;

use App\User;

use Illuminate\Database\Eloquent\Model;

class LandingSection extends Model
{
    protected $fillable = [
        'landing_id', 'shortname', 'name', 'description', 'title', 'content', 'image', 'bg-image', 'subtitle', 'primary_color', 'secondary_color'
    ];
     
    public function landing() 
    {
	   return $this->belongsTo(Landing::class, 'landing_id');
	}
	
}
