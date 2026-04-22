<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMontoDescuentoFieldsToPedidosTable extends Migration
{
     protected $connection = 'company';
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::connection($this->connection)->table('pedidos', function(Blueprint $table) {           
            $table->float('monto_descuento')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        Schema::connection($this->connection)->table('pedidos', function($table) { 
            $table->dropColumn('monto_descuento');
        });
    }
}
