<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Supplier extends Model
{
    public const QUALITY_LABELS = [
        'excellent' => 'Sangat Baik',
        'good' => 'Baik',
        'fair' => 'Cukup',
        'poor' => 'Kurang',
    ];

    protected $fillable = [
        'name',
        'location',
        'phone',
        'note',
    ];

    public function rawMaterials(): BelongsToMany
    {
        return $this->belongsToMany(RawMaterial::class, 'supplier_raw_material')
            ->withPivot('price', 'quality')
            ->withTimestamps();
    }

    public static function qualityLabel(string $quality): string
    {
        return self::QUALITY_LABELS[$quality] ?? $quality;
    }
}
