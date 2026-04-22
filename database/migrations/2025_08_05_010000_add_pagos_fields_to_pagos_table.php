<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPagosFieldsToPagosTable extends Migration
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
            $table->string('banco_codigo', 4)->nullable();
            $table->string('referencia', 50)->nullable();
            $table->float('rate', 10, 2)->nullable();
            $table->float('monto_bs', 10, 2)->nullable();
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
            $table->dropColumn('banco_codigo');
        });
    }
}
