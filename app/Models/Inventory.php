<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_barang',
        'no_barang',
        'jumlah_barang',
        'jenis_barang',
        'tipe_transaksi',
        'tanggal_masuk_keluar',
        'harga_satuan',
    ];

    protected $casts = [
        'tanggal_masuk_keluar' => 'date',
        'harga_satuan' => 'decimal:2',
        'jumlah_barang' => 'integer',
        'no_barang' => 'integer',
    ];
}
