<?php

namespace Tests\Feature;

use App\Models\Place;
use App\Models\User;
use App\Support\CacheKeys;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminInertiaPlacesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_places_are_rendered_by_inertia_with_explicit_contract(): void
    {
        $response = $this
            ->actingAs($this->createAdmin(), 'admin')
            ->withCookie('locale', 'id')
            ->get(route('admin.places.index'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Places/Index')
            ->where('copy.title', 'Kelola Destinasi Wisata')
            ->where('routes.places', '/admin/places')
            ->where('routes.createPlace', '/admin/places/create')
            ->has('places.data', 0)
            ->where('places.pagination.currentPage', 1)
            ->where('places.pagination.total', 0)
        );
    }

    public function test_admin_place_create_form_is_rendered_by_inertia_with_explicit_contract(): void
    {
        $response = $this
            ->actingAs($this->createAdmin(), 'admin')
            ->withCookie('locale', 'en')
            ->get(route('admin.places.create'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Places/Form')
            ->where('mode', 'create')
            ->where('copy.title', 'Add New Destination')
            ->where('routes.places', '/admin/places')
            ->where('routes.submitPlace', '/admin/places')
            ->where('initialValues.nameId', '')
            ->where('initialValues.currentImageUrl', null)
            ->where('initialValues.hasSchedule', false)
            ->has('scheduleOptions.days', 7)
            ->where('scheduleOptions.days.0.value', 'monday')
            ->where('scheduleOptions.hours.0', 1)
            ->where('scheduleOptions.periods.1', 'PM')
            ->where('scheduleValues.open_day_start', '')
        );
    }

    public function test_admin_place_edit_form_serializes_only_explicit_fields(): void
    {
        Storage::fake('public');
        config(['media.disk' => 'public']);
        Storage::disk('public')->put('uploads/places/edit.webp', 'image');

        $place = $this->createPlace([
            'address' => 'Kyoto',
            'description' => ['id' => 'Deskripsi Kyoto', 'en' => 'Kyoto description'],
            'facilities' => 'WiFi',
            'image' => 'uploads/places/edit.webp',
            'name' => ['id' => 'Kuil Kyoto', 'en' => 'Kyoto Temple'],
        ]);

        $response = $this
            ->actingAs($this->createAdmin(), 'admin')
            ->withCookie('locale', 'en')
            ->get(route('admin.places.edit', $place));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Places/Form')
            ->where('mode', 'edit')
            ->where('copy.title', 'Edit Destination')
            ->where('routes.submitPlace', '/admin/places/'.$place->id)
            ->where('initialValues.nameId', 'Kuil Kyoto')
            ->where('initialValues.nameEn', 'Kyoto Temple')
            ->where('initialValues.descriptionId', 'Deskripsi Kyoto')
            ->where('initialValues.descriptionEn', 'Kyoto description')
            ->where('initialValues.address', 'Kyoto')
            ->where('initialValues.facilities', 'WiFi')
            ->where('initialValues.currentImageAlt', 'Kyoto Temple')
            ->where('initialValues.currentImageUrl', Storage::disk('public')->url('uploads/places/edit.webp'))
            ->where('initialValues.hasSchedule', false)
            ->missing('initialValues.slug')
            ->missing('initialValues.created_by')
            ->missing('initialValues.opening_hours')
        );
    }

    public function test_places_are_localized_and_serialized_without_exposing_models(): void
    {
        Storage::fake('public');
        config(['media.disk' => 'public']);
        Storage::disk('public')->put('uploads/places/kyoto.webp', 'image');

        $place = $this->createPlace([
            'address' => null,
            'image' => 'uploads/places/kyoto.webp',
            'name' => ['id' => 'Kuil Kyoto', 'en' => 'Kyoto Temple'],
            'slug' => 'kyoto-temple',
        ]);

        $response = $this
            ->actingAs($this->createAdmin(), 'admin')
            ->withCookie('locale', 'en')
            ->get(route('admin.places.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('places.data.0.id', $place->id)
            ->where('places.data.0.reference', '#'.$place->id)
            ->where('places.data.0.name', 'Kyoto Temple')
            ->where('places.data.0.address', 'Address has not been added.')
            ->where('places.data.0.imageUrl', Storage::disk('public')->url('uploads/places/kyoto.webp'))
            ->where('places.data.0.editUrl', '/admin/places/'.$place->id.'/edit')
            ->where('places.data.0.deleteUrl', '/admin/places/'.$place->id)
            ->where('places.data.0.deleteConfirmation', 'Are you sure you want to delete Kyoto Temple? This cannot be undone.')
            ->missing('places.data.0.slug')
            ->missing('places.data.0.description')
            ->missing('places.data.0.created_by')
        );
    }

    public function test_places_pagination_uses_relative_urls(): void
    {
        foreach (range(1, 11) as $index) {
            $this->createPlace([
                'name' => ['id' => 'Destinasi '.$index, 'en' => 'Destination '.$index],
                'slug' => 'destination-'.$index,
            ]);
        }

        $response = $this
            ->actingAs($this->createAdmin(), 'admin')
            ->get(route('admin.places.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->has('places.data', 10)
            ->where('places.pagination.currentPage', 1)
            ->where('places.pagination.lastPage', 2)
            ->where('places.pagination.total', 11)
            ->where('places.pagination.nextUrl', '/admin/places?page=2')
            ->where('places.pagination.pages.1.url', '/admin/places?page=2')
        );
    }

    public function test_admin_can_delete_place_and_its_uploaded_image(): void
    {
        Storage::fake('public');
        config(['media.disk' => 'public']);
        Cache::put(CacheKeys::PLACES_VERSION, 7);
        Storage::disk('public')->put('uploads/places/delete-me.webp', 'image');

        $place = $this->createPlace([
            'image' => 'uploads/places/delete-me.webp',
        ]);

        $response = $this
            ->actingAs($this->createAdmin(), 'admin')
            ->withCookie('locale', 'en')
            ->delete(route('admin.places.destroy', $place));

        $response->assertRedirect(route('admin.places.index'));
        $response->assertSessionHas('success', 'Destination deleted.');
        $this->assertDatabaseMissing('places', ['id' => $place->id]);
        Storage::disk('public')->assertMissing('uploads/places/delete-me.webp');
        $this->assertSame(8, CacheKeys::version(CacheKeys::PLACES_VERSION));
    }

    private function createAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    /** @param array<string, mixed> $attributes */
    private function createPlace(array $attributes = []): Place
    {
        return Place::create(array_merge([
            'address' => 'Tokyo',
            'created_by' => null,
            'description' => ['id' => 'Deskripsi', 'en' => 'Description'],
            'facilities' => null,
            'image' => null,
            'name' => ['id' => 'Destinasi', 'en' => 'Destination'],
            'open_days' => null,
            'open_hours' => null,
            'opening_hours' => null,
            'slug' => 'destination-'.uniqid(),
        ], $attributes));
    }
}
