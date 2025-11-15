<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\ProcessedEvent;
use App\Models\ReplicatedProduct;

class ReplicationController extends Controller
{
    public function receive(Request $request)
    {
        // Validar cabecera interna
        $key = $request->header('X-Internal-Key');
        if ($key !== env('INTERNAL_API_KEY_MAIN')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $payload = $request->all();

        // Validar event_id
        $eventId = $payload['event_id'] ?? null;
        if (!$eventId) {
            return response()->json(['error' => 'event_id required'], 400);
        }

        // Idempotencia
        if (ProcessedEvent::where('event_id', $eventId)->exists()) {
            Log::info("🔁 Evento ignorado (ya procesado): {$eventId}", ['source' => $payload['source'] ?? null]);
            return response()->json(['status' => 'ignored', 'reason' => 'already_processed'], 200);
        }

        $action = strtolower($payload['action'] ?? 'update');
        $product = $payload['product'] ?? null;

        if (!$product && $action !== 'delete') {
            return response()->json(['error' => 'product payload missing'], 400);
        }

        try {
            $id = $product['_id'] ?? ($product['id'] ?? null);
            $category = $product['category'] ?? 'default'; // 👈 clave
            // Crear instancia dinámica del modelo según categoría
            $repo = ReplicatedProduct::forCategory($category);
            switch ($action) {
                case 'create':
                    // Si viene id, intentamos updateOrCreate por ese _id; si no viene, creamos nuevo documento
                    if ($id) {
                        $repo->updateOrCreate(
                            ['_id' => (string) $id],
                            $product
                        );
                    } else {
                        $repo->create($product);
                    }
                    break;

                case 'update':
                    // Update requiere id
                    if (! $id) {
                        return response()->json(['error' => 'product id required for update'], 400);
                    }
                    $repo->updateOrCreate(
                        ['_id' => (string) $id],
                        $product
                    );
                    break;

                case 'delete':
                    if ($id) {
                        // $repo->where('_id', (string) $id)->delete();
                        // Registrar qué id se está intentando eliminar
                        \Log::info('Intentando eliminar producto', [
                            'id_recibido' => $id,
                            'category' => $category
                        ]);

                        // Buscar producto antes de eliminar
                        $found = $repo->where('_id', (string) $id)->first();

                        // Log del producto encontrado (si existe)
                        if ($found) {
                            \Log::info('Producto encontrado para eliminar', [
                                'producto' => $found->toArray()
                            ]);

                            // Eliminar el producto
                            $repo->where('_id', (string) $id)->delete();

                            return response()->json([
                                'message' => 'Producto eliminado correctamente',
                                'id' => $id
                            ]);
                        } else {
                            \Log::warning('Producto no encontrado para eliminar', [
                                'id_busqueda' => (string) $id
                            ]);

                            return response()->json([
                                'message' => 'Producto no encontrado',
                                'id' => $id
                            ], 200);
                        }
                    }
                    break;

                default:
                    return response()->json(['error' => 'invalid action'], 400);
            }

            // Registrar evento procesado
            ProcessedEvent::create([
                'event_id'    => $eventId,
                'source'      => $payload['source'] ?? null,
                'action'      => $action,
                'payload'     => $payload,
                'processed_at'=> now(),
            ]);

            return response()->json(['status' => 'ok'], 200);
        } catch (\Throwable $e) {
            Log::error('ReplicationController error', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'payload' => $payload,
            ]);

            return response()->json(['error' => 'internal', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener todos los productos de una categoría específica
     */
    public function getByCategory(Request $request, $category)
    {
        $model = ReplicatedProduct::forCategory($category);
        $products = $model->newQuery()->get();
        return response()->json($products);
    }

    /**
     * Obtener un producto específico por ID y categoría
     */
    public function getProduct(Request $request, $category, $id)
    {
        $model = ReplicatedProduct::forCategory($category);
        $product = $model->where('_id', $id)->first();

        if (!$product) {
            return response()->json(['error' => 'Product not found', 'info' => new ReplicatedProduct()], 404);
        }

        return response()->json($product);
    }
}
