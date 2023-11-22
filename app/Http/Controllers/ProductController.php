<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator as FacadesValidator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Products::all();
        return response()->json(['products' => $products], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $check = FacadesValidator::make($request->all(), [
            'name' => 'required|string|max:200',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'description' => 'required|string|max:2000',
        ]);

        if ($check->fails()) {
            return response()->json(['errors' => $check->errors()], 400);
        }
        $product = Products::create($request->all());
        return response()->json(['product' => $product], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $product = Products::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        return response()->json(['product' => $product], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $product = Products::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $check = FacadesValidator::make($request->all(), [
            'name' => 'required|string|max:200',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'description' => 'required|string|max:2000',
        ]);

        if ($check->fails()) {
            return response()->json(['errors' => $check->errors()], 400);
        }
        $product->update($request->all());
        return response()->json(['product' => $product], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Products::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        $product->delete();
        return response()->json(['message' => 'Product deleted successfully'], 200);
    }
}
