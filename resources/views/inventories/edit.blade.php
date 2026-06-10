@extends('layouts.app')

@section('title', 'Ubah Transaksi')

@section('content')
<header>
    <div class="logo-container">
        <h1>Ubah Transaksi</h1>
        <p>Edit riwayat transaksi inventori #{{ $inventory->id }}</p>
    </div>
    <div>
        <a href="{{ route('inventories.index') }}" class="btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>
</header>

<div class="card-section" style="max-width: 800px; margin: 0 auto;">
    <h2 class="section-title">
        <span class="section-title-accent" style="background: var(--accent-amber);"></span>
        Form Perubahan Transaksi
    </h2>

    @if($errors->any())
        <div class="alert alert-danger" style="flex-direction: column; align-items: flex-start;">
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                <i class="fa-solid fa-circle-exclamation"></i>
                <strong>Terjadi kesalahan:</strong>
            </div>
            <ul style="margin-left: 1.5rem; font-size: 0.85rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('inventories.update', $inventory->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="form-group">
                <label for="nama_barang" class="form-label">Nama Barang</label>
                <input type="text" name="nama_barang" id="nama_barang" class="form-control" value="{{ old('nama_barang', $inventory->nama_barang) }}" placeholder="Contoh: Laptop Asus ROG" required>
            </div>

            <div class="form-group">
                <label for="no_barang" class="form-label">No. Barang (Kode/SKU INT)</label>
                <input type="number" name="no_barang" id="no_barang" class="form-control" value="{{ old('no_barang', $inventory->no_barang) }}" placeholder="Contoh: 1001" required>
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="jumlah_barang" class="form-label">Jumlah Barang</label>
                <input type="number" name="jumlah_barang" id="jumlah_barang" class="form-control" value="{{ old('jumlah_barang', $inventory->jumlah_barang) }}" placeholder="Contoh: 10" min="1" required>
            </div>

            <div class="form-group">
                <label for="harga_satuan" class="form-label">Harga Satuan (Rupiah)</label>
                <input type="number" name="harga_satuan" id="harga_satuan" class="form-control" value="{{ old('harga_satuan', $inventory->harga_satuan) }}" placeholder="Contoh: 15000000" min="0" step="0.01" required>
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="jenis_barang" class="form-label">Jenis Barang (Category)</label>
                <select name="jenis_barang" id="jenis_barang" class="form-control" required>
                    <option value="Elektronik" {{ old('jenis_barang', $inventory->jenis_barang) === 'Elektronik' ? 'selected' : '' }}>Elektronik</option>
                    <option value="Pakaian" {{ old('jenis_barang', $inventory->jenis_barang) === 'Pakaian' ? 'selected' : '' }}>Pakaian</option>
                    <option value="Makanan" {{ old('jenis_barang', $inventory->jenis_barang) === 'Makanan' ? 'selected' : '' }}>Makanan</option>
                    <option value="Lainnya" {{ old('jenis_barang', $inventory->jenis_barang) === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                </select>
            </div>

            <div class="form-group">
                <label for="tipe_transaksi" class="form-label">Tipe Transaksi</label>
                <select name="tipe_transaksi" id="tipe_transaksi" class="form-control" required>
                    <option value="Masuk" {{ old('tipe_transaksi', $inventory->tipe_transaksi) === 'Masuk' ? 'selected' : '' }}>Barang Masuk (Stock In)</option>
                    <option value="Keluar" {{ old('tipe_transaksi', $inventory->tipe_transaksi) === 'Keluar' ? 'selected' : '' }}>Barang Keluar (Stock Out)</option>
                </select>
            </div>
        </div>

        <div class="form-group" style="max-width: 50%; padding-right: 0.75rem;">
            <label for="tanggal_masuk_keluar" class="form-label">Tanggal Masuk / Keluar</label>
            <input type="date" name="tanggal_masuk_keluar" id="tanggal_masuk_keluar" class="form-control" value="{{ old('tanggal_masuk_keluar', $inventory->tanggal_masuk_keluar->format('Y-m-d')) }}" required>
        </div>

        <div class="form-actions">
            <a href="{{ route('inventories.index') }}" class="btn-secondary" style="padding: 0.75rem 1.5rem;">Batal</a>
            <button type="submit" class="btn-primary" style="background: var(--accent-amber); box-shadow: 0 4px 12px rgba(245, 158, 11, 0.25);">
                <i class="fa-solid fa-pen-to-square"></i> Update Transaksi
            </button>
        </div>
    </form>
</div>
@endsection
