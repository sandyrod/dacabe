<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductoBultosTable extends Migration
{
    protected $connection = 'company';

    public function up()
    {
        Schema::connection($this->connection)->create('producto_bultos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 15)->unique()->comment('INVEN.CODIGO');
            $table->decimal('unidades_por_bulto', 10, 3)->default(1)->comment('Unidades que trae un bulto/caja');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::connection($this->connection)->dropIfExists('producto_bultos');
    }
}
