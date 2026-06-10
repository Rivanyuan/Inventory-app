<?php

namespace Database\Seeders;

use App\Models\Inventory;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Item 1: Laptop Asus ROG (Code #1001)
        // Batch 1: Masuk
        Inventory::create([
            'nama_barang' => 'Laptop Asus ROG',
            'no_barang' => 1001,
            'jumlah_barang' => 10,
            'jenis_barang' => 'Elektronik',
            'tipe_transaksi' => 'Masuk',
            'tanggal_masuk_keluar' => '2026-06-01',
            'harga_satuan' => 10000000.00
        ]);

        // Batch 2: Masuk (with higher price due to inflation)
        Inventory::create([
            'nama_barang' => 'Laptop Asus ROG',
            'no_barang' => 1001,
            'jumlah_barang' => 5,
            'jenis_barang' => 'Elektronik',
            'tipe_transaksi' => 'Masuk',
            'tanggal_masuk_keluar' => '2026-06-03',
            'harga_satuan' => 12000000.00
        ]);

        // Transaction 3: Keluar (Stock Out of 8 units)
        Inventory::create([
            'nama_barang' => 'Laptop Asus ROG',
            'no_barang' => 1001,
            'jumlah_barang' => 8,
            'jenis_barang' => 'Elektronik',
            'tipe_transaksi' => 'Keluar',
            'tanggal_masuk_keluar' => '2026-06-04',
            'harga_satuan' => 0.00 // stock out does not have an acquisition price
        ]);


        // Item 2: Kaos Polos Cotton (Code #2001)
        // Batch 1: Masuk
        Inventory::create([
            'nama_barang' => 'Kaos Polos Cotton',
            'no_barang' => 2001,
            'jumlah_barang' => 100,
            'jenis_barang' => 'Pakaian',
            'tipe_transaksi' => 'Masuk',
            'tanggal_masuk_keluar' => '2026-06-02',
            'harga_satuan' => 50000.00
        ]);

        // Batch 2: Masuk
        Inventory::create([
            'nama_barang' => 'Kaos Polos Cotton',
            'no_barang' => 2001,
            'jumlah_barang' => 50,
            'jenis_barang' => 'Pakaian',
            'tipe_transaksi' => 'Masuk',
            'tanggal_masuk_keluar' => '2026-06-05',
            'harga_satuan' => 60000.00
        ]);

        // Transaction 3: Keluar
        Inventory::create([
            'nama_barang' => 'Kaos Polos Cotton',
            'no_barang' => 2001,
            'jumlah_barang' => 120,
            'jenis_barang' => 'Pakaian',
            'tipe_transaksi' => 'Keluar',
            'tanggal_masuk_keluar' => '2026-06-06',
            'harga_satuan' => 0.00
        ]);
    }
}
