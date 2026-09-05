<?php

namespace App\Services\Orders;

use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Souvenir;
use App\Services\Inventory\InventoryService;
use Illuminate\Support\Facades\DB;

class OrderInventoryService
{
    public function __construct(private readonly InventoryService $inventory) {}

    public function restore(int $orderId, ?int $actorId = null): bool
    {
        return DB::transaction(function () use ($actorId, $orderId): bool {
            $order = Order::query()
                ->whereKey($orderId)
                ->lockForUpdate()
                ->first();

            if (! $order || $order->stock_restored_at !== null) {
                return false;
            }

            $itemQuantities = OrderItem::query()
                ->where('order_id', $order->id)
                ->whereNotNull('souvenir_id')
                ->selectRaw('souvenir_id, SUM(quantity) as total_qty')
                ->groupBy('souvenir_id')
                ->pluck('total_qty', 'souvenir_id')
                ->map(fn ($quantity): int => (int) $quantity);

            if ($itemQuantities->isNotEmpty()) {
                $souvenirs = Souvenir::query()
                    ->whereIn('id', $itemQuantities->keys()->all())
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($itemQuantities as $souvenirId => $quantity) {
                    $souvenir = $souvenirs->get((int) $souvenirId);
                    if ($souvenir) {
                        $this->inventory->adjust(
                            souvenirId: (int) $souvenir->getKey(),
                            quantityDelta: $quantity,
                            type: InventoryMovement::TYPE_ORDER_RESTORATION,
                            reference: 'order:'.$order->id.':restoration:souvenir:'.$souvenir->getKey(),
                            orderId: (int) $order->getKey(),
                            actorId: $actorId,
                        );
                    }
                }
            }

            $order->forceFill(['stock_restored_at' => now()])->save();

            return true;
        });
    }
}
