@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<header>
    <div class="logo-container">
        <h1>YUASATABL</h1>
    </div>
    <div>
        <a href="{{ route('inventories.create') }}" class="btn-primary">
            <i class="fa-solid fa-plus"></i> Tambah Transaksi
        </a>
    </div>
</header>

@if(session('success'))
    <div class="alert alert-success">
        <i class="fa-solid fa-circle-check"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger">
        <i class="fa-solid fa-circle-exclamation"></i>
        <span>{{ session('error') }}</span>
    </div>
@endif

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="card-stat stat-blue">
        <div class="stat-label">Total Transaksi</div>
        <div class="stat-value">{{ $totalTransactions }}</div>
        <div class="stat-desc">Riwayat masuk & keluar</div>
    </div>
    <div class="card-stat stat-violet">
        <div class="stat-label">Total Barang Masuk</div>
        <div class="stat-value">{{ $totalMasuk }} <span style="font-size:1rem;font-weight:400;color:var(--text-secondary)">Unit</span></div>
        <div class="stat-desc">Akumulasi stok ditambah</div>
    </div>
    <div class="card-stat stat-rose">
        <div class="stat-label">Total Barang Keluar</div>
        <div class="stat-value">{{ $totalKeluar }} <span style="font-size:1rem;font-weight:400;color:var(--text-secondary)">Unit</span></div>
        <div class="stat-desc">Akumulasi stok dikurangi</div>
    </div>
    <div class="card-stat stat-emerald">
        <div class="stat-label">Sisa Stok Saat Ini</div>
        <div class="stat-value">{{ $totalCurrentStock }} <span style="font-size:1rem;font-weight:400;color:var(--text-secondary)">Unit</span></div>
        <div class="stat-desc">Stok fisik tersedia saat ini</div>
    </div>
</div>

