<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::dropIfExists('companies');
        Schema::create('companies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('url')->nullable();
            $table->string('logo')->nullable();
            $table->string('location')->nullable();
            $table->string('token');           
            $table->integer('company_status_id')->unsigned()->nullable(); 
            $table->timestamps();

            $table->foreign('company_status_id')->references('id')->on('company_status')
                    ->onUpdate('cascade')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        Schema::dropIfExists('companies');
    }
}
