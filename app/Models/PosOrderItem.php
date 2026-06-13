<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosOrderItem extends Model
{
    protected $fillable = [
        'pos_order_id',
        'menu_code',
        'menu_name',
        'menu_price',
        'quantity',
        'variant',
        'size',
        'ice',
        'sugar',
        'subtotal',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'menu_price' => 'integer',
            'quantity' => 'integer',
            'subtotal' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(PosOrder::class, 'pos_order_id');
    }
}
