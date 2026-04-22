<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = 'company';
    /**
     * Run the migrations.
     */
    public function up()
    {
        $connection = config('database.connections.'.$this->connection);
        $tablePrefix = $connection['prefix'];
        $tableName = $tablePrefix.'comision_vendedores';
        
        // First, we need to modify the column to allow NULL temporarily
        DB::connection($this->connection)->statement("ALTER TABLE {$tableName} MODIFY COLUMN estatus_comision VARCHAR(20) NULL");
        
        // Then update the values if needed (optional)
        DB::connection($this->connection)->statement("UPDATE {$tableName} SET estatus_comision = 'pendiente' WHERE estatus_comision IS NULL");
        
        // Finally, modify the column to the new enum
        DB::connection($this->connection)->statement("ALTER TABLE {$tableName} MODIFY COLUMN estatus_comision ENUM('pendiente', 'pagada', 'rechazada') NOT NULL DEFAULT 'pendiente'");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        $connection = config('database.connections.'.$this->connection);
        $tablePrefix = $connection['prefix'];
        $tableName = $tablePrefix.'comision_vendedores';
        
        // Revert back to the original enum
        DB::connection($this->connection)->statement("ALTER TABLE {$tableName} MODIFY COLUMN estatus_comision VARCHAR(20) NULL");
        DB::connection($this->connection)->statement("UPDATE {$tableName} SET estatus_comision = 'pendiente' WHERE estatus_comision = 'rechazada'");
        DB::connection($this->connection)->statement("ALTER TABLE {$tableName} MODIFY COLUMN estatus_comision ENUM('pendiente', 'pagada') NOT NULL DEFAULT 'pendiente'");
    }
};
