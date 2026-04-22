<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFtpInvoicesDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::dropIfExists('ftp_invoice_details');
        Schema::create('ftp_invoice_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ftp_invoice_id')->unsigned()->nullable();
            $table->string('number', 20);
            $table->string('product_code', 10);
            $table->string('product_type', 30);
            $table->string('product_name', 50);
            $table->integer('quantity');
            $table->double('net_amount');
            $table->double('price');
            $table->double('discount_amount');
            $table->double('accumulated');
            $table->double('tax');
            $table->double('discount');
            $table->double('packing_discount');
            $table->double('ufi_discount');
            $table->double('package_discount');
            $table->double('comercial_discount');
            $table->string('package', 20);
            $table->string('barcode', 20);
            $table->string('order_number', 20);
            $table->string('sale_number', 20);
            $table->string('barcode_package', 20);
            $table->string('regulated', 10);
            $table->double('pp_discount');
            $table->string('lot', 30);
            $table->string('expired_at', 20);
            $table->string('currency', 20);
            $table->string('rate');
            $table->string('total_currency');
            $table->timestamps();

            $table->foreign('ftp_invoice_id')->references('id')->on('ftp_invoices')
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
        Schema::dropIfExists('ftp_invoice_details');
    }
}
