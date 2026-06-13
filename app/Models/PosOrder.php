<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosOrder extends Model
{
    protected $fillable = [
        'external_order_id',
        'pos_order_id',
        'customer',
        'total',
        'amount_paid',
        'amount_change',
        'order_status',
        'payment_status',
        'payment_method',
        'payment_reference',
        'cashier_name',
        'source',
        'ordered_at',
    ];

    protected function casts(): array
    {
        return [
            'ordered_at' => 'datetime',
            'total' => 'integer',
            'amount_paid' => 'integer',
            'amount_change' => 'integer',
            'pos_order_id' => 'integer',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(PosOrderItem::class);
    }

    public function displayReference(): string
    {
        if ($this->payment_reference && $this->payment_reference !== '-') {
            return $this->payment_reference;
        }

        return '-';
    }
}