<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); margin-bottom: 2.5rem;">
    <div class="card-stat stat-violet">
        <div class="stat-label">Total Nilai Inventori (FIFO)</div>
        <div class="stat-value">Rp {{ number_format($fifoTotalValuation, 2, ',', '.') }}</div>
        <div class="stat-desc">Berdasarkan harga unit barang masuk pertama</div>
    </div>
    <div class="card-stat stat-emerald">
        <div class="stat-label">Total Nilai Inventori (LIFO)</div>
        <div class="stat-value">Rp {{ number_format($lifoTotalValuation, 2, ',', '.') }}</div>
        <div class="stat-desc">Berdasarkan harga unit barang masuk terakhir</div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="main-grid">
    
    <!-- Left: Transactions List -->
    <div class="card-section">
        <h2 class="section-title">
            <span class="section-title-accent"></span>
            Daftar Transaksi Masuk/Keluar
        </h2>
        
        <div class="table-wrapper">
            @if($transactions->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-icon"><i class="fa-solid fa-box-open"></i></div>
                    <p>Belum ada transaksi inventori.</p>
                </div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>No. Barang</th>
                            <th>Nama Barang</th>
                            <th>Jenis</th>
                            <th>Tipe</th>
                            <th>Jumlah</th>
                            <th>Harga Satuan</th>
                            <th>Total Harga</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $t)
                            <tr>
                                <td>{{ $t->tanggal_masuk_keluar->format('d/m/Y') }}</td>
                                <td style="font-family: monospace; font-weight: 600;">#{{ $t->no_barang }}</td>
                                <td style="font-weight: 500;">{{ $t->nama_barang }}</td>
                                <td>
                                    <span class="badge badge-cat">{{ $t->jenis_barang }}</span>
                                </td>
                                <td>
                                    @if($t->tipe_transaksi === 'Masuk')
                                        <span class="badge badge-in"><i class="fa-solid fa-arrow-down-long"></i> Masuk</span>
                                    @else
                                        <span class="badge badge-out"><i class="fa-solid fa-arrow-up-long"></i> Keluar</span>
                                    @endif
                                </td>
                                <td style="font-weight: 600;">{{ $t->jumlah_barang }} Unit</td>
                                <td>Rp {{ number_format($t->harga_satuan, 0, ',', '.') }}</td>
                                <td style="font-weight: 600;">Rp {{ number_format($t->harga_satuan * $t->jumlah_barang, 0, ',', '.') }}</td>
                                <td>
                                    <div class="actions-cell">
                                        <a href="{{ route('inventories.edit', $t->id) }}" class="btn-icon" title="Ubah Transaksi">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <form action="{{ route('inventories.destroy', $t->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus transaksi ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-icon btn-icon-delete" title="Hapus Transaksi">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <!-- Right: FIFO / LIFO Valuation Breakdown -->
    <div class="card-section">
        <h2 class="section-title">
            <span class="section-title-accent" style="background: var(--accent-emerald);"></span>
            Kalkulasi Stok (FIFO & LIFO)
        </h2>
        
        @if(empty($stockSummary))
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fa-solid fa-calculator"></i></div>
                <p>Belum ada data kalkulasi barang.</p>
            </div>
        @else
            <div class="valuation-list">
                @foreach($stockSummary as $code => $summary)
                    <div class="valuation-item">
                        <div class="val-item-header">
                            <span class="val-item-title">{{ $summary['nama_barang'] }}</span>
                            <span class="val-item-no">Code: #{{ $summary['no_barang'] }}</span>
                        </div>
                        <div class="val-item-stock">
                            Jenis: <span style="color:var(--text-primary);">{{ $summary['jenis_barang'] }}</span> | 
                            Stok Saat Ini: <strong>{{ $summary['current_stock'] }} Unit</strong>
                        </div>
                        
                        <div class="val-method-row">
                            <div class="val-box">
                                <div class="val-box-label">FIFO Val</div>
                                <div class="val-box-value">Rp {{ number_format($summary['fifo']['valuation'], 0, ',', '.') }}</div>
                            </div>
                            <div class="val-box val-lifo">
                                <div class="val-box-label">LIFO Val</div>
                                <div class="val-box-value">Rp {{ number_format($summary['lifo']['valuation'], 0, ',', '.') }}</div>
                            </div>
                        </div>

                        <!-- FIFO Batches Details -->
                        <div class="batches-details">
                            <div class="batches-title"><i class="fa-solid fa-layer-group"></i> Detail Batch Tersisa (FIFO)</div>
                            <div class="batches-scroll">
                                @if(count($summary['fifo']['batches']) === 0)
                                    <div style="font-style: italic; color: var(--text-secondary);">Stok habis</div>
                                @else
                                    @foreach($summary['fifo']['batches'] as $b)
                                        <div class="batch-row">
                                            <span>{{ $b['jumlah'] }} unit @ Rp{{ number_format($b['harga'], 0, ',', '.') }}</span>
                                            <span style="color:var(--text-secondary)">{{ \Carbon\Carbon::parse($b['tanggal'])->format('d/m') }}</span>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <!-- LIFO Batches Details -->
                        <div class="batches-details" style="border-top: 1px dashed var(--border-color); margin-top: 0.5rem; padding-top: 0.5rem;">
                            <div class="batches-title" style="color: var(--accent-emerald);"><i class="fa-solid fa-layer-group"></i> Detail Batch Tersisa (LIFO)</div>
                            <div class="batches-scroll">
                                @if(count($summary['lifo']['batches']) === 0)
                                    <div style="font-style: italic; color: var(--text-secondary);">Stok habis</div>
                                @else
                                    @foreach($summary['lifo']['batches'] as $b)
                                        <div class="batch-row">
                                            <span>{{ $b['jumlah'] }} unit @ Rp{{ number_format($b['harga'], 0, ',', '.') }}</span>
                                            <span style="color:var(--text-secondary)">{{ \Carbon\Carbon::parse($b['tanggal'])->format('d/m') }}</span>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
