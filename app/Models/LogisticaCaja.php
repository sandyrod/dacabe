<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogisticaCaja extends Model
{
    protected $connection = 'company';
    protected $table = 'logistica_cajas';

    protected $fillable = [
        'codigo',
        'public_token',
        'bulto_codigo',
        'bulto_posicion',
        'bulto_total',
        'cliente_rif',
        'cliente_codcli',
        'cliente_nombre',
        'telefono',
        'direccion_fiscal',
        'direccion_entrega',
        'ciudad',
        'estado',
        'vendedor_user_id',
        'vendedor_nombre',
        'chofer_user_id',
        'chofer_nombre',
        'estatus',
        'fecha_armado',
        'fecha_entrega',
        'observaciones',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fecha_armado' => 'datetime',
        'fecha_entrega' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(LogisticaCajaItem::class, 'caja_id');
    }
}
