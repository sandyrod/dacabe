<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPagoGrupoIdFieldsToPagosTable extends Migration
{
     protected $connection = 'company';
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::connection($this->connection)->table('pagos', function(Blueprint $table) {           
            $table->integer('pago_grupo_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        Schema::connection($this->connection)->table('pagos', function($table) { 
            $table->dropColumn('pago_grupo_id');
        });
    }
}
