<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource (Dashboard + Transactions list).
     */
    public function index()
    {
        $transactions = Inventory::orderBy('tanggal_masuk_keluar', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        // Get chronological transactions for calculations
        $chronoTransactions = Inventory::orderBy('tanggal_masuk_keluar', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $stockSummary = $this->calculateStock($chronoTransactions);

        // General statistics
        $totalTransactions = $transactions->count();
        $totalMasuk = $transactions->where('tipe_transaksi', 'Masuk')->sum('jumlah_barang');
        $totalKeluar = $transactions->where('tipe_transaksi', 'Keluar')->sum('jumlah_barang');
        
        $fifoTotalValuation = 0;
        $lifoTotalValuation = 0;
        $totalCurrentStock = 0;
        
        foreach ($stockSummary as $summary) {
            $fifoTotalValuation += $summary['fifo']['valuation'];
            $lifoTotalValuation += $summary['lifo']['valuation'];
            $totalCurrentStock += $summary['current_stock'];
        }

        return view('inventories.index', compact(
            'transactions',
            'stockSummary',
            'totalTransactions',
            'totalMasuk',
            'totalKeluar',
            'totalCurrentStock',
            'fifoTotalValuation',
            'lifoTotalValuation'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('inventories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'no_barang' => 'required|integer',
            'jumlah_barang' => 'required|integer|min:1',
            'jenis_barang' => 'required|in:Elektronik,Pakaian,Makanan,Lainnya',
            'tipe_transaksi' => 'required|in:Masuk,Keluar',
            'tanggal_masuk_keluar' => 'required|date',
            'harga_satuan' => 'required|numeric|min:0',
        ]);

        // Additional validation: Check if we have enough stock before registering a "Keluar" transaction
        if ($validated['tipe_transaksi'] === 'Keluar') {
            $chronoTransactions = Inventory::orderBy('tanggal_masuk_keluar', 'asc')
                ->orderBy('id', 'asc')
                ->get();
            $stockSummary = $this->calculateStock($chronoTransactions);
            
            $currentStock = isset($stockSummary[$validated['no_barang']]) 
                ? $stockSummary[$validated['no_barang']]['current_stock'] 
                : 0;

            if ($currentStock < $validated['jumlah_barang']) {
                return back()->withErrors([
                    'jumlah_barang' => "Stok tidak mencukupi untuk mengeluarkan barang ini. Sisa stok saat ini: {$currentStock} unit."
                ])->withInput();
            }
        }

        Inventory::create($validated);

        return redirect()->route('inventories.index')->with('success', 'Transaksi inventori berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Inventory $inventory)
    {
        return view('inventories.edit', compact('inventory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Inventory $inventory)
    {
        $validated = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'no_barang' => 'required|integer',
            'jumlah_barang' => 'required|integer|min:1',
            'jenis_barang' => 'required|in:Elektronik,Pakaian,Makanan,Lainnya',
            'tipe_transaksi' => 'required|in:Masuk,Keluar',
            'tanggal_masuk_keluar' => 'required|date',
            'harga_satuan' => 'required|numeric|min:0',
        ]);

        // Save original and verify stock consistency temporarily before saving
        // To be safe, we can update it and check if any product stock goes below 0.
        // If it does, we rollback.
        \DB::beginTransaction();
        try {
            $inventory->update($validated);

            $chronoTransactions = Inventory::orderBy('tanggal_masuk_keluar', 'asc')
                ->orderBy('id', 'asc')
                ->get();
            $stockSummary = $this->calculateStock($chronoTransactions);

            foreach ($stockSummary as $summary) {
                if ($summary['current_stock'] < 0) {
                    throw new \Exception("Perubahan ini menyebabkan stok barang '{$summary['nama_barang']}' menjadi negatif ({$summary['current_stock']}). Transaksi dibatalkan.");
                }
            }

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }

        return redirect()->route('inventories.index')->with('success', 'Transaksi inventori berhasil diubah.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Inventory $inventory)
    {
        \DB::beginTransaction();
        try {
            $inventory->delete();

            $chronoTransactions = Inventory::orderBy('tanggal_masuk_keluar', 'asc')
                ->orderBy('id', 'asc')
                ->get();
            $stockSummary = $this->calculateStock($chronoTransactions);

            foreach ($stockSummary as $summary) {
                if ($summary['current_stock'] < 0) {
                    throw new \Exception("Penghapusan transaksi ini menyebabkan stok barang '{$summary['nama_barang']}' menjadi negatif ({$summary['current_stock']}). Tindakan dibatalkan.");
                }
            }

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('inventories.index')->with('success', 'Transaksi inventori berhasil dihapus.');
    }

    /**
     * Algorithm to calculate current stock level, FIFO batches, and LIFO batches
     */
    private function calculateStock($transactions)
    {
        $grouped = $transactions->groupBy('no_barang');
        $results = [];

        foreach ($grouped as $no_barang => $items) {
            $sortedItems = $items->sortBy(function($item) {
                return $item->tanggal_masuk_keluar->format('Y-m-d') . '_' . $item->id;
            })->values();

            $nama_barang = $sortedItems->first()->nama_barang;
            $jenis_barang = $sortedItems->first()->jenis_barang;

            // --- FIFO Stock Calculation ---
            $fifoBatches = [];
            foreach ($sortedItems as $item) {
                if ($item->tipe_transaksi === 'Masuk') {
                    $fifoBatches[] = [
                        'id' => $item->id,
                        'jumlah' => (int) $item->jumlah_barang,
                        'harga' => (float) $item->harga_satuan,
                        'tanggal' => $item->tanggal_masuk_keluar->format('Y-m-d'),
                    ];
                } else { // Keluar
                    $qtyToDeduct = (int) $item->jumlah_barang;
                    while ($qtyToDeduct > 0 && count($fifoBatches) > 0) {
                        if ($fifoBatches[0]['jumlah'] <= $qtyToDeduct) {
                            $qtyToDeduct -= $fifoBatches[0]['jumlah'];
                            array_shift($fifoBatches);
                        } else {
                            $fifoBatches[0]['jumlah'] -= $qtyToDeduct;
                            $qtyToDeduct = 0;
                        }
                    }
                }
            }
            $fifoValuation = 0;
            $fifoQty = 0;
            foreach ($fifoBatches as $b) {
                $fifoValuation += $b['jumlah'] * $b['harga'];
                $fifoQty += $b['jumlah'];
            }

            // --- LIFO Stock Calculation ---
            $lifoBatches = [];
            foreach ($sortedItems as $item) {
                if ($item->tipe_transaksi === 'Masuk') {
                    $lifoBatches[] = [
                        'id' => $item->id,
                        'jumlah' => (int) $item->jumlah_barang,
                        'harga' => (float) $item->harga_satuan,
                        'tanggal' => $item->tanggal_masuk_keluar->format('Y-m-d'),
                    ];
                } else { // Keluar
                    $qtyToDeduct = (int) $item->jumlah_barang;
                    while ($qtyToDeduct > 0 && count($lifoBatches) > 0) {
                        $lastIdx = count($lifoBatches) - 1;
                        if ($lifoBatches[$lastIdx]['jumlah'] <= $qtyToDeduct) {
                            $qtyToDeduct -= $lifoBatches[$lastIdx]['jumlah'];
                            array_pop($lifoBatches);
                        } else {
                            $lifoBatches[$lastIdx]['jumlah'] -= $qtyToDeduct;
                            $qtyToDeduct = 0;
                        }
                    }
                }
            }
            $lifoValuation = 0;
            $lifoQty = 0;
            foreach ($lifoBatches as $b) {
                $lifoValuation += $b['jumlah'] * $b['harga'];
                $lifoQty += $b['jumlah'];
            }

            $results[$no_barang] = [
                'no_barang' => $no_barang,
                'nama_barang' => $nama_barang,
                'jenis_barang' => $jenis_barang,
                'current_stock' => $fifoQty, // same as $lifoQty
                'fifo' => [
                    'valuation' => $fifoValuation,
                    'batches' => $fifoBatches
                ],
                'lifo' => [
                    'valuation' => $lifoValuation,
                    'batches' => $lifoBatches
                ]
            ];
        }

        return $results;
    }
}
