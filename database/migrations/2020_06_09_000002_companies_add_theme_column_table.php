<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CompaniesAddThemeColumnTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::table('companies', function(Blueprint $table) {
            $table->string('theme')->nullable();            
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
            $table->dropColumn('theme');
        });
    }
}
