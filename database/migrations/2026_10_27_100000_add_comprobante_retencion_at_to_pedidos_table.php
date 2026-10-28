<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddComprobanteRetencionAtToPedidosTable extends Migration
{
    protected $connection = 'company';

    public function up()
    {
        if (!Schema::connection($this->connection)->hasColumn('pedidos', 'comprobante_retencion_at')) {
            Schema::connection($this->connection)->table('pedidos', function (Blueprint $table) {
                $table->timestamp('comprobante_retencion_at')->nullable()->after('comprobante_retencion');
            });
        }
    }

    public function down()
    {
        if (Schema::connection($this->connection)->hasColumn('pedidos', 'comprobante_retencion_at')) {
            Schema::connection($this->connection)->table('pedidos', function (Blueprint $table) {
                $table->dropColumn('comprobante_retencion_at');
            });
        }
    }
}