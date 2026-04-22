<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixCollationProductoBultos extends Migration
{
    protected $connection = 'company';

    public function up()
    {
        // Alinear la collation con las tablas legadas (inven, artdepos, etc.)
        // que usan utf8mb4_0900_ai_ci para que los JOINs funcionen sin error 1267.
        DB::connection($this->connection)->statement(
            "ALTER TABLE producto_bultos
             CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci"
        );
    }

    public function down()
    {
        DB::connection($this->connection)->statement(
            "ALTER TABLE producto_bultos
             CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
        );
    }
}
