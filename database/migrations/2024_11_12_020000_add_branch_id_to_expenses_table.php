<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddBranchIdToExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::table('expenses', function(Blueprint $table) {           
            $table->integer('branch_id')->unsigned()->nullable();

            $table->foreign('branch_id')->references('id')->on('branches');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        Schema::table('expenses', function($table) { 
            $table->dropColumn('branch_id');
        });
    }
}
