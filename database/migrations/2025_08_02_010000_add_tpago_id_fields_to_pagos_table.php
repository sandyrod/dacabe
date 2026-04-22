<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTpagoIdFieldsToPagosTable extends Migration
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
            $table->integer('tpago_id')->nullable();
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
            $table->dropColumn('tpago_id');
        });
    }
}
