<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['code' => 'BRG-001', 'name' => 'Panci Besar', 'stock' => 8, 'description' => 'Panci stainless 30 cm untuk masak porsi besar'],
            ['code' => 'BRG-002', 'name' => 'Kompor Portable', 'stock' => 5, 'description' => 'Kompor gas 1 tungku untuk kebutuhan outdoor'],
            ['code' => 'BRG-003', 'name' => 'Meja Lipat', 'stock' => 12, 'description' => 'Meja lipat plastik untuk acara tambahan'],
            ['code' => 'BRG-004', 'name' => 'Kursi Plastik', 'stock' => 30, 'description' => 'Kursi tamu warna merah/biru'],
            ['code' => 'BRG-005', 'name' => 'Termos Air Panas', 'stock' => 6, 'description' => 'Termos 5 liter untuk kopi dan teh'],
            ['code' => 'BRG-006', 'name' => 'Set Sendok & Garpu', 'stock' => 20, 'description' => 'Isi 10 set per paket'],
            ['code' => 'BRG-007', 'name' => 'Tandon Galon', 'stock' => 4, 'description' => 'Tandon air minum 19 liter'],
            ['code' => 'BRG-008', 'name' => 'Wajan Anti Lengket', 'stock' => 7, 'description' => 'Wajan 28 cm untuk gorengan'],
            ['code' => 'BRG-009', 'name' => 'Rak Bumbu Dapur', 'stock' => 3, 'description' => 'Rak besi 3 tingkat area dapur'],
            ['code' => 'BRG-010', 'name' => 'Lampu Emergency', 'stock' => 10, 'description' => 'Lampu LED rechargeable saat listrik padam'],
        ];

        foreach ($items as $item) {
            Item::updateOrCreate(['code' => $item['code']], $item);
        }
    }
}
