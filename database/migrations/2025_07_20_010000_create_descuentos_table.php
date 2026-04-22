<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDescuentosTable extends Migration
{
    /**
     * The connection name for the migration.
     *
     * @var string
     */
    protected $connection = 'company';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection($this->connection)->dropIfExists('descuentos');
        Schema::connection($this->connection)->create('descuentos', function (Blueprint $table) {
            $table->increments('id');
            $table->float('porcentaje');
            $table->string('nombre', 150)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection($this->connection)->dropIfExists('descuentos');
    }
}
