<x-admin-layout>
    <x-slot name="header">
        <div class="rounded-2xl border border-[#E7E3DC] bg-white p-5 dark:border-[#2A333D] dark:bg-[#161B22] sm:p-6">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#667085] dark:text-[#AEB8C7]">{{ __('Inventory') }}</p>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-[#1F2937] dark:text-[#F4F1ED] sm:text-3xl">{{ __('Stok Rendah') }}</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-[#526071] dark:text-[#AEB8C7]">{{ __('Pantau produk di bawah batas stok dan tambahkan persediaan tanpa meninggalkan halaman.') }}</p>
        </div>
    </x-slot>

    @if(session('success'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('success') }}
        </x-ui.alert>
    @endif

    @if ($errors->any())
        <x-ui.alert variant="danger" class="mb-6">
            <p class="font-semibold">{{ __('Stok belum dapat diperbarui.') }}</p>
            <ul class="mt-2 list-disc space-y-1 pl-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-ui.alert>
    @endif

    <section class="rounded-2xl border border-[#E7E3DC] bg-white p-5 dark:border-[#2A333D] dark:bg-[#161B22] sm:p-6" aria-labelledby="stock-filter-heading">
        <div>
            <h2 id="stock-filter-heading" class="text-base font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Batas Pemantauan Stok') }}</h2>
            <p class="mt-1 text-sm text-[#526071] dark:text-[#AEB8C7]">{{ __('Tampilkan produk dengan jumlah stok sama dengan atau di bawah batas yang dipilih.') }}</p>
        </div>

        <form method="GET" class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="sm:w-56">
                <label for="threshold" class="block text-[11px] font-semibold uppercase tracking-[0.12em] text-[#667085] dark:text-[#AEB8C7]">{{ __('Batas Stok') }}</label>
                <input id="threshold" type="number" name="threshold" value="{{ $threshold }}" min="1" class="mt-2 w-full rounded-xl border border-[#DDD6CC] bg-white px-3.5 py-2.5 text-sm text-[#1F2937] outline-none transition focus:border-[#B33A3A] focus:ring-2 focus:ring-[#B33A3A]/15 dark:border-[#2A333D] dark:bg-[#0E1116] dark:text-[#F4F1ED] dark:focus:border-[#D96B6B] dark:focus:ring-[#D96B6B]/20">
            </div>
            <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-xl bg-[#B33A3A] px-4 text-sm font-semibold text-white transition hover:bg-[#8F2E2E] sm:w-auto dark:bg-[#D96B6B] dark:text-[#0E1116] dark:hover:bg-[#E18484]">
                {{ __('Tampilkan') }}
            </button>
        </form>
    </section>

    <section class="mt-6 rounded-2xl border border-[#E7E3DC] bg-white dark:border-[#2A333D] dark:bg-[#161B22]" aria-labelledby="low-stock-list-heading">
        <div class="border-b border-[#E7E3DC] px-5 py-4 dark:border-[#2A333D] sm:px-6">
            <h2 id="low-stock-list-heading" class="text-base font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Produk yang Perlu Diperiksa') }}</h2>
            <p class="mt-1 text-sm text-[#526071] dark:text-[#AEB8C7]">{{ __('Daftar diurutkan dari stok paling sedikit untuk membantu prioritas restock.') }}</p>
        </div>

        <div class="hidden overflow-x-auto md:block">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-[#E7E3DC] text-left text-[11px] font-semibold uppercase tracking-[0.12em] text-[#667085] dark:border-[#2A333D] dark:text-[#AEB8C7]">
                        <th class="px-6 py-3">{{ __('Produk') }}</th>
                        <th class="whitespace-nowrap px-4 py-3">{{ __('Harga') }}</th>
                        <th class="px-4 py-3">{{ __('Sisa') }}</th>
                        <th class="px-6 py-3">{{ __('Restock') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E7E3DC] dark:divide-[#2A333D]">
                    @forelse($souvenirs as $souvenir)
                        <tr>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ $souvenir->name }}</p>
                                <p class="mt-1 text-xs text-[#526071] dark:text-[#AEB8C7]">{{ __('SKU') }} #{{ $souvenir->id }}</p>
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 font-semibold text-[#1F2937] dark:text-[#F4F1ED]">Rp {{ number_format($souvenir->price, 0, ',', '.') }}</td>
                            <td class="px-4 py-4">
                                <x-ui.badge variant="{{ $souvenir->stock === 0 ? 'danger' : 'warning' }}">
                                    {{ $souvenir->stock === 0 ? __('Habis') : __('Rendah') }} · {{ $souvenir->stock }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4">
                                <form method="POST" action="{{ route('admin.inventory.restock', $souvenir) }}" class="flex items-center gap-2">
                                    @csrf
                                    <label for="amount-desktop-{{ $souvenir->id }}" class="sr-only">{{ __('Jumlah restock untuk :product', ['product' => $souvenir->name]) }}</label>
                                    <input id="amount-desktop-{{ $souvenir->id }}" type="number" name="amount" value="10" min="1" class="w-24 rounded-xl border border-[#DDD6CC] bg-white px-3 py-2 text-sm text-[#1F2937] outline-none transition focus:border-[#B33A3A] focus:ring-2 focus:ring-[#B33A3A]/15 dark:border-[#2A333D] dark:bg-[#0E1116] dark:text-[#F4F1ED] dark:focus:border-[#D96B6B] dark:focus:ring-[#D96B6B]/20">
                                    <button type="submit" class="inline-flex min-h-9 items-center justify-center rounded-xl bg-[#B33A3A] px-3 text-xs font-semibold text-white transition hover:bg-[#8F2E2E] dark:bg-[#D96B6B] dark:text-[#0E1116] dark:hover:bg-[#E18484]">
                                        {{ __('Tambah') }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center">
                                <p class="text-sm font-semibold text-[#2F5D50] dark:text-[#8AB7A4]">{{ __('Semua stok aman.') }}</p>
                                <p class="mt-1 text-sm text-[#526071] dark:text-[#AEB8C7]">{{ __('Tidak ada produk dengan stok di bawah batas saat ini.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="space-y-3 p-4 md:hidden">
            @forelse($souvenirs as $souvenir)
                <article class="rounded-xl border border-[#E7E3DC] bg-[#FAF8F3] p-4 dark:border-[#2A333D] dark:bg-[#0E1116]">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ $souvenir->name }}</p>
                            <p class="mt-1 text-sm text-[#526071] dark:text-[#AEB8C7]">{{ __('SKU') }} #{{ $souvenir->id }}</p>
                        </div>
                        <x-ui.badge variant="{{ $souvenir->stock === 0 ? 'danger' : 'warning' }}">
                            {{ $souvenir->stock === 0 ? __('Habis') : __('Rendah') }} · {{ $souvenir->stock }}
                        </x-ui.badge>
                    </div>
                    <p class="mt-4 border-t border-[#E7E3DC] pt-3 text-sm font-semibold text-[#1F2937] dark:border-[#2A333D] dark:text-[#F4F1ED]">Rp {{ number_format($souvenir->price, 0, ',', '.') }}</p>
                    <form method="POST" action="{{ route('admin.inventory.restock', $souvenir) }}" class="mt-4 grid gap-2 min-[360px]:grid-cols-[1fr_auto]">
                        @csrf
                        <div>
                            <label for="amount-mobile-{{ $souvenir->id }}" class="block text-[11px] font-semibold uppercase tracking-[0.1em] text-[#667085] dark:text-[#AEB8C7]">{{ __('Jumlah Restock') }}</label>
                            <input id="amount-mobile-{{ $souvenir->id }}" type="number" name="amount" value="10" min="1" class="mt-2 w-full rounded-xl border border-[#DDD6CC] bg-white px-3 py-2.5 text-sm text-[#1F2937] outline-none transition focus:border-[#B33A3A] focus:ring-2 focus:ring-[#B33A3A]/15 dark:border-[#2A333D] dark:bg-[#161B22] dark:text-[#F4F1ED] dark:focus:border-[#D96B6B] dark:focus:ring-[#D96B6B]/20">
                        </div>
                        <button type="submit" class="inline-flex min-h-10 w-full items-center justify-center self-end rounded-xl bg-[#B33A3A] px-4 text-sm font-semibold text-white transition hover:bg-[#8F2E2E] min-[360px]:w-auto dark:bg-[#D96B6B] dark:text-[#0E1116] dark:hover:bg-[#E18484]">
                            {{ __('Tambah Stok') }}
                        </button>
                    </form>
                </article>
            @empty
                <div class="rounded-xl border border-dashed border-[#E7E3DC] p-6 text-center dark:border-[#2A333D]">
                    <p class="text-sm font-semibold text-[#2F5D50] dark:text-[#8AB7A4]">{{ __('Semua stok aman.') }}</p>
                    <p class="mt-1 text-sm text-[#526071] dark:text-[#AEB8C7]">{{ __('Tidak ada produk dengan stok di bawah batas saat ini.') }}</p>
                </div>
            @endforelse
        </div>

        @if($souvenirs->hasPages())
            <div class="border-t border-[#E7E3DC] px-4 py-4 dark:border-[#2A333D] sm:px-6">
                {{ $souvenirs->links() }}
            </div>
        @endif
    </section>
</x-admin-layout>
