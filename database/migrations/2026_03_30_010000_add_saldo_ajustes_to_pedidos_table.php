<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddSaldoAjustesToPedidosTable extends Migration
{
    public function up()
    {
        Schema::connection('company')->table('pedidos', function (Blueprint $table) {
            $table->decimal('saldo_ajustes', 15, 2)->default(0)->after('total_ajustes');
        });

        // Initialize saldo_ajustes from total_ajustes for existing records
        DB::connection('company')->statement('UPDATE pedidos SET saldo_ajustes = COALESCE(total_ajustes, 0)');
    }

    public function down()
    {
        Schema::connection('company')->table('pedidos', function (Blueprint $table) {
            $table->dropColumn('saldo_ajustes');
        });
    }
}
