<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComisionMovimiento extends Model
{
    protected $connection = 'company';
    protected $table = 'comision_movimientos';

    protected $fillable = [
        'correo_vendedor',
        'nombre_vendedor',
        'tipo',
        'monto',
        'es_credito',
        'concepto',
        'grupo_pago_id',
        'monto_comision_original',
        'monto_pagado_real',
        'saldo_aplicado',
        'saldo_anterior',
        'saldo_resultante',
        'registrado_por',
    ];

    protected $casts = [
        'monto'                  => 'decimal:2',
        'es_credito'             => 'boolean',
        'monto_comision_original'=> 'decimal:2',
        'monto_pagado_real'      => 'decimal:2',
        'saldo_aplicado'         => 'decimal:2',
        'saldo_anterior'         => 'decimal:2',
        'saldo_resultante'       => 'decimal:2',
    ];

    /**
     * Saldo actual del vendedor (positivo = admin debe al vendedor,
     * negativo = admin tiene crédito / vendedor debe al admin).
     */
    public static function saldoActual(string $correo): float
    {
        $ultimo = self::where('correo_vendedor', $correo)->latest('id')->first();
        return $ultimo ? (float) $ultimo->saldo_resultante : 0.0;
    }

    /**
     * Crea un movimiento calculando automáticamente saldo_anterior y saldo_resultante.
     */
    public static function registrarMovimiento(array $data): self
    {
        $saldoActual = self::saldoActual($data['correo_vendedor']);
        $efecto      = $data['es_credito'] ? abs($data['monto']) : -abs($data['monto']);

        return self::create(array_merge($data, [
            'saldo_anterior'   => $saldoActual,
            'saldo_resultante' => round($saldoActual + $efecto, 2),
        ]));
    }
}
