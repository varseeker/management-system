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
        'price',
        'category',
        'image_path',
        'most_ordered',
        'is_bundle',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'most_ordered' => 'boolean',
            'is_bundle' => 'boolean',
            'price' => 'integer',
        ];
    }

    public function imageUrl(): ?string
    {
        return \App\Support\MenuImageStorage::publicUrl($this->image_path);
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

    public function maxServings(): int
    {
        if ($this->rawMaterials->isEmpty()) {
            return 0;
        }

        $max = PHP_INT_MAX;

        foreach ($this->stockRequirements(1) as $row) {
            $required = (int) $row['required'];

            if ($required <= 0) {
                continue;
            }

            $max = min($max, intdiv((int) $row['material']->stock, $required));
        }

        return $max === PHP_INT_MAX ? 0 : $max;
    }
}
