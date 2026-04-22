<?php

namespace App\Models;

use App\Models\Company;
use Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\{User, Role};
use App\Models\{Deposito, Zona};

use Illuminate\Database\Eloquent\Model;

class Vendedor extends Model
{
    protected $connection = 'company';
    protected $table = 'vendedores';
    public $timestamps = false;

    protected $fillable = ['estatus', 'codigo', 'telefono', 'email', 'zona_id', 'recargo'];

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'seller_id')->with('pago_pedidos')->orderBy('fecha', 'DESC');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }

    public function depositos()
    {
        return $this->hasMany(VendedorDeposito::class, 'vendedor_id');
    }

    public function createNew($request)
    {
        if ((new User)->where('email', $request->email)->first()) {
            return 'Email ya existe';
        }

        $rol = (new Role)->where('name', 'vendedor')->first();
        $rol_id = $rol ? $rol->id : null;

        DB::transaction(function() use ($request) {
            $user = (new User)->create([
                'name' => $request->name,
                'last_name' => $request->last_name,
                'document' => $request->document,
                'phone' => $request->telefono,
                'email' => $request->email,
                'password' => $request->password,
                'status' => 1,
                'dashboard' => 'pedidos'
            ]);

            $vendedor = $this->create([
                'estatus' => $request->estatus,
                'codigo' => $request->codigo,
                //'CDEPOS' => $request->CDEPOS,
                'telefono' => $request->telefono,
                'email' => $request->email,
                'zona_id' => $request->zona_id,
                'recargo' => $request->recargo
            ]);

            if ($request->depositos) {
                VendedorDeposito::where('vendedor_id', $vendedor->id)->delete();
                foreach ($request->depositos as $item) {
                    $vend_dep = new VendedorDeposito([
                        'vendedor_id' => $vendedor->id,
                        'CDEPOS' => $item
                    ]);
                    $vend_dep->save();
                }
            }

            return 'OK';
        });
        
    }

    public function deleteRecord($id)
    {
        $vendedor = $this->find($id);
        if ($vendedor) {
            (new User)->where('email', $vendedor->email)->delete();

            return $this->where('id', $id)->delete();
        }
    }

    public function getDataSeller($id = null)
    {
        if ($id) {
            $user = (new User)->find($id);
            if (!$user) {
                return null;
            }
            $vendedor = $this
                    ->select('vendedores.*')
                    ->with('pagos')
                    ->where('email', $user->email)
                    ->first();

            $vendedor->user = (new User)->where('email', $vendedor->email)->select('id', 'name', 'last_name', 'document')->first();
            $vendedor->zona = (new Zona)->find($vendedor->zona_id);
            //$vendedor->deposito = (new Deposito)->where('CDEPOS', $vendedor->CDEPOS)->first();

            $pedidos_detalle = (new PedidoDetalle)
                ->selectRaw('pedido_id, SUM(cantidad * precio_dolar) as total')
                ->whereIn('pedido_id', function( $q ) use ($vendedor) {
                    $q->select('id')
                        ->from('pedidos')
                        ->where('user_id', $vendedor->user->id)
                        ->where('estatus', 'APROBADO');
                })
                ->groupBy('pedido_id')
                ->get();

            $vendedor->totales = $pedidos_detalle->sum('total');
            $vendedor->saldo = $pedidos_detalle->sum('total') - $vendedor->pagos->sum('monto');

            return $vendedor;
        }
    }

    public function getSellerBalance($seller_id = null)
    {
        if ($seller_id) {
            $vendedor = $this
                    ->select('vendedores.*')
                    ->with('pagos')
                    ->find($seller_id);
            
            if ($vendedor) {
                $vendedor->user = $vendedor ? (new User)->where('email', $vendedor->email)->select('id', 'name', 'last_name', 'document')->first() : null;
                $vendedor->zona = $vendedor ? (new Zona)->find($vendedor->zona_id) : null;
                //$vendedor->deposito = $vendedor ? (new Deposito)->where('CDEPOS', $vendedor->CDEPOS)->first() : null;

                $pedidos_detalle = (new PedidoDetalle)
                    ->selectRaw('pedido_id, SUM(cantidad * precio_dolar) as total')
                    ->whereIn('pedido_id', function( $q ) use ($vendedor) {
                        $q->select('id')
                            ->from('pedidos')
                            ->where('user_id', $vendedor->user->id)
                            ->where('estatus', 'APROBADO');
                    })
                    ->groupBy('pedido_id')
                    ->get();                

                $pagos = $vendedor ? $vendedor->pagos->sum('monto') : 0;

                $vendedor->payments = $pagos;
                $vendedor->ventas = $pedidos_detalle->sum('total');
                $vendedor->saldo = $pedidos_detalle->sum('total') - $pagos;
                $vendedor->pedidos_detalle = $pedidos_detalle;
            }


            return $vendedor;
        }
    }


    public function getData($id = null)
    {
        if ($id) {
            $vendedores = $this
                    ->select('vendedores.*')
                    ->with('pagos')
                    ->with('depositos')
                    ->where('id', $id)
                    ->first();

            $vendedores->user = (new User)->where('email', $vendedores->email)->select('id', 'name', 'last_name', 'document')->first();
            $vendedores->zona = (new Zona)->find($vendedores->zona_id);
            //$vendedores->deposito = (new Deposito)->where('CDEPOS', $vendedores->CDEPOS)->first();
            //$vendedores->depositos = (new VendedorDeposito)->where('vendedor_id', $vendedores->id)->first();
            
            return $vendedores;
        }
        $vendedores = $this
            ->select('vendedores.*') 
            ->with('depositos')
            ->get();

        foreach($vendedores as $item) {
            $item->user = (new User)->where('email', $item->email)->select('name', 'last_name', 'document', 'photo', 'id')->first();
            $item->zona = (new Zona)->find($item->zona_id);
            //$item->deposito = (new Deposito)->where('CDEPOS', $item->CDEPOS)->first();

            $user_id = @$item->user->id ? $item->user->id : null;

            $pedidos_detalle = (new PedidoDetalle)
                ->selectRaw('pedido_id, SUM(cantidad * precio_dolar) as total')
                ->whereIn('pedido_id', function( $q ) use ($user_id) {
                    $q->select('id')
                        ->from('pedidos')
                        ->where('user_id', $user_id)
                        ->where('estatus', 'APROBADO');
                })
                ->groupBy('pedido_id')
                ->get();

            
            $item->totales = $pedidos_detalle; 
            $item->saldo = $pedidos_detalle->sum('total') - $item->pagos->sum('monto');
        }

        return $vendedores;
    }

    public function getAdminData($id = null)
    {
        if ($id) {
            return (new User)->where('email', $vendedores->email)->select('id', 'name', 'last_name', 'document')->first();
        }
        // CEDANO FALTA EL HASROLE ADMIN_DACABE (admin_pedidos)
        return (new User)->where('company_id', auth()->user()->company_id)->select('name', 'last_name', 'document')->get();
    }

    public function updateItem($id, $request)
    {
        $vendedor = $this->find( $request->vendedor_id);
        if (! $vendedor) {
            return null;
        }

        $user = (new User)->where('email', $vendedor->email)->first();
        if (! $user) {
            return;
        }

        DB::transaction(function() use ($request, $user, $vendedor) {
            $user->name = $request->name;
            $user->last_name = $request->last_name;
            $user->document = $request->document;
            $user->phone = $request->telefono;
            $user->email = $request->email;
            if ($request->password) {
                $user->password = $request->password;
            }
            $user->save();

            $vendedor->codigo = $request->codigo;
            //$vendedor->CDEPOS = $request->CDEPOS;
            $vendedor->telefono = $request->telefono;
            $vendedor->email = $request->email;
            $vendedor->zona_id = $request->zona_id;
            $vendedor->recargo = $request->recargo;
            $vendedor->save();

            if ($request->depositos) {
                VendedorDeposito::where('vendedor_id', $vendedor->id)->delete();
                foreach ($request->depositos as $item) {
                    $vend_dep = new VendedorDeposito([
                        'vendedor_id' => $vendedor->id,
                        'CDEPOS' => $item
                    ]);
                    $vend_dep->save();
                }
            }
        });
    }

    public function getReportConfig()
    {
        return [
            'title' => 'Listado de Vendedores', 
            'company' => Auth::user()->company
        ];
    }

}
