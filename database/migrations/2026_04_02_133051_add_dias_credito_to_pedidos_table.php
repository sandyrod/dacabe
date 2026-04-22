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
        Schema::connection($this->connection)->table('pedidos', function (Blueprint $table) {
            $table->integer('dias_credito')->nullable()->after('fecha')->comment('Días de crédito otorgados para el pago del pedido');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->connection)->table('pedidos', function (Blueprint $table) {
            $table->dropColumn('dias_credito');
        });
    }
};
