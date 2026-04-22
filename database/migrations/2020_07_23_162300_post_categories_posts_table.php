<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class PostCategoriesPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::dropIfExists('post_category_post');
        Schema::create('post_category_post', function (Blueprint $table) {
            $table->unsignedInteger('post_id');
            $table->unsignedInteger('post_category_id');
            
            $table->foreign('post_id')->references('id')->on('posts')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('post_category_id')->references('id')->on('post_categories')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['post_id', 'post_category_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        Schema::dropIfExists('post_category_post');
    }
}
