<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFtpInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::dropIfExists('ftp_invoices');
        Schema::create('ftp_invoices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned()->nullable();
            $table->string('number', 20);
            $table->integer('quantity');
            $table->double('subtotal_drugs');
            $table->double('subtotal_misc');
            $table->double('tax');
            $table->double('total_and_tax');
            $table->double('pp_discount');
            $table->double('pp_misc_discount');
            $table->double('comercial_discount');
            $table->double('com_discount');
            $table->double('esp_discount');
            $table->double('vol_discount');
            $table->double('invoice_discount');
            $table->string('invoice_date', 50);
            $table->double('subtotal_drug_pp');
            $table->double('subtotal_misc_pp');
            $table->integer('lines');
            $table->string('currency', 20);
            $table->string('rate');
            $table->string('total_currency');
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
        Schema::dropIfExists('ftp_invoices');
    }
}
