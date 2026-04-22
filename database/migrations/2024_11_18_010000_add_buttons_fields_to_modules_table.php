<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddButtonsFieldsToModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::table('modules', function(Blueprint $table) {           
            $table->string('icon', 20)->nullable();
            $table->string('url', 100)->nullable();
            $table->string('button_text', 20)->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        Schema::table('modules', function($table) { 
            $table->dropColumn('icon');
            $table->dropColumn('button_text');
            $table->dropColumn('url');
        });
    }
}
