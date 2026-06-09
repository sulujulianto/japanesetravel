<?php

namespace Tests\Feature;

use App\Models\Place;
use App\Models\PlaceReview;
use App\Models\User;
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

    public function test_destination_detail_does_not_render_placeholder_map_copy(): void
    {
        $place = $this->createPlace();

        $this->withHeader('Accept-Language', 'id-ID,id;q=0.9')
            ->get(route('place.show', $place->slug))
            ->assertOk()
            ->assertSee('Lokasi')
            ->assertDontSee('Peta interaktif belum tersedia.');
    }

    public function test_destination_catalog_formats_rating_for_indonesian_locale(): void
    {
        $place = $this->createPlace();
        $this->createReview($place, 4);
        $this->createReview($place, 5);

        $this->withHeader('Accept-Language', 'id-ID,id;q=0.9')
            ->get(route('places.index'))
            ->assertOk()
            ->assertSee('Rating 4,5');
    }

    public function test_destination_catalog_formats_rating_for_english_locale(): void
    {
        $place = $this->createPlace();
        $this->createReview($place, 4);
        $this->createReview($place, 5);

        $this->withHeader('Accept-Language', 'en-US,en;q=0.9')
            ->get(route('places.index'))
            ->assertOk()
            ->assertSee('Rating 4.5')
            ->assertDontSee('Rating 4,5');
    }

    public function test_destination_catalog_pluralizes_review_count_for_indonesian_locale(): void
    {
        $singleReviewPlace = $this->createPlace('Single Review Place', 'single-review-place');
        $multipleReviewPlace = $this->createPlace('Multiple Review Place', 'multiple-review-place');
        $this->createReview($singleReviewPlace, 5);
        $this->createReview($multipleReviewPlace, 4);
        $this->createReview($multipleReviewPlace, 5);

        $this->withHeader('Accept-Language', 'id-ID,id;q=0.9')
            ->get(route('places.index'))
            ->assertOk()
            ->assertSee('1 ulasan')
            ->assertSee('2 ulasan');
    }

    public function test_destination_catalog_pluralizes_review_count_for_english_locale(): void
    {
        $singleReviewPlace = $this->createPlace('Single Review Place', 'single-review-place');
        $multipleReviewPlace = $this->createPlace('Multiple Review Place', 'multiple-review-place');
        $this->createReview($singleReviewPlace, 5);
        $this->createReview($multipleReviewPlace, 4);
        $this->createReview($multipleReviewPlace, 5);

        $this->withHeader('Accept-Language', 'en-US,en;q=0.9')
            ->get(route('places.index'))
            ->assertOk()
            ->assertSee('1 review')
            ->assertSee('2 reviews')
            ->assertDontSee('1 reviews');
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

    private function createReview(Place $place, int $rating): PlaceReview
    {
        return PlaceReview::create([
            'place_id' => $place->id,
            'user_id' => User::factory()->create()->id,
            'rating' => $rating,
            'comment' => 'Stable formatting test review.',
        ]);
    }
}
