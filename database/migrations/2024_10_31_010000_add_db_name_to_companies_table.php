<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddDbNameToCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::table('companies', function(Blueprint $table) {           
            $table->string('db_name',150)->nullable();           
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        Schema::table('companies', function($table) {            
            $table->dropColumn('db_name');
        });
    }
}
