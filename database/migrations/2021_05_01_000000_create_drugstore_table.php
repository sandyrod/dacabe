<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDrugstoreTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::dropIfExists('drugstores');
        Schema::create('drugstores', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 30)->nullable();
            $table->string('name');
            $table->string('url');
            $table->string('address')->nullable();
            $table->string('logo')->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        Schema::dropIfExists('drugstores');
    }
}
