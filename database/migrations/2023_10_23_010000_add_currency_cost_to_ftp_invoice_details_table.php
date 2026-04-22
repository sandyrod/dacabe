<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddCurrencyCostToFtpInvoiceDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::table('ftp_invoice_details', function(Blueprint $table) {           
            $table->float('currency_cost')->nullable();           
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        Schema::table('ftp_invoice_details', function($table) {            
            $table->dropColumn('currency_cost');
        });
    }
}
