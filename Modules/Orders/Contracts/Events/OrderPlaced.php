<?php

namespace Modules\Orders\Contracts\Events;

/**
 * Public event: other modules may listen to it.
 *
 * It describes a fact that already happened. It is dispatched by PlaceOrder
 * only after its transaction has finished, never from inside it.
 */
final readonly class OrderPlaced
{
    public function __construct(
        public int $orderId,
        public int $productId,
        public int $quantity,
        public int $totalCents,
    ) {}
}
