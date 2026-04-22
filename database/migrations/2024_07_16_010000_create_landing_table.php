<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLandingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::dropIfExists('landings');
        Schema::create('landings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned();
            $table->integer('theme_id')->unsigned();
            $table->string('name', 50); 
            $table->string('slug', 100); 
            $table->string('status', 1); 
            $table->string('company_name', 100); 
            $table->string('main_logo', 100)->nullable(); 
            $table->string('footer_logo', 100)->nullable(); 
            $table->string('email', 100)->nullable(); 
            $table->string('phone', 50)->nullable(); 
            $table->string('whatsapp', 50)->nullable(); 
            $table->string('float_whatsapp', 1)->nullable(); 
            $table->string('float_whatsapp_text', 200)->nullable(); 
            $table->string('schedule', 60)->nullable(); 
            $table->string('instagram', 60)->nullable(); 
            $table->string('facebook', 60)->nullable(); 
            $table->string('twitter', 60)->nullable(); 
            $table->string('title', 200)->nullable(); 
            $table->string('slogan', 200)->nullable(); 
            $table->string('video', 200)->nullable(); 
            $table->string('address', 250)->nullable(); 
            $table->string('map_location', 250)->nullable(); 
            $table->string('primary_color', 50)->nullable(); 
            $table->string('secondary_color', 50)->nullable(); 
            $table->timestamps();
        
            $table->foreign('company_id')->references('id')->on('companies')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('theme_id')->references('id')->on('themes')->onUpdate('cascade')->onDelete('cascade');
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        Schema::dropIfExists('landings');
    }
}
