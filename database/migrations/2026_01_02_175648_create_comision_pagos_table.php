<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'company';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection($this->connection)->create('comision_pagos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('comision_vendedores_id');
            $table->unsignedInteger('pagos_destino_id');
            $table->text('observaciones')->nullable();
            $table->date('fecha_pago');
            $table->decimal('monto_bs', 14, 2);
            $table->decimal('monto_divisa', 14, 2);
            $table->decimal('tasa', 14, 4);
            $table->enum('forma_pago', ['Divisa', 'Bolivares', 'Otro']);
            $table->timestamps();

            // Index and foreign key logic
            $table->index('comision_vendedores_id');
            $table->index('pagos_destino_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('comision_pagos');
    }
};
