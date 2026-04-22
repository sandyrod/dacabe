<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLandingSectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::dropIfExists('landing_sections');
        Schema::create('landing_sections', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('landing_id')->unsigned();
            $table->string('shortname', 30); 
            $table->string('name', 30); 
            $table->string('description', 250)->nullable(); 
            $table->string('title', 100)->nullable(); 
            $table->string('content', 250)->nullable(); 
            $table->string('image', 250)->nullable(); 
            $table->string('bg-image', 250)->nullable(); 
            $table->string('subtitle', 200)->nullable(); 
            $table->string('primary_color', 100)->nullable(); 
            $table->string('secondary_color', 100)->nullable();             
            $table->timestamps();

            $table->foreign('landing_id')->references('id')->on('landings')->onUpdate('cascade')->onDelete('cascade');
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        Schema::dropIfExists('landing_sections');
    }
}
