<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\RawMaterial;
use Illuminate\Database\Seeder;

class StockMenuSeeder extends Seeder
{
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
                'recipe' => ['Indomie' => 1, 'Telur' => 1],
            ],
            [
                'code' => 'MN-002',
                'name' => 'Indomie Double',
                'description' => 'Dua bungkus mie instan untuk porsi ekstra lapar',
                'recipe' => ['Indomie' => 2],
            ],
            [
                'code' => 'MN-003',
                'name' => 'Indomie Kapal Api',
                'description' => 'Kombinasi mie instan dan kopi hitam sachet',
                'recipe' => ['Indomie' => 1, 'Kapal Api' => 1],
            ],
            [
                'code' => 'MN-004',
                'name' => 'Nutrisari Dingin',
                'description' => 'Minuman jeruk segar dengan es batu',
                'recipe' => ['Nutrisari' => 1, 'Es Batu' => 1],
            ],
            [
                'code' => 'MN-005',
                'name' => 'Teh Manis Panas',
                'description' => 'Teh celup manis hangat khas warung',
                'recipe' => ['Teh Celup' => 1, 'Gula' => 1],
            ],
            [
                'code' => 'MN-006',
                'name' => 'Kopi Susu',
                'description' => 'Kopi kapal api dengan susu kental manis',
                'recipe' => ['Kapal Api' => 1, 'Susu Kental' => 1, 'Gula' => 1],
            ],
            [
                'code' => 'MN-007',
                'name' => 'Indomie Goreng Spesial',
                'description' => 'Mie goreng dengan telur dan sosis',
                'recipe' => ['Indomie' => 1, 'Telur' => 1, 'Sosis' => 1, 'Minyak Goreng' => 1],
            ],
            [
                'code' => 'MN-008',
                'name' => 'Roti Bakar Keju',
                'description' => 'Roti tawar panggang dengan keju leleh',
                'recipe' => ['Roti Tawar' => 2, 'Keju Slice' => 2, 'Minyak Goreng' => 1],
            ],
            [
                'code' => 'MN-009',
                'name' => 'Nasi Goreng Warkop',
                'description' => 'Nasi goreng kampung dengan telur dan sosis',
                'recipe' => ['Beras' => 1, 'Telur' => 1, 'Sosis' => 1, 'Minyak Goreng' => 1, 'Kapal Api' => 1],
            ],
            [
                'code' => 'MN-010',
                'name' => 'Es Teh Manis',
                'description' => 'Teh manis dingin dengan es batu',
                'recipe' => ['Teh Celup' => 1, 'Gula' => 1, 'Es Batu' => 1],
            ],
            [
                'code' => 'MN-011',
                'name' => 'Pisang Goreng',
                'description' => 'Pisang goreng crispy 3 potong',
                'recipe' => ['Pisang' => 1, 'Minyak Goreng' => 1, 'Gula' => 1],
            ],
            [
                'code' => 'MN-012',
                'name' => 'Sosis Goreng',
                'description' => 'Sosis goreng 2 batang dengan sambal',
                'recipe' => ['Sosis' => 2, 'Minyak Goreng' => 1],
            ],
        ];

        foreach ($menus as $row) {
            $menu = Menu::updateOrCreate(
                ['code' => $row['code']],
                [
                    'name' => $row['name'],
                    'description' => $row['description'],
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
}
