<?php

namespace Database\Seeders;

use App\Models\RawMaterial;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $materials = RawMaterial::all()->keyBy('name');

        if ($materials->isEmpty()) {
            $this->command?->warn('Jalankan StockMenuSeeder terlebih dahulu.');

            return;
        }

        $suppliers = [
            [
                'name' => 'Toko Sembako Makmur',
                'location' => 'Jakarta Utara',
                'phone' => '0812-3456-7890',
                'note' => 'Pemasok utama sembako harian, pengiriman same-day.',
                'offers' => [
                    ['material' => 'Indomie', 'price' => 2800, 'quality' => 'good'],
                    ['material' => 'Telur', 'price' => 2200, 'quality' => 'excellent'],
                    ['material' => 'Kapal Api', 'price' => 1500, 'quality' => 'good'],
                ],
            ],
            [
                'name' => 'Distributor Nutrisari Nusantara',
                'location' => 'Bandung',
                'phone' => '022-1234567',
                'note' => 'Kontrak bulanan min. 50 sachet.',
                'offers' => [
                    ['material' => 'Nutrisari', 'price' => 1200, 'quality' => 'excellent'],
                ],
            ],
            [
                'name' => 'CV Sumber Telur Segar',
                'location' => 'Bogor',
                'phone' => '0813-9988-7766',
                'note' => 'Telur peternak lokal, grading A.',
                'offers' => [
                    ['material' => 'Telur', 'price' => 2000, 'quality' => 'excellent'],
                ],
            ],
            [
                'name' => 'Grosir Indomie Jaya',
                'location' => 'Surabaya',
                'phone' => '031-555-8899',
                'note' => 'Harga grosir khusus order 5 dus ke atas.',
                'offers' => [
                    ['material' => 'Indomie', 'price' => 2500, 'quality' => 'good'],
                ],
            ],
            [
                'name' => 'UD Kopi & Teh Makassar',
                'location' => 'Makassar',
                'phone' => '0411-222-3344',
                'note' => 'Kombinasi kopi sachet dan minuman serbuk.',
                'offers' => [
                    ['material' => 'Kapal Api', 'price' => 1400, 'quality' => 'fair'],
                    ['material' => 'Nutrisari', 'price' => 1350, 'quality' => 'good'],
                ],
            ],
        ];

        foreach ($suppliers as $row) {
            $supplier = Supplier::firstOrCreate(
                ['name' => $row['name']],
                [
                    'location' => $row['location'],
                    'phone' => $row['phone'],
                    'note' => $row['note'],
                ]
            );

            $sync = [];
            foreach ($row['offers'] as $offer) {
                $material = $materials->get($offer['material']);
                if ($material) {
                    $sync[$material->id] = [
                        'price' => $offer['price'],
                        'quality' => $offer['quality'],
                    ];
                }
            }

            $supplier->rawMaterials()->sync($sync);
        }
    }
}
