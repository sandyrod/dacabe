<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMontoBaseToComisionVendedoresTable extends Migration
{
    protected $connection = 'company';

    public function up()
    {
        Schema::connection($this->connection)->table('comision_vendedores', function (Blueprint $table) {
            $table->decimal('monto_base_comision', 12, 2)->nullable()->after('cantidad')
                  ->comment('Base en USD sobre la que se calculó la comisión (precio_dolar × cantidad menos descuento proporcional)');
        });
    }

    public function down()
    {
        Schema::connection($this->connection)->table('comision_vendedores', function (Blueprint $table) {
            $table->dropColumn('monto_base_comision');
        });
    }
}
