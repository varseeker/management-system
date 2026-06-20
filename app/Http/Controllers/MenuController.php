<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\MenuSale;
use App\Models\RawMaterial;
use App\Support\MenuImageStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

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
        try {
            $validated = $this->validateMenuRequest($request);

            $imagePath = null;

            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $imagePath = MenuImageStorage::store($request->file('image'), $validated['code']);
            }

            $menu = Menu::create([
                'code' => $validated['code'],
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'price' => $validated['price'],
                'category' => $validated['category'],
                'most_ordered' => $request->boolean('most_ordered'),
                'image_path' => $imagePath,
            ]);

            $this->syncIngredients($menu, $validated['ingredients']);
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors([
                    'menu' => 'Gagal menambahkan menu. '.$this->friendlyErrorMessage($exception),
                ]);
        }

        return redirect()
            ->route('menus.index')
            ->with('success', 'Menu berhasil ditambahkan. POS akan sync otomatis.');
    }

    public function edit(Menu $menu)
    {
        $menu->load('rawMaterials');
        $rawMaterials = RawMaterial::orderBy('name')->get();

        return view('menus.edit', compact('menu', 'rawMaterials'));
    }

    public function update(Request $request, Menu $menu)
    {
        try {
            $validated = $this->validateMenuRequest($request, $menu);
            $imagePath = $this->resolveMenuImagePath($request, $menu, $validated);

            $menu->update([
                'code' => $validated['code'],
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'price' => $validated['price'],
                'category' => $validated['category'],
                'most_ordered' => $request->boolean('most_ordered'),
                'is_active' => $request->boolean('is_active'),
                'image_path' => $imagePath,
            ]);

            $this->syncIngredients($menu, $validated['ingredients']);
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors([
                    'menu' => 'Gagal memperbarui menu. '.$this->friendlyErrorMessage($exception),
                ]);
        }

        return redirect()
            ->route('menus.index')
            ->with('success', 'Menu diperbarui. POS akan sync otomatis.');
    }

    public function destroy(Menu $menu)
    {
        try {
            MenuImageStorage::delete($menu->image_path);
            $menu->delete();
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'menu' => 'Gagal menghapus menu. '.$this->friendlyErrorMessage($exception),
            ]);
        }

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

    private function validateMenuRequest(Request $request, ?Menu $menu = null): array
    {
        if ($request->has('price')) {
            $request->merge([
                'price' => (int) preg_replace('/\D/', '', (string) $request->input('price')),
            ]);
        }

        $codeRule = $menu
            ? 'required|unique:menus,code,'.$menu->id
            : 'required|unique:menus';

        return $request->validate([
            'code' => $codeRule,
            'name' => 'required',
            'description' => 'nullable',
            'price' => 'required|integer|min:0',
            'category' => 'required|in:Snack,Non-coffee,Coffee',
            'is_active' => 'nullable|boolean',
            'most_ordered' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.raw_material_id' => 'required|exists:raw_materials,id',
            'ingredients.*.quantity' => 'required|integer|min:1',
        ]);
    }

    private function resolveMenuImagePath(Request $request, Menu $menu, array $validated): ?string
    {
        $imagePath = $menu->image_path;

        if (! $request->hasFile('image') || ! $request->file('image')->isValid()) {
            return $imagePath;
        }

        MenuImageStorage::delete($menu->image_path);

        return MenuImageStorage::store($request->file('image'), $validated['code']);
    }

    private function friendlyErrorMessage(Throwable $exception): string
    {
        $message = trim($exception->getMessage());

        if ($message === '') {
            return 'Silakan coba lagi.';
        }

        if (str_contains($message, 'SQLSTATE')) {
            return 'Terjadi masalah database. Pastikan migrasi terbaru sudah dijalankan.';
        }

        return $message;
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
