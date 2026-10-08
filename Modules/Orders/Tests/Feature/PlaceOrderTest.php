<?php

namespace Modules\Orders\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Catalog\Contracts\ProductCatalog;
use Modules\Orders\Contracts\Events\OrderPlaced;
use Modules\Orders\Models\Order;
use Tests\TestCase;

class PlaceOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_places_an_order_and_takes_stock(): void
    {
        Event::fake([OrderPlaced::class]);

        $catalog = $this->app->make(ProductCatalog::class);
        $product = $catalog->create('Coffee', 1500, 10);

        $this->postJson('/api/orders', [
            'product_id' => $product->id,
            'quantity' => 3,
        ])
            ->assertCreated()
            ->assertJsonPath('data.total_cents', 4500)
            ->assertJsonPath('data.unit_price_cents', 1500);

        $this->assertDatabaseHas('orders', [
            'product_id' => $product->id,
            'quantity' => 3,
            'total_cents' => 4500,
        ]);

        $this->assertSame(7, $catalog->find($product->id)->stock);

        Event::assertDispatched(OrderPlaced::class, fn (OrderPlaced $event) => $event->totalCents === 4500);
    }

    public function test_rejects_an_order_when_stock_is_insufficient(): void
    {
        $catalog = $this->app->make(ProductCatalog::class);
        $product = $catalog->create('Tea', 900, 2);

        $this->postJson('/api/orders', [
            'product_id' => $product->id,
            'quantity' => 5,
        ])->assertUnprocessable();

        $this->assertSame(2, $catalog->find($product->id)->stock);
        $this->assertSame(0, Order::query()->count());
    }

    public function test_returns_404_for_an_unknown_product(): void
    {
        $this->postJson('/api/orders', [
            'product_id' => 999,
            'quantity' => 1,
        ])->assertNotFound();

        $this->assertSame(0, Order::query()->count());
    }

    public function test_validates_the_request(): void
    {
        $this->postJson('/api/orders', [
            'product_id' => 1,
            'quantity' => 0,
        ])->assertUnprocessable()->assertJsonValidationErrors(['quantity']);
    }
}
