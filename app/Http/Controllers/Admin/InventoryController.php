<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InventoryAdjustmentRequest;
use App\Models\Souvenir;
use App\Support\AdminPagination;
use App\Support\AdminShell;
use App\Support\CacheKeys;
use App\Support\Format;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    public function lowStock(Request $request): Response
    {
        $threshold = max(1, (int) $request->input('threshold', 5));

        $souvenirs = Souvenir::where('stock', '<=', $threshold)
            ->orderBy('stock')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Inventory/LowStock', [
            'copy' => [
                ...AdminShell::copy(),
                ...$this->copy(),
            ],
            'filters' => [
                'threshold' => $threshold,
            ],
            'inventory' => [
                'data' => $souvenirs->getCollection()
                    ->map(fn (Souvenir $souvenir): array => $this->serializeLowStockItem($souvenir))
                    ->values()
                    ->all(),
                'pagination' => AdminPagination::serialize($souvenirs, __('Menampilkan :from–:to dari :total souvenir', [
                    'from' => Format::number($souvenirs->firstItem() ?? 0),
                    'to' => Format::number($souvenirs->lastItem() ?? 0),
                    'total' => Format::number($souvenirs->total()),
                ])),
            ],
            'routes' => AdminShell::routes(),
        ]);
    }

    public function restock(InventoryAdjustmentRequest $request, Souvenir $souvenir): RedirectResponse
    {
        $amount = $request->integer('amount');

        $souvenir->increment('stock', $amount);

        CacheKeys::bump(CacheKeys::SOUVENIRS_VERSION);

        return redirect()->back()->with('success', __('Stok berhasil ditambahkan.'));
    }

    public function deduct(InventoryAdjustmentRequest $request, Souvenir $souvenir): RedirectResponse
    {
        $amount = $request->integer('amount');

        $updated = Souvenir::query()
            ->whereKey($souvenir->getKey())
            ->where('stock', '>=', $amount)
            ->decrement('stock', $amount);

        if ($updated !== 1) {
            $currentStock = (int) Souvenir::query()
                ->whereKey($souvenir->getKey())
                ->value('stock');

            return redirect()->back()->withErrors([
                'amount' => __('Stok tidak mencukupi. Stok saat ini: :stock.', [
                    'stock' => Format::number($currentStock),
                ]),
            ]);
        }

        CacheKeys::bump(CacheKeys::SOUVENIRS_VERSION);

        return redirect()->back()->with('success', __('Stok berhasil dikurangi.'));
    }

    /** @return array<string, mixed> */
    private function serializeLowStockItem(Souvenir $souvenir): array
    {
        $name = trim($souvenir->getTranslation('name', app()->getLocale()));
        $stock = (int) $souvenir->stock;

        return [
            'id' => (int) $souvenir->getKey(),
            'name' => $name,
            'price' => Format::idr($souvenir->price),
            'reference' => __('SKU').' #'.$souvenir->getKey(),
            'adjustmentLabel' => __('Jumlah penyesuaian stok untuk :product', ['product' => $name]),
            'deductUrl' => route('admin.inventory.deduct', $souvenir, absolute: false),
            'restockUrl' => route('admin.inventory.restock', $souvenir, absolute: false),
            'stock' => $stock,
            'stockCount' => Format::number($stock),
            'stockLabel' => $stock === 0 ? __('Habis') : __('Rendah'),
            'stockStatus' => $stock === 0 ? 'out-of-stock' : 'low',
        ];
    }

    /** @return array<string, string> */
    private function copy(): array
    {
        return [
            'add' => __('Tambah'),
            'adjustment' => __('Penyesuaian Stok'),
            'amount' => __('Jumlah Penyesuaian Stok'),
            'description' => __('Pantau produk di bawah batas stok dan sesuaikan persediaan tanpa meninggalkan halaman.'),
            'emptyDescription' => __('Tidak ada produk dengan stok di bawah batas saat ini.'),
            'emptyTitle' => __('Semua stok aman.'),
            'eyebrow' => __('Inventory'),
            'filterDescription' => __('Tampilkan produk dengan jumlah stok sama dengan atau di bawah batas yang dipilih.'),
            'filterLabel' => __('Batas Stok'),
            'filterTitle' => __('Batas Pemantauan Stok'),
            'next' => __('Berikutnya'),
            'previous' => __('Sebelumnya'),
            'price' => __('Harga'),
            'product' => __('Produk'),
            'remaining' => __('Sisa'),
            'reset' => __('Reset'),
            'resultsDescription' => __('Daftar diurutkan dari stok paling sedikit untuk membantu prioritas restock.'),
            'resultsTitle' => __('Produk yang Perlu Diperiksa'),
            'show' => __('Tampilkan'),
            'subtract' => __('Kurangi'),
            'title' => __('Stok Rendah'),
        ];
    }
}
