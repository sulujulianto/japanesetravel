<?php

namespace Tests\Feature;

use App\Models\Place;
use App\Models\PlaceReview;
use App\Models\Souvenir;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicInertiaHomeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_public_home_is_rendered_by_inertia_with_explicit_contract(): void
    {
        $response = $this
            ->withCookie('locale', 'id')
            ->get(route('home'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Public/Home')
            ->where('app.name', 'Japan Travel')
            ->where('app.mark', 'JT')
            ->where('app.region', 'Jepang')
            ->where('copy.pageTitle', 'Japan Travel · Portal Wisata Jepang')
            ->where('copy.heroTitle', 'Temukan destinasi Jepang dan oleh-oleh pilihan.')
            ->where('copy.themeDark', 'Tema gelap')
            ->where('copy.themeLight', 'Tema terang')
            ->where('copy.useDarkTheme', 'Gunakan tema gelap')
            ->where('copy.useLightTheme', 'Gunakan tema terang')
            ->where('routes.home', '/')
            ->where('routes.places', '/places')
            ->where('routes.shop', '/shop')
            ->where('routes.cart', '/cart')
            ->where('routes.login', '/login')
            ->where('routes.register', '/register')
            ->where('routes.localeId', '/lang/id')
            ->where('routes.localeEn', '/lang/en')
            ->where('cart.count', 0)
            ->where('summary.places', 0)
            ->where('summary.souvenirs', 0)
            ->where('summary.reviews', 0)
            ->has('featuredPlaces', 0)
        );
    }

    public function test_featured_places_are_localized_and_serialized_without_exposing_models(): void
    {
        $place = $this->createPlace([
            'address' => 'Kyoto, Japan',
            'description' => [
                'id' => 'Kuil bersejarah dengan taman yang tenang.',
                'en' => 'A historic temple with a peaceful garden.',
            ],
            'name' => ['id' => 'Kuil Kyoto', 'en' => 'Kyoto Temple'],
            'slug' => 'kyoto-temple',
        ]);
        $this->createReview($place, 4);
        $this->createReview($place, 5);

        $response = $this
            ->withCookie('locale', 'en')
            ->get(route('home'));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('copy.pageTitle', 'Japan Travel · Japan Travel Portal')
            ->where('app.region', 'Japan')
            ->where('featuredPlaces.0.id', $place->id)
            ->where('featuredPlaces.0.name', 'Kyoto Temple')
            ->where('featuredPlaces.0.address', 'Kyoto, Japan')
            ->where('featuredPlaces.0.excerpt', 'A historic temple with a peaceful garden.')
            ->where('featuredPlaces.0.initial', 'K')
            ->where('featuredPlaces.0.rating', '4.5')
            ->where('featuredPlaces.0.reviewCount', 2)
            ->where('featuredPlaces.0.reviewLabel', '2 reviews')
            ->where('featuredPlaces.0.showUrl', '/place/kyoto-temple')
            ->where('featuredPlaces.0.imageUrl', fn (string $url): bool => str_ends_with($url, '/demo/place-placeholder.svg'))
            ->missing('featuredPlaces.0.description')
            ->missing('featuredPlaces.0.created_at')
            ->missing('featuredPlaces.0.updated_at')
            ->missing('featuredPlaces.0.reviews')
        );
    }

    public function test_featured_places_are_limited_and_ordered_deterministically(): void
    {
        $timestamp = now()->subDay()->startOfSecond();
        $places = collect();

        foreach (range(1, 7) as $index) {
            $place = $this->createPlace([
                'name' => ['id' => 'Destinasi '.$index, 'en' => 'Destination '.$index],
                'slug' => 'destination-'.$index,
            ]);
            Place::query()->whereKey($place->id)->update([
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
            $places->push($place);
        }

        $response = $this->get(route('home'));

        $response->assertInertia(fn (Assert $page) => $page
            ->has('featuredPlaces', 6)
            ->where('featuredPlaces.0.id', $places->last()->id)
            ->where('featuredPlaces.5.id', $places->get(1)->id)
            ->where('featuredPlaces', function (Collection $items) use ($places): bool {
                return $items->pluck('id')->doesntContain($places->first()->id);
            })
        );
    }

    public function test_public_shell_shares_cart_count_and_authenticated_user(): void
    {
        $user = User::factory()->create(['username' => 'public-customer']);
        $firstSouvenir = Souvenir::factory()->create(['stock' => 10]);
        $secondSouvenir = Souvenir::factory()->create(['stock' => 10]);

        $response = $this
            ->actingAs($user)
            ->withSession([
                'cart' => [
                    $firstSouvenir->id => 2,
                    $secondSouvenir->id => 3,
                ],
            ])
            ->get(route('home'));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('auth.user.id', $user->id)
            ->where('auth.user.username', 'public-customer')
            ->where('cart.count', 5)
            ->where('routes.dashboard', '/dashboard')
            ->where('routes.orders', '/orders')
        );
    }

    /** @param array<string, mixed> $attributes */
    private function createPlace(array $attributes = []): Place
    {
        return Place::create(array_merge([
            'address' => 'Tokyo, Japan',
            'description' => [
                'id' => 'Destinasi kota dengan pemandangan yang mudah diakses.',
                'en' => 'A city destination with an accessible view.',
            ],
            'image' => null,
            'name' => ['id' => 'Menara Tokyo', 'en' => 'Tokyo Tower'],
            'slug' => 'destination-'.uniqid(),
        ], $attributes));
    }

    private function createReview(Place $place, int $rating): PlaceReview
    {
        return PlaceReview::create([
            'place_id' => $place->id,
            'user_id' => User::factory()->create()->id,
            'rating' => $rating,
            'comment' => 'Stable homepage contract review.',
        ]);
    }
}
