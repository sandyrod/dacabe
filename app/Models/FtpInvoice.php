<?php

namespace App\Models;

use App\Models\Company;
use Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class FtpInvoice extends Model
{
    protected $table = 'ftp_invoices';
    
     protected $fillable = ['id','company_id', 'number', 'quantity', 'subtotal_drugs', 'subtotal_misc','tax','total_and_tax','pp_discount','pp_misc_discount','comercial_discount','com_discount','esp_discount','vol_discount','invoice_discount','invoice_date','subtotal_drug_pp','subtotal_misc_pp','lines','currency', 'rate', 'total_currency', 'status'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($ftp_invoice) {            
            $ftp_invoice->status = 'PENDING';
        });
    }

    public function ftp_invoice_details()
    {
        return $this->hasMany(FtpInvoiceDetail::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function getData($request, $status, $paginate = 1000) {
        $company_id = Auth::user()->company_id;
        if ($request->invoice) {
            return $this->with('ftp_invoice_details')
                ->where('ftp_invoices.company_id', $company_id)
                ->where('ftp_invoices.number', $request->invoice)
                ->get();
        }
        if ($request->month) {
            $now = \Carbon\Carbon::now();
            $month = ($request->month < 10) ? '0'.$request->month : $request->month;
            return $this->with('ftp_invoice_details')
                ->where('ftp_invoices.company_id', $company_id)
                ->where('ftp_invoices.status', $status)
                ->where(\DB::raw('substr(invoice_date, 4, 2)'), '=' , $month)
                ->where(\DB::raw('substr(invoice_date, 7, 4)'), '=' , $now->year)
                ->orderBy('id', 'DESC')
                ->get();
        }

        return $this->with('ftp_invoice_details')
                ->where('ftp_invoices.company_id', $company_id)
                ->where('ftp_invoices.status', $status)
                ->orderBy('id', 'DESC')
                ->get();
    }

    public function getDataIndicators($request, $status) {
        $now = \Carbon\Carbon::now();        
        $month = ($now->month < 10) ? '0'.$now->month : $now->month;
        return $this
            ->where('ftp_invoices.company_id', Auth::user()->company_id)
            ->where('ftp_invoices.status', $status)
            ->where(\DB::raw('substr(invoice_date, 4, 2)'), '=' , $month)
            ->where(\DB::raw('substr(invoice_date, 7, 4)'), '=' , $now->year)
            ->orderBy('id', 'DESC')
            ->count();
        
    }

}
