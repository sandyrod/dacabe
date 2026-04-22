<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAjustesMontoToPagosPedidosTable extends Migration
{
    public function up()
    {
        Schema::connection('company')->table('pagos_pedidos', function (Blueprint $table) {
            $table->decimal('ajustes_monto', 15, 2)->default(0)->after('descuento');
        });
    }

    public function down()
    {
        Schema::connection('company')->table('pagos_pedidos', function (Blueprint $table) {
            $table->dropColumn('ajustes_monto');
        });
    }
}
