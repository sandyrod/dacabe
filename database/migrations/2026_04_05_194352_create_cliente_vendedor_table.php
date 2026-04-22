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
        Schema::connection('company')->create('cliente_vendedor', function (Blueprint $table) {
            $table->id();
            $table->string('rif', 15); // RIF del cliente, mayúscula
            $table->string('email_vendedor', 100); // Email del vendedor, minúscula
            $table->timestamps();
            $table->unique(['rif', 'email_vendedor']); // Un cliente no puede estar asociado a un vendedor más de una vez
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cliente_vendedor');
    }
};
