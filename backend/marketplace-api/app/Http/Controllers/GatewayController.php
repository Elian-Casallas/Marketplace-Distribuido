<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\LoadBalancerService;
use App\Models\PendingReplication;
use App\Models\SyncLog;
use App\Models\ReplicatedProduct;

class GatewayController extends Controller
{
    protected $balancer;

    public function __construct(LoadBalancerService $balancer)
    {
        $this->balancer = $balancer;
    }
    /**
     * Obtiene productos desde el nodo correspondiente
     * Ejemplo: GET /api/gateway/products?category=electronics
    */
    public function handleProducts(Request $request)
    {
        $category = $request->query('category');
        if (!$category) {
            return response()->json(['error' => 'Debe especificar la categoría'], 400);
        }
        try {
            $node = $this->balancer->resolveNode($category);
            // Petición GET autenticada con API key
            $response = Http::withHeaders([
                'X-Internal-Key' => $node['key'],
            ])->get($node['url'] . '/api/products');
            return response()->json($response->json(), $response->status());
        } catch (\Throwable $e) {
            // ⚠️ Nodo caído o error de red
            Log::warning("⚠️ Nodo {$category} inaccesible, usando respaldo local: " . $e->getMessage());
            $backup = $this->getBackupProducts($category);
            if ($backup['error']) {
                return response()->json([
                    'error' => $backup['message'],
                    'details' => $backup['details'] ?? null
                ], 503);
            }
            return response()->json($backup, 200);
        }
    }
    /**
     * Obtiene producto desde el nodo correspondiente
     * Ejemplo: GET /api/gateway/products/id
    */
    public function show(Request $request, $id)
    {
        $category = $request->input('category');
        if (!$category) {
            return response()->json(['error' => 'Debe especificar la categoría'], 400);
        }
        try {
            $node = $this->balancer->resolveNode($category);
            // Petición GET autenticada con API key
            $response = Http::withHeaders([
                'X-Internal-Key' => $node['key'],
            ])->get($node['url'] . '/api/products/{$id}');
            return response()->json($response->json(), $response->status());
        } catch (\Throwable $e) {
            // ⚠️ Nodo caído o error de red
            Log::warning("⚠️ Nodo {$category} inaccesible, usando respaldo local: " . $e->getMessage());
            $backup = $this->getBackupProduct($category, $id);
            if ($backup['error']) {
                return response()->json([
                    'error' => $backup['message'],
                    'details' => $backup['details'] ?? null
                ], 503);
            }
            return response()->json($backup, 200);
        }
    }
    /**
     * Crea un producto en el nodo correspondiente
     * Ejemplo: POST /api/gateway/products  { "category": "electronics", ... }
    */
    public function createProduct(Request $request)
    {
        $category = $request->input('category');

        if (!$category) {
            return response()->json(['error' => 'Debe especificar la categoría del producto'], 400);
        }

        try {
            $node = $this->balancer->resolveNode($category);
            // Enviar POST autenticado con la API Key del nodo
            $response = Http::withHeaders([
                'X-Internal-Key' => $node['key'],
            ])->post("{$node['url']}/api/products", $request->all());

            return response()->json($response->json(), $response->status());
        } catch (\Throwable $e) {
            $idData = $this->storeBackup($category, $request->all());
            // Si NO se pudo guardar en respaldo local
            if (!$idData) {
                return response()->json([
                    'message' => "No se pudo guardar en el respaldo local y el nodo {$node['name']} no responde.",
                    'nodo'  => false,
                    'local' => false,
                ], 500);
            }
            // 🔹 1. Crear registro pendiente
            PendingReplication::create([
                'node' => $node['name'], // ej: 'MarketplaceElectronics'
                'payload' => [
                    'operation' => 'create',
                    'product' => array_merge($request->all(), ['_id' => $idData]),
                ],
                'status' => 'pending',
                'attempts' => 0,
                'last_error' => $e->getMessage(),
            ]);

            // 🔹 2. Log de fallo inmediato
            SyncLog::create([
                'direction' => 'main->node',
                'target' => $node['name'],
                'status' => 'pending',
                'message' => 'Nodo caído. Se programó replicación diferida.',
                'timestamp' => now(),
            ]);

            // 🔹 3. Aquí devuelves éxito porque SÍ se guardó en backup
            return response()->json([
                'message' => "El nodo {$node['name']} no respondió, pero el producto se guardó en el respaldo local y se replicará más tarde.",
                'id' => $idData,
                'nodo'  => false,
                'local' => true,
            ], 201);
        }
    }
    /**
     * Actualizar un producto en el nodo correspondiente
     * Ejemplo: PUT /api/gateway/products  { "category": "electronics", ... }
    */
    public function updataProduct(Request $request, $id)
    {
        $category = $request->input('category');

        if (!$category) {
            return response()->json(['error' => 'Debe especificar la categoría del producto'], 400);
        }

        try {
            $node = $this->balancer->resolveNode($category);
            // Enviar POST autenticado con la API Key del nodo
            $response = Http::withHeaders([
                'X-Internal-Key' => $node['key'],
            ])->put("{$node['url']}/api/products/{$id}", $request->all());
            return response()->json($response->json(), $response->status());
        } catch (\Throwable $e) {
            $productData = $request->all();
            // Si no existe 'id' dentro de product, lo agregamos
            if (!isset($productData['id'])) {
                $productData['id'] = $id;
            }
            // Intentamos actualizar respaldo local
            $backupUpdated = $this->updateBackup($category, $id, $request->all());

            if ($backupUpdated) {
                PendingReplication::create([
                    'node' => $node['name'],
                    'payload' => [
                        'operation' => 'update',
                        'product' => $productData,
                    ],
                    'status' => 'pending',
                    'attempts' => 0,
                    'last_error' => $e->getMessage(),
                ]);

                SyncLog::create([
                    'direction' => 'main->node',
                    'target' => $node['name'],
                    'status' => 'pending',
                    'message' => 'Nodo caído. Actualización diferida.',
                    'timestamp' => now(),
                ]);
                // 🟩 SE GUARDÓ EN EL RESPALDO LOCAL
                return response()->json([
                    'message' => "La información se guardó en el respaldo local. El nodo {$node['name']} no respondió.",
                    'nodo'  => false,
                    'local' => true,
                    'id' => $id,
                ], 202);
            }

            // 🟥 NO SE PUDO ACTUALIZAR NI EL NODO NI EL RESPALDO
            return response()->json([
                'message' => "Fallo crítico. No se pudo actualizar ni el nodo {$node['name']} ni el respaldo local.",
                'nodo'  => false,
                'local' => false,
            ], 500);
        }
    }
    /**
     * Eliminar un producto en el nodo correspondiente
     * Ejemplo: PUT /api/gateway/products  { "category": "electronics", ... }
    */
    public function deleteProduct(Request $request, $id)
    {
        $category = $request->input('category');

        if (!$category) {
            return response()->json(['error' => 'Debe especificar la categoría del producto'], 400);
        }

        try {
            $node = $this->balancer->resolveNode($category);
            // Enviar POST autenticado con la API Key del nodo
            $response = Http::withHeaders([
                'X-Internal-Key' => $node['key'],
            ])->delete("{$node['url']}/api/products/{$id}", $request->all());
            return response()->json($response->json(), $response->status());
        } catch (\Throwable $e) {

            // Intentamos actualizar respaldo local
            $backupUpdated = $this->deleteBackup($category, $id);

            if ($backupUpdated) {
                PendingReplication::create([
                    'node' => $node['name'],
                    'payload' => [
                        'operation' => 'delete',
                        'product' => [
                            'id' => $id,
                        ]
                    ],
                    'status' => 'pending',
                    'attempts' => 0,
                    'last_error' => $e->getMessage(),
                ]);

                SyncLog::create([
                    'direction' => 'main->node',
                    'target' => $node['name'],
                    'status' => 'pending',
                    'message' => 'Nodo caído. Eliminación diferida.',
                    'timestamp' => now(),
                ]);
                // 🟩 SE ELIMINO EN EL RESPALDO LOCAL
                return response()->json([
                    'message' => "La información se eliminó en el respaldo local. El nodo {$node['name']} no respondió.",
                    'nodo'  => false,
                    'local' => true,
                    'id' => $id,
                ], 202);
            }

            // 🟥 NO SE PUDO Eliminar NI EN EL NODO NI EN EL RESPALDO
            return response()->json([
                'message' => "Fallo crítico. No se pudo eliminó ni el nodo {$node['name']} ni el respaldo local.",
                'nodo'  => false,
                'local' => false,
            ], 500);
        }
    }
    /**
     * Verifica el estado de un nodo
     * Ejemplo: GET /api/gateway/status?category=home
    */
    public function checkNodeStatus(Request $request)
    {
        $category = $request->query('category');

        if (!$category) {
            return response()->json(['error' => 'Debe especificar la categoría.'], 400);
        }

        try {
            $node = $this->balancer->resolveNode($category);

            $response = Http::withHeaders([
                'X-Internal-Key' => $node['key'],
            ])->timeout(3)->get("{$node['url']}/health");

            return response()->json([
                'status' => $response->successful() ? 'online' : 'offline',
                'node'   => $category,
                'url'    => $node['url'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'offline',
                'node' => $category,
                'error' => $e->getMessage(),
            ], 503);
        }
    }

    private function getBackupProducts(string $category)
    {
        try {
            $mongo = DB::connection('mongodb');
            $products = $mongo->table("replicated_products_{$category}")->get();
            if ($products->isEmpty())
            {
                return [
                    'error' => true,
                    'message' => "Nodo inaccesible y no hay respaldo local disponible",
                    'products' => []
                ];
            }
            return [
                'error' => false,
                'from_backup' => true,
                'message' => "✅ Datos obtenidos desde respaldo local replicated_products_{$category}",
                'products' => $products
            ];
        }
        catch (\Throwable $backupError)
        {
            Log::error("❌ Error obteniendo respaldo local: " . $e->getMessage());
            return [
                'error' => true,
                'message' => 'Error crítico obteniendo productos desde respaldo local',
                'details' => $e->getMessage()
            ];
        }
    }

    private function getBackupProduct(string $category, string $id)
    {
        try {
            $repo = ReplicatedProduct::forCategory($category);
            $product = $repo->find($id);
            if (!$product) {
                return [
                    'error' => true,
                    'message' => "Nodo inaccesible y no se encontro producto",
                    'product' => []
                ];
            }
            return [
                'error' => false,
                'from_backup' => true,
                'message' => "✅ Datos obtenidos desde respaldo local replicated_products_{$category}",
                'product' => $product
            ];
        } catch (\Throwable $e) {
            Log::error("❌ Error obteniendo respaldo local: " . $e->getMessage());
            return [
                'error' => true,
                'message' => 'Error crítico obteniendo productos desde respaldo local',
                'details' => $e->getMessage()
            ];
        }
    }

    private function storeBackup(string $category, array $data)
    {
        try {
            $repo = ReplicatedProduct::forCategory($category);
            $created = $repo->create($data);
            return $created->_id ?? null;
        } catch (\Throwable $e) {
            Log::error("❌ Error al crear respaldo local: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza un producto del respaldo local.
     */
    private function updateBackup(string $category, string $id, array $product)
    {
        try {
            $repo = ReplicatedProduct::forCategory($category);
            $repo->updateOrCreate(
                ['_id' => (string) $id],
                $product
            );
            return true;
        } catch (\Throwable $e) {
            Log::error("❌ Error al actualizar respaldo local: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un producto del respaldo local.
     */
    private function deleteBackup(string $category, string $id)
    {
        try {
            $repo = ReplicatedProduct::forCategory($category);
            // Buscar producto antes de eliminar
            $found = $repo->where('_id', (string) $id)->first();
            // Log del producto encontrado (si existe)
            if ($found) {
                \Log::info('Producto encontrado para eliminar', [
                    'producto' => $found->toArray()
                ]);
                // Eliminar el producto
                $repo->where('_id', (string) $id)->delete();
                return true;
            } else {
                \Log::warning('Producto no encontrado para eliminar', [
                    'id_busqueda' => (string) $id
                ]);
            }
            return false;
        } catch (\Throwable $e) {
            Log::error("❌ Error al eliminar respaldo local: " . $e->getMessage());
            return false;
        }
    }
}
