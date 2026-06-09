<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Menu extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function rawMaterials(): BelongsToMany
    {
        return $this->belongsToMany(RawMaterial::class, 'menu_raw_material')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function sales(): HasMany
    {
        return $this->hasMany(MenuSale::class);
    }

    public function stockRequirements(int $servings = 1): Collection
    {
        return $this->rawMaterials->map(fn ($material) => [
            'material' => $material,
            'required' => $material->pivot->quantity * $servings,
        ]);
    }

    public function hasEnoughStock(int $servings = 1): bool
    {
        foreach ($this->stockRequirements($servings) as $row) {
            if ($row['required'] > $row['material']->stock) {
                return false;
            }
        }

        return $this->rawMaterials->isNotEmpty();
    }

    public function consumeStock(int $servings = 1): void
    {
        foreach ($this->stockRequirements($servings) as $row) {
            $row['material']->decrement('stock', $row['required']);
        }
    }
}
