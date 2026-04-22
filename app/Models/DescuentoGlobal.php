<?php

namespace App\Models;

use App\Models\{Company, OrderGrupo};
use Auth;

use Illuminate\Database\Eloquent\Model;

class DescuentoGlobal extends Model
{
    protected $connection = 'company';
    protected $table = 'descuento_global';
    
     protected $fillable = [
        'porcentaje', 'ref1', 'ref2', 'discount', 'show_precio1'
    ];

    protected static function boot()
    {
        parent::boot();
        static::updating(function ($model) {
            if ($model->ref1 && $model->ref2){
                $factor = $model->ref2/$model->ref1;
                $products = (new OrderInven)->where('BASE1', '>', 0)->get();
                $n=0;
                $total=0;
                foreach($products as $product){
                    $base2=$product->BASE1*$factor;
                    // Cedano NO ACTUALZIAR PRODUCTOS DEL DPTO NACIONALES
                    $grupo = (new OrderGrupo)->where('DGRUPO', 'like', '%NACIONAL%')->first();
                    $cgrupo = $grupo ? $grupo->CGRUPO : null;
                    if ($cgrupo){
                        (new OrderInven)->where('CODIGO', $product->CODIGO)->where('CGRUPO', '!=', $cgrupo)->update(['BASE2'=>$base2]);
                    } else {
                        (new OrderInven)->where('CODIGO', $product->CODIGO)->update(['BASE2'=>$base2]);
                    }
                    $total+=$base2>0?(($product->BASE1/$base2)*100-100):0;
                    $n++;
                }
                $model->discount=$n>0?$total/$n:0;
                if ($n>0){
                    (new Pedido)->where('estatus', 'CARGANDO')->delete();
                    (new Rate)->create(['rate'=>$model->ref1, 'rate2'=>$model->ref2, 'discount'=>$model->discount]);
                }
            }
        });
    }

    public function getData()
    {
        return $this->get();
    }

    public function getReportConfig(){
        return [
            'title' => 'Descuentos', 
            'company' => Auth::user()->company
        ];
    }

}
