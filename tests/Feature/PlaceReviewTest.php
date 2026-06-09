<?php

namespace Tests\Feature;

use App\Models\Place;
use App\Models\PlaceReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PlaceReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_submit_review(): void
    {
        $place = $this->createPlace();

        $this->post(route('review.store', $place->id), [
            'rating' => 5,
            'comment' => 'Amazing place.',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseCount('place_reviews', 0);
    }

    public function test_unverified_user_cannot_submit_review(): void
    {
        $place = $this->createPlace();
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->post(route('review.store', $place->id), [
                'rating' => 5,
                'comment' => 'Great!',
            ])
            ->assertRedirect(route('verification.notice'));

        $this->assertDatabaseCount('place_reviews', 0);
    }

    public function test_verified_user_can_submit_review(): void
    {
        $place = $this->createPlace();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withHeader('Accept-Language', 'id-ID,id;q=0.9')
            ->from(route('place.show', $place->slug))
            ->post(route('review.store', $place->id), [
                'rating' => 5,
                'comment' => 'Tempatnya bagus banget.',
            ])
            ->assertRedirect(route('place.show', $place->slug))
            ->assertSessionHas('success', 'Ulasan Anda berhasil dikirim.');

        $this->assertDatabaseHas('place_reviews', [
            'place_id' => $place->id,
            'user_id' => $user->id,
            'rating' => 5,
        ]);
    }

    public function test_same_user_cannot_submit_second_review_for_same_place(): void
    {
        $place = $this->createPlace();
        $user = User::factory()->create();

        PlaceReview::create([
            'place_id' => $place->id,
            'user_id' => $user->id,
            'rating' => 4,
            'comment' => 'Review pertama.',
        ]);

        $this->actingAs($user)
            ->withHeader('Accept-Language', 'en-US,en;q=0.9')
            ->from(route('place.show', $place->slug))
            ->post(route('review.store', $place->id), [
                'rating' => 5,
                'comment' => 'Review kedua.',
            ])
            ->assertRedirect(route('place.show', $place->slug))
            ->assertSessionHas('error', 'You have already reviewed this destination.');

        $this->assertDatabaseCount('place_reviews', 1);
    }

    public function test_different_users_can_submit_review_for_same_place(): void
    {
        $place = $this->createPlace();
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();

        PlaceReview::create([
            'place_id' => $place->id,
            'user_id' => $firstUser->id,
            'rating' => 4,
            'comment' => 'Review user pertama.',
        ]);

        $this->actingAs($secondUser)
            ->post(route('review.store', $place->id), [
                'rating' => 5,
                'comment' => 'Review user kedua.',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseCount('place_reviews', 2);
    }

    public function test_invalid_review_payload_is_rejected(): void
    {
        $place = $this->createPlace();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('place.show', $place->slug))
            ->post(route('review.store', $place->id), [
                'rating' => 0,
                'comment' => Str::repeat('a', 501),
            ])
            ->assertRedirect(route('place.show', $place->slug))
            ->assertSessionHasErrors(['rating', 'comment']);

        $this->assertDatabaseCount('place_reviews', 0);
    }

    private function createPlace(): Place
    {
        return Place::create([
            'name' => [
                'id' => 'Menara Uji',
                'en' => 'Test Tower',
            ],
            'slug' => 'test-place-'.Str::lower((string) Str::uuid()),
            'description' => [
                'id' => 'Deskripsi uji',
                'en' => 'Test description',
            ],
            'address' => 'Tokyo',
            'facilities' => 'WiFi',
            'open_days' => 'Mon-Sun',
            'open_hours' => '09:00-18:00',
            'created_by' => null,
        ]);
    }
}
