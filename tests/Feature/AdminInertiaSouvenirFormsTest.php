<?php

namespace Tests\Feature;

use App\Models\Souvenir;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminInertiaSouvenirFormsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_souvenir_create_form_is_rendered_by_inertia_with_explicit_contract(): void
    {
        $response = $this
            ->actingAs($this->createAdmin(), 'admin')
            ->withCookie('locale', 'en')
            ->get(route('admin.souvenirs.create'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Souvenirs/Form')
            ->where('copy.title', 'Add New Souvenir')
            ->where('copy.descriptionId', 'Description (ID)')
            ->where('copy.descriptionEn', 'Description (EN)')
            ->where('copy.submit', 'Save')
            ->where('initialValues.currentImageAlt', null)
            ->where('initialValues.currentImageUrl', null)
            ->where('initialValues.descriptionEn', '')
            ->where('initialValues.descriptionId', '')
            ->where('initialValues.imageRequired', true)
            ->where('initialValues.nameEn', '')
            ->where('initialValues.nameId', '')
            ->where('initialValues.price', '')
            ->where('initialValues.stock', '')
            ->where('mode', 'create')
            ->where('routes.souvenirs', '/admin/souvenirs')
            ->where('routes.submitSouvenir', '/admin/souvenirs')
        );
    }

    public function test_admin_souvenir_edit_form_serializes_only_explicit_fields(): void
    {
        Storage::fake('public');
        config(['media.disk' => 'public']);
        Storage::disk('public')->put('uploads/souvenirs/edit.webp', 'image');

        $souvenir = Souvenir::factory()->create([
            'description' => ['id' => 'Deskripsi produk', 'en' => 'Product description'],
            'image' => 'uploads/souvenirs/edit.webp',
            'name' => ['id' => 'Teh Hijau', 'en' => 'Green Tea'],
            'price' => 125000,
            'stock' => 7,
        ]);

        $response = $this
            ->actingAs($this->createAdmin(), 'admin')
            ->withCookie('locale', 'en')
            ->get(route('admin.souvenirs.edit', $souvenir));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Souvenirs/Form')
            ->where('copy.title', 'Edit Souvenir')
            ->where('copy.submit', 'Save Changes')
            ->where('initialValues.currentImageAlt', 'Green Tea')
            ->where('initialValues.currentImageUrl', Storage::disk('public')->url('uploads/souvenirs/edit.webp'))
            ->where('initialValues.descriptionEn', 'Product description')
            ->where('initialValues.descriptionId', 'Deskripsi produk')
            ->where('initialValues.imageRequired', false)
            ->where('initialValues.nameEn', 'Green Tea')
            ->where('initialValues.nameId', 'Teh Hijau')
            ->where('initialValues.price', '125000.00')
            ->where('initialValues.stock', '7')
            ->where('mode', 'edit')
            ->where('routes.submitSouvenir', '/admin/souvenirs/'.$souvenir->id)
            ->missing('initialValues.id')
            ->missing('initialValues.image')
            ->missing('initialValues.created_at')
            ->missing('initialValues.updated_at')
            ->missing('souvenir')
        );
    }

    public function test_edit_form_requires_an_upload_when_the_existing_souvenir_has_no_image(): void
    {
        $souvenir = Souvenir::factory()->create([
            'image' => null,
            'name' => ['id' => 'Produk Lama', 'en' => 'Legacy Product'],
        ]);

        $response = $this
            ->actingAs($this->createAdmin(), 'admin')
            ->get(route('admin.souvenirs.edit', $souvenir));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('initialValues.currentImageAlt', null)
            ->where('initialValues.currentImageUrl', null)
            ->where('initialValues.imageRequired', true)
        );
    }

    public function test_storing_a_souvenir_requires_both_descriptions_and_an_image(): void
    {
        $response = $this
            ->actingAs($this->createAdmin(), 'admin')
            ->from(route('admin.souvenirs.create'))
            ->post(route('admin.souvenirs.store'), [
                'name_id' => 'Produk Baru',
                'name_en' => 'New Product',
                'price' => 100000,
                'stock' => 5,
            ]);

        $response
            ->assertRedirect(route('admin.souvenirs.create'))
            ->assertSessionHasErrors(['description_id', 'description_en', 'image']);

        $this->assertDatabaseCount('souvenirs', 0);
    }

    public function test_updating_a_souvenir_requires_both_descriptions(): void
    {
        $souvenir = Souvenir::factory()->create([
            'description' => ['id' => 'Deskripsi lama', 'en' => 'Old description'],
            'image' => 'uploads/souvenirs/existing.webp',
            'name' => ['id' => 'Produk Lama', 'en' => 'Old Product'],
        ]);

        $response = $this
            ->actingAs($this->createAdmin(), 'admin')
            ->from(route('admin.souvenirs.edit', $souvenir))
            ->put(route('admin.souvenirs.update', $souvenir), [
                'name_id' => 'Produk Baru',
                'name_en' => 'New Product',
                'price' => 120000,
                'stock' => 8,
            ]);

        $response
            ->assertRedirect(route('admin.souvenirs.edit', $souvenir))
            ->assertSessionHasErrors(['description_id', 'description_en'])
            ->assertSessionDoesntHaveErrors('image');

        $souvenir->refresh();
        $this->assertSame('Produk Lama', $souvenir->getTranslation('name', 'id'));
        $this->assertSame('Old Product', $souvenir->getTranslation('name', 'en'));
    }

    public function test_updating_a_souvenir_without_an_existing_image_requires_an_upload(): void
    {
        $souvenir = Souvenir::factory()->create([
            'image' => null,
        ]);

        $response = $this
            ->actingAs($this->createAdmin(), 'admin')
            ->from(route('admin.souvenirs.edit', $souvenir))
            ->put(route('admin.souvenirs.update', $souvenir), [
                'name_id' => 'Produk Baru',
                'name_en' => 'New Product',
                'description_id' => 'Deskripsi baru',
                'description_en' => 'New description',
                'price' => 120000,
                'stock' => 8,
            ]);

        $response
            ->assertRedirect(route('admin.souvenirs.edit', $souvenir))
            ->assertSessionHasErrors('image');

        $souvenir->refresh();
        $this->assertNull($souvenir->image);
    }

    public function test_updating_a_souvenir_preserves_its_existing_image_without_a_new_upload(): void
    {
        $souvenir = Souvenir::factory()->create([
            'image' => 'uploads/souvenirs/existing.webp',
        ]);

        $response = $this
            ->actingAs($this->createAdmin(), 'admin')
            ->put(route('admin.souvenirs.update', $souvenir), [
                'name_id' => 'Produk Baru',
                'name_en' => 'New Product',
                'description_id' => 'Deskripsi baru',
                'description_en' => 'New description',
                'price' => 120000,
                'stock' => 8,
            ]);

        $response->assertRedirect(route('admin.souvenirs.index'));

        $souvenir->refresh();
        $this->assertSame('uploads/souvenirs/existing.webp', $souvenir->image);
        $this->assertSame('Deskripsi baru', $souvenir->getTranslation('description', 'id'));
        $this->assertSame('New description', $souvenir->getTranslation('description', 'en'));
    }

    private function createAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }
}
