<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PedidoAjuste extends Model
{
    protected $connection = 'company';
    protected $table     = 'pedido_ajustes';

    protected $fillable = [
        'pedido_id',
        'tipo',
        'concepto',
        'monto',
        'pagado',
        'registrado_por',
    ];

    protected $casts = [
        'monto'  => 'float',
        'pagado' => 'boolean',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    /**
     * Net effect on the total to pay (positive = increases debt, negative = reduces debt).
     */
    public function getNetoAttribute(): float
    {
        return $this->tipo === 'cargo' ? $this->monto : -$this->monto;
    }

    /**
     * Sum of unpaid adjustments net for a pedido.
     */
    public static function netoPendiente(int $pedidoId): float
    {
        $row = self::where('pedido_id', $pedidoId)
            ->where('pagado', false)
            ->selectRaw("SUM(CASE WHEN tipo='cargo' THEN monto ELSE -monto END) as neto")
            ->first();

        return round((float) ($row->neto ?? 0), 2);
    }

    /**
     * Mark all pending adjustments for a pedido as paid.
     */
    public static function marcarPagados(int $pedidoId): void
    {
        self::where('pedido_id', $pedidoId)
            ->where('pagado', false)
            ->update(['pagado' => true]);
    }

    protected static function boot()
    {
        parent::boot();

        // Actualizar total_ajustes cuando se crea un nuevo ajuste
        static::created(function ($ajuste) {
            self::actualizarTotalAjustes($ajuste->pedido_id);
        });

        // Actualizar total_ajustes cuando se elimina un ajuste
        static::deleted(function ($ajuste) {
            self::actualizarTotalAjustes($ajuste->pedido_id);
        });

        // Actualizar total_ajustes cuando se actualiza un ajuste (cambio en monto o pagado)
        static::updated(function ($ajuste) {
            if ($ajuste->wasChanged('monto') || $ajuste->wasChanged('pagado')) {
                self::actualizarTotalAjustes($ajuste->pedido_id);
            }
        });
    }

    /**
     * Actualizar total_ajustes y saldo_ajustes en la tabla pedidos.
     * - total_ajustes: suma neta de TODOS los ajustes (pagados o no).
     * - saldo_ajustes: suma neta de los ajustes aún NO pagados (mismo valor que
     *   netoPendiente(); coincide con total_ajustes cuando ningún ajuste ha sido cobrado).
     */
    private static function actualizarTotalAjustes(int $pedidoId): void
    {
        $totalAjustes = self::where('pedido_id', $pedidoId)
            ->selectRaw("SUM(CASE WHEN tipo='cargo' THEN monto ELSE -monto END) as total")
            ->value('total');

        $saldoAjustes = self::where('pedido_id', $pedidoId)
            ->where('pagado', false)
            ->selectRaw("SUM(CASE WHEN tipo='cargo' THEN monto ELSE -monto END) as total")
            ->value('total');

        DB::connection('company')
            ->table('pedidos')
            ->where('id', $pedidoId)
            ->update([
                'total_ajustes' => $totalAjustes ?? 0,
                'saldo_ajustes' => $saldoAjustes ?? 0,
            ]);
    }
}
