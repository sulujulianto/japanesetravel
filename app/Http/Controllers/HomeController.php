<?php

namespace App\Http\Controllers;

use App\Models\Place;
use App\Models\PlaceReview;
use App\Models\Souvenir;
use App\Support\Brand;
use App\Support\CacheKeys;
use App\Support\Format;
use App\Support\PublicShell;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    // Halaman Depan (Preview Wisata)
    public function index(): Response
    {
        $placesVersion = CacheKeys::version(CacheKeys::PLACES_VERSION);
        $featuredPlaces = Cache::remember('home:featured-places:'.$placesVersion, now()->addMinutes(5), function () {
            return Place::query()
                ->withCount('reviews')
                ->withAvg('reviews', 'rating')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->take(6)
                ->get();
        });

        return Inertia::render('Public/Home', [
            'copy' => [...PublicShell::copy(), ...$this->homeCopy()],
            'featuredPlaces' => $featuredPlaces->map(fn (Place $place): array => $this->serializeFeaturedPlace($place))->values(),
            'routes' => PublicShell::routes(),
            'summary' => $this->summary(),
        ]);
    }

    // Halaman Katalog Wisata
    public function places(Request $request): View
    {
        $search = $request->string('search')->toString();
        $rating = $request->input('rating');
        $sort = $this->normalizePlaceSort($request->input('sort', 'latest'));
        $placesVersion = CacheKeys::version(CacheKeys::PLACES_VERSION);
        $placesKey = 'places:list:'.md5(json_encode([
            'v' => $placesVersion,
            'search' => $search,
            'rating' => $rating,
            'sort' => $sort,
            'page' => $request->integer('page', 1),
        ]));

        $places = Cache::remember($placesKey, now()->addMinutes(5), function () use ($search, $rating, $sort) {
            return $this->placeCatalogQuery($search, $rating, $sort)->paginate(9);
        });

        $places->withQueryString();

        return view('places.index', compact('places', 'search', 'rating', 'sort'));
    }

    /**
     * @return Builder<Place>
     */
    private function placeCatalogQuery(string $search, mixed $rating, string $sort): Builder
    {
        $query = Place::query()
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('name->id', 'like', '%'.$search.'%')
                    ->orWhere('name->en', 'like', '%'.$search.'%')
                    ->orWhere('description->id', 'like', '%'.$search.'%')
                    ->orWhere('description->en', 'like', '%'.$search.'%')
                    ->orWhere('address', 'like', '%'.$search.'%');
            });
        }

        if ($rating !== null && $rating !== '') {
            $query->whereRaw(
                '(select avg(place_reviews.rating) from place_reviews where place_reviews.place_id = places.id) >= ?',
                [(float) $rating]
            );
        }

        return $query->when($sort === 'rating', function ($builder) {
            $builder->orderByDesc('reviews_avg_rating');
        })->when($sort === 'reviews', function ($builder) {
            $builder->orderByDesc('reviews_count');
        })->when($sort === 'latest', function ($builder) {
            $builder->latest();
        });
    }

    private function normalizePlaceSort(mixed $sort): string
    {
        if (! in_array($sort, ['latest', 'rating', 'reviews'], true)) {
            return 'latest';
        }

        return (string) $sort;
    }

    /**
     * @return array{places: int, souvenirs: int, reviews: int}
     */
    private function summary(): array
    {
        $summaryVersion = implode(':', [
            CacheKeys::version(CacheKeys::PLACES_VERSION),
            CacheKeys::version(CacheKeys::SOUVENIRS_VERSION),
            CacheKeys::version(CacheKeys::REVIEWS_VERSION),
        ]);

        return Cache::remember('home:summary:'.$summaryVersion, now()->addMinutes(5), function () {
            return [
                'places' => Place::count(),
                'souvenirs' => Souvenir::count(),
                'reviews' => PlaceReview::count(),
            ];
        });
    }

    /**
     * @return array<string, string|array<int, string>>
     */
    private function homeCopy(): array
    {
        return [
            'allDestinations' => __('Lihat semua destinasi'),
            'emptyDestinations' => __('Belum ada destinasi...'),
            'eyebrow' => __('Penjelajahan :region', ['region' => Brand::region()]),
            'featuredDescription' => __('Mulai dari beberapa destinasi terbaru, lalu gunakan katalog lengkap untuk mencari dan membandingkan tempat.'),
            'featuredEyebrow' => __('Destinasi pilihan'),
            'featuredTitle' => __('Inspirasi perjalanan terbaru'),
            'heroDescription' => __('Lihat kota, ulasan, dan rekomendasi produk dalam satu tempat. Mulai dari destinasi, lalu lanjutkan ke katalog oleh-oleh saat Anda siap.'),
            'heroTitle' => __('Temukan destinasi :region dan oleh-oleh pilihan.', ['region' => Brand::region()]),
            'pageTitle' => Brand::name().' · '.__('Portal Wisata :region', ['region' => Brand::region()]),
            'portfolioNote' => __('Informasi perjalanan bersifat referensi. Konfirmasikan jadwal, harga, dan ketersediaan melalui kanal resmi sebelum berangkat.'),
            'primaryCta' => __('Lihat semua destinasi'),
            'proofItems' => [
                __('Katalog dwibahasa'),
                __('Ulasan pengguna terverifikasi'),
                __('Checkout oleh-oleh'),
            ],
            'quickLookDescription' => __('Sekilas dari katalog saat ini.'),
            'quickLookTitle' => __('Destinasi pilihan'),
            'rating' => __('Rating'),
            'reviews' => __('Ulasan'),
            'secondaryCta' => __('Lanjutkan ke katalog oleh-oleh'),
            'summaryPlaces' => __('Destinasi'),
            'summarySouvenirs' => __('Produk'),
            'summaryTitle' => __('Ringkasan katalog'),
            'souvenirCta' => __('Lihat Katalog'),
            'souvenirDescription' => __('Temukan produk pilihan setelah Anda melihat destinasi dan ulasan perjalanan.'),
            'souvenirEyebrow' => __('Oleh-oleh'),
            'souvenirTitle' => __('Lanjutkan ke katalog oleh-oleh'),
        ];
    }

    /**
     * @return array{id: int, name: string, address: string, excerpt: string, imageUrl: string, initial: string, rating: string, reviewCount: int, reviewLabel: string, showUrl: string}
     */
    private function serializeFeaturedPlace(Place $place): array
    {
        $reviewCount = (int) ($place->reviews_count ?? 0);
        $name = (string) $place->name;

        return [
            'id' => (int) $place->getKey(),
            'name' => $name,
            'address' => (string) ($place->address ?? ''),
            'excerpt' => Str::limit((string) $place->description, 100),
            'imageUrl' => $place->image_url ?: asset('demo/place-placeholder.svg'),
            'initial' => Str::upper(Str::substr($name, 0, 1)),
            'rating' => Format::rating($place->reviews_avg_rating ?? 0),
            'reviewCount' => $reviewCount,
            'reviewLabel' => trans_choice('Jumlah ulasan', $reviewCount, ['count' => $reviewCount]),
            'showUrl' => route('place.show', ['slug' => $place->slug], absolute: false),
        ];
    }

    // Halaman Detail Wisata
    public function show(string $slug): View
    {
        $place = Place::where('slug', $slug)
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->firstOrFail();

        $reviews = $place->reviews()
            ->latest()
            ->with('user')
            ->paginate(6)
            ->withQueryString();

        return view('places.show', compact('place', 'reviews'));
    }
}
