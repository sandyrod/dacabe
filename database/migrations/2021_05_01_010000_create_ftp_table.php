<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFtpTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::dropIfExists('ftp');
        Schema::create('ftp', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned()->nullable();
            $table->integer('drugstore_id')->unsigned()->nullable();
            $table->string('server', 50);
            $table->string('username');
            $table->string('password');
            $table->string('remote_dir');
            $table->string('local_dir');
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')
                ->onUpdate('cascade')->onDelete('cascade');
                            
            $table->foreign('drugstore_id')->references('id')->on('drugstores')
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
        Schema::dropIfExists('ftp');
    }
}
