<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GlobalProductsController extends Controller
{
    // 🟢 Listar todos los productos combinados desde todas las colecciones por categoría
    public function index()
    {
        try {
            // Cada colección representa una categoría/nodo replicado
            $categories = ['clothes', 'electronics', 'home'];
            $allProducts = [];

            $mongo = DB::connection('mongodb');

            foreach ($categories as $category) {
                try {
                    $products = $mongo->table("replicated_products_{$category}")->get();
                    $allProducts = array_merge($allProducts, $products->toArray());
                } catch (\Exception $e) {
                    \Log::warning("No se pudo leer la colección replicated_products_{$category}", [
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return response()->json([
                'total' => count($allProducts),
                'products' => $allProducts
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al obtener productos globales', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'No se pudo obtener la lista de productos globales.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // 🟡 Listar productos de una sola categoría (colección específica)
    public function byCategory($category)
    {
        try {
            $validCategories = ['clothes', 'electronics', 'home'];

            if (!in_array($category, $validCategories)) {
                return response()->json([
                    'error' => "Categoría inválida. Debe ser una de: " . implode(', ', $validCategories)
                ], 400);
            }

            $products = DB::connection('mongodb')
                ->table("products_{$category}")
                ->get();

            return response()->json([
                'category' => $category,
                'count' => $products->count(),
                'products' => $products
            ]);
        } catch (\Exception $e) {
            \Log::error("Error al obtener productos de la categoría {$category}", [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'No se pudo obtener los productos de la categoría.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
