<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePedidoAjustesTable extends Migration
{
    protected $connection = 'company';

    public function up()
    {
        if (Schema::connection($this->connection)->hasTable('pedido_ajustes')) {
            return;
        }

        Schema::connection($this->connection)->create('pedido_ajustes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pedido_id');
            $table->enum('tipo', ['cargo', 'descuento'])->comment('cargo=nota débito, descuento=nota crédito');
            $table->string('concepto');
            $table->decimal('monto', 12, 2);
            $table->boolean('pagado')->default(false);
            $table->unsignedBigInteger('registrado_por')->nullable();
            $table->timestamps();

            $table->index('pedido_id');
        });
    }

    public function down()
    {
        Schema::connection($this->connection)->dropIfExists('pedido_ajustes');
    }
}
