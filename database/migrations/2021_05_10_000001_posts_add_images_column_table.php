<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class PostsAddImagesColumnTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::table('posts', function(Blueprint $table) {
            $table->string('path_images', 100)->nullable();            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        Schema::table('posts', function($table) {
            $table->dropColumn('path_images');
        });
    }
}
