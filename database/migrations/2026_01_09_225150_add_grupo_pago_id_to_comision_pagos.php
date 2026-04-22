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
        Schema::connection('company')->table('comision_pagos', function (Blueprint $table) {
            $table->string('grupo_pago_id')->nullable()->after('id');
            $table->index('grupo_pago_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('company')->table('comision_pagos', function (Blueprint $table) {
            $table->dropIndex(['grupo_pago_id']);
            $table->dropColumn('grupo_pago_id');
        });
    }
};
