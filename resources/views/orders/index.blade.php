<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Riwayat Pesanan Saya') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @forelse ($orders as $order)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6 border border-gray-200 dark:border-gray-700">
                    <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 border-b border-gray-200 dark:border-gray-600 flex justify-between items-center">
                        <div>
                            <span class="text-xs uppercase font-bold text-gray-500">Nomor Order</span>
                            <div class="font-bold text-gray-900 dark:text-white">#ORDER-{{ $order->id }}</div>
                        </div>
                        <div>
                            <span class="text-xs uppercase font-bold text-gray-500">Tanggal</span>
                            <div class="text-sm text-gray-900 dark:text-white">{{ $order->created_at->format('d M Y') }}</div>
                        </div>
                        <div>
                            <span class="text-xs uppercase font-bold text-gray-500">Total</span>
                            <div class="font-bold text-sky-600 dark:text-red-400">Rp {{ number_format($order->total_price, 0, ',', '.') }}</div>
                        </div>
                        <div>
                            <span class="px-3 py-1 text-xs font-bold rounded-full bg-yellow-100 text-yellow-800">
                                {{ strtoupper($order->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="p-6">
                        <table class="min-w-full">
                            <tbody>
                                @foreach ($order->items as $item)
                                <tr class="border-b last:border-0 border-gray-100 dark:border-gray-700">
                                    <td class="py-3 flex items-center">
                                        <img src="{{ asset('storage/' . $item->product->image) }}" class="w-10 h-10 rounded object-cover mr-3">
                                        <span class="text-gray-700 dark:text-gray-300">{{ $item->product->name }}</span>
                                    </td>
                                    <td class="py-3 text-sm text-gray-500 text-right">
                                        {{ $item->quantity }} x Rp {{ number_format($item->price, 0, ',', '.') }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 text-gray-500 bg-white dark:bg-gray-800 rounded-lg shadow">
                    <p>Belum ada riwayat pesanan.</p>
                    <a href="{{ route('shop.index') }}" class="text-sky-600 hover:underline mt-2 block">Mulai Belanja</a>
                </div>
            @endforelse

        </div>
    </div>
</x-app-layout>