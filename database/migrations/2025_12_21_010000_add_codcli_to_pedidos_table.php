<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'company';
    
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection($this->connection)->table('pedidos', function (Blueprint $table) {
            $table->string('codcli',10)->nullable()->after('rif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->connection)->table('pedidos', function (Blueprint $table) {
            $table->dropColumn('codcli');
        });
    }
};
