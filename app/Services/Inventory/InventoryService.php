<?php

namespace App\Services\Inventory;

use App\Models\InventoryMovement;
use App\Models\Souvenir;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryService
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function adjust(
        int $souvenirId,
        int $quantityDelta,
        string $type,
        string $reference,
        ?int $orderId = null,
        ?int $actorId = null,
        array $metadata = []
    ): bool {
        if ($quantityDelta === 0) {
            throw new InvalidArgumentException('Inventory adjustment cannot be zero.');
        }

        return DB::transaction(function () use (
            $actorId,
            $metadata,
            $orderId,
            $quantityDelta,
            $reference,
            $souvenirId,
            $type
        ): bool {
            $souvenir = Souvenir::query()
                ->whereKey($souvenirId)
                ->lockForUpdate()
                ->firstOrFail();

            if (InventoryMovement::query()->where('reference', $reference)->exists()) {
                return false;
            }

            $stockBefore = (int) $souvenir->stock;
            $stockAfter = $stockBefore + $quantityDelta;

            if ($stockAfter < 0) {
                throw new InsufficientStock($stockBefore);
            }

            $souvenir->forceFill(['stock' => $stockAfter])->save();

            InventoryMovement::query()->create([
                'souvenir_id' => $souvenir->id,
                'order_id' => $orderId,
                'actor_id' => $actorId,
                'type' => $type,
                'quantity_delta' => $quantityDelta,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reference' => $reference,
                'product_name_snapshot' => $souvenir->getTranslations('name'),
                'actor_name_snapshot' => $actorId !== null
                    ? User::query()->whereKey($actorId)->value('username')
                    : null,
                'metadata' => $metadata === [] ? null : $metadata,
            ]);

            return true;
        });
    }
}
