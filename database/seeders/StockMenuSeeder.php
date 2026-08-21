<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\RawMaterial;
use Illuminate\Database\Seeder;

class StockMenuSeeder extends Seeder
{
    /** Bahan khas makanan / snack */
    private const FOOD_MATERIALS = [
        'Indomie',
        'Telur',
        'Sosis',
        'Keju Slice',
        'Pisang',
        'Roti Tawar',
        'Beras',
        'Minyak Goreng',
    ];

    /** Bahan khas minuman */
    private const DRINK_MATERIALS = [
        'Kapal Api',
        'Nutrisari',
        'Teh Celup',
        'Susu Kental',
        'Es Batu',
    ];

    public function run(): void
    {
        $materials = [
            ['code' => 'BB-001', 'name' => 'Telur', 'stock' => 120, 'unit' => 'butir'],
            ['code' => 'BB-002', 'name' => 'Indomie', 'stock' => 200, 'unit' => 'pcs'],
            ['code' => 'BB-003', 'name' => 'Kapal Api', 'stock' => 150, 'unit' => 'sachet'],
            ['code' => 'BB-004', 'name' => 'Nutrisari', 'stock' => 100, 'unit' => 'sachet'],
            ['code' => 'BB-005', 'name' => 'Gula', 'stock' => 80, 'unit' => 'kg'],
            ['code' => 'BB-006', 'name' => 'Susu Kental', 'stock' => 60, 'unit' => 'kaleng'],
            ['code' => 'BB-007', 'name' => 'Teh Celup', 'stock' => 200, 'unit' => 'sachet'],
            ['code' => 'BB-008', 'name' => 'Beras', 'stock' => 50, 'unit' => 'kg'],
            ['code' => 'BB-009', 'name' => 'Minyak Goreng', 'stock' => 40, 'unit' => 'liter'],
            ['code' => 'BB-010', 'name' => 'Sosis', 'stock' => 90, 'unit' => 'pcs'],
            ['code' => 'BB-011', 'name' => 'Keju Slice', 'stock' => 70, 'unit' => 'lembar'],
            ['code' => 'BB-012', 'name' => 'Pisang', 'stock' => 60, 'unit' => 'pcs'],
            ['code' => 'BB-013', 'name' => 'Roti Tawar', 'stock' => 45, 'unit' => 'pcs'],
            ['code' => 'BB-014', 'name' => 'Es Batu', 'stock' => 30, 'unit' => 'kg'],
        ];

        $map = [];
        foreach ($materials as $row) {
            $map[$row['name']] = RawMaterial::updateOrCreate(['code' => $row['code']], $row);
        }

        $menus = [
            [
                'code' => 'MN-001',
                'name' => 'Indomie Telur',
                'description' => 'Mie instan rebus dengan telur ceplok — menu andalan warkop',
                'price' => 15000,
                'category' => 'Makanan',
                'recipe' => ['Indomie' => 1, 'Telur' => 1],
            ],
            [
                'code' => 'MN-002',
                'name' => 'Indomie Double',
                'description' => 'Dua bungkus mie instan untuk porsi ekstra lapar',
                'price' => 20000,
                'category' => 'Makanan',
                'recipe' => ['Indomie' => 2],
            ],
            [
                'code' => 'MN-003',
                'name' => 'Indomie Kapal Api',
                'description' => 'Paket mie instan dan kopi hitam sachet',
                'price' => 22000,
                'category' => 'Makanan',
                'is_bundle' => true,
                'recipe' => ['Indomie' => 1, 'Kapal Api' => 1],
            ],
            [
                'code' => 'MN-004',
                'name' => 'Nutrisari Dingin',
                'description' => 'Minuman jeruk segar dengan es batu',
                'price' => 8000,
                'category' => 'Minuman',
                'recipe' => ['Nutrisari' => 1, 'Es Batu' => 1],
            ],
            [
                'code' => 'MN-005',
                'name' => 'Teh Manis Panas',
                'description' => 'Teh celup manis hangat khas warung',
                'price' => 5000,
                'category' => 'Minuman',
                'recipe' => ['Teh Celup' => 1, 'Gula' => 1],
            ],
            [
                'code' => 'MN-006',
                'name' => 'Kopi Susu',
                'description' => 'Kopi kapal api dengan susu kental manis',
                'price' => 12000,
                'category' => 'Minuman',
                'recipe' => ['Kapal Api' => 1, 'Susu Kental' => 1, 'Gula' => 1],
            ],
            [
                'code' => 'MN-007',
                'name' => 'Indomie Goreng Spesial',
                'description' => 'Mie goreng dengan telur dan sosis',
                'price' => 25000,
                'category' => 'Makanan',
                'recipe' => ['Indomie' => 1, 'Telur' => 1, 'Sosis' => 1, 'Minyak Goreng' => 1],
            ],
            [
                'code' => 'MN-008',
                'name' => 'Roti Bakar Keju',
                'description' => 'Roti tawar panggang dengan keju leleh',
                'price' => 18000,
                'category' => 'Makanan',
                'recipe' => ['Roti Tawar' => 2, 'Keju Slice' => 2, 'Minyak Goreng' => 1],
            ],
            [
                'code' => 'MN-009',
                'name' => 'Nasi Goreng Warkop',
                'description' => 'Paket nasi goreng kampung dengan telur, sosis, dan kopi',
                'price' => 28000,
                'category' => 'Makanan',
                'most_ordered' => true,
                'is_bundle' => true,
                'recipe' => ['Beras' => 1, 'Telur' => 1, 'Sosis' => 1, 'Minyak Goreng' => 1, 'Kapal Api' => 1],
            ],
            [
                'code' => 'MN-010',
                'name' => 'Es Teh Manis',
                'description' => 'Teh manis dingin dengan es batu',
                'price' => 6000,
                'category' => 'Minuman',
                'recipe' => ['Teh Celup' => 1, 'Gula' => 1, 'Es Batu' => 1],
            ],
            [
                'code' => 'MN-011',
                'name' => 'Pisang Goreng',
                'description' => 'Pisang goreng crispy 3 potong',
                'price' => 12000,
                'category' => 'Makanan',
                'recipe' => ['Pisang' => 1, 'Minyak Goreng' => 1, 'Gula' => 1],
            ],
            [
                'code' => 'MN-012',
                'name' => 'Sosis Goreng',
                'description' => 'Sosis goreng 2 batang dengan sambal',
                'price' => 15000,
                'category' => 'Makanan',
                'recipe' => ['Sosis' => 2, 'Minyak Goreng' => 1],
            ],
        ];

        foreach ($menus as $row) {
            $isBundle = $row['is_bundle'] ?? $this->recipeIsBundle(array_keys($row['recipe']));

            $menu = Menu::updateOrCreate(
                ['code' => $row['code']],
                [
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'price' => $row['price'],
                    'category' => $isBundle ? null : $row['category'],
                    'most_ordered' => $row['most_ordered'] ?? false,
                    'is_bundle' => $isBundle,
                    'is_active' => true,
                ]
            );

            $sync = [];
            foreach ($row['recipe'] as $materialName => $qty) {
                $sync[$map[$materialName]->id] = ['quantity' => $qty];
            }
            $menu->rawMaterials()->sync($sync);
        }
    }

    /**
     * Paket/bundle: resep memakai bahan makanan dan minuman sekaligus.
     *
     * @param  list<string>  $recipeMaterials
     */
    private function recipeIsBundle(array $recipeMaterials): bool
    {
        $hasFood = false;
        $hasDrink = false;

        foreach ($recipeMaterials as $material) {
            if (in_array($material, self::FOOD_MATERIALS, true)) {
                $hasFood = true;
            }

            if (in_array($material, self::DRINK_MATERIALS, true)) {
                $hasDrink = true;
            }
        }

        return $hasFood && $hasDrink;
    }
}
