<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDiasDespachoFieldsToDescuentosTable extends Migration
{
     protected $connection = 'company';
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::connection($this->connection)->table('descuentos', function(Blueprint $table) {           
            $table->integer('dias_despacho')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        Schema::connection($this->connection)->table('descuentos', function($table) { 
            $table->dropColumn('dias_despacho');
        });
    }
}
