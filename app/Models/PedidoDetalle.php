<?php

namespace App\Models;

use App\Models\{Company, ArtDepos, Pedido, Vendedor};
use App\User;
use Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Model;

class PedidoDetalle extends Model
{
    protected $connection = 'company';
    protected $table = 'pedido_detalle';
    
     
    public function createNew($pedido_id, $cantidad, $precio, $precio_dolar, $tasa, $codigo_inven, $inven_descr, $inven_unidad, $modo, $iva)
    {
        $pedido = $this->insert([
            'pedido_id' => $pedido_id,
            'cantidad' => $cantidad,
            'precio' => $precio,
            //'precio_dolar' => $precio_dolar,
            'precio_dolar' => $precio,
            'tasa' => $tasa,
            'codigo_inven' => $codigo_inven,
            'inven_descr' => $inven_descr,
            'inven_unidad' => $inven_unidad,
            'iva' => $iva,
            'pago' => $modo,
            'created_at' => now(),            
        ]);
        if ($modo=='Bs'){
            //(new Pedido)->updateDescuento($pedido_id);
        }

        (new Pedido)->updateTotals($pedido_id);

        return $pedido;
    }

    public function addProduct($request)
    {
        /*
        return $this->insert([
            'pedido_id' => $request->pedido_id,
            'user_id' => auth()->user()->id,
            'cantidad' => $request->cantidad,
            'precio' => $request->precio,
            'precio_dolar' => $request->precio_dolar,
            'tasa' => $request->tasa,
            'codigo_inven' => $request->codigo_inven,
            'created_at' => now()            
        ]);
        */

        $detail = $this->create([
            'pedido_id' => $request->pedido_id,
            'user_id' => auth()->user()->id,
            'cantidad' => $request->cantidad,
            'precio' => $request->precio,
            'precio_dolar' => $request->precio_dolar,
            'tasa' => $request->tasa,
            'codigo_inven' => $request->codigo_inven,
            'created_at' => now()            
        ]);

        if ($detail) {
            $pedido = (new Pedido)->find($detail->pedido_id);
            if ($pedido) {
                $this->updateReserva($pedido, $detail, 'set');
                (new Pedido)->updateTotals($pedido->id);
            }
        }

        return $detail;
        
    }

    public function deleteRecord($id)
    {
        /*
        $company = Company::find(Auth::user()->company->id);

         Config::set('database.connections.secondary', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $company->db_name,
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]); 

        DB::purge('secondary');
        $secondaryConnection = DB::connection('secondary');

        return $secondaryConnection->table('pedido_detalle')->where('id', $id)->delete();
        */
        $detail = $this->find($id);
        $res = $this->where('id', $id)->delete();
        if ($detail) {
            (new Pedido)->updateTotals($detail->pedido_id);
        }
        return $res;
    }

    public function getActiveOrder()
    {
        /*
        $company = Company::find(Auth::user()->company->id);

         Config::set('database.connections.secondary', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $company->db_name,
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]); 

        DB::purge('secondary');
        $secondaryConnection = DB::connection('secondary');

        return $secondaryConnection->table('pedido_detalle')
            ->where('user_id', auth()->user()->id)
            ->where('estatus', 'CARGANDO')
            ->get();
        */
        return $this
            ->where('user_id', auth()->user()->id)
            ->where('estatus', 'CARGANDO')
            ->get();
    }

    public function getActiveOrderDetail()
    {
        /*
        $company = Company::find(Auth::user()->company->id);

         Config::set('database.connections.secondary', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $company->db_name,
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]); 

        DB::purge('secondary');
        $secondaryConnection = DB::connection('secondary');

        return $secondaryConnection->table('pedido_detalle')
            ->join('pedidos', 'pedidos.id', 'pedido_detalle.pedido_id')
            ->where('pedidos.user_id', auth()->user()->id)
            ->where('estatus', 'CARGANDO')
            ->get();
        */
        return $this
            ->join('pedidos', 'pedidos.id', 'pedido_detalle.pedido_id')
            ->where('pedidos.user_id', auth()->user()->id)
            ->where('estatus', 'CARGANDO')
            ->get();
    }

