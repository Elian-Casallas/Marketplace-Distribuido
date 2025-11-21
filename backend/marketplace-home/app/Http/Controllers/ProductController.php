<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Jobs\ReplicateProductJob;

class ProductController extends Controller
{

    public function index(Request $request)
    {
        // filtro por atributo: ?filter=color:Azul
        try{
            if ($request->has('filter')) {
                $parts = explode(':', $request->query('filter'), 2);
                if (count($parts) === 2) {
                    $key = $parts[0];
                    $value = $parts[1];
                    $items = Product::where($key, $value)->get();
                    Log::info('✅ ' . $items->count() . " productos obtenidos con filtro $key:$value.");
                    return response()->json($items);
                }
            }
            $products = Product::all();
            Log::info('✅ (ProductController - index) Productos obtenidos correctamente.');
            return response()->json($products);
        } catch (\Throwable $e) {
            Log::warning("⚠️ Error en index - Product: " . $e->getMessage());
            return response()->json(['error' => 'Error interno al obtener los producto.'], 500);
        }

    }

    public function store(Request $request)
    {
        try {
            Log::info('🟢 (ProductController - store) Intentando crear producto...');

            $validated = $request->validate([
                'name' => 'required|string',
                'description' => 'nullable|string',
                'price' => 'required|numeric',
                'stock' => 'nullable|integer',
                'link' => 'sometimes|nullable|string',
                'seller_id' => 'sometimes|nullable|string',
                'category' => 'required|string',
                'attributes' => 'nullable|array',
            ]);

            $product = Product::create($validated);
            Log::info("✅ (ProductController - store) Producto creado: {$product->name}");
            $event = [
                'event_id'   => (string) Str::uuid(),
                'action'     => 'create',
                'source'     => env('APP_NAME'),
                'product'    => $product->toArray(),
                'created_at' => now()->toISOString(),
            ];
            ReplicateProductJob::dispatch($event);
            return response()->json([
                'message' => 'Producto creado correctamente',
                'product' => $product
            ], 201);
        } catch (\Throwable $e) {
            Log::error("❌ (ProductController - store) Error al crear producto: " . $e->getMessage());
            return response()->json(['error' => 'Error interno al crear el producto.'], 500);
        }
    }

    public function show($id)
    {
        try {
            Log::info("🔍 (ProductController - show) Buscando producto con ID: $id");

            $product = Product::find($id);
            if (!$product) {
                Log::warning("⚠️ (ProductController - show) Producto con ID $id no encontrado.");
                return response()->json(['message' => 'Producto no encontrado'], 404);
            }

            Log::info("✅ (ProductController - show) Producto encontrado: {$product->name}");
            return response()->json($product);
        } catch (\Throwable $e) {
            Log::error("❌ (ProductController - show) Error: " . $e->getMessage());
            return response()->json(['error' => 'Error interno al buscar el producto.'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            Log::info("🟡 (ProductController - update) Intentando actualizar producto ID: $id");
            $product = Product::find($id);

            if (!$product) {
                Log::warning("⚠️ (ProductController - update) Producto con ID $id no encontrado.");
                return response()->json(['message' => 'Producto no encontrado'], 404);
            }
            $validated = $request->validate([
                'name' => 'sometimes|required|string',
                'description' => 'sometimes|nullable|string',
                'price' => 'sometimes|required|numeric',
                'stock' => 'sometimes|integer',
                'link' => 'sometimes|nullable|string',
                'seller_id' => 'sometimes|nullable|string',
                'category' => 'sometimes|required|string',
                'attributes' => 'sometimes|array',
            ]);

            $product->update($validated);

            Log::info("✅ (ProductController - update) Producto actualizado: {$product->name}");

            $event = [
                'event_id'   => (string) Str::uuid(),
                'action'     => 'update',
                'source'     => env('APP_NAME'),
                'product'    => $product->toArray(),
                'created_at' => now()->toISOString(),
            ];
            ReplicateProductJob::dispatch($event);

            return response()->json([
                'message' => 'Producto actualizado correctamente',
                'product' => $product
            ]);
        } catch (\Throwable $e) {
            Log::error("❌ (ProductController - update) Error al actualizar producto ID $id: " . $e->getMessage());
            return response()->json(['error' => 'Error interno al actualizar el producto.'], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            Log::info("🗑️ (ProductController - destroy) Intentando eliminar producto ID: $id");

            $product = Product::find($id);

            if (!$product) {
                Log::warning("⚠️ (ProductController - destroy) Producto con ID $id no encontrado. Se enviará al main.");
                $event = [
                    'event_id'   => (string) Str::uuid(),
                    'action'     => 'delete',
                    'source'     => env('APP_NAME'),
                    'product'    => ['_id' => (string) $id, 'category' => $request->input('category')],
                    'created_at' => now()->toISOString(),
                ];
                ReplicateProductJob::dispatch($event);
                return response()->json(['message' => 'Producto no encontrado, operación enviada al main.'], 404);
            }

            $product->delete();
            Log::info("✅ (ProductController - destroy) Producto eliminado correctamente: {$product->name}");
            $event = [
                'event_id'   => (string) Str::uuid(),
                'action'     => 'delete',
                'source'     => env('APP_NAME'),
                'product'    => ['_id' => (string)$id, 'category' => $request->input('category')],
                'created_at' => now()->toISOString(),
            ];
            ReplicateProductJob::dispatch($event);

            return response()->json(['message' => 'Producto eliminado correctamente']);
        } catch (\Throwable $e) {
            Log::error("❌ (ProductController - destroy) Error al eliminar producto ID $id: " . $e->getMessage());
            return response()->json(['error' => 'Error interno al eliminar el producto.'], 500);
        }
    }

    public function recommended($excludeId = null, $max = 20)
    {
        try {
            Log::info("🔍 (ProductController - recommended) Obteniendo productos recomendados");

            $query = Product::query();

            // Excluir un producto si se pasa $excludeId
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }

            // Obtener productos aleatorios
            $products = $query->limit($max)
                            ->get();

            Log::info("✅ (ProductController - recommended) Productos recomendados obtenidos: " . $products->count());

            return response()->json($products);

        } catch (\Throwable $e) {
            Log::error("❌ (ProductController - recommended) Error: " . $e->getMessage());
            return response()->json(['error' => 'Error interno al obtener productos recomendados.'], 500);
        }
    }
}
