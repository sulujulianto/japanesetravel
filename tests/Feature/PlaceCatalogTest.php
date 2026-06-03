<?php

namespace Tests\Feature;

use App\Models\Place;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PlaceCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_homepage_renders_and_links_to_destination_catalog(): void
    {
        $this->createPlace();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('places.index'), false);
    }

    public function test_destination_catalog_renders_without_query(): void
    {
        $this->createPlace();

        $this->get(route('places.index'))
            ->assertOk()
            ->assertSee('Destination catalog');
    }

    public function test_destination_catalog_supports_search_query(): void
    {
        $place = $this->createPlace(name: 'Kyoto Garden', slug: 'kyoto-garden');

        $this->get(route('places.index', ['search' => 'Kyoto']))
            ->assertOk()
            ->assertSee($place->name)
            ->assertSee(route('place.show', $place->slug), false);
    }

    public function test_destination_catalog_supports_rating_and_sort_query(): void
    {
        $this->createPlace();

        $this->get(route('places.index', [
            'rating' => '3',
            'sort' => 'latest',
        ]))->assertOk();
    }

    private function createPlace(string $name = 'Tokyo Tower', string $slug = 'tokyo-tower'): Place
    {
        return Place::create([
            'name' => [
                'id' => $name,
                'en' => $name,
            ],
            'slug' => $slug,
            'description' => [
                'id' => 'Destinasi kota dengan pemandangan yang mudah diakses.',
                'en' => 'A city destination with an accessible view.',
            ],
            'address' => 'Tokyo, Japan',
        ]);
    }
}
