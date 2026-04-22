<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

use App\Http\Requests\FtpInvoiceRequest;
use App\User;
use App\Models\FtpInvoice;
use App\Models\Company;
use App\Models\FtpInvoiceDetail;
use App\Traits\FtpInvoiceTrait;

use App\Http\Resources\FtpInvoiceResource;
use App\Http\Resources\FtpInvoiceCollection;
use Illuminate\Support\Facades\Response;

use DB;

class FtpInvoicesController extends Controller
{
    use FtpInvoiceTrait;

    private $permission;
    private $paginate;

    public function __construct()
    {
        //$this->middleware('auth');
        $this->permission = 'ftp-invoice';
        $this->paginate = 50;
    }

    public function index(Request $request)
    {
        if ( ! hasPermission($this->permission) )
            return response()->json([
                'message' => 'not authorized'
            ], 403);

        $status = $request->status ?? 'PENDING';
        $model = new FtpInvoice();

        return new FtpInvoiceCollection($model->getData($request, $status, $this->paginate));
    }

    public function store(Request $request)
    {
    }

   
    public function show(FtpInvoice $ftp_invoice)
    {
        if ( ! hasPermission($this->permission) )
            return response()->json([
                'message' => 'not authorized'
            ], 403);

        return new FtpInvoiceResource($ftp_invoice);
    }

    public function update(Request $request, FtpInvoice $ftp_invoice)
    {
        if ( ! hasPermission('edit-'.$this->permission) )
            return response()->json([
                'message' => 'not authorized'
            ], 403);

        //if (! $request->status)
        //    return response()->json([
        //        'message' => 'field not found'
        //    ], 403);

        $model = new FtpInvoice();
        if ($request->ids){
            foreach ($request->ids as $id) {
                $invoice = $model->find($id);
                if ($invoice){
                    $invoice->status = 'DOWNLOADED'; //$request->status;
                    $invoice->save();
                    //return new FtpInvoiceResource($ftp_invoice);
                }            
            }
        }

        return response()->json([
            'message' => 'ok'
        ], 200);

    }

    public function getIndicators(Request $request)
    {
        if ( ! hasPermission($this->permission) )
            return response()->json([
                'message' => 'not authorized'
            ], 403);

        $status = $request->status ?? 'PENDING';
        $model = new FtpInvoice();

        return response()->json([
            'invoices_number' => $model->getDataIndicators($request, $status)
        ], 200);
    }

    public function getFaqs(Request $request)
    {
        return response()->json((new FtpInvoice)->orderBy('id', 'DESC')->first(), 200);
    }

}
