<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InventoryAdjustmentRequest;
use App\Models\InventoryMovement;
use App\Models\Souvenir;
use App\Services\Inventory\InsufficientStock;
use App\Services\Inventory\InventoryService;
use App\Support\AdminPagination;
use App\Support\AdminShell;
use App\Support\CacheKeys;
use App\Support\Format;
use DateTimeInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $movements = InventoryMovement::query()
            ->latest('id')
            ->limit(20)
            ->get();

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
            'movements' => $movements
                ->map(fn (InventoryMovement $movement): array => $this->serializeMovement($movement))
                ->values()
                ->all(),
            'routes' => AdminShell::routes(),
        ]);
    }

    public function restock(
        InventoryAdjustmentRequest $request,
        Souvenir $souvenir,
        InventoryService $inventory
    ): RedirectResponse {
        $amount = $request->integer('amount');
        $applied = $inventory->adjust(
            souvenirId: (int) $souvenir->getKey(),
            quantityDelta: $amount,
            type: InventoryMovement::TYPE_ADMIN_RESTOCK,
            reference: $this->adjustmentReference($request, $souvenir),
            actorId: (int) Auth::guard('admin')->id(),
        );

        if ($applied) {
            CacheKeys::bump(CacheKeys::SOUVENIRS_VERSION);
        }

        return redirect()->back()->with('success', __('Stok berhasil ditambahkan.'));
    }

    public function deduct(
        InventoryAdjustmentRequest $request,
        Souvenir $souvenir,
        InventoryService $inventory
    ): RedirectResponse {
        $amount = $request->integer('amount');
        try {
            $applied = $inventory->adjust(
                souvenirId: (int) $souvenir->getKey(),
                quantityDelta: -$amount,
                type: InventoryMovement::TYPE_ADMIN_DEDUCTION,
                reference: $this->adjustmentReference($request, $souvenir),
                actorId: (int) Auth::guard('admin')->id(),
            );
        } catch (InsufficientStock $exception) {
            return redirect()->back()->withErrors([
                'amount' => __('Stok tidak mencukupi. Stok saat ini: :stock.', [
                    'stock' => Format::number($exception->currentStock),
                ]),
            ]);
        }

        if ($applied) {
            CacheKeys::bump(CacheKeys::SOUVENIRS_VERSION);
        }

        return redirect()->back()->with('success', __('Stok berhasil dikurangi.'));
    }

    private function adjustmentReference(
        InventoryAdjustmentRequest $request,
        Souvenir $souvenir
    ): string {
        return 'admin:'.(int) Auth::guard('admin')->id()
            .':souvenir:'.$souvenir->getKey()
            .':'.$request->string('adjustment_token')->toString();
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

    /** @return array<string, mixed> */
    private function serializeMovement(InventoryMovement $movement): array
    {
        $names = $movement->product_name_snapshot;
        $locale = app()->getLocale();
        $fallbackLocale = (string) config('app.fallback_locale', 'en');
        $productName = trim((string) ($names[$locale] ?? $names[$fallbackLocale] ?? array_values($names)[0] ?? ''));
        $quantityDelta = (int) $movement->quantity_delta;
        $createdAt = $movement->getAttribute('created_at');

        return [
            'id' => (int) $movement->getKey(),
            'productName' => $productName !== '' ? $productName : __('Produk dihapus'),
            'type' => (string) $movement->type,
            'typeLabel' => $this->movementTypeLabel((string) $movement->type),
            'quantityDelta' => $quantityDelta,
            'quantityDeltaLabel' => ($quantityDelta > 0 ? '+' : '').Format::number($quantityDelta),
            'stockBefore' => Format::number($movement->stock_before),
            'stockAfter' => Format::number($movement->stock_after),
            'actor' => $movement->actor_name_snapshot ?? __('Sistem'),
            'orderReference' => $movement->order_id !== null
                ? '#'.Format::number($movement->order_id)
                : '—',
            'reference' => (string) $movement->reference,
            'createdAt' => Format::dateTime(
                $createdAt instanceof DateTimeInterface || is_string($createdAt) ? $createdAt : null
            ),
        ];
    }

    private function movementTypeLabel(string $type): string
    {
        return match ($type) {
            InventoryMovement::TYPE_ORDER_RESERVATION => __('Reservasi pesanan'),
            InventoryMovement::TYPE_ORDER_RESTORATION => __('Pengembalian pesanan'),
            InventoryMovement::TYPE_ADMIN_RESTOCK => __('Penambahan admin'),
            InventoryMovement::TYPE_ADMIN_DEDUCTION => __('Pengurangan admin'),
            InventoryMovement::TYPE_INITIAL_STOCK => __('Stok awal'),
            InventoryMovement::TYPE_ADMIN_CORRECTION => __('Koreksi admin'),
            default => __('Penyesuaian stok'),
        };
    }

    /** @return array<string, string> */
    private function copy(): array
    {
        return [
            'add' => __('Tambah'),
            'adjustment' => __('Penyesuaian Stok'),
            'actor' => __('Aktor'),
            'amount' => __('Jumlah Penyesuaian Stok'),
            'description' => __('Pantau produk di bawah batas stok dan sesuaikan persediaan tanpa meninggalkan halaman.'),
            'emptyDescription' => __('Tidak ada produk dengan stok di bawah batas saat ini.'),
            'emptyTitle' => __('Semua stok aman.'),
            'eyebrow' => __('Inventory'),
            'filterDescription' => __('Tampilkan produk dengan jumlah stok sama dengan atau di bawah batas yang dipilih.'),
            'filterLabel' => __('Batas Stok'),
            'filterTitle' => __('Batas Pemantauan Stok'),
            'historyDescription' => __('Dua puluh pergerakan stok terbaru dengan saldo sebelum dan sesudah perubahan.'),
            'historyEmpty' => __('Belum ada pergerakan stok yang tercatat.'),
            'historyTitle' => __('Riwayat Pergerakan Stok'),
            'next' => __('Berikutnya'),
            'order' => __('Order'),
            'previous' => __('Sebelumnya'),
            'price' => __('Harga'),
            'product' => __('Produk'),
            'quantityChange' => __('Perubahan'),
            'recordedAt' => __('Dicatat'),
            'reference' => __('Referensi'),
            'remaining' => __('Sisa'),
            'reset' => __('Reset'),
            'resultsDescription' => __('Daftar diurutkan dari stok paling sedikit untuk membantu prioritas restock.'),
            'resultsTitle' => __('Produk yang Perlu Diperiksa'),
            'show' => __('Tampilkan'),
            'subtract' => __('Kurangi'),
            'stockChange' => __('Saldo Stok'),
            'title' => __('Stok Rendah'),
            'type' => __('Jenis'),
        ];
    }
}
