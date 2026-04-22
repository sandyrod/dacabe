<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Client;
use App\Models\ClientType;
use App\User;

use Illuminate\Http\Request;
use App\Http\Resources\ClientResource;
use App\Http\Resources\ClientCollection;


class ClientsController extends Controller
{
    private $paginate = 10;
    private $permission;

    public function __construct()
    {
        $this->permission = 'client';
    }
  

    public function index(Request $request)
    {
        if ( ! hasPermission($this->permission) )
            return response()->json([
                'message' => 'not authorized'
            ], 403);

        $data = Client::with('client_type')->with('user')
                ->where(function($q) use ($request){
                    if  ($request->q)
                        $q->where(function($query) use ($request) {
                            $query->where('phone', 'like', '%'.$request->q.'%')
                                ->orWhere('cellphone', 'like', '%'.$request->q.'%')
                                ->orWhere('address', 'like', '%'.$request->q.'%')
                                ->orWhereIn('user_id', function( $qu ) use ($request) {
                                    $qu->select('id')
                                        ->from('users')
                                        ->where('name', 'like', '%'.$request->q.'%')
                                        ->orWhere('email', 'like', '%'.$request->q.'%');
                                })
                                ->orWhereIn('client_type_id', function( $qu ) use ($request) {
                                    $qu->select('id')
                                        ->from('client_types')
                                        ->where('name', 'like', '%'.$request->q.'%');
                                });
                        });
                })
                ->paginate($this->paginate);
        
        return new ClientCollection($data);

    }

    public function getAll()
    {
        if ( ! hasPermission($this->permission) )
            return response()->json([
                'message' => 'not authorized'
            ], 403);
        
        return new ClientCollection(Client::with('client_type')->with('user')->get());

    }

    public function store(Request $request)
    {
        if ( ! hasPermission($this->permission.'-store') )
            return response()->json([
                'message' => 'not authorized'
            ], 403);

        $request->validate([
            'address' => 'required|string',
            'user_id' => 'required|numeric'
        ]);

        if (! User::where('id', $request->user_id)->exists())
            return response()->json([
                'message' => 'User Not Found'
            ], 404);

        $status = $request->status ? $request->status : 1;
        $workshop = $request->workshop ? $request->workshop : 0;

        $client = new Client([
            'address' => $request->address,
            'phone' => $request->phone,
            'cellphone' => $request->cellphone,
            'status' => $status,
            'client_type_id' => $request->client_type_id,
            'workshop' => $workshop,
            'user_id' => $request->user_id
        ]);
        
        $client->save();

        return new ClientResource($client);
    }

   
    public function show(Client $client)
    {
        if ( ! hasPermission($this->permission.'-show') )
            return response()->json([
                'message' => 'not authorized'
            ], 403);

        return new ClientResource($client);
    }

    public function update(Request $request, Client $client)
    {
        if ( ! hasPermission($this->permission.'-update') )
            return response()->json([
                'message' => 'not authorized'
            ], 403);

       
        if (! $request->status)
            $request->status = 1;
        
        $client->update($request->only('address', 'phone', 'cellphone', 'client_type_id', 'status', 'workshop'));

        return new ClientResource($client);
    }

    public function destroy(Client $client)
    {
        if ( ! hasPermission($this->permission.'-destroy') )
            return response()->json([
                'message' => 'not authorized'
            ], 403);

        $client->delete();
        
        return response()->json([
            'message' => 'Client deleted!'
        ], 204);
    }
}
