<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RestockRequest;
use App\Models\Souvenir;
use App\Support\CacheKeys;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function lowStock(Request $request)
    {
        $threshold = max(1, (int) $request->input('threshold', 5));

        $souvenirs = Souvenir::where('stock', '<=', $threshold)
            ->orderBy('stock')
            ->paginate(15)
            ->withQueryString();

        return view('admin.inventory.low-stock', compact('souvenirs', 'threshold'));
    }

    public function restock(RestockRequest $request, Souvenir $souvenir)
    {
        $amount = $request->integer('amount');

        $souvenir->increment('stock', $amount);

        CacheKeys::bump(CacheKeys::SOUVENIRS_VERSION);

        return redirect()->back()->with('success', __('Stok berhasil ditambahkan.'));
    }
}
