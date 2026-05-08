<?php

namespace Tests\Feature;

use App\Models\Place;
use App\Models\Souvenir;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminMediaUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_store_place_uploads_as_webp_for_jpeg_and_png(): void
    {
        $admin = $this->createAdmin();

        foreach (['cover.jpg', 'cover.png'] as $index => $filename) {
            Storage::fake('public');
            config(['media.disk' => 'public']);

            $response = $this->actingAs($admin, 'admin')->post(route('admin.places.store'), [
                'name_id' => "Tempat {$index}",
                'name_en' => "Place {$index}",
                'description_id' => 'Deskripsi tempat',
                'description_en' => 'Place description',
                'address' => 'Tokyo',
                'facilities' => 'WiFi,Restoran',
                'open_days' => 'Mon-Sun',
                'open_hours' => '09:00-18:00',
                'image' => UploadedFile::fake()->image($filename, 1200, 900),
            ]);

            $response->assertRedirect(route('admin.places.index'));

            $place = Place::query()->latest('id')->firstOrFail();

            $this->assertStringEndsWith('.webp', $place->image);
            Storage::disk('public')->assertExists($place->image);
        }
    }

    public function test_admin_place_image_replacement_deletes_old_file(): void
    {
        Storage::fake('public');
        config(['media.disk' => 'public']);

        $admin = $this->createAdmin();
        $oldPath = 'uploads/places/old-place.webp';
        Storage::disk('public')->put($oldPath, 'old-image');

        $place = Place::create([
            'name' => ['id' => 'Lama', 'en' => 'Old'],
            'slug' => 'old-place',
            'description' => ['id' => 'Deskripsi lama', 'en' => 'Old description'],
            'image' => $oldPath,
            'address' => 'Kyoto',
            'facilities' => 'Cafe',
            'open_days' => 'Daily',
            'open_hours' => '08:00-17:00',
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin, 'admin')->put(route('admin.places.update', $place), [
            'name_id' => 'Baru',
            'name_en' => 'New',
            'description_id' => 'Deskripsi baru',
            'description_en' => 'New description',
            'address' => 'Kyoto',
            'facilities' => 'Cafe',
            'open_days' => 'Daily',
            'open_hours' => '08:00-17:00',
            'image' => UploadedFile::fake()->image('replacement.jpg', 1200, 900),
        ]);

        $response->assertRedirect(route('admin.places.index'));

        $place->refresh();

        $this->assertStringEndsWith('.webp', $place->image);
        $this->assertNotSame($oldPath, $place->image);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($place->image);
    }

    public function test_admin_can_store_souvenir_uploads_as_webp_for_jpeg_and_png(): void
    {
        $admin = $this->createAdmin();

        foreach (['item.jpg', 'item.png'] as $index => $filename) {
            Storage::fake('public');
            config(['media.disk' => 'public']);

            $response = $this->actingAs($admin, 'admin')->post(route('admin.souvenirs.store'), [
                'name_id' => "Souvenir {$index}",
                'name_en' => "Souvenir {$index}",
                'description_id' => 'Souvenir lokal',
                'description_en' => 'Local souvenir',
                'price' => 100000,
                'stock' => 5,
                'image' => UploadedFile::fake()->image($filename, 1200, 900),
            ]);

            $response->assertRedirect(route('admin.souvenirs.index'));

            $souvenir = Souvenir::query()->latest('id')->firstOrFail();

            $this->assertStringEndsWith('.webp', $souvenir->image);
            Storage::disk('public')->assertExists($souvenir->image);
        }
    }

    public function test_admin_souvenir_image_replacement_deletes_old_file(): void
    {
        Storage::fake('public');
        config(['media.disk' => 'public']);

        $admin = $this->createAdmin();
        $oldPath = 'uploads/souvenirs/old-souvenir.webp';
        Storage::disk('public')->put($oldPath, 'old-image');

        $souvenir = Souvenir::create([
            'name' => ['id' => 'Matcha lama', 'en' => 'Old Matcha'],
            'description' => ['id' => 'Deskripsi lama', 'en' => 'Old description'],
            'price' => 50000,
            'stock' => 10,
            'image' => $oldPath,
        ]);

        $response = $this->actingAs($admin, 'admin')->put(route('admin.souvenirs.update', $souvenir), [
            'name_id' => 'Matcha baru',
            'name_en' => 'New Matcha',
            'description_id' => 'Deskripsi baru',
            'description_en' => 'New description',
            'price' => 75000,
            'stock' => 12,
            'image' => UploadedFile::fake()->image('replacement.png', 1200, 900),
        ]);

        $response->assertRedirect(route('admin.souvenirs.index'));

        $souvenir->refresh();

        $this->assertStringEndsWith('.webp', $souvenir->image);
        $this->assertNotSame($oldPath, $souvenir->image);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($souvenir->image);
    }

    protected function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
        ]);
    }
}
