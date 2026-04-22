<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::dropIfExists('expenses');
        Schema::create('expenses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned();
            $table->integer('expense_group_id')->unsigned();
            $table->string('reference', 50)->nullable();
            $table->string('name', 100);
            $table->string('description')->nullable();
            $table->date('date_at')->nullable();
            $table->float('amount', 13, 2)->nullable();
            $table->float('dollar_amount', 13, 2)->nullable();
            $table->float('rate', 13, 2)->nullable();

            $table->timestamps();
            
            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('expense_group_id')->references('id')->on('companies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        Schema::dropIfExists('expenses');
    }
}
