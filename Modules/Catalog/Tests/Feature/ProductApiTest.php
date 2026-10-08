<?php

namespace Modules\Catalog\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Catalog\Contracts\ProductCatalog;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_products(): void
    {
        $this->app->make(ProductCatalog::class)->create('Coffee', 1500, 10);

        $this->getJson('/api/catalog/products')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Coffee')
            ->assertJsonPath('data.0.price_cents', 1500);
    }

    public function test_shows_a_product(): void
    {
        $product = $this->app->make(ProductCatalog::class)->create('Tea', 900, 5);

        $this->getJson("/api/catalog/products/{$product->id}")
            ->assertOk()
            ->assertJsonPath('data.name', 'Tea')
            ->assertJsonPath('data.stock', 5);
    }

    public function test_returns_404_for_an_unknown_product(): void
    {
        $this->getJson('/api/catalog/products/999')
            ->assertNotFound();
    }
}
