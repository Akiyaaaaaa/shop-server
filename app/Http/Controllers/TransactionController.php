<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Products;
use App\Models\Transactions;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $transactions = Transactions::all();
        return response()->json(['transactions' => $transactions], 200);
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
        $check = Validator::make($request->all(), [
            'quantity' => 'required|integer',
            'product_id' => 'required|integer'
        ]);
        if ($check->fails()) {
            return response()->json(['errors' => $check->errors()], 400);
        }

        $product = Products::find($request->product_id);
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }
        $price = $product->price;

        $paymentAmount = $request->quantity * $price;

        $httpMethod = 'POST';
        $xAPIKey = 'DATAUTAMA';
        $signature = hash('sha256', $httpMethod . ':' . $xAPIKey);

        $response = Http::withHeaders([
            'X-API-KEY' => $xAPIKey,
            'X-SIGNATURE' => $signature
        ])->post('http://tes-skill.datautama.com/test-skill/api/v1/transactions', [
            'quantity' => $request->quantity,
            'price' => $price,
            'payment_amount' => $paymentAmount,
        ]);

        if ($response->successful()) {
            $responseData = $response->json();

            $referenceNum = $responseData['data']['reference_no'] ?? null;
            $price = $responseData['data']['price'] ?? null;
            $quantity = $responseData['data']['quantity'] ?? null;
            $paymentAmount = $responseData['data']['payment_amount'] ?? null;

            if ($referenceNum === null || $price === null || $quantity === null || $paymentAmount === null) {
                return response()->json(['error' => 'Incomplete data in API response'], 500);
            }

            $transaction = Transactions::create([
                'reference_no' => $referenceNum,
                'price' => $price,
                'quantity' => $quantity,
                'payment_amount' => $paymentAmount,
                'product_id' => $request->product_id,
            ]);

            $newStock = $product->stock - $quantity;
            $product->update(['stock' => $newStock]);
            return response()->json(['transaction' => $transaction], 201);
        } else {
            info('API Error Response: ' . $response->body());

            return response()->json(['error' => 'Failed to add transaction'], $response->status());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $transaction = Transactions::find($id);
        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }
        return response()->json(['transaction' => $transaction], 200);
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
        // Validate the request data
        $check = Validator::make($request->all(), [
            'quantity' => 'required|integer',
            'product_id' => 'required|integer',
        ]);

        if ($check->fails()) {
            return response()->json(['errors' => $check->errors()], 400);
        }

        try {
            $transaction = Transactions::findOrFail($id);
            $product = Products::findOrFail($request->product_id);

            $newPaymentAmount = $request->quantity * $product->price;

            $transaction->update([
                'quantity' => $request->quantity,
                'payment_amount' => $newPaymentAmount,
                'product_id' => $request->product_id,
            ]);

            $newStock = $product->stock - $request->quantity;
            $product->update(['stock' => $newStock]);
            return response()->json(['transaction' => $transaction], 200);
        } catch (QueryException $e) {
            // Log the error
            Log::error('Database error: ' . $e->getMessage());

            return response()->json(['error' => 'Failed to update transaction'], 500);
        } catch (\Exception $e) {
            // Log other exceptions
            Log::error('Exception: ' . $e->getMessage());

            return response()->json(['error' => 'Failed to update transaction'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $transaction = Transactions::findOrFail($id);

            $product = $transaction->product;
            $newStock = $product->stock + $transaction->quantity;
            $product->update(['stock' => $newStock]);

            $transaction->delete();

            return response()->json(['message' => 'Transaction deleted successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Exception: ' . $e->getMessage());

            return response()->json(['error' => 'Failed to delete transaction'], 500);
        }
    }
}
