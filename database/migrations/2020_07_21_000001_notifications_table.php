<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class NotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::dropIfExists('notifications');
        Schema::create('notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->string('description');
            $table->string('icon')->nullable()->default('fas fa-info-circle');
            $table->string('url')->nullable();
            $table->integer('status')->unsigned()->nullable()->default(1);            
            $table->integer('level')->unsigned()->default(0);            
            $table->integer('company_id')->unsigned();
            $table->bigInteger('user_id')->unsigned()->index();
            $table->timestamps();
        
            $table->foreign('company_id')->references('id')->on('companies')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('user_id')->references('id')->on('users')
                ->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        Schema::dropIfExists('notifications');
    }
}
