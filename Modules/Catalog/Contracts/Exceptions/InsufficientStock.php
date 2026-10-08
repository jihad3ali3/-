<?php

namespace Modules\Catalog\Contracts\Exceptions;

use RuntimeException;

final class InsufficientStock extends RuntimeException
{
    public static function forProduct(int $productId, int $requested, int $available): self
    {
        return new self("Product [{$productId}] has {$available} unit(s) left, {$requested} requested.");
    }
}
