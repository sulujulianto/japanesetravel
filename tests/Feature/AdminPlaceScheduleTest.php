<?php

namespace Tests\Feature;

use App\Models\Place;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPlaceScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_form_uses_structured_day_and_twelve_hour_time_controls(): void
    {
        $response = $this
            ->actingAs($this->createAdmin(), 'admin')
            ->get(route('admin.places.create'));

        $response
            ->assertOk()
            ->assertSee('name="open_day_start"', false)
            ->assertSee('name="open_day_end"', false)
            ->assertSee('name="open_time_start_hour"', false)
            ->assertSee('name="open_time_end_period"', false)
            ->assertSee('value="AM"', false)
            ->assertSee('value="PM"', false)
            ->assertDontSee('name="open_days"', false)
            ->assertDontSee('name="open_hours"', false);
    }

    public function test_admin_can_store_a_structured_place_schedule(): void
    {
        $response = $this
            ->actingAs($this->createAdmin(), 'admin')
            ->post(route('admin.places.store'), [
                ...$this->placePayload(),
                ...$this->schedulePayload(),
            ]);

        $response->assertRedirect(route('admin.places.index'));

        $place = Place::query()->latest('id')->firstOrFail();
        $this->assertSame('Senin - Jumat', $place->open_days);
        $this->assertSame('09:30 AM - 05:45 PM', $place->open_hours);
        $this->assertSame([
            'version' => 1,
            'dayStart' => 'monday',
            'dayEnd' => 'friday',
            'timeStart' => ['hour' => 9, 'minute' => '30', 'period' => 'AM'],
            'timeEnd' => ['hour' => 5, 'minute' => '45', 'period' => 'PM'],
        ], $place->opening_hours);
    }

    public function test_edit_form_preselects_a_structured_schedule(): void
    {
        $place = $this->createPlace([
            'open_days' => 'Senin - Jumat',
            'open_hours' => '09:30 AM - 05:45 PM',
            'opening_hours' => [
                'version' => 1,
                'dayStart' => 'monday',
                'dayEnd' => 'friday',
                'timeStart' => ['hour' => 9, 'minute' => '30', 'period' => 'AM'],
                'timeEnd' => ['hour' => 5, 'minute' => '45', 'period' => 'PM'],
            ],
        ]);

        $response = $this
            ->actingAs($this->createAdmin(), 'admin')
            ->get(route('admin.places.edit', $place));

        $response
            ->assertOk()
            ->assertSee('value="monday" selected', false)
            ->assertSee('<option value="PM" selected>PM</option>', false)
            ->assertSee('name="clear_schedule"', false);
    }

    public function test_updating_other_fields_preserves_a_legacy_free_form_schedule(): void
    {
        $place = $this->createPlace([
            'open_days' => 'Setiap hari kecuali hari libur nasional',
            'open_hours' => 'Area terbuka; fasilitas memiliki jadwal masing-masing',
        ]);

        $response = $this
            ->actingAs($this->createAdmin(), 'admin')
            ->put(route('admin.places.update', $place), $this->placePayload(['address' => 'Kyoto']));

        $response->assertRedirect(route('admin.places.index'));
        $place->refresh();

        $this->assertSame('Kyoto', $place->address);
        $this->assertSame('Setiap hari kecuali hari libur nasional', $place->open_days);
        $this->assertSame('Area terbuka; fasilitas memiliki jadwal masing-masing', $place->open_hours);
        $this->assertNull($place->opening_hours);
    }

    public function test_an_incomplete_structured_schedule_is_rejected(): void
    {
        $response = $this
            ->actingAs($this->createAdmin(), 'admin')
            ->from(route('admin.places.create'))
            ->post(route('admin.places.store'), [
                ...$this->placePayload(),
                'open_day_start' => 'monday',
            ]);

        $response
            ->assertRedirect(route('admin.places.create'))
            ->assertSessionHasErrors([
                'open_day_end',
                'open_time_start_hour',
                'open_time_start_minute',
                'open_time_start_period',
                'open_time_end_hour',
                'open_time_end_minute',
                'open_time_end_period',
            ]);
        $this->assertDatabaseCount('places', 0);
    }

    public function test_admin_can_clear_an_existing_schedule(): void
    {
        $place = $this->createPlace([
            'open_days' => 'Senin - Jumat',
            'open_hours' => '09:30 AM - 05:45 PM',
            'opening_hours' => [
                'version' => 1,
                'dayStart' => 'monday',
                'dayEnd' => 'friday',
                'timeStart' => ['hour' => 9, 'minute' => '30', 'period' => 'AM'],
                'timeEnd' => ['hour' => 5, 'minute' => '45', 'period' => 'PM'],
            ],
        ]);

        $response = $this
            ->actingAs($this->createAdmin(), 'admin')
            ->put(route('admin.places.update', $place), [
                ...$this->placePayload(),
                'clear_schedule' => '1',
            ]);

        $response->assertRedirect(route('admin.places.index'));
        $place->refresh();

        $this->assertNull($place->open_days);
        $this->assertNull($place->open_hours);
        $this->assertNull($place->opening_hours);
    }

    private function createAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    /** @param  array<string, mixed>  $attributes */
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

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function placePayload(array $overrides = []): array
    {
        return array_merge([
            'name_id' => 'Destinasi Baru',
            'name_en' => 'New Destination',
            'description_id' => 'Deskripsi destinasi',
            'description_en' => 'Destination description',
            'address' => 'Tokyo',
            'facilities' => 'WiFi',
        ], $overrides);
    }

    /** @return array<string, string> */
    private function schedulePayload(): array
    {
        return [
            'open_day_start' => 'monday',
            'open_day_end' => 'friday',
            'open_time_start_hour' => '9',
            'open_time_start_minute' => '30',
            'open_time_start_period' => 'AM',
            'open_time_end_hour' => '5',
            'open_time_end_minute' => '45',
            'open_time_end_period' => 'PM',
        ];
    }
}
