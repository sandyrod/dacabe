<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Command extends Model
{
	protected $fillable = ['command', 'command_response', 'status'];

	protected static function boot()
    {
        parent::boot();
        static::creating(function ($command) {
            $command->status = 'A';
        });
    }

    public function getData (  )
    {
        return $this
            ->select('id', 'command', 'command_response', 'status', 'created_at', 'updated_at')
            ->get();
    }
}
