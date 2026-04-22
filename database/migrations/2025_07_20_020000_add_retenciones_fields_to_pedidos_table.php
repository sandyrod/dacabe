<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRetencionesFieldsToPedidosTable extends Migration
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
            $table->string('factura', 20)->default("NO")->nullable();
            $table->float('retencion')->nullable();
            $table->float('porc_retencion')->nullable();
            
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
            $table->dropColumn('factura');
            $table->dropColumn('retencion');
            $table->dropColumn('porc_retencion');
        });
    }
}
