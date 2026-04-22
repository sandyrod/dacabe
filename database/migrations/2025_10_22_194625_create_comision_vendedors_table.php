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
        Schema::connection($this->connection)->dropIfExists('comision_vendedores');
        Schema::connection($this->connection)->create('comision_vendedores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pago_id');
            $table->string('codigo_producto');
            $table->integer('cantidad');
            $table->decimal('monto_comision', 12, 2);
            $table->decimal('porcentaje_comision', 5, 2);
            $table->string('nombre_vendedor');
            $table->string('correo_vendedor');
            $table->enum('estatus_comision', ['pendiente', 'pagada'])->default('pendiente');
            $table->timestamps();
            
            // Foreign key constraint
            $table->index('codigo_producto');
            $table->index('estatus_comision');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comision_vendedores');
    }
};
