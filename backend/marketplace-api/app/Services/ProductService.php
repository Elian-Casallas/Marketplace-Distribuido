<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ProductService
{
    protected $nodes = [];

    public function __construct()
    {
        // URLs y claves de cada nodo
        $this->nodes = [
            'electronics' => [
                'url' => env('ELECTRONICS_API_URL'),
                'key' => env('ELECTRONICS_API_KEY'),
            ],
            'clothes' => [
                'url' => env('CLOTHES_API_URL'),
                'key' => env('CLOTHES_API_KEY'),
            ],
            'home' => [
                'url' => env('HOME_API_URL'),
                'key' => env('HOME_API_KEY'),
            ],
        ];
    }

    public function getAllProducts()
    {
        $results = [];

        foreach ($this->nodes as $category => $node) {
            try {
                $response = Http::withHeaders([
                        'X-Internal-Key' => $node['key'],
                    ])
                    ->get($node['url'] . '/api/products');

                if ($response->successful()) {
                    $results[$category] = $response->json();
                } else {
                    $results[$category] = [
                        'error' => 'Error al consultar ' . $category,
                        'status' => $response->status(),
                    ];
                }

            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                $results[$category] = [
                    'error' => 'Nodo no disponible: ' . $category,
                    'message' => $e->getMessage(),
                ];
            } catch (\Exception $e) {
                $results[$category] = [
                    'error' => 'Error desconocido en ' . $category,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}
