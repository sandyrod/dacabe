<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Models\{Ftp, Download, Company, Drugstore};

use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;
use Auth;
use DB;

use App\Http\Requests\FtpRequest;
use App\Traits\FtpTrait;

use App\Http\Resources\FtpResource;

use Illuminate\Support\Facades\Response;

class FtpController extends Controller
{
    private $server;
    private $user;
    private $password;
    private $localdir;
    private $remotedir;
    private $connect;
    private $company_id;
    private $drugstore_id;

    use FtpTrait;

    private $permission;
    private $module;
    private $paginate;

    public function __construct()
    {
        $this->middleware('auth');
        $this->permission = 'ftp';
        $this->module = 'ftp';
        $this->paginate = 10;
    }

    public function index(Request $request)
    {
        if ( ! hasPermission($this->permission) )
            abort(403);
     
        $ftps = (new Ftp)->getData();
     
        if (requestAjaxOrJson($request, $ftps)) 
            return $this->getJsonOrDatatableResponse($request, $ftps);

        return view($this->module.'.index');
    }

    public function store(FtpRequest $request)
    {
        if ( ! hasPermission('create-'.$this->permission) )
            abort(403);

        $ftp = Ftp::create($request->all());

        if (requestAjaxOrJson($request))
            return new FtpResource($ftp);
       
        return redirect()->route($this->module.'.index')
                ->with('info', 'El registro '.$ftp->username.' ha sido creado satisfactoriamente');
    }

    public function create()
    {
        if ( ! hasPermission('create-'.$this->permission) ) {
            abort(403);
        }

        $companies = (new Company)->getData();
        $drugstores = (new Drugstore)->getData();
        $route = $this->module.'.index';
        return view($this->module.'.create', compact(['route', 'companies', 'drugstores']));
    }

    public function update(FtpRequest $request, Ftp $ftp)
    {
        if ( ! hasPermission('edit-'.$this->permission) )
            abort(403);
        
        $ftp->fill($request->all())->save();

        if (requestAjaxOrJson($request))
            return new FtpResource($ftp);
       
        return redirect()->route($this->module.'.index')
                ->with('info', 'El registro '.$ftp->username.' ha sido modificado satisfactoriamente');
    }

    public function edit(Ftp $ftp)
    {
        if ( ! hasPermission('edit-'.$this->permission) )
            abort(403);

        $companies = (new Company)->getData();
        $drugstores = (new Drugstore)->getData();
        $route = $this->module.'.index';
        return view($this->module.'.edit', compact(['ftp', 'route', 'companies', 'drugstores']));
    }

    public function destroy(Ftp $ftp)
    {
        if ( ! hasPermission('delete-'.$this->permission) )
            abort(403);

        $name = $ftp->username;
        $ftp->delete();

        return Response::json([
            'type' => 'success',
            'title' => 'Registro Eliminado!',
            'text' => 'El registro '.$name . ' ha sido eliminado satisfactoriamente.'
        ], 200);
    }

    public function show(Ftp $ftp)
    {
        if ( ! hasPermission($this->permission) )
            return notPermissionResponse($module);

        return  new FtpResource($ftp);
    }

    public function showPdfList()
    {
        $ftp = new Ftp();
        $ftps = $ftp->getData();

        $today = Carbon::now()->format('d/m/Y');

        $pdf = \PDF::loadView($this->module.'.partials.print', compact('ftps', 'today'));
 
        return $pdf->download('lista_comandos.pdf');
    }

     public function showPrintList()
    {
        $ftp = new Ftp();
        $ftps = $ftp->getData();
        $print = 1;
        
        $report_data = $ftp->getReportConfig();

        return view($this->module.'.partials.print', compact(['ftps', 'print', 'report_data']));
    }

    public function downloadDrugstoreFiles () 
    {
        $ftp_model = new Ftp();
        $downloads = $ftp_model->get();
        
        foreach ($downloads as $ftp) {
            $credentials = $ftp_model->getCompanyCredentials($ftp->drugstore_id, $ftp->company_id);
            if ($credentials){              
                $this->setFtpCredentials($credentials);
                $this->startDownloadDrugstoreFiles();
            }            
        }
    }
 
    private function startDownloadDrugstoreFiles()
    {
        $dirlocal = public_path('files/dronena/'.$this->localdir.'/');
            $locales = scandir($dirlocal);
            $this->connect = ftp_connect($this->server);
            ftp_pasv($this->connect, true);
            if (! @ftp_login($this->connect, $this->user, $this->password))
                return response()->json([
                    'status' => false,
                    'controller' => 'ftp',
                    'title'  => 'no Conecto!',
                    'company_id'  => $this->company_id,
                    'drugstore_id'  => $this->drugstore_id,
                    'type' => 'error'
                ],401);

            $contents = ftp_nlist($this->connect, $this->remotedir);
            $s = sizeof($contents);
            if ( $s <= 0)
                return response()->json([
                    'status' => false,
                    'controller' => 'ftp',
                    'title'  => 'Problemas de conexion!',
                    'company_id'  => $this->company_id,
                    'drugstore_id'  => $this->drugstore_id,
                    'type' => 'error'
                ],401);

            for ($i=0; $i < $s ; $i++) {
                if (!in_array(basename($contents[$i]), $locales)){
                    ftp_get($this->connect, $dirlocal.$this->localdir.basename($contents[$i]), $this->remotedir.basename($contents[$i]), FTP_ASCII);
                    $inserts[] = [
                        'company_id' => $this->company_id,
                        'drugstore_id' => $this->drugstore_id,
                        'invoice_file' => $this->localdir.basename($contents[$i]),
                        'status' => 0
                    ];
                }
            }

            try{
                $download = new Download();
                foreach ($inserts as $insert) {
                    if (! $download->where('company_id', $insert['company_id'])
                        ->where('drugstore_id', $insert['drugstore_id'])
                        ->where('invoice_file', $insert['invoice_file'])
                        ->first())
                        $download->insert($insert);
                }
           
            }catch (Exception $e) {
                echo 'Excepcion: ',  $e->getMessage(), "\n";
            }

            return response()->json([
                'status' => true,
                'controller' => 'ftp',
                'title'  => 'Operación exitosa!',
                'company_id'  => $this->company_id,
                'drugstore_id'  => $this->drugstore_id,
                'files'  => $s,
                'type' => 'success'
            ],200);
              

        ftp_close($this->connect);
        
        return response()->json([
                'status' => true,
                'controller' => 'ftp',
                'title'  => 'Salio!',
                'company_id'  => $this->company_id,
                'drugstore_id'  => $this->drugstore_id,
                'type' => 'success'
            ],200);

    }

    private function setFtpCredentials($credentials)
    {
            $this->server = $credentials->server;
            $this->user = $credentials->usuario;
            $this->password = $credentials->clave;
            $this->localdir = $credentials->dir_local;
            $this->remotedir = $credentials->dir_remoto;
            $this->company_id = $credentials->company_id;
            $this->drugstore_id = $credentials->drugstore_id;
            
            return true;
    }

}
