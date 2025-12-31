<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">{{ __('Dashboard') }}</p>
            <h2 class="text-2xl font-semibold text-slate-900 dark:text-white">{{ __('Halo, :name!', ['name' => Auth::user()->username]) }}</h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 md:grid-cols-3">
                <x-ui.card>
                    <p class="text-xs uppercase tracking-wider text-slate-400">{{ __('Total Pesanan') }}</p>
                    <p class="mt-4 text-2xl font-semibold text-slate-900 dark:text-white">{{ $data['my_orders'] ?? 0 }}</p>
                    <p class="mt-2 text-sm text-slate-500">{{ __('Pesanan yang pernah Anda buat.') }}</p>
                </x-ui.card>
                <x-ui.card>
                    <p class="text-xs uppercase tracking-wider text-slate-400">{{ __('Total Belanja') }}</p>
                    <p class="mt-4 text-2xl font-semibold text-slate-900 dark:text-white">Rp {{ number_format($data['spent'] ?? 0, 0, ',', '.') }}</p>
                    <p class="mt-2 text-sm text-slate-500">{{ __('Akumulasi transaksi yang berhasil.') }}</p>
                </x-ui.card>
                <x-ui.card>
                    <p class="text-xs uppercase tracking-wider text-slate-400">{{ __('Aksi Cepat') }}</p>
                    <div class="mt-4 flex flex-col gap-3">
                        <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-rose-500">{{ __('Belanja Souvenir') }} →</a>
                        <a href="{{ route('orders.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-200">{{ __('Lihat Pesanan') }} →</a>
                    </div>
                </x-ui.card>
            </div>

            <x-ui.card>
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Pesanan Terbaru') }}</h3>
                    <a href="{{ route('orders.index') }}" class="text-sm font-semibold text-rose-500">{{ __('Lihat Semua') }}</a>
                </div>
                <div class="mt-6 space-y-3">
                    @forelse($data['recent_orders'] ?? [] as $order)
                        <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-200/70 bg-white/70 px-4 py-3 text-sm dark:border-slate-800 dark:bg-slate-950/40">
                            <div>
                                <p class="font-semibold text-slate-900 dark:text-white">#ORDER-{{ $order->id }}</p>
                                <p class="text-xs text-slate-500">{{ $order->created_at->format('d M Y') }}</p>
                            </div>
                            <div class="text-sm font-semibold text-slate-900 dark:text-white">Rp {{ number_format($order->total_price, 0, ',', '.') }}</div>
                            <a href="{{ route('orders.show', $order) }}" class="text-sm font-semibold text-rose-500">{{ __('Detail') }}</a>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-200 p-6 text-center text-sm text-slate-500 dark:border-slate-700">
                            {{ __('Belum ada pesanan.') }}
                        </div>
                    @endforelse
                </div>
            </x-ui.card>
        </div>
    </div>
</x-app-layout>
