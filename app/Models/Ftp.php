<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

use App\Models\Ftp_error;
use App\Models\FtpInvoice;
use App\Models\Download;
use App\Models\Drugstore;
use Auth;

class Ftp extends Model
{
    protected $table = "ftp";
    protected $fillable = ['company_id', 'drugstore_id', 'server', 'username', 'password', 'remote_dir', 'local_dir'];

    private $server;
    private $user;
    private $password;
    private $localdir;
    private $remotedir;
    private $connect;
    private $company_id;
    private $drugstore_id;

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (! $model->company_id) {
                $model->company_id = Auth::user()->company_id;
            }
            if (! $model->drugstore_id) {
                $model->drugstore_id = (new Drugstore)->first()->id;
            }
            if (! $model->server) {
                $model->server = '200.35.81.85';
            }
        });
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company', 'company_id');
    }

    public function drugstore()
    {
        return $this->belongsTo('App\Models\Drugstore', 'drugstore_id');
    }

    public function getCompanyCredentials($drugstore_id, $company_id)
    {
        return $this->where('company_id', $company_id)->where('drugstore_id', $drugstore_id)->first();
    }

    public function checkDrugstoreFiles()
    {
        $downloads = $this->leftJoin('companies', 'companies.id', 'ftp.company_id')
            ->select('ftp.company_id', 'ftp.drugstore_id', 'ftp.server', 'ftp.username', 'ftp.password', 'ftp.remote_dir', 'ftp.local_dir')
            ->where('companies.company_status_id', 1)
            ->get();
        
        foreach ($downloads as $ftp) {
        	$this->setFtpCredentials($ftp);
            $this->startDownloadDrugstoreFiles();
        }
    }

    private function saveError($message)
    {
    	if ($this->connect)
    		ftp_close($this->connect);

    	Ftp_error::create([
    		'message'=> $message,
    		'company_id'=> $this->company_id,
    		'drugstore_id'=> $this->drugstore_id,
    	]);

    	return null;
    }

    private function startDownloadDrugstoreFiles()
    {        
        $this->connect = ftp_connect($this->server);
        if (! $this->connect)
            return;

        ftp_pasv($this->connect, true);
        //log::info('credenciales');
        //log::info($this->user);
        //log::info($this->password);
        if (! @ftp_login($this->connect, $this->user, $this->password))
        	return $this->saveError('No Conecto');
        
        ftp_set_option($this->connect, FTP_USEPASVADDRESS, false); // set ftp option
        ftp_pasv($this->connect, true); //make connection to passive mode
        //log::info('calling...');
        return $this->processDownloadedFiles();
    }

    private function getLocalDir()
    {
        if ($this->drugstore_id==2) 
            return 'drolanca/';

        return 'dronena/';
    }

    private function isTxtFile($name)
    {
        //return 1;
        return (strtolower(substr($name, strlen($name)-3)) == 'txt');
    }

    private function processDownloadedFiles()
    {
    	$dirlocal = public_path('files/'.$this->getLocalDir().$this->localdir);
        $locales = scandir($dirlocal);
        
        //log::info('remotedir');
        //log::info($this->remotedir);
        //log::info('2');
        $contents = ftp_nlist($this->connect, $this->remotedir);
        //log::info('$contents');
        //log::info($contents);
        $s = sizeof($contents);
        //log::info('sizeof...' . $s);
        if ( $s <= 0)
        	return $this->saveError('Problemas de conexion');

        //ftp_get($this->connect, $dirlocal.'/_Inventario.txt', substr($this->remotedir, 0, strlen($this->remotedir)-8).'Inventario.txt', FTP_ASCII);

        for ($i=0; $i < $s ; $i++) {
            if (!in_array(basename($contents[$i]), $locales) && $this->isTxtFile(basename($contents[$i]))){
                //log::info('i: ' . $i);
                ftp_get($this->connect, $dirlocal.'/'.basename($contents[$i]), $this->remotedir.basename($contents[$i]), FTP_ASCII);                
            }
        }
        ftp_close($this->connect);

        return $this->updateDownloadsTable($locales);
        
    }

    private function updateDownloadsTable($locales)
    {
    	$descarga = new Download();
        foreach ($locales as $local) {
            if ($local == '_Inventario.txt' || $local == '.' || $local == '..')
                continue;

        	$exists =  $descarga->where('company_id', $this->company_id)
                ->where('drugstore_id', $this->drugstore_id)
                ->where('invoice_file', $local)
                ->first();

            if (!$exists)
                $descarga->insert([
                    'company_id' => $this->company_id,
                    'drugstore_id' => $this->drugstore_id,
                    'invoice_file' => $local,
                    'status' => 0,
                    'created_at' => now(),
                ]);
        }        
    }

    private function setFtpCredentials($ftp)
    {
        $this->server = $ftp['server'];
        $this->user = $ftp['username'];
        $this->password = $ftp['password'];
        $this->localdir = $ftp['local_dir'];
        $this->remotedir = $ftp['remote_dir'];
        $this->company_id = $ftp['company_id'];
        $this->drugstore_id = $ftp['drugstore_id'];
        
        return true;
    }

    public function processFtpInvoices()
    {
        $files = Download::where('status', 0)->get();
        $company_id = '';
        foreach ($files as $file) {  
            if (substr($file->invoice_file, strlen($file->invoice_file)-4) == 'fact')
                continue;

            if ($company_id != $file->company_id)
                $company_id = $this->getCompanyInformation($file->company_id);

            if (! $company_id)
                continue;

            $ftp_invoice_id = $this->processHeaderLine($file);
            if (! $ftp_invoice_id)
                continue;
            
            $this->processDetailsFile($file, $ftp_invoice_id);
            $file->ftp_invoice_id = $ftp_invoice_id;
            $file->status = 1;
            $file->save();
            
        }
    }

    private function getCompanyInformation($company_id)
    {
        $company = $this->leftJoin('companies', 'companies.id', 'ftp.company_id')
            ->select('ftp.company_id', 'ftp.drugstore_id', 'ftp.server', 'ftp.username', 'ftp.password', 'ftp.remote_dir', 'ftp.local_dir')
            ->where('companies.id', $company_id)
            ->first();
        
        if (! $company)
            return;

        $this->setFtpCredentials($company);
        
        return $this->company_id;
    }

    private function processHeaderLine($file)
    {
        return $this->evaluateLine($file, '02');
    }

    private function processDetailsFile($file, $ftp_invoice_id)
    {
        return $this->evaluateLine($file, '01', $ftp_invoice_id);
    }

    private function getNumberFormat($line, $from, $at)
    {
        $value = trim(substr($line, $from, $at));
        if (! $value)
            return 0;
        
        $result = floatval($value)/100;
        if ($result > 0)
            return $result;

        return 0;
    }

    private function getInsertHeader($line)
    {
        $invoice = new FtpInvoice();
        //if (! $this->isValidHeader($line))
        //    return 0;

        $fec = trim(substr($line, 116, 26));
        $stringTime = substr($fec, 6, 4).'-'.substr($fec, 3, 2).'-'.substr($fec, 0, 2);
        $invoice_date = date('Y-m-d', strtotime($stringTime));
        $deadline = \Carbon\Carbon::now()->subMonths(2);

        $status = ($invoice_date < $deadline) ? 'OLD' : 'PROCESSING';

        $quantity = 1;
        /*
        if (trim(substr($line, 18, 5)) && trim(substr($line, 18, 5)) != '' && trim(substr($line, 18, 5)) != null && trim(substr($line, 18, 5)) > 0) {
            $quantity = trim(substr($line, 18, 5));
        };
        */

        $id = $invoice->insertGetId([
                    'company_id' => $this->company_id,
                    'number' => trim(substr($line, 3, 10)) ?? '',
                    'quantity' => $quantity,
                    'subtotal_drugs' => $this->getNumberFormat($line, 23, 14) ?? 0,
                    'subtotal_misc' => $this->getNumberFormat($line, 37, 13) ?? 0,
                    'tax' => $this->getNumberFormat($line, 52, 12) ?? 0,
                    'total_and_tax' => $this->getNumberFormat($line, 65, 13) ?? 0,
                    'pp_discount' => $this->getNumberFormat($line, 79, 5) ?? 0,
                    'pp_misc_discount' => $this->getNumberFormat($line, 84, 5) ?? 0,
                    'comercial_discount' => $this->getNumberFormat($line, 89, 5) ?? 0,
                    'com_discount' => $this->getNumberFormat($line, 94, 5) ?? 0,
                    'esp_discount' => $this->getNumberFormat($line, 100, 5) ?? 0,
                    'vol_discount' => $this->getNumberFormat($line, 105, 5) ?? 0,
                    'invoice_discount' => $this->getNumberFormat($line, 110, 5) ?? 0,
                    'invoice_date' => trim(substr($line, 116, 26)) ?? null,
                    'subtotal_drug_pp' => $this->getNumberFormat($line, 151, 13) ?? 0,
                    'subtotal_misc_pp' => $this->getNumberFormat($line, 164, 13) ?? 0,
                    'lines' => (! trim(substr($line, 189, 12)) || trim(substr($line, 189, 12))!='') ?? 0,
                    'currency' => trim(substr($line, 207, 12)) ?? '',
                    'rate' => $this->getNumberFormat($line, 219, 14) ?? 0,
                    'total_currency' => $this->getNumberFormat($line, 234, 20) ?? 0,
                    'status' => $status,
                    'created_at' => now(),
                ]);
        
        return $id;
    }

    private function getInsertDetail($line, $ftp_invoice_id)
    {
        //if (! $this->isValidDetail($line))
        //    return 0;

        $invoice = new FtpInvoiceDetail();
        $id = $invoice->insertGetId([
                    'ftp_invoice_id' => $ftp_invoice_id,
                    'number' => trim(substr($line, 3, 10)) ?? '',
                    'product_code' => trim(substr($line, 13, 7)) ?? '',
                    'product_type' => trim(substr($line, 20, 3)) ?? '',
                    'product_name' => trim(substr($line, 23, 40)) ?? '',
                    'quantity' => trim(substr($line, 63, 5)) > 0 ? trim(substr($line, 63, 5)) : 0,
                    'net_amount' => $this->getNumberFormat($line, 68, 10) ?? 0,
                    'price' => $this->getNumberFormat($line, 78, 10) ?? 0,
                    'discount_amount' => $this->getNumberFormat($line, 88, 10) ?? 0,
                    'accumulated' => $this->getNumberFormat($line, 98, 10) ?? 0,
                    'tax' => $this->getNumberFormat($line, 108, 10) ?? 0,
                    'discount' => $this->getNumberFormat($line, 118, 5) ?? 0,
                    'packing_discount' => $this->getNumberFormat($line, 123, 5) ?? 0,
                    'ufi_discount' => $this->getNumberFormat($line, 128, 5) ?? 0,
                    'package_discount' => $this->getNumberFormat($line, 133, 5) ?? 0,
                    'comercial_discount' => $this->getNumberFormat($line, 138, 5) ?? 0,
                    'package' => trim(substr($line, 143, 10)) ?? '',
                    'barcode' => trim(substr($line, 153, 15)) ?? '',
                    'order_number' => trim(substr($line, 168, 15)) ?? '',
                    'sale_number' => trim(substr($line, 183, 15)) ?? '',
                    'barcode_package' => trim(substr($line, 198, 15)) ?? '',
                    'regulated' => trim(substr($line, 213, 3)) ?? '',
                    'pp_discount' => $this->getNumberFormat($line, 216, 3) ?? 0,
                    'lot' => trim(substr($line, 219, 30)) ?? '',
                    'expired_at' => trim(substr($line, 249, 11)) ?? '',
                    'currency' => trim(substr($line, 260, 10)) ?? '',
                    'rate' => $this->getNumberFormat($line, 299, 20) ?? 0,
                    'total_currency' => $this->getNumberFormat($line, 284, 14) ?? 0,
                    'currency_cost' => $this->getNumberFormat($line, 271, 14) ?? 0,
                    'created_at' => now(),
                ]);

        $model = new FtpInvoice();
        $header = $model->find($ftp_invoice_id);
        if ($header){
            $header->status = 'PENDING';
            $header->save();
        }

        
        return $id;
    }

     private function evaluateLine($file, $code_process, $ftp_invoice_id=null)
    {
        $dirlocal = public_path('files/'.$this->getLocalDir().$this->localdir);

        $reader = @fopen($dirlocal.'/'.$file->invoice_file, "r");
        if ($reader) {
            while (!feof($reader)) {
                $line = fgets($reader, 4096);
                $code = substr($line, 0, 3);
                if (trim($code)=='02' && $code_process=='02'){
                    fclose ($reader);

                    return $this->getInsertHeader($line);
                }
                if (trim($code)=='01' && $code_process=='01')
                    $this->getInsertDetail($line, $ftp_invoice_id);
            }
            fclose ($reader);
        }
    }

    public function getData()
    {
        return $this->with('drugstore')->get();
    }

    public function getReportConfig()
    {
        return [
            'title' => 'Listado de Farmacias', 
            'company' => Auth::user()->company
        ];
    }

}
