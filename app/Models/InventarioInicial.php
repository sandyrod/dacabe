<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\User;

class InventarioInicial extends Model
{
    use HasFactory;

    protected $connection = 'company';

    protected $table = 'inventario_inicial';

    protected $fillable = [
        'user_id',
        'codigo',
        'cantidad',
        'fecha',
        'observacion',
    ];

    protected $casts = [
        'cantidad' => 'decimal:2',
        'fecha' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['producto_descripcion'];

    /**
     * Get the user that owns the inventory initial record.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get product that owns the inventory initial record.
     * Relación con tabla INVEN que usa CODIGO en lugar de ID
     * Nota: CODIGO puede ser 0, por lo que añadimos un scope de fallback
     */
    public function producto()
    {
        return $this->belongsTo(OrderInven::class, 'codigo', 'CODIGO')
            ->withDefault([
                'CODIGO' => 0, 
                'DESCR' => 'Producto no encontrado - Verificar código en INVEN',
                'ALTERNO' => null,
                'ARTICULO' => null,
                'REFERENCIA' => null,
                'BARRA' => null
            ]);
    }

    /**
     * Accessor: Obtener la descripción del producto considerando campos alternativos
     * Retorna producto_descripcion como atributo accesible
     * Usado cuando CODIGO puede ser 0
     */
    public function getProductoDescripcionAttribute()
    {
        if (!$this->producto || $this->producto->CODIGO == 0) {
            // Si CODIGO es 0, intentar encontrar por ALTERNO
            $alt = OrderInven::where('ALTERNO', $this->codigo)->first();
            
            return $alt ? $alt->DESCR : "Código: {$this->codigo}";
        }
        return $this->producto ? $this->producto->DESCR : "Código: {$this->codigo}";
    }

    /**
     * Get total initial inventory for a specific product.
     */
    public static function getTotalForProduct($productoCodigo)
    {
        return self::where('codigo', $productoCodigo)
            ->sum('cantidad');
    }

    /**
     * Get inventory initial records by date range.
     */
    public static function getByDateRange($startDate, $endDate, $userId = null)
    {
        $query = self::whereBetween('fecha', [$startDate, $endDate]);
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        return $query->with(['producto', 'user'])
            ->orderBy('fecha', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Scope para cargar productos con manejo especial para CODIGO = 0
     */
    public function scopeWithProduct($query)
    {
        return $query->with(['producto' => function ($q) {
            $q->select('CODIGO', 'DESCR', 'ALTERNO', 'CGRUPO');
        }]);
    }
}
