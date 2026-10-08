<?php

namespace Modules\Catalog\Contracts;

/**
 * Immutable data transfer object that crosses the module boundary.
 * Other modules receive this instead of the Eloquent Product model.
 */
final readonly class ProductData
{
    public function __construct(
        public int $id,
        public string $name,
        public int $priceCents,
        public int $stock,
    ) {}
}
