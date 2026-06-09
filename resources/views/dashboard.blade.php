<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[#B33A3A] dark:text-[#D96B6B]">{{ __('Dashboard') }}</p>
            <h2 class="text-2xl font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Halo, :name', ['name' => Auth::user()->username]) }}</h2>
            <p class="max-w-2xl text-sm leading-6 text-[#526071] dark:text-[#AEB8C7]">{{ __('Lihat ringkasan akun, pesanan terbaru, dan akses cepat ke katalog oleh-oleh.') }}</p>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <section class="rounded-2xl border border-[#E7E3DC] bg-white p-5 shadow-sm dark:border-[#2A333D] dark:bg-[#161B22] sm:p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#667085] dark:text-[#AEB8C7]">{{ __('Total Pesanan') }}</p>
                    <p class="mt-3 text-3xl font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ $data['my_orders'] ?? 0 }}</p>
                    <p class="mt-2 text-sm leading-6 text-[#526071] dark:text-[#AEB8C7]">{{ __('Pesanan oleh-oleh yang pernah Anda buat.') }}</p>
                </section>

                <section class="rounded-2xl border border-[#E7E3DC] bg-white p-5 shadow-sm dark:border-[#2A333D] dark:bg-[#161B22] sm:p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#667085] dark:text-[#AEB8C7]">{{ __('Total Belanja') }}</p>
                    <p class="mt-3 text-3xl font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ \App\Support\Format::idr($data['spent'] ?? 0) }}</p>
                    <p class="mt-2 text-sm leading-6 text-[#526071] dark:text-[#AEB8C7]">{{ __('Akumulasi pesanan berstatus diproses atau selesai.') }}</p>
                </section>

                <section class="rounded-2xl border border-[#E7E3DC] bg-white p-5 shadow-sm dark:border-[#2A333D] dark:bg-[#161B22] sm:col-span-2 sm:p-6 lg:col-span-1">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#667085] dark:text-[#AEB8C7]">{{ __('Aksi Cepat') }}</p>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                        <a href="{{ route('shop.index') }}" class="inline-flex items-center justify-between rounded-xl bg-[#B33A3A] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#8F2E2E] dark:bg-[#D96B6B] dark:text-[#0E1116] dark:hover:bg-[#E18484]">
                            <span>{{ __('Belanja oleh-oleh') }}</span>
                            <span aria-hidden="true">→</span>
                        </a>
                        <a href="{{ route('profile.edit') }}" class="inline-flex items-center justify-between rounded-xl border border-[#DDD6CC] px-4 py-3 text-sm font-semibold text-[#374151] transition hover:border-[#B33A3A] hover:text-[#B33A3A] dark:border-[#2A333D] dark:text-[#D8DEE8] dark:hover:border-[#D96B6B] dark:hover:text-[#D96B6B]">
                            <span>{{ __('Kelola profil') }}</span>
                            <span aria-hidden="true">→</span>
                        </a>
                    </div>
                </section>
            </div>

            <section class="rounded-2xl border border-[#E7E3DC] bg-white p-5 shadow-sm dark:border-[#2A333D] dark:bg-[#161B22] sm:p-6">
                <div class="flex flex-col gap-3 border-b border-[#E7E3DC] pb-5 dark:border-[#2A333D] sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#B33A3A] dark:text-[#D96B6B]">{{ __('Aktivitas akun') }}</p>
                        <h3 class="mt-2 text-xl font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Pesanan terbaru') }}</h3>
                    </div>
                    <a href="{{ route('orders.index') }}" class="text-sm font-semibold text-[#B33A3A] hover:text-[#8F2E2E] dark:text-[#D96B6B] dark:hover:text-[#E18484]">{{ __('Lihat semua pesanan') }}</a>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse($data['recent_orders'] ?? [] as $order)
                        <div class="grid gap-3 rounded-2xl border border-[#E7E3DC] bg-[#FAF9F6] p-4 dark:border-[#2A333D] dark:bg-[#1F2630] sm:grid-cols-[minmax(0,1fr)_auto_auto] sm:items-center sm:gap-6">
                            <div>
                                <p class="font-semibold text-[#1F2937] dark:text-[#F4F1ED]">#ORDER-{{ $order->id }}</p>
                                <p class="mt-1 text-sm text-[#526071] dark:text-[#AEB8C7]">{{ \App\Support\Format::date($order->created_at) }}</p>
                            </div>
                            <div class="font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ \App\Support\Format::idr($order->total_price) }}</div>
                            <a href="{{ route('orders.show', $order) }}" class="w-fit text-sm font-semibold text-[#B33A3A] hover:text-[#8F2E2E] dark:text-[#D96B6B] dark:hover:text-[#E18484]">{{ __('Lihat detail') }}</a>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-[#DDD6CC] px-5 py-10 text-center dark:border-[#2A333D]">
                            <h4 class="font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Belum ada pesanan') }}</h4>
                            <p class="mt-2 text-sm text-[#526071] dark:text-[#AEB8C7]">{{ __('Pesanan baru akan tampil di sini setelah Anda checkout oleh-oleh.') }}</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
