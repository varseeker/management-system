<?php

namespace Database\Seeders;

use App\Models\Borrowing;
use App\Models\Item;
use App\Models\User;
use Database\Seeders\Support\PlaceholderImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class BorrowingSeeder extends Seeder
{
    public function run(): void
    {
        $letoy = User::where('email', 'letoy@warkopkayu.test')->first();
        $ketoy = User::where('email', 'ketoy@warkopkayu.test')->first();
        $owner = User::where('email', 'dzaky.poke@warkopkayu.test')->first();

        if (! $letoy || ! $ketoy || ! $owner) {
            $this->command?->warn('Pengguna belum tersedia. Jalankan UserSeeder terlebih dahulu.');

            return;
        }

        $items = Item::all()->keyBy('code');

        if ($items->isEmpty()) {
            $this->command?->warn('Barang belum tersedia. Jalankan ItemSeeder terlebih dahulu.');

            return;
        }

        $records = [
            [
                'user' => $letoy,
                'item' => 'BRG-001',
                'quantity' => 1,
                'status' => 'pending',
                'description' => 'Membutuhkan panci besar untuk acara ulang tahun pelanggan di area depan.',
                'borrow_label' => 'Panci - kondisi baik',
                'days_ago' => 0,
                'loan_days' => 3,
            ],
            [
                'user' => $ketoy,
                'item' => 'BRG-003',
                'quantity' => 2,
                'status' => 'pending',
                'description' => 'Meja lipat untuk tambahan kapasitas tamu akhir pekan.',
                'borrow_label' => 'Meja lipat - siap pakai',
                'days_ago' => 0,
                'loan_days' => 2,
            ],
            [
                'user' => $letoy,
                'item' => 'BRG-005',
                'quantity' => 1,
                'status' => 'approved',
                'description' => 'Termos untuk layanan kopi keliling ke meja tamu.',
                'borrow_label' => 'Termos - bersih',
                'approval_note' => 'Disetujui, pastikan dikembalikan dalam kondisi bersih.',
                'days_ago' => 2,
                'loan_days' => 4,
            ],
            [
                'user' => $ketoy,
                'item' => 'BRG-008',
                'quantity' => 1,
                'status' => 'approved',
                'description' => 'Wajan cadangan karena wajan utama sedang rusak ringan.',
                'borrow_label' => 'Wajan - anti lengket',
                'approval_note' => 'Disetujui untuk kebutuhan operasional dapur.',
                'days_ago' => 1,
                'loan_days' => 3,
            ],
            [
                'user' => $letoy,
                'item' => 'BRG-004',
                'quantity' => 4,
                'status' => 'returned',
                'description' => 'Kursi tambahan untuk acara arisan ibu-ibu.',
                'borrow_label' => 'Kursi - sebelum dipinjam',
                'return_label' => 'Kursi - setelah dikembalikan',
                'approval_note' => 'Disetujui untuk acara 1 hari.',
                'return_condition' => 'good',
                'return_note' => 'Semua kursi dikembalikan lengkap dan bersih.',
                'days_ago' => 5,
                'loan_days' => 1,
            ],
            [
                'user' => $ketoy,
                'item' => 'BRG-002',
                'quantity' => 1,
                'status' => 'returned',
                'description' => 'Kompor portable untuk demo masak di halaman belakang.',
                'borrow_label' => 'Kompor - kondisi awal',
                'return_label' => 'Kompor - setelah dipakai',
                'approval_note' => 'Disetujui dengan pengawasan owner.',
                'return_condition' => 'minor_damage',
                'return_note' => 'Regulator sedikit longgar, perlu dicek sebelum dipinjam lagi.',
                'days_ago' => 7,
                'loan_days' => 2,
            ],
            [
                'user' => $letoy,
                'item' => 'BRG-010',
                'quantity' => 2,
                'status' => 'rejected',
                'description' => 'Meminjam lampu untuk dekorasi nonoperasional.',
                'borrow_label' => 'Lampu - pengajuan',
                'approval_note' => 'Ditolak karena lampu emergency harus tetap tersedia di warung.',
                'days_ago' => 3,
                'loan_days' => 2,
            ],
        ];

        foreach ($records as $index => $record) {
            $item = $items->get($record['item']);

            if (! $item) {
                continue;
            }

            $borrowDate = now()->subDays($record['days_ago'])->toDateString();
            $expectedReturn = Carbon::parse($borrowDate)->addDays($record['loan_days'])->toDateString();

            $borrowImage = PlaceholderImage::create(
                'uploads/borrowings/seed/pengajuan-' . ($index + 1) . '.png',
                $record['borrow_label'],
                [52, 120, 180]
            );

            $data = [
                'user_id' => $record['user']->id,
                'item_id' => $item->id,
                'quantity' => $record['quantity'],
                'borrow_date' => $borrowDate,
                'expected_return_date' => $expectedReturn,
                'description' => $record['description'],
                'borrow_image' => $borrowImage,
                'status' => $record['status'],
                'approval_note' => $record['approval_note'] ?? null,
            ];

            if ($record['status'] === 'approved') {
                $item->decrement('stock', $record['quantity']);
            }

            if ($record['status'] === 'returned') {
                $data['return_date'] = Carbon::parse($expectedReturn);
                $data['return_condition'] = $record['return_condition'];
                $data['return_note'] = $record['return_note'] ?? null;
                $data['return_image'] = PlaceholderImage::create(
                    'uploads/borrowings/seed/pengembalian-' . ($index + 1) . '.png',
                    $record['return_label'] ?? 'Pengembalian',
                    [46, 139, 87]
                );
            }

            if ($record['status'] === 'rejected') {
                // stok tidak berubah
            }

            Borrowing::create($data);
        }
    }
}
