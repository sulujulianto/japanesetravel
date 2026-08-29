<?php

namespace Tests\Feature;

use App\Models\Souvenir;
use App\Models\User;
use App\Support\CacheKeys;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminInertiaSouvenirsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_souvenirs_are_rendered_by_inertia_with_explicit_contract(): void
    {
        $response = $this
            ->actingAs($this->createAdmin(), 'admin')
            ->withCookie('locale', 'id')
            ->get(route('admin.souvenirs.index'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Souvenirs/Index')
            ->where('copy.title', 'Kelola Souvenir')
            ->where('routes.souvenirs', '/admin/souvenirs')
            ->where('routes.createSouvenir', '/admin/souvenirs/create')
            ->has('souvenirs.data', 0)
            ->where('souvenirs.pagination.currentPage', 1)
            ->where('souvenirs.pagination.total', 0)
        );
    }

    public function test_souvenirs_are_localized_formatted_and_serialized_without_exposing_models(): void
    {
        Storage::fake('public');
        config(['media.disk' => 'public']);
        Storage::disk('public')->put('uploads/souvenirs/matcha.webp', 'image');

        $souvenir = Souvenir::factory()->create([
            'description' => ['id' => 'Teh lokal', 'en' => 'Local tea'],
            'image' => 'uploads/souvenirs/matcha.webp',
            'name' => ['id' => 'Matcha Kyoto', 'en' => 'Kyoto Matcha'],
            'price' => 125000,
            'stock' => 3,
        ]);

        $response = $this
            ->actingAs($this->createAdmin(), 'admin')
            ->withCookie('locale', 'en')
            ->get(route('admin.souvenirs.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('souvenirs.data.0.id', $souvenir->id)
            ->where('souvenirs.data.0.reference', 'SKU #'.$souvenir->id)
            ->where('souvenirs.data.0.name', 'Kyoto Matcha')
            ->where('souvenirs.data.0.price', 'IDR 125,000')
            ->where('souvenirs.data.0.stock', 3)
            ->where('souvenirs.data.0.stockLabel', 'Low')
            ->where('souvenirs.data.0.stockStatus', 'low')
            ->where('souvenirs.data.0.imageUrl', Storage::disk('public')->url('uploads/souvenirs/matcha.webp'))
            ->where('souvenirs.data.0.editUrl', '/admin/souvenirs/'.$souvenir->id.'/edit')
            ->where('souvenirs.data.0.deleteUrl', '/admin/souvenirs/'.$souvenir->id)
            ->where('souvenirs.data.0.deleteConfirmation', 'Are you sure you want to delete Kyoto Matcha? This cannot be undone.')
            ->missing('souvenirs.data.0.description')
            ->missing('souvenirs.data.0.image')
            ->missing('souvenirs.data.0.created_at')
            ->missing('souvenirs.data.0.updated_at')
        );
    }

    public function test_souvenirs_serialize_each_stock_state(): void
    {
        Souvenir::factory()->create(['stock' => 6]);
        Souvenir::factory()->create(['stock' => 5]);
        Souvenir::factory()->create(['stock' => 0]);

        $response = $this
            ->actingAs($this->createAdmin(), 'admin')
            ->withCookie('locale', 'en')
            ->get(route('admin.souvenirs.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('souvenirs.data.0.stockStatus', 'out-of-stock')
            ->where('souvenirs.data.0.stockLabel', 'Sold Out')
            ->where('souvenirs.data.1.stockStatus', 'low')
            ->where('souvenirs.data.1.stockLabel', 'Low')
            ->where('souvenirs.data.2.stockStatus', 'available')
            ->where('souvenirs.data.2.stockLabel', 'Available')
        );
    }

    public function test_souvenirs_pagination_uses_relative_urls(): void
    {
        Souvenir::factory()->count(11)->create();

        $response = $this
            ->actingAs($this->createAdmin(), 'admin')
            ->get(route('admin.souvenirs.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->has('souvenirs.data', 10)
            ->where('souvenirs.pagination.currentPage', 1)
            ->where('souvenirs.pagination.lastPage', 2)
            ->where('souvenirs.pagination.total', 11)
            ->where('souvenirs.pagination.nextUrl', '/admin/souvenirs?page=2')
            ->where('souvenirs.pagination.pages.1.url', '/admin/souvenirs?page=2')
        );
    }

    public function test_admin_can_delete_souvenir_and_its_uploaded_image(): void
    {
        Storage::fake('public');
        config(['media.disk' => 'public']);
        Cache::put(CacheKeys::SOUVENIRS_VERSION, 7);
        Storage::disk('public')->put('uploads/souvenirs/delete-me.webp', 'image');

        $souvenir = Souvenir::factory()->create([
            'image' => 'uploads/souvenirs/delete-me.webp',
        ]);

        $response = $this
            ->actingAs($this->createAdmin(), 'admin')
            ->withCookie('locale', 'en')
            ->delete(route('admin.souvenirs.destroy', $souvenir));

        $response->assertRedirect(route('admin.souvenirs.index'));
        $response->assertSessionHas('success', 'Product deleted.');
        $this->assertDatabaseMissing('souvenirs', ['id' => $souvenir->id]);
        Storage::disk('public')->assertMissing('uploads/souvenirs/delete-me.webp');
        $this->assertSame(8, CacheKeys::version(CacheKeys::SOUVENIRS_VERSION));
    }

    private function createAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }
}
