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
        Schema::connection($this->connection)->dropIfExists('pago_grupo_detalles');
        Schema::connection($this->connection)->create('pago_grupo_detalles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pago_grupo_id');
            $table->unsignedBigInteger('pedido_id');
            $table->float('monto', 12, 2);
            $table->float('base', 12, 2);
            $table->float('descuento', 12, 2);
            $table->float('total', 12, 2);
            $table->float('iva', 12, 2);
            $table->float('retencion', 12, 2);
            $table->timestamps();
            
            // Foreign key constraint
            $table->index('pago_grupo_id');
            $table->index('pedido_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pago_grupo_detalles');
    }
};
