<?php

return [

    'borrowing' => [
        'max_quantity' => 5,
        'max_loan_days' => 3,
        'late_penalty' => 'Denda keterlambatan sebesar 10% dari nilai barang per hari keterlambatan.',
        'minor_damage_penalty' => 'Biaya perbaikan ditanggung peminjam sesuai estimasi kerusakan ringan yang ditetapkan pemilik.',
        'broken_penalty' => 'Peminjam wajib mengganti barang secara utuh atau membayar ganti rugi penuh sesuai harga perolehan barang.',
    ],

    'roles' => [
        'admin' => 'Admin',
        'owner' => 'Pemilik',
        'staff' => 'Staf',
    ],

];
