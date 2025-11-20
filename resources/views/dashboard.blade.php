<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ Auth::user()->role === 'admin' ? __('Admin Dashboard') : __('Dashboard Saya') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(Auth::user()->role === 'admin')
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-2xl border-l-4 border-green-500 p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">💰</div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Total Pendapatan</p>
                                <p class="text-2xl font-bold text-gray-800 dark:text-white">Rp {{ number_format($data['revenue'], 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-2xl border-l-4 border-orange-500 p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-orange-100 text-orange-600 mr-4">⏳</div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Pesanan Baru</p>
                                <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $data['pending_orders'] }}</p>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('admin.souvenirs.index') }}" class="block bg-white dark:bg-gray-800 shadow-lg rounded-2xl border-l-4 border-sky-500 p-6 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-sky-100 text-sky-600 mr-4">📦</div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Total Produk</p>
                                <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $data['total_products'] }}</p>
                                <p class="text-xs text-sky-600 mt-1">Kelola Stok &rarr;</p>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('admin.places.index') }}" class="block bg-white dark:bg-gray-800 shadow-lg rounded-2xl border-l-4 border-purple-500 p-6 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">⛩️</div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Destinasi</p>
                                <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $data['total_places'] }}</p>
                                <p class="text-xs text-purple-600 mt-1">Update Wisata &rarr;</p>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 bg-white dark:bg-gray-800 shadow-lg rounded-2xl overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                            <h3 class="font-bold text-gray-800 dark:text-white">🛒 5 Pesanan Terakhir</h3>
                        </div>
                        <table class="min-w-full text-sm text-left">
                            <thead class="bg-gray-50 dark:bg-gray-700 text-gray-500">
                                <tr>
                                    <th class="px-6 py-3">Pelanggan</th>
                                    <th class="px-6 py-3">Total</th>
                                    <th class="px-6 py-3">Status</th>
                                    <th class="px-6 py-3">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse($data['recent_orders'] as $order)
                                <tr>
                                    <td class="px-6 py-4 font-bold">{{ $order->user->username }}</td>
                                    <td class="px-6 py-4">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 rounded text-xs font-bold {{ $order->status == 'completed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                            {{ strtoupper($order->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500">{{ $order->created_at->format('d M Y') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="px-6 py-8 text-center">Belum ada pesanan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-2xl p-6">
                        <h3 class="font-bold text-red-600 mb-4">⚠️ Stok Menipis (< 5)</h3>
                        @if($data['low_stock']->count() > 0)
                            <ul class="space-y-3">
                                @foreach($data['low_stock'] as $item)
                                <li class="flex justify-between items-center bg-red-50 p-2 rounded">
                                    <span class="text-gray-800 font-medium">{{ $item->name }}</span>
                                    <span class="text-red-600 font-bold text-sm">{{ $item->stock }} unit</span>
                                    <a href="{{ route('admin.souvenirs.edit', $item->id) }}" class="text-xs bg-white border px-2 py-1 rounded">Isi Stok</a>
                                </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-green-600 text-center bg-green-50 p-2 rounded">Semua stok aman! ✅</p>
                        @endif
                    </div>
                </div>

            @else
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg text-center">
                    <h3 class="text-2xl font-bold mb-2">Selamat Datang, {{ Auth::user()->username }}! 👋</h3>
                    <p class="text-gray-500 mb-6">Kamu sudah belanja sebanyak <span class="text-green-600 font-bold">Rp {{ number_format($data['spent'], 0, ',', '.') }}</span> di sini.</p>
                    
                    <div class="flex justify-center gap-4">
                        <a href="{{ route('shop.index') }}" class="bg-sky-600 text-white px-6 py-2 rounded-full hover:bg-sky-700 transition">Mulai Belanja</a>
                        <a href="{{ route('orders.index') }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-full hover:bg-gray-300 transition">Lihat Pesanan</a>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>