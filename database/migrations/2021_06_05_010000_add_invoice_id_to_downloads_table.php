<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddInvoiceIdToDownloadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::table('downloads', function(Blueprint $table) {           
            $table->integer('ftp_invoice_id')->unsigned()->nullable();           
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        Schema::table('downloads', function($table) {            
            $table->dropColumn('ftp_invoice_id');
        });
    }
}
