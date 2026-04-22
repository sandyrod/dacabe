<?php

namespace App\Models;

use App\Models\ClientType;
use App\Models\Rate;
use App\User;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'address', 'phone', 'cellphone', 'status', 'client_type_id', 'user_id', 'workshop'
    ];
       

    public function client_type() {
	   return $this->belongsTo(ClientType::class,'client_type_id');
	}

	public function user() {
	   return $this->belongsTo(User::class,'user_id');
	}

	public function rates() {
	   return $this->belongsToMany(Rate::class,'clients_rates');
	}
}
