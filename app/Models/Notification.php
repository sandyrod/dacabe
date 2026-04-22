<?php

namespace App\Models;

use App\User;
use Auth;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'description', 'icon', 'status', 'url', 'level', 'company_id', 'user_id'
    ];
    
    public function users()
    {
        return $this->hasMany(User::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($notification) {
            if (!$notification->user_id)
                $notification->user_id = Auth::user()->id;
            if (!$notification->company_id)
                $notification->company_id = Auth::user()->company_id;
            if (!$notification->status)
                $notification->status = 1;
            if (!$notification->level)
                $notification->level = 1;
        });
    }

    public function getData()
    {
        if (Auth::user())
            return $this
                ->where(function($query) { 
                    $query->where('user_id', Auth::user()->id)
                        ->where('level', 1)
                        ->where('status', 1); 
                })
                ->orWhere(function($query) { 
                    $query->where('level', 0)
                        ->where('status', 1); 
                })
                ->orderBy('id', 'desc')
                ->get()
                ->take(10);

        return [];
    }

}
