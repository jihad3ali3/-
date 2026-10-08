<?php

namespace Modules\Catalog\Contracts;

use Modules\Catalog\Contracts\Exceptions\InsufficientStock;
use Modules\Catalog\Contracts\Exceptions\ProductNotFound;

/**
 * Public API of the Catalog module.
 *
 * Other modules must depend on this interface only. The implementation
 * lives in the module's Services folder and is bound in CatalogServiceProvider.
 */
interface ProductCatalog
{
    /**
     * @return list<ProductData>
     */
    public function all(): array;

    public function find(int $productId): ?ProductData;

    public function create(string $name, int $priceCents, int $stock): ProductData;

    /**
     * Atomically takes $quantity units out of stock and returns the product
     * (including its current price) so the caller can snapshot it.
     *
     * @throws ProductNotFound
     * @throws InsufficientStock
     */
    public function reserve(int $productId, int $quantity): ProductData;
}
