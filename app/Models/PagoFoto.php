<?php

namespace App\Models;

use App\User;
use Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Model;

class PagoFoto extends Model
{
    protected $connection = 'company';
    protected $table = 'pago_fotos';

    protected $fillable = ['pago_id', 'foto', 'user_id'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->user_id = Auth::user()->id;
        });
    }

    public function getData($id = null)
    {
        if ($id) {
            return $this->where('id', $id)->first();
        }
        return $this->get();
    }

    public function getReportConfig()
    {
        return [
            'title' => 'Listado de Comprobantes', 
            'company' => Auth::user()->company
        ];
    }
}
