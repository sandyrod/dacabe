<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPercentFieldsToInvenInformacionTable extends Migration
{
     protected $connection = 'company';
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::connection($this->connection)->table('inven_informacion', function(Blueprint $table) {           
            $table->float('descuento', 5, 2)->nullable();
            $table->float('comision', 5, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        Schema::connection($this->connection)->table('inven_informacion', function($table) { 
            $table->dropColumn('descuento');
            $table->dropColumn('comision');
        });
    }
}
