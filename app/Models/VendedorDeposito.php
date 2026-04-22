<?php

namespace App\Models;

use App\Models\Company;
use Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Model;

class VendedorDeposito extends Model
{
    protected $connection = 'company';
    protected $table = 'vendedor_deposito';
    public $timestamps = false;

    protected $fillable = ['vendedor_id', 'CDEPOS'];

    public function deposito()
    {
        return $this->belongsTo(Deposito::class, 'CDEPOS', 'CDEPOS');
    }
    
}
