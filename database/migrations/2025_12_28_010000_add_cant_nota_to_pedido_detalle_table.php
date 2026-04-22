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
        Schema::connection($this->connection)->table('pedido_detalle', function (Blueprint $table) {
            $table->integer('cant_nota')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->connection)->table('pedido_detalle', function (Blueprint $table) {
            $table->dropColumn('cant_nota');
        });
    }
};
