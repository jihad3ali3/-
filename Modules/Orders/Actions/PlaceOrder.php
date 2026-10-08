<?php

namespace Modules\Orders\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Catalog\Contracts\ProductCatalog;
use Modules\Orders\Contracts\Events\OrderPlaced;
use Modules\Orders\Models\Order;

final class PlaceOrder
{
    public function __construct(private ProductCatalog $catalog) {}

    /**
     * @throws \Modules\Catalog\Contracts\Exceptions\ProductNotFound
     * @throws \Modules\Catalog\Contracts\Exceptions\InsufficientStock
     */
    public function __invoke(int $productId, int $quantity): Order
    {
        $order = DB::transaction(function () use ($productId, $quantity) {
            // Stock is taken through the Catalog contract, never by touching its tables.
            $product = $this->catalog->reserve($productId, $quantity);

            // Snapshot the price: later price changes must not alter past orders.
            return Order::query()->create([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price_cents' => $product->priceCents,
                'total_cents' => $product->priceCents * $quantity,
            ]);
        });

        // Dispatched after the transaction closure has returned, so listeners
        // never see an order that might still be rolled back.
        event(new OrderPlaced(
            orderId: $order->id,
            productId: $order->product_id,
            quantity: $order->quantity,
            totalCents: $order->total_cents,
        ));

        return $order;
    }
}
