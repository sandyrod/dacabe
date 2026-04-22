<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComisionPago extends Model
{
    use HasFactory;

    protected $connection = 'company';
    protected $table = 'comision_pagos';

    protected $fillable = [
        'grupo_pago_id',
        'comision_vendedores_id',
        'pagos_destino_id',
        'observaciones',
        'fecha_pago',
        'monto_bs',
        'monto_divisa',
        'tasa',
        'forma_pago',
        'numero_referencia',
        'recibido',
    ];

    public function comisionVendedor()
    {
        return $this->belongsTo(ComisionVendedor::class, 'comision_vendedores_id');
    }

    public function pagoDestino()
    {
        return $this->belongsTo(Bank::class, 'pagos_destino_id', 'id');
    }
}
