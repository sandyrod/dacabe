<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagoGruposTable extends Migration
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
        Schema::connection($this->connection)->dropIfExists('pago_grupos');
        Schema::connection($this->connection)->create('pago_grupos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('moneda_pago', 150);
            $table->date('fecha_pago')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('seller_id')->nullable();
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
        Schema::connection($this->connection)->dropIfExists('pago_grupos');
    }
}
