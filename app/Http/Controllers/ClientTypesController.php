<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Client;
use App\Models\ClientType;

use Illuminate\Http\Request;
use App\Http\Resources\ClientTypeResource;
use App\Http\Resources\ClientTypeCollection;


class ClientTypesController extends Controller
{
    private $paginate = 10;
    private $permission;

    public function __construct()
    {
        $this->permission = 'client-type';
    }
  
    public function index()
    {
        if ( ! hasPermission($this->permission) )
            return response()->json([
                'message' => 'not authorized'
            ], 403);
        
        return new ClientTypeCollection(ClientType::paginate($this->paginate));

    }

    public function store(Request $request)
    {
        if ( ! hasPermission($this->permission.'-store') )
            return response()->json([
                'message' => 'not authorized'
            ], 403);

        $request->validate([
            'name' => 'required|string'
        ]);

        $client_type = new ClientType([
            'name' => $request->name
        ]);
        
        $client_type->save();

        return new ClientTypeResource($client_type);
    }

   
    public function show(ClientType $client_type)
    {
        if ( ! hasPermission($this->permission.'-show') )
            return response()->json([
                'message' => 'not authorized'
            ], 403);

        return new ClientTypeResource($client_type);
    }

    public function update(Request $request, ClientType $client_type)
    {
        if ( ! hasPermission($this->permission.'-update') )
            return response()->json([
                'message' => 'not authorized'
            ], 403);

        $request->validate([
            'name' => 'required|string'
        ]);

        $client_type->update($request->only('name'));

        return new ClientTypeResource($client_type);
    }

    public function destroy(ClientType $client_type)
    {
        if ( ! hasPermission($this->permission.'-destroy') )
            return response()->json([
                'message' => 'not authorized'
            ], 403);

        $client_type->delete();
        
        return response()->json([
            'message' => 'Client Type deleted!'
        ], 204);
    }
}
