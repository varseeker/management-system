<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\MenuSale;
use App\Models\RawMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::with('rawMaterials')->latest()->get();

        return view('menus.index', compact('menus'));
    }

    public function create()
    {
        $rawMaterials = RawMaterial::orderBy('name')->get();

        return view('menus.create', compact('rawMaterials'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:menus',
            'name' => 'required',
            'description' => 'nullable',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.raw_material_id' => 'required|exists:raw_materials,id',
            'ingredients.*.quantity' => 'required|integer|min:1',
        ]);

        $menu = Menu::create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $this->syncIngredients($menu, $validated['ingredients']);

        return redirect()
            ->route('menus.index')
            ->with('success', 'Menu berhasil ditambahkan');
    }

    public function edit(Menu $menu)
    {
        $menu->load('rawMaterials');
        $rawMaterials = RawMaterial::orderBy('name')->get();

        return view('menus.edit', compact('menu', 'rawMaterials'));
    }

    public function update(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'code' => 'required|unique:menus,code,' . $menu->id,
            'name' => 'required',
            'description' => 'nullable',
            'is_active' => 'nullable|boolean',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.raw_material_id' => 'required|exists:raw_materials,id',
            'ingredients.*.quantity' => 'required|integer|min:1',
        ]);

        $menu->update([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->syncIngredients($menu, $validated['ingredients']);

        return redirect()
            ->route('menus.index')
            ->with('success', 'Menu berhasil diperbarui');
    }

    public function destroy(Menu $menu)
    {
        $menu->delete();

        return redirect()
            ->route('menus.index')
            ->with('success', 'Menu berhasil dihapus');
    }

    public function sellIndex()
    {
        $menus = Menu::with('rawMaterials')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $sales = MenuSale::with(['menu', 'user'])->latest()->get();

        $menuFilterOptions = $sales->pluck('menu.name')->unique()->sort()->values();
        $userFilterOptions = $sales->pluck('user.name')->unique()->sort()->values();

        return view('menus.sell', compact('menus', 'sales', 'menuFilterOptions', 'userFilterOptions'));
    }

    public function sell(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'note' => 'nullable',
        ]);

        $menu->load('rawMaterials');

        if ($menu->rawMaterials->isEmpty()) {
            return back()->withErrors([
                'menu' => 'Menu belum memiliki resep bahan baku',
            ]);
        }

        if (! $menu->hasEnoughStock($validated['quantity'])) {
            return back()->withErrors([
                'stock' => 'Stok bahan baku tidak mencukupi untuk ' . $validated['quantity'] . ' porsi',
            ]);
        }

        DB::transaction(function () use ($menu, $validated) {
            $menu->consumeStock($validated['quantity']);

            MenuSale::create([
                'menu_id' => $menu->id,
                'user_id' => Auth::id(),
                'quantity' => $validated['quantity'],
                'note' => $validated['note'] ?? null,
            ]);
        });

        return redirect()
            ->route('menus.sell.index')
            ->with('success', 'Stok berhasil dikurangi untuk ' . $menu->name);
    }

    private function syncIngredients(Menu $menu, array $ingredients): void
    {
        $sync = [];

        foreach ($ingredients as $row) {
            $id = (int) $row['raw_material_id'];
            $sync[$id] = ['quantity' => (int) $row['quantity']];
        }

        $menu->rawMaterials()->sync($sync);
    }
}
