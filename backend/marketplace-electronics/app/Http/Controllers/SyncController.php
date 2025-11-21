<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class SyncController extends Controller
{
    public function receiveFromMain(Request $request)
    {
        try {
            $operation = $request->input('operation', []);
            $productData = $request->input('product', []);
            // Validación mínima según operación
            if (!$operation || !in_array($operation, ['create','update','delete'])) {
                return response()->json(['error' => 'Invalid operation'], 400);
            }
            Log::info("🟢 Nodo recibiendo acción del main: {$operation}", ['product' => $productData]);
            switch ($operation) {
                case 'create':
                    $productId = $productData['id'] ?? null;
                    $product = new Product();
                    $product->_id = $productId; // tu id de 24 caracteres
                    $product->name = $productData['name'] ?? null;
                    $product->description = $productData['description'] ?? null;
                    $product->price = $productData['price'] ?? null;
                    $product->stock = $productData['stock'] ?? null;
                    $product->link = $productData['link'] ?? null;
                    $product->seller_id = $productData['seller_id'] ?? null;
                    $product->category = $productData['category'] ?? null;
                    $product->attributes = $productData['attributes'] ?? [];
                    try {
                        $product->save();

                        // Log de éxito
                        Log::info("🟢 Nodo: acción '{$operation}' realizada con éxito", [
                            'product' => $product->toArray()
                        ]);
                    } catch (\Exception $e) {
                        // Log de fallo
                        Log::warning("⚠️ Nodo: acción '{$operation}' NO se logró", [
                            'product' => $product->toArray(),
                            'error' => $e->getMessage()
                        ]);
                    }
                    break;

                case 'update':
                    if (!isset($productData['id'])) {
                        return response()->json(['error' => 'Missing product id for update'], 400);
                    }
                    $id = $productData['id'];
                    unset($productData['id']);
                    Product::where('_id', $id)->update($productData);
                    Log::info("🟢 Nodo: acción '{$operation}' realizada con éxito", ['info' => $productData]);
                    break;

                case 'delete':
                    if (!isset($productData['id'])) {
                        return response()->json(['error' => 'Missing product _id for delete'], 400);
                    }
                    Product::where('_id', $productData['id'])->delete();
                    Log::info("🟢 Nodo: acción '{$operation}' realizada con éxito", ['info' => $productData]);
                    break;
            }

            return response()->json(['message' => "Operación, '{$operation}', exitosa"], 200);

        } catch (\Throwable $e) {
            Log::error("❌ Error al aplicar acción del main: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error' => 'Failed to apply main sync event',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
