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
        if (!Schema::connection('company')->hasTable('inventario_inicial')) {
            Schema::connection('company')->create('inventario_inicial', function (Blueprint $table) {
                $table->id();
                // User foreign key sin constrained ya que users está en otra conexión
                $table->unsignedBigInteger('user_id');
                $table->string('codigo', 30);
                $table->integer('cantidad')->default(0);
                $table->date('fecha');
                $table->text('observacion')->nullable();
                $table->timestamps();
                
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('company')->dropIfExists('inventario_inicial');
    }
};
