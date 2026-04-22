<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddComprobanteRetencionToPedidosTable extends Migration
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
            $table->string('comprobante_retencion')->nullable()->after('porc_retencion');
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
            $table->dropColumn('comprobante_retencion');
        });
    }
}
