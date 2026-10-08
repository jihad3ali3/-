<?php

namespace Modules\Catalog\Contracts\Exceptions;

use RuntimeException;

final class ProductNotFound extends RuntimeException
{
    public static function withId(int $productId): self
    {
        return new self("Product [{$productId}] was not found.");
    }
}
