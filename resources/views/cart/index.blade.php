<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shopping Cart - Japan Travel</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @includeIf('partials.theme-script')
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">

    <nav class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('shop.index') }}" class="text-gray-500 hover:text-sky-600 transition flex items-center">
                        ← {{ __('Lanjut Belanja') }}
                    </a>
                    <span class="text-xl font-bold text-sky-500 dark:text-red-500">🇯🇵 Japan Travel Shop</span>
                </div>
                <div class="flex items-center space-x-3 text-sm font-medium">
                    <div class="flex space-x-2 text-xs font-bold">
                        <a href="{{ route('lang.switch', 'id') }}" class="px-3 py-1 rounded-full border border-gray-200 dark:border-gray-700 {{ App::getLocale() == 'id' ? 'bg-sky-600 text-white border-sky-600' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800' }}">ID</a>
                        <a href="{{ route('lang.switch', 'en') }}" class="px-3 py-1 rounded-full border border-gray-200 dark:border-gray-700 {{ App::getLocale() == 'en' ? 'bg-sky-600 text-white border-sky-600' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800' }}">EN</a>
                    </div>
                    <button onclick="toggleTheme()" class="w-10 h-10 rounded-full border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition" title="Toggle theme">
                        <span class="text-lg" aria-hidden="true">🌗</span>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-3xl font-extrabold mb-8">{{ __('Keranjang Belanja') }}</h1>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if(count($cartItems) > 0)
            <div class="flex flex-col lg:flex-row gap-8">
                
                <div class="lg:w-2/3">
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                        <form action="{{ route('cart.update') }}" method="POST">
                            @csrf
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Produk') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Harga') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Qty') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Total') }}</th>
                                        <th class="px-6 py-3"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($cartItems as $item)
                                    <tr>
                                        <td class="px-6 py-4 flex items-center">
                                            @if($item['product']->image)
                                                <img src="{{ asset('storage/' . $item['product']->image) }}" class="w-12 h-12 object-cover rounded mr-3">
                                            @else
                                                <div class="w-12 h-12 bg-gray-200 rounded mr-3"></div>
                                            @endif
                                            <span class="font-bold">{{ $item['product']->name }}</span>
                                        </td>
                                        <td class="px-6 py-4">Rp {{ number_format($item['product']->price, 0, ',', '.') }}</td>
                                        <td class="px-6 py-4">
                                            <input type="number" name="qty[{{ $item['product']->id }}]" value="{{ $item['qty'] }}" min="1" class="w-16 text-center border-gray-300 rounded-md dark:bg-gray-900 shadow-sm focus:ring-sky-500 focus:border-sky-500">
                                        </td>
                                        <td class="px-6 py-4 font-bold text-sky-600">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="{{ route('cart.remove', $item['product']->id) }}" class="text-red-500 hover:text-red-700 font-bold text-sm" onclick="return confirm('Hapus barang ini?')">x</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            
                            <div class="p-4 bg-gray-50 dark:bg-gray-700 text-right">
                                <button type="submit" class="text-sm text-gray-600 hover:text-gray-900 font-medium underline">
                                    {{ __('Update Keranjang') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="lg:w-1/3">
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                        <h3 class="text-lg font-bold mb-4 border-b pb-2">{{ __('Ringkasan Pesanan') }}</h3>
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600">{{ __('Total Barang') }}</span>
                            <span class="font-bold">Rp {{ number_format($total, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between mb-6 text-xl font-extrabold text-sky-600 dark:text-red-500">
                            <span>Total</span>
                            <span>Rp {{ number_format($total, 0, ',', '.') }}</span>
                        </div>
                        
                        <form action="{{ route('checkout.process') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full bg-sky-600 dark:bg-red-600 hover:bg-sky-700 text-white font-bold py-3 px-4 rounded-lg transition transform hover:scale-105 shadow-lg">
                                {{ __('Checkout Sekarang') }} 
                            </button>
                        </form>
                        <p class="text-xs text-center text-gray-500 mt-3">
                            {{ __('Lanjut ke pembayaran via WhatsApp / Transfer') }}
                        </p>
                    </div>
                </div>

            </div>
        @else
            <div class="text-center py-20">
                <div class="text-6xl mb-4">🛒</div>
                <h3 class="text-xl font-bold text-gray-500 mb-4">{{ __('Keranjangmu masih kosong.') }}</h3>
                <a href="{{ route('shop.index') }}" class="bg-sky-600 text-white px-6 py-2 rounded-full hover:bg-sky-700 transition">
                    {{ __('Mulai Belanja') }}
                </a>
            </div>
        @endif
    </div>

</body>
</html>
