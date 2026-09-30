<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('company')->table('dacabe.pagos', function (Blueprint $table) {
            $table->boolean('saldos_actualizados')->default(false);
        });
    }

    public function down(): void
    {
        Schema::connection('company')->table('dacabe.pagos', function (Blueprint $table) {
            $table->dropColumn('saldos_actualizados');
        });
    }
};