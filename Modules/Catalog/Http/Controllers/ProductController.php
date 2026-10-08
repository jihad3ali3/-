<?php

namespace Modules\Catalog\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Catalog\Contracts\ProductCatalog;
use Modules\Catalog\Contracts\ProductData;

final class ProductController extends Controller
{
    public function index(ProductCatalog $catalog): JsonResponse
    {
        return response()->json([
            'data' => array_map(fn (ProductData $product) => $this->present($product), $catalog->all()),
        ]);
    }

    public function show(int $product, ProductCatalog $catalog): JsonResponse
    {
        $data = $catalog->find($product);

        if ($data === null) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        return response()->json(['data' => $this->present($data)]);
    }

    /**
     * @return array{id: int, name: string, price_cents: int, stock: int}
     */
    private function present(ProductData $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'price_cents' => $product->priceCents,
            'stock' => $product->stock,
        ];
    }
}
