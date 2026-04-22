<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagoArchivosTable extends Migration
{
    protected $connection = 'company';

    public function up()
    {
        if (Schema::connection($this->connection)->hasTable('pago_archivos')) {
            return;
        }
        Schema::connection($this->connection)->create('pago_archivos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('pago_grupo_id');
            $table->string('nombre_original', 255);
            $table->string('ruta', 500);
            $table->string('tipo_mime', 100)->nullable();
            $table->unsignedBigInteger('tamano')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::connection($this->connection)->dropIfExists('pago_archivos');
    }
}
