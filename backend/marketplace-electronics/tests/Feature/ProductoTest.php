<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;

class ProductoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Limpiar la colección antes de cada test
        Product::truncate();
    }

    /** @test */
    public function puede_listar_productos()
    {
        Product::factory()->count(3)->create();

        $response = $this->get('/api/products');

        $response->assertStatus(200)
                 ->assertJsonCount(3);
    }

    /** @test */
    public function puede_crear_un_producto()
    {
        $data = [
            'name' => 'Producto de prueba',
            'description' => 'Descripción del producto de prueba',
            'price' => 15000,
            'stock' => 10,
            'category' => 'Categoría de prueba',
            'attributes' => [1, 2, 3],
        ];

        $response = $this->post('/api/products', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['name' => 'Producto de prueba']);

        $this->assertEquals(1, Product::where('name', 'Producto de prueba')->count());
    }

    /** @test */
    public function puede_actualizar_un_producto()
    {
        $producto = Product::factory()->create();

        $data = ['name' => 'Producto actualizado'];
         dump($producto->_id);
        $response = $this->put("/api/products/{$producto->_id}", $data);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Producto actualizado']);

        $this->assertEquals(1, Product::where('name', 'Producto actualizado')->count());
    }

    /** @test */
    public function puede_eliminar_un_producto()
    {
        $producto = Product::factory()->create();

        $response = $this->delete("/api/products/{$producto->_id}");

        $response->assertStatus(200);

        $this->assertEquals(0, Product::where('_id', $producto->_id)->count());
    }
}