    public function getData($id = null)
    {
        /*
        $company = Company::find(Auth::user()->company->id);

         Config::set('database.connections.secondary', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $company->db_name,
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]); 

        DB::purge('secondary');
        $secondaryConnection = DB::connection('secondary');

        if ($id) {
            return $secondaryConnection->table('pedidos')->join('pedido_detalle', 'pedidos.id', 'pedido_id')->where('id', $id)->first();
        }
        return $secondaryConnection->table('pedidos')->join('pedido_detalle', 'pedidos.id', 'pedido_id')->get();
        */
        if ($id) {
            return $this->join('pedido_detalle', 'pedidos.id', 'pedido_id')->where('id', $id)->first();
        }
        return $this->join('pedido_detalle', 'pedidos.id', 'pedido_id')->get();
    }

    public function searchProduct($pedido_id, $codigo)
    {
        /*
        $company = Company::find(Auth::user()->company->id);

         Config::set('database.connections.secondary', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $company->db_name,
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]); 

        DB::purge('secondary');
        $secondaryConnection = DB::connection('secondary');

        return $secondaryConnection->table('pedido_detalle')
            ->where('pedido_id', $pedido_id)
            ->where('codigo_inven', $codigo)
            ->first();
        */
        return $this
            ->where('pedido_id', $pedido_id)
            ->where('codigo_inven', $codigo)
            ->first();
    }

    public function searchOrderDetail($pedido_id)
    {
        /*
        $company = Company::find(Auth::user()->company->id);

         Config::set('database.connections.secondary', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $company->db_name,
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]); 

        DB::purge('secondary');
        $secondaryConnection = DB::connection('secondary');

        return $secondaryConnection->table('pedido_detalle')
            ->where('pedido_id', $pedido_id)
            ->get();
        */
        return $this
            ->where('pedido_id', $pedido_id)
            ->get();
    }

    public function searchProductById($id)
    {
        /*
        $company = Company::find(Auth::user()->company->id);

         Config::set('database.connections.secondary', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $company->db_name,
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]); 

        DB::purge('secondary');
        $secondaryConnection = DB::connection('secondary');

        return $secondaryConnection->table('pedido_detalle')
            ->find($id);
        */
        return $this->find($id);
    }

    public function deleteProductById($id)
    {        
        $detail = $this->where('id', $id)->first();
        if ($detail) {
            $pedido = (new Pedido)->find($detail->pedido_id);
            if ($pedido) {
                $this->updateReserva($pedido, $detail, 'delete');
            }
            $this->where('id', $id)->delete();
            
            if ($pedido) {
                (new Pedido)->updateTotals($pedido->id);
            }
            
            return 'ok';
        }
        /*
        if ($this->where('id', $id)->delete()) {
            return 'ok';
        }
        */
    }

    private function updateReserva($pedido, $detail, $mode)
    {
        $user = (new User)->find($pedido->user_id);
        $seller = (new Vendedor)->where('email', $user->email)->first();
        if ($seller) {
            $cdepos = $pedido->cdepos;
            $artdepos = (new ArtDepos)->where('CODIGO', $detail->codigo_inven)->where('CDEPOS', $cdepos)->first();
            if ($artdepos) {
                //$qty = $mode == 'set' ? $detail->cantidad : ($artdepos->RESERVA <= $detail->cantidad ? 0 : $artdepos->RESERVA - $detail->cantidad);
                $qty = $mode == 'delete' ? $artdepos->RESERVA - $detail->cantidad : $artdepos->RESERVA + $detail->cantidad;
                //$artdepos->RESERVA = $qty;
                //$artdepos->save();
                (new ArtDepos)->where('CODIGO', $detail->codigo_inven)->where('CDEPOS', $cdepos)->update(['RESERVA' => $qty]);
            } 
        }
    }

    public function updateItem($id, $field, $value)
    {
        $res = $this->where('id', $id)->update([
            $field => $value
        ]);

        $detail = $this->find($id);
        if ($detail) {
            (new Pedido)->updateTotals($detail->pedido_id);
        }

        return $res;
    }

    public function getReportConfig()
    {
        return [
            'title' => 'Listado de Pedidos', 
            'company' => \Illuminate\Support\Facades\Auth::user()->company
        ];
    }

}
