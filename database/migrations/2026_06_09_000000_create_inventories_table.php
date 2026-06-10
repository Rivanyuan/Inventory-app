<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->string('nama_barang');
            $table->integer('no_barang');
            $table->integer('jumlah_barang');
            $table->enum('jenis_barang', ['Elektronik', 'Pakaian', 'Makanan', 'Lainnya']);
            $table->enum('tipe_transaksi', ['Masuk', 'Keluar']);
            $table->date('tanggal_masuk_keluar');
            $table->decimal('harga_satuan', 15, 2)->default(0.00); // helpful for LIFO/FIFO stock value calculations
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
