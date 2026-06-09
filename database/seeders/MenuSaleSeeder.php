<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuSale;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MenuSaleSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all()->keyBy('email');

        if ($users->isEmpty()) {
            $this->command?->warn('Tidak ada pengguna. Jalankan UserSeeder terlebih dahulu.');

            return;
        }

        $staffLetoy = $users->get('letoy@warkopkayu.test') ?? $users->first();
        $staffKetoy = $users->get('ketoy@warkopkayu.test') ?? $users->first();
        $owner = $users->get('dzaky.poke@warkopkayu.test') ?? $users->first();

        $orders = [
            ['menu' => 'Indomie Telur', 'quantity' => 3, 'user' => $staffLetoy, 'note' => 'Pesanan meja 2 pagi', 'at' => now()->subDays(6)->setTime(7, 45)],
            ['menu' => 'Indomie Telur', 'quantity' => 2, 'user' => $staffKetoy, 'note' => 'Tamu nongkrong lama', 'at' => now()->subDays(4)->setTime(14, 10)],
            ['menu' => 'Indomie Telur', 'quantity' => 1, 'user' => $staffLetoy, 'note' => null, 'at' => now()->subHours(3)],
            ['menu' => 'Indomie Double', 'quantity' => 2, 'user' => $staffKetoy, 'note' => 'Porsi jumbo', 'at' => now()->subDays(5)->setTime(12, 30)],
            ['menu' => 'Indomie Double', 'quantity' => 1, 'user' => $owner, 'note' => 'Pesanan owner shift sore', 'at' => now()->subDays(1)->setTime(16, 0)],
            ['menu' => 'Indomie Double', 'quantity' => 3, 'user' => $staffLetoy, 'note' => 'Paket kombo 3 orang', 'at' => now()->subHours(6)],
            ['menu' => 'Indomie Kapal Api', 'quantity' => 2, 'user' => $staffKetoy, 'note' => 'Favorit pelanggan tetap', 'at' => now()->subDays(3)->setTime(10, 15)],
            ['menu' => 'Indomie Kapal Api', 'quantity' => 1, 'user' => $staffLetoy, 'note' => null, 'at' => now()->subHours(8)],
            ['menu' => 'Nutrisari Dingin', 'quantity' => 5, 'user' => $owner, 'note' => 'Stok minuman harian', 'at' => now()->subDays(2)->setTime(8, 0)],
            ['menu' => 'Nutrisari Dingin', 'quantity' => 2, 'user' => $staffKetoy, 'note' => 'Pesanan siang panas', 'at' => now()->subHours(4)],
            ['menu' => 'Teh Manis Panas', 'quantity' => 4, 'user' => $staffLetoy, 'note' => 'Pesanan grup pagi', 'at' => now()->subDays(5)->setTime(6, 30)],
            ['menu' => 'Teh Manis Panas', 'quantity' => 2, 'user' => $staffKetoy, 'note' => 'Hangat-hangat kopi dulu', 'at' => now()->subDays(1)->setTime(7, 0)],
            ['menu' => 'Teh Manis Panas', 'quantity' => 1, 'user' => $staffLetoy, 'note' => null, 'at' => now()->subMinutes(90)],
            ['menu' => 'Kopi Susu', 'quantity' => 3, 'user' => $owner, 'note' => 'Pesanan ojol pagi', 'at' => now()->subDays(4)->setTime(8, 20)],
            ['menu' => 'Kopi Susu', 'quantity' => 2, 'user' => $staffKetoy, 'note' => 'Extra manis', 'at' => now()->subHours(2)],
            ['menu' => 'Indomie Goreng Spesial', 'quantity' => 2, 'user' => $staffLetoy, 'note' => 'Menu sore favorit', 'at' => now()->subDays(2)->setTime(17, 45)],
            ['menu' => 'Indomie Goreng Spesial', 'quantity' => 1, 'user' => $staffKetoy, 'note' => null, 'at' => now()->subHours(5)],
            ['menu' => 'Roti Bakar Keju', 'quantity' => 3, 'user' => $staffLetoy, 'note' => 'Camilan pelanggan anak-anak', 'at' => now()->subDays(3)->setTime(15, 30)],
            ['menu' => 'Roti Bakar Keju', 'quantity' => 2, 'user' => $owner, 'note' => 'Promo sore', 'at' => now()->subHours(7)],
            ['menu' => 'Nasi Goreng Warkop', 'quantity' => 2, 'user' => $staffKetoy, 'note' => 'Makan malam staf', 'at' => now()->subDays(1)->setTime(19, 0)],
            ['menu' => 'Nasi Goreng Warkop', 'quantity' => 1, 'user' => $staffLetoy, 'note' => 'Pedas sedang', 'at' => now()->subMinutes(45)],
            ['menu' => 'Es Teh Manis', 'quantity' => 6, 'user' => $owner, 'note' => 'Pesanan acara kecil', 'at' => now()->subDays(2)->setTime(11, 0)],
            ['menu' => 'Es Teh Manis', 'quantity' => 2, 'user' => $staffKetoy, 'note' => null, 'at' => now()->subHours(1)],
            ['menu' => 'Pisang Goreng', 'quantity' => 4, 'user' => $staffLetoy, 'note' => 'Cemilan sore ramai', 'at' => now()->subDays(1)->setTime(15, 0)],
            ['menu' => 'Pisang Goreng', 'quantity' => 2, 'user' => $staffKetoy, 'note' => 'Tanpa gula tabur', 'at' => now()->subMinutes(30)],
            ['menu' => 'Sosis Goreng', 'quantity' => 3, 'user' => $owner, 'note' => 'Pesanan anak sekolah', 'at' => now()->subDays(3)->setTime(13, 45)],
            ['menu' => 'Sosis Goreng', 'quantity' => 2, 'user' => $staffLetoy, 'note' => 'Sambal extra', 'at' => now()->subMinutes(20)],
        ];

        foreach ($orders as $order) {
            $menu = Menu::with('rawMaterials')
                ->where('name', $order['menu'])
                ->first();

            if (! $menu) {
                continue;
            }

            if (! $menu->hasEnoughStock($order['quantity'])) {
                continue;
            }

            DB::transaction(function () use ($menu, $order) {
                $menu->consumeStock($order['quantity']);

                $sale = MenuSale::create([
                    'menu_id' => $menu->id,
                    'user_id' => $order['user']->id,
                    'quantity' => $order['quantity'],
                    'note' => $order['note'],
                ]);

                $timestamp = Carbon::parse($order['at']);
                $sale->update([
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
            });
        }
    }
}
