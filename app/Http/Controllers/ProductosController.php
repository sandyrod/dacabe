<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Promocion;
use Illuminate\Http\Request;

class ProductosController extends Controller
{
    public function index()
    {
        $productos = Producto::with('promociones')->get();
        return view('productos.index', compact('productos'));
    }

    public function show($id)
    {
        $producto = Producto::with('promociones')->findOrFail($id);
        return view('productos.show', compact('producto'));
    }

    public function catalogo()
    {
        $productos = Producto::with('promociones')->get();
        return view('productos.catalogo', compact('productos'));
    }
}
