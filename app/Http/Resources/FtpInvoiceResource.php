<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FtpInvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $request->ftp_invoice_details = $this->ftp_invoice_details;
        
        return parent::toArray($request);
    }
}
