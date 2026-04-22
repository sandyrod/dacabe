<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class UsersAddFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::table('users', function(Blueprint $table) {
            $table->string('last_name')->after('name')->nullable();
            $table->string('document')->after('last_name')->nullable();
            $table->string('phone')->after('document')->nullable();
            $table->string('mobile')->after('phone')->nullable();
            $table->date('birthday')->after('mobile')->nullable();
            $table->string('photo')->after('birthday')->default('nofoto.jpg');
            $table->integer('status')->default(1);
            $table->integer('department_id')->unsigned()->after('status')->nullable();
            $table->integer('company_id')->unsigned()->after('department_id')->nullable();
           
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
        Schema::table('users', function($table) {
            $table->dropColumn('last_name');
            $table->dropColumn('document');
            $table->dropColumn('phone');
            $table->dropColumn('mobile');
            $table->dropColumn('birthday');
            $table->dropColumn('photo');
            $table->dropColumn('status');
            $table->dropColumn('department_id');
            $table->dropColumn('company_id');
        });
    }
}
