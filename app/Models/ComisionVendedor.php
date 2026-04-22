<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComisionVendedor extends Model
{
    use HasFactory;

    protected $connection = 'company';
    protected $table = 'comision_vendedores';

    protected $fillable = [
        'pago_id',
        'codigo_producto',
        'nombre_producto',
        'cantidad',
        'monto_base_comision',
        'monto_comision',
        'porcentaje_comision',
        'nombre_vendedor',
        'correo_vendedor',
        'estatus_comision'
    ];

    protected $casts = [
        'monto_base_comision' => 'decimal:2',
        'monto_comision' => 'decimal:2',
        'porcentaje_comision' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function pago()
    {
        return $this->belongsTo(\App\Models\Pago::class, 'pago_id')->withDefault();
    }

    public function comisionPagos()
    {
        return $this->hasMany(\App\Models\ComisionPago::class, 'comision_vendedores_id');
    }

    public function getPendingCommissions()
    {
        return $this
            ->where('estatus_comision', 'pagada')
            ->where('correo_vendedor', auth()->user()->email)
            ->whereIn('id', \App\Models\ComisionPago::where('recibido', '!=', 1)->select('comision_vendedores_id'))
            ->get();
    }
}
