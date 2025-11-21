<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class LoadBalancerService
{
    protected $nodes = [];

    public function __construct()
    {
        // URLs y claves de cada nodo
        $this->nodes = [
            'electronics' => [
                'url' => env('ELECTRONICS_API_URL'),
                'key' => env('ELECTRONICS_API_KEY'),
                'name' => env('ELECTRONICS_API_NAME'),
            ],
            'clothes' => [
                'url' => env('CLOTHES_API_URL'),
                'key' => env('CLOTHES_API_KEY'),
                'name' => env('CLOTHES_API_NAME'),
            ],
            'home' => [
                'url' => env('HOME_API_URL'),
                'key' => env('HOME_API_KEY'),
                'name' => env('HOME_API_NAME'),
            ],
        ];
    }

    public function resolveNode($category)
    {
        $category = strtolower($category);

        if (!isset($this->nodes[$category])) {
            throw new \Exception("Categoría no válida o nodo no registrado: $category");
        }

        return $this->nodes[$category];
    }

    public function getAllNodes(): array
    {
        $allNodes = [];

        foreach ($this->nodes as $category => $node) {
            $allNodes[] = array_merge(['category' => $category], $node);
        }

        return $allNodes;
    }
}
