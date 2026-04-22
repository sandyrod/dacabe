<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class PostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::dropIfExists('posts');
        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 70);
            $table->string('resume');
            $table->text('content');
            $table->string('slug', 50)->unique();
            $table->string('image')->nullable();
            $table->dateTime('start_at', 0);
            $table->dateTime('end_at', 0)->nullale();   
            $table->integer('views')->default(0);
            $table->integer('likes')->default(0);
            $table->integer('status')->default(1);
            $table->integer('company_id')->unsigned();
            $table->bigInteger('user_id')->unsigned();
            $table->timestamps();
        
            $table->foreign('company_id')->references('id')->on('companies')
                ->onUpdate('cascade')->onDelete('cascade');
        
            $table->foreign('user_id')->references('id')->on('users')
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
        Schema::dropIfExists('posts');
    }
}
