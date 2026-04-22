<?php

namespace App\Http\Controllers;

use Auth;
use App\User;
use App\Models\Car;

use Illuminate\Http\Request;
use App\Http\Resources\CarResource;
use App\Http\Resources\CarCollection;


class CarsController extends Controller
{
    private $paginate = 10;
    private $permission;

    public function __construct()
    {
        $this->permission = 'cars';
    }
  
    public function index()
    {
        if ( ! hasPermission($this->permission) )
            return response()->json([
                'message' => 'not authorized'
            ], 403);
        
        return new CarCollection(Car::with('user')->paginate($this->paginate));

    }

    public function getAll()
    {
        if ( ! hasPermission($this->permission) )
            return response()->json([
                'message' => 'not authorized'
            ], 403);
        
        return new CarCollection(Car::with('user')->get());

    }

    public function store(Request $request)
    {
        if ( ! hasPermission($this->permission.'-store') )
            return response()->json([
                'message' => 'not authorized'
            ], 403);

        $request->validate([
            'name_owner' => 'required|string|max:200',
        ]);

        if ($request->user_id){
            if (! User::where('id', $request->user_id)->exists())
                return response()->json([
                    'message' => 'user_id not found'
                ], 404);
            
        }

        $car = new Car([
            'name_owner' => $request->name_owner,
            'serial' => $request->serial,
            'plate' => $request->plate,
            'color' => $request->color,
            'description' => $request->description,
            'user_id' => $request->user_id
        ]);
        
        $car->save();

        return new CarResource($car);
    }

   
    public function show(Car $car)
    {
        if ( ! hasPermission($this->permission.'-show') )
            return response()->json([
                'message' => 'not authorized'
            ], 403);

        return  new CarResource($car);
    }

    public function update(Request $request, Car $car)
    {
        if ( ! hasPermission($this->permission.'-update') )
            return response()->json([
                'message' => 'not authorized'
            ], 403);

        $car->update($request->only(['name_owner', 'serial', 'plate', 'color', 'description', 'user_id']));

        return new CarResource($car);
    }

    public function destroy(Car $car)
    {
        if ( ! hasPermission($this->permission.'-destroy') )
            return response()->json([
                'message' => 'not authorized'
            ], 403);

        $car->delete();
        
        return response()->json([
            'message' => 'Procedure Type deleted!'
        ], 204);
    }
}
