<?php

namespace Modules\Orders\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Catalog\Contracts\Exceptions\InsufficientStock;
use Modules\Catalog\Contracts\Exceptions\ProductNotFound;
use Modules\Orders\Actions\PlaceOrder;
use Modules\Orders\Http\Requests\PlaceOrderRequest;

final class OrderController extends Controller
{
    public function store(PlaceOrderRequest $request, PlaceOrder $placeOrder): JsonResponse
    {
        try {
            $order = $placeOrder(
                $request->integer('product_id'),
                $request->integer('quantity'),
            );
        } catch (ProductNotFound $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (InsufficientStock $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'data' => [
                'id' => $order->id,
                'product_id' => $order->product_id,
                'quantity' => $order->quantity,
                'unit_price_cents' => $order->unit_price_cents,
                'total_cents' => $order->total_cents,
            ],
        ], 201);
    }
}
