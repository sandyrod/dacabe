<?php

namespace App\Models;

use App\Models\Company;
use Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class ArtDepos extends Model
{
    protected $connection = 'company';
    protected $table = 'ARTDEPOS';
    public $timestamps = false;

    protected $fillable = [
        'NOMBRE',
        'EUNIDAD',
        'ECAJA', 
        'CDEPOS',
        'CODIGO',
        'CODDEPOS',
        'TIPDOC',
        'EUNIDADG',
        'EUNIDADN',
        'RESERVA'
    ];

    /**
     * Obtener stock total por producto
     */
    public static function getStockTotal($codigo)
    {
        return self::where('CODIGO', $codigo)
            ->sum('EUNIDAD');
    }

    /**
     * Obtener stock por depósito
     */
    public static function getStockByDeposito($codigo)
    {
        return self::where('CODIGO', $codigo)
            ->select('CDEPOS', 'EUNIDAD', 'ECAJA', 'RESERVA')
            ->get();
    }

    /**
     * Obtener productos con movimientos recientes
     */
    public static function getProductosConMovimientos($dias = 30)
    {
        return self::join('inven as i', 'i.CODIGO', '=', 'ARTDEPOS.CODIGO')
            ->select('ARTDEPOS.CODIGO', 'i.DESCR', 'i.TIPO', 
                    DB::raw('SUM(ARTDEPOS.EUNIDAD) as total_unidades'),
                    DB::raw('COUNT(DISTINCT ARTDEPOS.CDEPOS) as total_depositos'))
            ->where('ARTDEPOS.EUNIDAD', '>', 0)
            ->groupBy('ARTDEPOS.CODIGO', 'i.DESCR', 'i.TIPO')
            ->orderByDesc('total_unidades')
            ->get();
    }

    /**
     * Verificar si un producto tiene stock disponible
     */
    public static function hasStock($codigo, $cantidad = 1)
    {
        $stockTotal = self::where('CODIGO', $codigo)
            ->sum(DB::raw('EUNIDAD - COALESCE(RESERVA, 0)'));
        
        return $stockTotal >= $cantidad;
    }

    /**
     * Obtener productos críticos (bajo stock)
     */
    public static function getProductosCriticos($limite = 10)
    {
        return self::join('inven as i', 'i.CODIGO', '=', 'ARTDEPOS.CODIGO')
            ->select('ARTDEPOS.CODIGO', 'i.DESCR', 'i.SMIN', 'i.SMAX',
                    DB::raw('SUM(ARTDEPOS.EUNIDAD) as total_unidades'))
            ->groupBy('ARTDEPOS.CODIGO', 'i.DESCR', 'i.SMIN', 'i.SMAX')
            ->havingRaw('SUM(ARTDEPOS.EUNIDAD) <= COALESCE(i.SMIN, 0)')
            ->orderBy('total_unidades', 'asc')
            ->limit($limite)
            ->get();
    }

    /**
     * Relación con el inventario
     */
    public function inventario()
    {
        return $this->belongsTo(Inven::class, 'CODIGO', 'CODIGO');
    }

    /**
     * Obtener historial de sincronización
     */
    public static function getHistorialSincronizacion($codigo = null, $dias = 30)
    {
        $query = self::join('inven as i', 'i.CODIGO', '=', 'ARTDEPOS.CODIGO')
            ->select('ARTDEPOS.CODIGO', 'i.DESCR', 'ARTDEPOS.CDEPOS',
                    'ARTDEPOS.EUNIDAD', 'ARTDEPOS.ECAJA', 'ARTDEPOS.RESERVA',
                    DB::raw('CURRENT_TIMESTAMP as fecha_sincronizacion'),
                    DB::raw("'SINCRONIZACION' as tipo_movimiento"));

        if ($codigo) {
            $query->where('ARTDEPOS.CODIGO', $codigo);
        }

        return $query->orderByDesc('ARTDEPOS.CODIGO')->get();
    }

    /**
     * Obtener estadísticas de stock
     */
    public static function getEstadisticasStock()
    {
        return [
            'total_productos' => self::distinct('CODIGO')->count('CODIGO'),
            'total_unidades' => self::sum('EUNIDAD'),
            'total_reservas' => self::sum('RESERVA'),
            'productos_con_stock' => self::where('EUNIDAD', '>', 0)->distinct('CODIGO')->count('CODIGO'),
            'productos_sin_stock' => self::where('EUNIDAD', '<=', 0)->distinct('CODIGO')->count('CODIGO'),
            'valor_total_stock' => self::join('inven as i', 'i.CODIGO', '=', 'ARTDEPOS.CODIGO')
                ->sum(DB::raw('ARTDEPOS.EUNIDAD * COALESCE(i.PRECIO1, 0)'))
        ];
    }
}
