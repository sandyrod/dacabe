<?php

namespace App\Models;

use Auth;
use DB;

use Illuminate\Database\Eloquent\Model;

class Rate extends Model
{
    protected $fillable = [
        'rate', 'rate2', 'discount', 'bcv'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            /*
            if ($model->rate && $model->rate2){
                $factor = $model->rate2/$model->rate;
                $products = (new OrderInven)->where('BASE1', '>', 0)->get();
                $n=0;
                $total=0;
                foreach($products as $product){
                    $base2=$product->BASE1*$factor;
                    (new OrderInven)->where('CODIGO', $product->CODIGO)->update(['BASE2'=>$base2]);
                    $total+=$base2>0?(($product->BASE1/$base2)*100-100):0;
                    $n++;
                }
                $model->discount=$n>0?$total/$n:0;
                if ($n>0){
                    (new Pedido)->where('estatus', 'CARGANDO')->delete();
                }
            }
            */
        });
    }

    public function createOrUpdateRate($rate, $rate2=0)
    {
        $exists = $this->gatLastRate();
        if (! $exists || (isset($exists->bcv) && isset($exists->rate) && $exists->bcv != $rate)) {
            //$this->create(['rate' => $rate, 'rate2' => $rate2]);
            $this->create(['bcv' => $rate]);
        }
    }

    public function gatLastRate()
    {
        //return $this->select('rate', 'rate2', 'discount')->orderBy('id', 'DESC')->first();
        return $this->select('bcv', 'rate', 'rate2', 'discount')->orderBy('id', 'DESC')->first();
    }
    
}
