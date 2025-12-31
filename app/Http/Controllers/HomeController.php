<?php

namespace App\Http\Controllers;

use App\Models\Place;
use App\Models\PlaceReview;
use App\Models\Souvenir;
use App\Support\CacheKeys;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    // Halaman Depan (Daftar Wisata)
    public function index(Request $request)
    {
        $search = $request->string('search')->toString();
        $rating = $request->input('rating');
        $sort = $request->input('sort', 'latest');
        if (! in_array($sort, ['latest', 'rating', 'reviews'], true)) {
            $sort = 'latest';
        }

        $placesVersion = CacheKeys::version(CacheKeys::PLACES_VERSION);
        $placesKey = 'places:list:' . md5(json_encode([
            'v' => $placesVersion,
            'search' => $search,
            'rating' => $rating,
            'sort' => $sort,
            'page' => $request->integer('page', 1),
        ]));

        $places = Cache::remember($placesKey, now()->addMinutes(5), function () use ($search, $rating, $sort) {
            $query = Place::query()
                ->withCount('reviews')
                ->withAvg('reviews', 'rating');

            if ($search !== '') {
                $query->where(function ($builder) use ($search) {
                    $builder->where('name->id', 'like', '%' . $search . '%')
                        ->orWhere('name->en', 'like', '%' . $search . '%')
                        ->orWhere('description->id', 'like', '%' . $search . '%')
                        ->orWhere('description->en', 'like', '%' . $search . '%')
                        ->orWhere('address', 'like', '%' . $search . '%');
                });
            }

            if ($rating !== null && $rating !== '') {
                $query->having('reviews_avg_rating', '>=', (float) $rating);
            }

            $query->when($sort === 'rating', function ($builder) {
                $builder->orderByDesc('reviews_avg_rating');
            })->when($sort === 'reviews', function ($builder) {
                $builder->orderByDesc('reviews_count');
            })->when($sort === 'latest', function ($builder) {
                $builder->latest();
            });

            return $query->paginate(9);
        });

        $places->withQueryString();

        $summaryVersion = implode(':', [
            CacheKeys::version(CacheKeys::PLACES_VERSION),
            CacheKeys::version(CacheKeys::SOUVENIRS_VERSION),
            CacheKeys::version(CacheKeys::REVIEWS_VERSION),
        ]);
        $summary = Cache::remember('home:summary:' . $summaryVersion, now()->addMinutes(5), function () {
            return [
                'places' => Place::count(),
                'souvenirs' => Souvenir::count(),
                'reviews' => PlaceReview::count(),
            ];
        });

        return view('welcome', compact('places', 'summary', 'search', 'rating', 'sort'));
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
