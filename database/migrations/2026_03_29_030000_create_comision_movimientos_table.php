<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComisionMovimientosTable extends Migration
{
    protected $connection = 'company';

    public function up()
    {
        Schema::connection($this->connection)->create('comision_movimientos', function (Blueprint $table) {
            $table->id();
            $table->string('correo_vendedor');
            $table->string('nombre_vendedor')->nullable();
            // Tipos: comision_devengada = lo que ganó el vendedor
            //        pago_comision      = lo que el admin le pagó
            //        aplicacion_saldo   = aplicación de saldo previo (crédito/débito)
            $table->enum('tipo', ['comision_devengada', 'pago_comision', 'aplicacion_saldo']);
            $table->decimal('monto', 12, 2);  // siempre positivo
            // true  = crédito al vendedor → balance sube  (admin le debe más)
            // false = débito al vendedor  → balance baja  (admin pagó / aplicó crédito)
            $table->boolean('es_credito');
            $table->text('concepto');
            $table->string('grupo_pago_id', 50)->nullable();
            $table->decimal('monto_comision_original', 12, 2)->nullable(); // total adeudado en el batch
            $table->decimal('monto_pagado_real', 12, 2)->nullable();       // total pagado en el batch
            $table->decimal('saldo_aplicado', 12, 2)->nullable();          // crédito previo aplicado
            $table->decimal('saldo_anterior', 12, 2)->default(0);
            $table->decimal('saldo_resultante', 12, 2)->default(0);
            // Positivo = admin le debe al vendedor | Negativo = vendedor le debe al admin (admin pagó de más)
            $table->unsignedBigInteger('registrado_por')->nullable();
            $table->timestamps();

            $table->index(['correo_vendedor', 'id']);
            $table->index('grupo_pago_id');
            $table->index('tipo');
        });
    }

    public function down()
    {
        Schema::connection($this->connection)->dropIfExists('comision_movimientos');
    }
}
