<?php

namespace App\Http\Controllers;

use App\Models\Souvenir;
use App\Support\CacheKeys;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ShopController extends Controller
{
    // Halaman Katalog Toko
    public function index()
    {
        $search = request()->string('search')->toString();
        $minPrice = request()->input('min_price');
        $maxPrice = request()->input('max_price');
        $availability = request()->input('availability');
        $sort = request()->input('sort', 'latest');
        if (! in_array($sort, ['latest', 'price_low', 'price_high'], true)) {
            $sort = 'latest';
        }

        $souvenirsVersion = CacheKeys::version(CacheKeys::SOUVENIRS_VERSION);
        $souvenirsKey = 'souvenirs:list:' . md5(json_encode([
            'v' => $souvenirsVersion,
            'search' => $search,
            'min' => $minPrice,
            'max' => $maxPrice,
            'availability' => $availability,
            'sort' => $sort,
            'page' => request()->integer('page', 1),
        ]));

        $souvenirs = Cache::remember($souvenirsKey, now()->addMinutes(5), function () use ($search, $minPrice, $maxPrice, $availability, $sort) {
            $query = Souvenir::query();

            if ($search !== '') {
                $query->where(function ($builder) use ($search) {
                    $builder->where('name->id', 'like', '%' . $search . '%')
                        ->orWhere('name->en', 'like', '%' . $search . '%')
                        ->orWhere('description->id', 'like', '%' . $search . '%')
                        ->orWhere('description->en', 'like', '%' . $search . '%');
                });
            }

            if ($minPrice !== null && $minPrice !== '') {
                $query->where('price', '>=', (float) $minPrice);
            }

            if ($maxPrice !== null && $maxPrice !== '') {
                $query->where('price', '<=', (float) $maxPrice);
            }

            if ($availability === 'in_stock') {
                $query->where('stock', '>', 0);
            }

            if ($sort === 'price_low') {
                $query->orderBy('price');
            } elseif ($sort === 'price_high') {
                $query->orderByDesc('price');
            } else {
                $query->latest();
            }

            return $query->paginate(12);
        });

        $souvenirs->withQueryString();

        return view('shop.index', compact('souvenirs', 'search', 'minPrice', 'maxPrice', 'availability', 'sort'));
    }
}
