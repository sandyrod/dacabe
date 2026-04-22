<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class PermissionsAddUrlColumnTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::table('permissions', function(Blueprint $table) {
            $table->string('url')->nullable();            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        Schema::table('permissions', function($table) {
            $table->dropColumn('url');
        });
    }
}
