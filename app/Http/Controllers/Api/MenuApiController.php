<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\JsonResponse;

class MenuApiController extends Controller
{
    public function index(): JsonResponse
    {
        $menus = Menu::with('rawMaterials')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Menu $menu) => [
                'id' => $menu->id,
                'code' => $menu->code,
                'name' => $menu->name,
                'description' => $menu->description,
                'price' => (int) $menu->price,
                'category' => $menu->category,
                'most_ordered' => (bool) $menu->most_ordered,
                'is_active' => $menu->is_active,
                'image_url' => $menu->imageUrl(),
                'available_servings' => $menu->maxServings(),
                'ingredients' => $menu->rawMaterials->map(fn ($material) => [
                    'raw_material_id' => $material->id,
                    'name' => $material->name,
                    'quantity_per_serving' => (int) $material->pivot->quantity,
                    'stock' => (int) $material->stock,
                    'unit' => $material->unit,
                ])->values(),
            ]);

        return response()->json([
            'data' => $menus,
        ]);
    }
}
