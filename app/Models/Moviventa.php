<?php

namespace App\Models;

use DB;
use Auth;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class Moviventa extends Model
{ 
    protected $table = 'moviventas';
    protected $fillable = ['codigo', 'descr', 'descrl', 'cantidad', 'monto', 'impuesto', 'poriva', 'total', 'fecha', 'tpago', 'nro', 'grupo', 'modelo', 'tipprod', 'coddpto', 'desdpto', 'codubic', 'desubic', 'unidad', 'impreso', 'codimpre', 'porservi', 'servicio', 'emple', 'nomemple', 'espera', 'tipoitem', 'nroitem', 'cantelim', 'montod', 'porcd', 'tipod', 'fechad', 'coddesc', 'ddesc', 'catego', 'caja', 'codcli', 'nombre', 'dpto', 'rp', 'impuest', 'montorp', 'ivarp', 'porivarp', 'porserrp', 'servirp', 'devolver', 'artenv', 'precio', 'puntos', 'nrocorte', 'tipocli', 'unicaja', 'unidadc', 'hora', 'cantdev', 'unipaq', 'estac', 'peso', 'cdepos', 'cofer', 'codseri', 'impadic', 'impmonto', 'fechades', 'fechahast', 'fecharet', 'pagdes', 'coldes', 'cendes', 'colcendes', 'comprob', 'comprob2', 'montoret', 'ctitulo', 'nseccion', 'ccontrol', 'montondc', 'localemp', 'nref', 'fechadif', 'arttipnot',
             'aplicacert', 'nrodocu', 'nommaqfiscal', 'montofina', 'codbarra', 'codalter', 'facnrofis', 'tasa', 'prodreg', 'prodimp', 'rutaimp', 'tasadeldia', 'tipomoneda', 'company_id',
        ];

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

    public function getData($request) 
    {
        $products = $this->where('company_id', Auth::user()->company_id)->orderBy('descr')->get();
        
        return $products;        
    }
    

}
