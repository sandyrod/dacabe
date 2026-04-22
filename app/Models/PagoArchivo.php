<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagoArchivo extends Model
{
    protected $connection = 'company';
    protected $table = 'pago_archivos';
    protected $fillable = [
        'pago_grupo_id',
        'nombre_original',
        'ruta',
        'tipo_mime',
        'tamano',
    ];

    public function pago_grupo()
    {
        return $this->belongsTo(PagoGrupo::class);
    }

    public function esImagen()
    {
        return in_array($this->tipo_mime, ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }

    public function esPdf()
    {
        return $this->tipo_mime === 'application/pdf';
    }
}
