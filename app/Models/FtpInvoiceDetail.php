<?php

namespace App\Models;

use App\Models\Company;
use Auth;

use Illuminate\Database\Eloquent\Model;

class FtpInvoiceDetail extends Model
{
    protected $table = 'ftp_invoice_details';
    
     protected $fillable = ['id','ftp_invoice_id','number','product_code','product_type','product_name', 'quantity', 'net_amount', 'price', 'discount_amount', 'accumulated', 'tax', 'discount','packing_discount', 'ufi_discount', 'package_discount', 'comercial_discount', 'package', 'barcode','order_number','sale_number','barcode_package','regulated','pp_discount','lot', 'expired_at','currency','rate', 'total_currency', 'currency_cost'
    ];

    public function ftp_invoice()
    {
        return $this->belongsTo(FtpInvoice::class);
    }

    public function getData(){
        return $this->get();
    }

}
