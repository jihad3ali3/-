<?php

namespace Modules\Catalog\Services;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Modules\Catalog\Contracts\Exceptions\InsufficientStock;
use Modules\Catalog\Contracts\Exceptions\ProductNotFound;
use Modules\Catalog\Contracts\ProductCatalog;
use Modules\Catalog\Contracts\ProductData;
use Modules\Catalog\Models\Product;

final class EloquentProductCatalog implements ProductCatalog
{
    public function all(): array
    {
        return Product::query()
            ->orderBy('id')
            ->get()
            ->map(fn (Product $product) => $this->toData($product))
            ->all();
    }

    public function find(int $productId): ?ProductData
    {
        $product = Product::query()->find($productId);

        return $product ? $this->toData($product) : null;
    }

    public function create(string $name, int $priceCents, int $stock): ProductData
    {
        $product = Product::query()->create([
            'name' => $name,
            'price_cents' => $priceCents,
            'stock' => $stock,
        ]);

        return $this->toData($product);
    }

    public function reserve(int $productId, int $quantity): ProductData
    {
        if ($quantity < 1) {
            throw new InvalidArgumentException('Quantity must be at least 1.');
        }

        return DB::transaction(function () use ($productId, $quantity) {
            $product = Product::query()->lockForUpdate()->find($productId);

            if ($product === null) {
                throw ProductNotFound::withId($productId);
            }

            if ($product->stock < $quantity) {
                throw InsufficientStock::forProduct($productId, $quantity, $product->stock);
            }

            $product->decrement('stock', $quantity);

            return $this->toData($product);
        });
    }

    private function toData(Product $product): ProductData
    {
        return new ProductData(
            id: $product->id,
            name: $product->name,
            priceCents: $product->price_cents,
            stock: $product->stock,
        );
    }
}
