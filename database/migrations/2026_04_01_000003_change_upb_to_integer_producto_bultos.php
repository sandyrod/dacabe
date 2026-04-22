<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ChangeUpbToIntegerProductoBultos extends Migration
{
    protected $connection = 'company';

    public function up()
    {
        DB::connection($this->connection)->statement(
            "ALTER TABLE producto_bultos
             MODIFY unidades_por_bulto INT UNSIGNED NOT NULL DEFAULT 1
             COMMENT 'Unidades enteras que trae un bulto/caja'"
        );
    }

    public function down()
    {
        DB::connection($this->connection)->statement(
            "ALTER TABLE producto_bultos
             MODIFY unidades_por_bulto DECIMAL(10,3) NOT NULL DEFAULT 1.000
             COMMENT 'Unidades que trae un bulto/caja'"
        );
    }
}
