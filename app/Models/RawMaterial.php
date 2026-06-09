<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RawMaterial extends Model
{
    protected $fillable = [
        'code',
        'name',
        'stock',
        'unit',
        'description',
    ];

    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'menu_raw_material')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'supplier_raw_material')
            ->withPivot('price', 'quality')
            ->withTimestamps();
    }
}
