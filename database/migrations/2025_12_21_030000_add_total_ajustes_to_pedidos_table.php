<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('company')->table('pedidos', function (Blueprint $table) {
            $table->decimal('total_ajustes', 12, 2)->default(0)->after('saldo_iva_bs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('company')->table('pedidos', function (Blueprint $table) {
            $table->dropColumn('total_ajustes');
        });
    }
};
