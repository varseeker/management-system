<?php

namespace App\Http\Controllers;

use App\Models\RawMaterial;
use Illuminate\Http\Request;

class RawMaterialController extends Controller
{
    public function index()
    {
        $rawMaterials = RawMaterial::latest()->get();

        return view('raw-materials.index', compact('rawMaterials'));
    }

    public function create()
    {
        return view('raw-materials.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:raw_materials',
            'name' => 'required',
            'stock' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'description' => 'nullable',
        ]);

        RawMaterial::create($validated);

        return redirect()
            ->route('raw-materials.index')
            ->with('success', 'Bahan baku berhasil ditambahkan');
    }

    public function edit(RawMaterial $rawMaterial)
    {
        return view('raw-materials.edit', compact('rawMaterial'));
    }

    public function update(Request $request, RawMaterial $rawMaterial)
    {
        $validated = $request->validate([
            'code' => 'required|unique:raw_materials,code,' . $rawMaterial->id,
            'name' => 'required',
            'stock' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'description' => 'nullable',
        ]);

        $rawMaterial->update($validated);

        return redirect()
            ->route('raw-materials.index')
            ->with('success', 'Bahan baku berhasil diperbarui');
    }

    public function destroy(RawMaterial $rawMaterial)
    {
        $rawMaterial->delete();

        return redirect()
            ->route('raw-materials.index')
            ->with('success', 'Bahan baku berhasil dihapus');
    }
}
