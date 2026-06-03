<?php

namespace App\Http\Controllers;

use App\Models\Place;
use App\Models\PlaceReview;
use App\Models\Souvenir;
use App\Support\CacheKeys;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    // Halaman Depan (Preview Wisata)
    public function index()
    {
        $placesVersion = CacheKeys::version(CacheKeys::PLACES_VERSION);
        $featuredPlaces = Cache::remember('home:featured-places:'.$placesVersion, now()->addMinutes(5), function () {
            return Place::query()
                ->withCount('reviews')
                ->withAvg('reviews', 'rating')
                ->latest()
                ->take(6)
                ->get();
        });

        $summary = $this->summary();

        return view('welcome', compact('featuredPlaces', 'summary'));
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

    // Halaman Detail Wisata
    // Halaman Detail Wisata
    public function show($slug)
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
