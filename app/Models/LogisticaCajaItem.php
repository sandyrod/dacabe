<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogisticaCajaItem extends Model
{
    protected $connection = 'company';
    protected $table = 'logistica_caja_items';

    protected $fillable = [
        'caja_id',
        'pedido_id',
        'pedido_detalle_id',
        'factura_numero',
        'producto_codigo',
        'producto_descripcion',
        'unidad',
        'cantidad',
        'vendedor_codigo',
        'vendedor_nombre',
    ];

    protected $casts = [
        'cantidad' => 'float',
    ];

    public function caja()
    {
        return $this->belongsTo(LogisticaCaja::class, 'caja_id');
    }
}
