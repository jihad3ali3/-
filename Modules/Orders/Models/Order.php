<?php

namespace Modules\Orders\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['product_id', 'quantity', 'unit_price_cents', 'total_cents'])]
class Order extends Model
{
    protected $table = 'orders';

    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
            'quantity' => 'integer',
            'unit_price_cents' => 'integer',
            'total_cents' => 'integer',
        ];
    }
}
