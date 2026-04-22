<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateThemesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::dropIfExists('themes');
        Schema::create('themes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50); 
            $table->string('slug', 100); 
            $table->string('template', 100); 
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
        Schema::dropIfExists('themes');
    }
}
