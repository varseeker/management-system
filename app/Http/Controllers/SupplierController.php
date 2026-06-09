<?php

namespace App\Http\Controllers;

use App\Models\RawMaterial;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::with('rawMaterials')->latest()->get();

        return view('suppliers.index', compact('suppliers'));
    }

    public function show(Supplier $supplier)
    {
        $supplier->load('rawMaterials');

        return view('suppliers.show', compact('supplier'));
    }

    public function create()
    {
        $rawMaterials = RawMaterial::orderBy('name')->get();

        return view('suppliers.create', compact('rawMaterials'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateSupplier($request);

        $supplier = Supplier::create([
            'name' => $validated['name'],
            'location' => $validated['location'],
            'phone' => $validated['phone'] ?? null,
            'note' => $validated['note'] ?? null,
        ]);

        $this->syncOffers($supplier, $validated['offers']);

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Pemasok berhasil ditambahkan');
    }

    public function edit(Supplier $supplier)
    {
        $supplier->load('rawMaterials');
        $rawMaterials = RawMaterial::orderBy('name')->get();

        return view('suppliers.edit', compact('supplier', 'rawMaterials'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $this->validateSupplier($request);

        $supplier->update([
            'name' => $validated['name'],
            'location' => $validated['location'],
            'phone' => $validated['phone'] ?? null,
            'note' => $validated['note'] ?? null,
        ]);

        $this->syncOffers($supplier, $validated['offers']);

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Pemasok berhasil diperbarui');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Pemasok berhasil dihapus');
    }

    private function validateSupplier(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'note' => 'nullable',
            'offers' => 'required|array|min:1',
            'offers.*.raw_material_id' => 'required|exists:raw_materials,id',
            'offers.*.price' => 'required|numeric|min:0',
            'offers.*.quality' => ['required', Rule::in(array_keys(Supplier::QUALITY_LABELS))],
        ]);
    }

    private function syncOffers(Supplier $supplier, array $offers): void
    {
        $sync = [];

        foreach ($offers as $row) {
            $id = (int) $row['raw_material_id'];
            $sync[$id] = [
                'price' => $row['price'],
                'quality' => $row['quality'],
            ];
        }

        $supplier->rawMaterials()->sync($sync);
    }
}
