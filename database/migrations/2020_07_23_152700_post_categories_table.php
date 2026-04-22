<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class PostCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::dropIfExists('post_categories');
        Schema::create('post_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('description');
            $table->string('icon')->nullable()->default('fas fa-info-circle');
            $table->integer('company_id')->unsigned();
            $table->timestamps();
        
            $table->foreign('company_id')->references('id')->on('companies')
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
        Schema::dropIfExists('post_categories');
    }
}
