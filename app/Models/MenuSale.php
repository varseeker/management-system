<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuSale extends Model
{
    protected $fillable = [
        'menu_id',
        'user_id',
        'quantity',
        'note',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
