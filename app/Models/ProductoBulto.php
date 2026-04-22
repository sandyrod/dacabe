<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoBulto extends Model
{
    protected $connection = 'company';
    protected $table     = 'producto_bultos';

    protected $fillable = ['codigo', 'unidades_por_bulto'];

    protected $casts = [
        'unidades_por_bulto' => 'integer',
    ];
}
