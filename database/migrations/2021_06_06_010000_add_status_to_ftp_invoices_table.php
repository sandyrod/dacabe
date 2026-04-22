<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddStatusToFtpInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::table('ftp_invoices', function(Blueprint $table) {           
            $table->string('status', 20)->nullable();           
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        Schema::table('ftp_invoices', function($table) {            
            $table->dropColumn('status');
        });
    }
}
