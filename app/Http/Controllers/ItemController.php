<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::latest()->get();

        return view('items.index', compact('items'));
    }

    public function create()
    {
        return view('items.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:items',
            'name' => 'required',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable'
        ]);

        Item::create($validated);

        return redirect()
            ->route('items.index')
            ->with('success', 'Barang berhasil ditambahkan');
    }

    public function edit(Item $item)
    {
        return view('items.edit', compact('item'));
    }

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'code' => 'required|unique:items,code,' . $item->id,
            'name' => 'required',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable'
        ]);

        $item->update($validated);

        return redirect()
            ->route('items.index')
            ->with('success', 'Barang berhasil diperbarui');
    }

    public function destroy(Item $item)
    {
        $item->delete();

        return redirect()
            ->route('items.index')
            ->with('success', 'Barang berhasil dihapus');
    }
}
