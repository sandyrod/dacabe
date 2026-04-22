<?php

namespace App\Models;

use DB;
use Auth;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class Inven extends Model
{ 
    protected $table = 'inven';
    protected $fillable = ['codigo', 'descr', 'alterno', 'aplic1', 'tipo', 'controla','cunimedd', 'dunimedd', 'cunimedm','dunimedm', 'unidademp', 'unipaq', 'peso', 'cgrupo', 'csgrupo', 'cdpto', 'cubica', 'ctipprod', 'cimpuest', 'impuest', 'monto', 'aplicaisv', 'aplicdes', 'balanza', 'ulttdolar', 'actudolar', 'actualdl', 'precio1', 'precio2', 'precio3', 'precio4', 'precio5', 'base1', 'base2', 'base3', 'base4', 'base5', 'porc1', 'porc2', 'porc3', 'porc4', 'porc5', 'actunidad', 'impadic', 'impdes', 'impmonto', 'cantidad', 'iva1', 'iva2', 'iva3', 'iva4', 'iva5', 'pvpm1', 'pvpm2', 'pvpm3', 'pvpm4', 'pvpm5', 'tasa', 'codubic', 'company_id'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->company_id = Auth::user()->company_id;
        });
        static::updating(function ($model) {
            $model->company_id = Auth::user()->company_id;
        });
    }        

    public function getPrices($inven, $config)
    {
        $poriva = $inven ? $inven->impuest : ($product['iva1'] > 0 ? '16' : '0');
        $precio=$iva=$base=0;
        if ($inven && $inven->aplicaisv) {
            $precio = ($inven->pvpm1 * $config[0]['tasadolar']);
        } else {
            if ($inven && $inven->peso == 2) {
                $precio = ($inven->pvpm1 * $config[0]['tasadolarrec']);
            } else {
                $precio = $inven->precio1;
            }
        }
        $base = $precio / (1 + ($poriva/100));
        $iva = $precio - $base;
        return ['precio'=>$precio, 'iva'=>$iva, 'base'=>$base];
    }

    public function getData($request) 
    {
        //if ($id) {
        //    return $this->find($id);
        //}
        $products = $this->where('company_id', Auth::user()->company_id)->orderBy('descr')->get();
        $config = $this->getServerConfig($request);
        foreach($products as $product) {
            $prices = $this->getPrices($product, $config);
            $product->precio = $prices['precio'];
            $product->iva = $prices['iva'];
            $product->base = $prices['base'];
        }
        return $products;
        //return $this->where('precio1', '>', 0)->orderBy('descr')->get();
    }

    public function getDataFromServer() 
    {
        return $this->getData();
    }

    private function notHasChanges ($record, $item)
    {
        return (
            $record->descr == $item['descr'] 
            && $record->alterno == $item['alterno'] 
            && $record->controla == $item['controla'] 
            && $record->cgrupo == $item['cgrupo']
            && $record->csgrupo == $item['csgrupo'] 
            && $record->cimpuest == $item['cimpuest'] 
            && $record->impuest == $item['impuest'] 
            && $record->precio1 == $item['precio1'] 
            && $record->precio2 == $item['precio2'] 
            && $record->porc1 == $item['porc1'] 
            && $record->porc2 == $item['porc2'] 
            && $record->iva1 == $item['iva1'] 
            && $record->iva2 == $item['iva2']             
        );
    }

    
    public function searchProduct($search) 
    {
        return $this
            ->where('company_id', Auth::user()->company_id)
            ->where(function ($query) use ($search) {
                $query->where('codigo', 'like', '%'.$search.'%')
                    ->orWhere('descr', 'like', '%'.$search.'%')
                    ->orWhere('alterno', 'like', '%'.$search.'%')
                    ->orWhere('controla', 'like', '%'.$search.'%');
            })            
            ->orderBy('descr')
            ->get();
    }

    public function getProducts($request) 
    {
        $products = $this->orderBy('descr')->where('company_id', Auth::user()->company_id)->get();
        /*
        $config = $this->getServerConfig($request);
        */
        foreach($products as $product) {
            /*
            $prices = $this->getPrices($product, $config);
            $product->precio = $prices['precio'];
            $product->iva = $prices['iva'];
            $product->base = $prices['base'];
            */
            $product->precio = $product->precio1;
            $product->iva = $product->iva1;
            $product->base = $product->base1;
        }
        return $products;
    }
    

}
