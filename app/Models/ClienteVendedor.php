<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClienteVendedor extends Model
{
    use HasFactory;

    protected $connection = 'company';
    protected $table = 'cliente_vendedor';
    protected $fillable = ['rif', 'email_vendedor'];

    // Relación con Cliente (OrderClient)
    public function cliente()
    {
        return $this->belongsTo(OrderClient::class, 'rif', 'RIF');
    }

    // Relación con Vendedor
    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class, 'email_vendedor', 'email')->with('user');
    }
}
