<?php

namespace Tests\Feature;

use App\Models\InventoryMovement;
use App\Models\Souvenir;
use App\Models\User;
use App\Support\CacheKeys;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminInertiaInventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_low_stock_is_rendered_by_inertia_with_explicit_contract(): void
    {
        $response = $this
            ->actingAs($this->createAdmin(), 'admin')
            ->withCookie('locale', 'id')
            ->get(route('admin.inventory.low-stock'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Inventory/LowStock')
            ->where('copy.title', 'Stok Rendah')
            ->where('copy.reset', 'Reset')
            ->where('copy.subtract', 'Kurangi')
            ->where('routes.lowStock', '/admin/inventory/low-stock')
            ->where('filters.threshold', 5)
            ->has('inventory.data', 0)
            ->where('inventory.pagination.currentPage', 1)
            ->where('inventory.pagination.total', 0)
            ->has('movements', 0)
        );
    }

    public function test_low_stock_items_are_localized_formatted_and_serialized_without_exposing_models(): void
    {
        $souvenir = Souvenir::factory()->create([
            'description' => ['id' => 'Produk lokal', 'en' => 'Local product'],
            'image' => 'uploads/souvenirs/low-stock.webp',
            'name' => ['id' => 'Teh Rendah Stok', 'en' => 'Low Stock Tea'],
            'price' => 125000,
            'stock' => 3,
        ]);

        $response = $this
            ->actingAs($this->createAdmin(), 'admin')
            ->withCookie('locale', 'en')
            ->get(route('admin.inventory.low-stock', ['threshold' => 5]));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('inventory.data.0.id', $souvenir->id)
            ->where('inventory.data.0.reference', 'SKU #'.$souvenir->id)
            ->where('inventory.data.0.name', 'Low Stock Tea')
            ->where('inventory.data.0.price', 'IDR 125,000')
            ->where('inventory.data.0.stock', 3)
            ->where('inventory.data.0.stockCount', '3')
            ->where('inventory.data.0.stockLabel', 'Low')
            ->where('inventory.data.0.stockStatus', 'low')
            ->where('inventory.data.0.adjustmentLabel', 'Stock adjustment amount for Low Stock Tea')
            ->where('inventory.data.0.deductUrl', '/admin/inventory/'.$souvenir->id.'/deduct')
            ->where('inventory.data.0.restockUrl', '/admin/inventory/'.$souvenir->id.'/restock')
            ->missing('inventory.data.0.description')
            ->missing('inventory.data.0.image')
            ->missing('inventory.data.0.created_at')
            ->missing('inventory.data.0.updated_at')
        );
    }

    public function test_low_stock_filter_is_applied_and_order_is_deterministic(): void
    {
        $firstTie = Souvenir::factory()->create(['stock' => 3]);
        $secondTie = Souvenir::factory()->create(['stock' => 3]);
        $soldOut = Souvenir::factory()->create(['stock' => 0]);
        Souvenir::factory()->create(['stock' => 4]);

        $response = $this
            ->actingAs($this->createAdmin(), 'admin')
            ->get(route('admin.inventory.low-stock', ['threshold' => 3]));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('filters.threshold', 3)
            ->has('inventory.data', 3)
            ->where('inventory.data.0.id', $soldOut->id)
            ->where('inventory.data.0.stockStatus', 'out-of-stock')
            ->where('inventory.data.1.id', $firstTie->id)
            ->where('inventory.data.2.id', $secondTie->id)
        );
    }

    public function test_inventory_movements_are_localized_and_serialized_for_audit(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'username' => 'inventory-auditor',
        ]);
        $souvenir = Souvenir::factory()->create([
            'name' => ['id' => 'Teh Sakura', 'en' => 'Sakura Tea'],
            'stock' => 6,
        ]);
        InventoryMovement::create([
            'souvenir_id' => $souvenir->id,
            'actor_id' => $admin->id,
            'type' => InventoryMovement::TYPE_ADMIN_RESTOCK,
            'quantity_delta' => 4,
            'stock_before' => 2,
            'stock_after' => 6,
            'reference' => 'admin-audit-reference',
            'product_name_snapshot' => ['id' => 'Teh Sakura', 'en' => 'Sakura Tea'],
            'actor_name_snapshot' => 'inventory-auditor',
            'metadata' => ['source' => 'test'],
        ]);

        $response = $this
            ->actingAs($admin, 'admin')
            ->withCookie('locale', 'en')
            ->get(route('admin.inventory.low-stock'));

        $response->assertInertia(fn (Assert $page) => $page
            ->has('movements', 1)
            ->where('movements.0.productName', 'Sakura Tea')
            ->where('movements.0.type', InventoryMovement::TYPE_ADMIN_RESTOCK)
            ->where('movements.0.typeLabel', 'Admin restock')
            ->where('movements.0.quantityDelta', 4)
            ->where('movements.0.quantityDeltaLabel', '+4')
            ->where('movements.0.stockBefore', '2')
            ->where('movements.0.stockAfter', '6')
            ->where('movements.0.actor', 'inventory-auditor')
            ->where('movements.0.orderReference', '—')
            ->where('movements.0.reference', 'admin-audit-reference')
            ->missing('movements.0.metadata')
            ->missing('movements.0.souvenir')
        );
    }

    public function test_low_stock_threshold_is_normalized_to_at_least_one(): void
    {
        $included = Souvenir::factory()->create(['stock' => 1]);
        Souvenir::factory()->create(['stock' => 2]);

        $response = $this
            ->actingAs($this->createAdmin(), 'admin')
            ->get(route('admin.inventory.low-stock', ['threshold' => 0]));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('filters.threshold', 1)
            ->has('inventory.data', 1)
            ->where('inventory.data.0.id', $included->id)
        );
    }

    public function test_low_stock_pagination_uses_relative_urls_and_preserves_threshold(): void
    {
        Souvenir::factory()->count(16)->create(['stock' => 3]);

        $response = $this
            ->actingAs($this->createAdmin(), 'admin')
            ->get(route('admin.inventory.low-stock', ['threshold' => 5]));

        $response->assertInertia(fn (Assert $page) => $page
            ->has('inventory.data', 15)
            ->where('inventory.pagination.currentPage', 1)
            ->where('inventory.pagination.lastPage', 2)
            ->where('inventory.pagination.total', 16)
            ->where('inventory.pagination.nextUrl', '/admin/inventory/low-stock?threshold=5&page=2')
            ->where('inventory.pagination.pages.1.url', '/admin/inventory/low-stock?threshold=5&page=2')
        );
    }

    public function test_admin_can_restock_souvenir_and_cache_is_invalidated(): void
    {
        Cache::put(CacheKeys::SOUVENIRS_VERSION, 7);
        $admin = $this->createAdmin();
        $souvenir = Souvenir::factory()->create(['stock' => 2]);
        $returnUrl = route('admin.inventory.low-stock', ['threshold' => 5]);
        $adjustmentToken = (string) Str::uuid();

        $response = $this
            ->actingAs($admin, 'admin')
            ->withCookie('locale', 'en')
            ->from($returnUrl)
            ->post(route('admin.inventory.restock', $souvenir), [
                'adjustment_token' => $adjustmentToken,
                'amount' => 4,
            ]);

        $response->assertRedirect($returnUrl);
        $response->assertSessionHas('success', 'Stock added successfully.');
        $this->assertSame(6, (int) $souvenir->refresh()->stock);
        $this->assertSame(8, CacheKeys::version(CacheKeys::SOUVENIRS_VERSION));
        $this->assertDatabaseHas('inventory_movements', [
            'souvenir_id' => $souvenir->id,
            'order_id' => null,
            'actor_id' => $admin->id,
            'type' => InventoryMovement::TYPE_ADMIN_RESTOCK,
            'quantity_delta' => 4,
            'stock_before' => 2,
            'stock_after' => 6,
            'reference' => 'admin:'.$admin->id.':souvenir:'.$souvenir->id.':'.$adjustmentToken,
        ]);
    }

    public function test_restock_rejects_amounts_outside_allowed_range(): void
    {
        Cache::put(CacheKeys::SOUVENIRS_VERSION, 7);
        $souvenir = Souvenir::factory()->create(['stock' => 2]);
        $returnUrl = route('admin.inventory.low-stock', ['threshold' => 5]);

        foreach ([0, 10001, 'not-an-integer'] as $amount) {
            $response = $this
                ->actingAs($this->createAdmin(), 'admin')
                ->from($returnUrl)
                ->post(route('admin.inventory.restock', $souvenir), [
                    'adjustment_token' => (string) Str::uuid(),
                    'amount' => $amount,
                ]);

            $response->assertRedirect($returnUrl);
            $response->assertSessionHasErrors('amount');
            $this->assertSame(2, (int) $souvenir->refresh()->stock);
            $this->assertSame(7, CacheKeys::version(CacheKeys::SOUVENIRS_VERSION));
        }
    }

    public function test_admin_can_reduce_stock_to_zero_and_cache_is_invalidated(): void
    {
        Cache::put(CacheKeys::SOUVENIRS_VERSION, 7);
        $admin = $this->createAdmin();
        $souvenir = Souvenir::factory()->create(['stock' => 4]);
        $returnUrl = route('admin.inventory.low-stock', ['threshold' => 5]);
        $adjustmentToken = (string) Str::uuid();

        $response = $this
            ->actingAs($admin, 'admin')
            ->withCookie('locale', 'en')
            ->from($returnUrl)
            ->post(route('admin.inventory.deduct', $souvenir), [
                'adjustment_token' => $adjustmentToken,
                'amount' => 4,
            ]);

        $response->assertRedirect($returnUrl);
        $response->assertSessionHas('success', 'Stock reduced successfully.');
        $this->assertSame(0, (int) $souvenir->refresh()->stock);
        $this->assertSame(8, CacheKeys::version(CacheKeys::SOUVENIRS_VERSION));
        $this->assertDatabaseHas('inventory_movements', [
            'souvenir_id' => $souvenir->id,
            'actor_id' => $admin->id,
            'type' => InventoryMovement::TYPE_ADMIN_DEDUCTION,
            'quantity_delta' => -4,
            'stock_before' => 4,
            'stock_after' => 0,
            'reference' => 'admin:'.$admin->id.':souvenir:'.$souvenir->id.':'.$adjustmentToken,
        ]);
    }

    public function test_stock_reduction_rejects_an_amount_above_current_stock(): void
    {
        Cache::put(CacheKeys::SOUVENIRS_VERSION, 7);
        $souvenir = Souvenir::factory()->create(['stock' => 3]);
        $returnUrl = route('admin.inventory.low-stock', ['threshold' => 5]);

        $response = $this
            ->actingAs($this->createAdmin(), 'admin')
            ->withCookie('locale', 'en')
            ->from($returnUrl)
            ->post(route('admin.inventory.deduct', $souvenir), [
                'adjustment_token' => (string) Str::uuid(),
                'amount' => 4,
            ]);

        $response->assertRedirect($returnUrl);
        $response->assertSessionHasErrors([
            'amount' => 'Insufficient stock. Current stock: 3.',
        ]);
        $this->assertSame(3, (int) $souvenir->refresh()->stock);
        $this->assertSame(7, CacheKeys::version(CacheKeys::SOUVENIRS_VERSION));
        $this->assertDatabaseCount('inventory_movements', 0);
    }

    public function test_stock_reduction_rejects_amounts_outside_allowed_range(): void
    {
        Cache::put(CacheKeys::SOUVENIRS_VERSION, 7);
        $souvenir = Souvenir::factory()->create(['stock' => 20_000]);
        $returnUrl = route('admin.inventory.low-stock', ['threshold' => 20_000]);

        foreach ([0, 10001, 'not-an-integer'] as $amount) {
            $response = $this
                ->actingAs($this->createAdmin(), 'admin')
                ->from($returnUrl)
                ->post(route('admin.inventory.deduct', $souvenir), [
                    'adjustment_token' => (string) Str::uuid(),
                    'amount' => $amount,
                ]);

            $response->assertRedirect($returnUrl);
            $response->assertSessionHasErrors('amount');
            $this->assertSame(20_000, (int) $souvenir->refresh()->stock);
            $this->assertSame(7, CacheKeys::version(CacheKeys::SOUVENIRS_VERSION));
        }
    }

    public function test_repeated_admin_adjustment_token_changes_stock_and_cache_once(): void
    {
        Cache::put(CacheKeys::SOUVENIRS_VERSION, 7);
        $admin = $this->createAdmin();
        $souvenir = Souvenir::factory()->create(['stock' => 2]);
        $adjustmentToken = (string) Str::uuid();
        $payload = [
            'adjustment_token' => $adjustmentToken,
            'amount' => 4,
        ];

        $this->actingAs($admin, 'admin')
            ->post(route('admin.inventory.restock', $souvenir), $payload)
            ->assertSessionHas('success');

        $this->post(route('admin.inventory.restock', $souvenir), $payload)
            ->assertSessionHas('success');

        $this->assertSame(6, (int) $souvenir->refresh()->stock);
        $this->assertSame(8, CacheKeys::version(CacheKeys::SOUVENIRS_VERSION));
        $this->assertDatabaseCount('inventory_movements', 1);
        $this->assertDatabaseHas('inventory_movements', [
            'reference' => 'admin:'.$admin->id.':souvenir:'.$souvenir->id.':'.$adjustmentToken,
        ]);
    }

    public function test_inventory_history_survives_product_deletion_with_snapshot(): void
    {
        $admin = $this->createAdmin();
        $souvenir = Souvenir::factory()->create([
            'name' => ['id' => 'Teh Sakura', 'en' => 'Sakura Tea'],
            'stock' => 2,
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.inventory.restock', $souvenir), [
                'adjustment_token' => (string) Str::uuid(),
                'amount' => 4,
            ])
            ->assertSessionHas('success');

        $movement = InventoryMovement::query()->firstOrFail();
        $souvenir->delete();
        $admin->delete();
        $movement->refresh();

        $this->assertNull($movement->souvenir_id);
        $this->assertNull($movement->actor_id);
        $this->assertSame($admin->username, $movement->actor_name_snapshot);
        $this->assertEquals(['id' => 'Teh Sakura', 'en' => 'Sakura Tea'], $movement->product_name_snapshot);
    }

    private function createAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }
}
