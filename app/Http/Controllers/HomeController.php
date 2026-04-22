<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\{Rate, Module, Company, Tasa};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $permissions = (new Module)->getPermissions(Auth::user()->company->id);
        $company = Company::select('company_status_id')->find(Auth::user()->company->id);

        if (hasOnlyOrderClient()) {
            return redirect()->route('facturacion.dashboard');
        }

        if (hasOrderPermission()) {
            return redirect()->route('manager.dashboard');
        }

        // Detectar pedidos vencidos para vendedores
        $pedidosVencidos = [];
        if (Auth::user()->hasRole('vendedor')) {
            $pedidosVencidos = DB::connection('company')
                ->table('pedidos')
                ->select('pedidos.id', 'pedidos.descripcion', 'pedidos.dias_credito', 'pedidos.fecha')
                ->where('pedidos.user_id', Auth::user()->id)
                ->where('pedidos.estatus', 'APROBADO')
                ->whereNotNull('pedidos.dias_credito')
                ->where('pedidos.dias_credito', '>', 0)
                ->get()
                ->filter(function ($pedido) {
                    $fechaPedido = Carbon::parse($pedido->fecha);
                    $fechaLimite = $fechaPedido->copy()->addDays($pedido->dias_credito);
                    return Carbon::now()->greaterThan($fechaLimite);
                });
        }
        
        return view('dashboard', compact(['permissions', 'company', 'pedidosVencidos']));
    }

    public function getBcvRate()
    {
        /*
        $response = file_get_contents('https://bcv-api.deno.dev/v1/exchange');
        if ($response) {
            $data = json_decode($response);
            $value = 0;
            $modified = '';
            if (sizeof($data)) {
                foreach($data as $item) {
                    if ($item->currency == 'Dolar') {
                        $value = number_format((float)$item->exchange, 2, '.', '');
                        $modified = $item->date;
                    }
                }                
            }
            
            if ($value > 0) {
                (new Rate)->createOrUpdateRate($value);
            }
            return Response::json([
                'type' => 'success',
                'data' => ['last_modified' => $modified, 'rate' => $value]
            ], 200);
        }
        
        */
        return $this->getLastRate();
    }

    public function getLastRate()
    {
        $rate = (new Rate)->gatLastRate();
        $data = ($rate) ? ['user' => auth()->user(), 'last_modified' => $rate->created_at, 'rate' => $rate->rate, 'rate2' => $rate->rate2] : ['rate' => 0, 'rate2' => 0];

        return Response::json([
            'type' => 'success',
            'data' => $data
        ], 200);

        $response = file_get_contents('https://pydolarve.org/api/v1/dollar?page=bcv');
        if ($response) {
            $data = json_decode($response);
            //(new Rate)->createOrUpdateRate(@$data->monitors->usd->price, $rate2);
            (new Rate)->createOrUpdateRate(@$data->monitors->usd->price, 0);
        }
        $rate = (new Rate)->gatLastRate();
        $data = ($rate) ? ['last_modified' => $rate->created_at, 'rate' => $rate->rate, 'rate2' => $rate->rate2, 'bcv' => $rate->bcv] : ['rate' => 0, 'rate2' => 0, 'bcv' => 0];


        return Response::json([
            'type' => 'success',
            'data' => $data
        ], 200);
    }

    public function getBcvRateNew()
    {
        $data = null;
        return Response::json([
            'type' => 'success',
            'data' => $data
        ], 200);
        $response = file_get_contents('https://pydolarve.org/api/v1/dollar?page=bcv');
        if ($response) {
            $data = json_decode($response);
            //(new Rate)->createOrUpdateRate(@$data->monitors->usd->price, 0);
        }
        $rate = (new Rate)->gatLastRate();
        $data = ($rate) ? ['rate' => @$data->monitors->usd->price] : ['rate' => 0];


        return Response::json([
            'type' => 'success',
            'data' => $data
        ], 200);
    }

    public function getLastDiscount()
    {
        /*
        $rate = (new Rate)->gatLastRate();
        $data = ($rate) ? $rate->discount : 0;
        */
        //$data = env('DACABE_PERCENT', 35);
        $data = obtenerDescuentoGlobal() ?? 0;

        return Response::json([
            'type' => 'success',
            'data' => $data
        ], 200);
    }

    public function storeRate(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('storeRate called with data: ', $request->all());

        try {
            $request->validate([
                'rate' => 'required|numeric'
            ]);

            $valor = str_replace(',', '.', $request->rate);

            if ($valor < 50 || $valor > 5000) {
                return Response::json([
                    'type' => 'error',
                    'message' => 'El valor de la tasa debe estar entre 50 y 5000.'
                ], 422);
            }

            \Illuminate\Support\Facades\Log::info('Saving tasa: ' . $valor . ' for date: ' . now()->format('Y-m-d'));

            $tasa = Tasa::updateOrCreate(
                ['fecha' => now()->format('Y-m-d')],
                [
                    'valor' => $valor,
                    'usuario' => auth()->user()->name
                ]
            );

            \Illuminate\Support\Facades\Log::info('Tasa saved successfully ID: ' . $tasa->id);
            /*
            // Notificación WhatsApp
            $phone = '584129003985';
            $template = 'tasa_del_dia';
            $userName = urlencode(auth()->user()->name);
            $tasaValue = urlencode($valor);
            
            $url = "https://santiscodes.com/send-whatsapp?to={$phone}&template={$template}&pedido_id={$tasaValue}&cliente={$userName}";
            
            \Illuminate\Support\Facades\Log::info('Sending WhatsApp notification: ' . $url);
            
            try {
                \Illuminate\Support\Facades\Http::timeout(5)->get($url);
                \Illuminate\Support\Facades\Log::info('WhatsApp notification sent.');
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error sending WhatsApp: ' . $e->getMessage());
            }
*/
            session()->forget('prompt_bcv_rate');

            return Response::json([
                'type' => 'success',
                'message' => 'Tasa guardada correctamente y notificación enviada.'
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Illuminate\Support\Facades\Log::warning('Validation error: ', $e->errors());
            return Response::json([
                'type' => 'error',
                'message' => 'Error de validación.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in storeRate: ' . $e->getMessage());
            return Response::json([
                'type' => 'error',
                'message' => 'Ocurrió un error al guardar la tasa: ' . $e->getMessage()
            ], 500);
        }
    }
}
