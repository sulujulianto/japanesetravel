<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">{{ __('Inventory') }}</p>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">{{ __('Stok Rendah') }}</h1>
        </div>
    </x-slot>

    @if(session('success'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('success') }}
        </x-ui.alert>
    @endif

    <x-ui.card>
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <x-ui.label value="{{ __('Batas Stok') }}" />
                <x-ui.input type="number" name="threshold" value="{{ $threshold }}" min="1" />
            </div>
            <x-ui.button type="submit">{{ __('Tampilkan') }}</x-ui.button>
        </form>
    </x-ui.card>

    <div class="mt-6">
        <x-ui.card>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wider text-slate-400">
                            <th class="pb-3">{{ __('Produk') }}</th>
                            <th class="pb-3">{{ __('Harga') }}</th>
                            <th class="pb-3">{{ __('Sisa') }}</th>
                            <th class="pb-3">{{ __('Restock') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/60 dark:divide-slate-800">
                        @forelse($souvenirs as $souvenir)
                            <tr>
                                <td class="py-3">
                                    <div class="font-semibold text-slate-900 dark:text-white">{{ $souvenir->name }}</div>
                                    <div class="text-xs text-slate-500">{{ __('SKU') }} #{{ $souvenir->id }}</div>
                                </td>
                                <td class="py-3 text-slate-900 dark:text-white">Rp {{ number_format($souvenir->price, 0, ',', '.') }}</td>
                                <td class="py-3">
                                    <x-ui.badge variant="warning">{{ $souvenir->stock }}</x-ui.badge>
                                </td>
                                <td class="py-3">
                                    <form method="POST" action="{{ route('admin.inventory.restock', $souvenir) }}" class="flex items-center gap-2">
                                        @csrf
                                        <x-ui.input type="number" name="amount" value="10" min="1" class="w-24" />
                                        <x-ui.button type="submit" size="sm">{{ __('Tambah') }}</x-ui.button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-sm text-slate-500">
                                    {{ __('Tidak ada produk dengan stok rendah.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-6">
                {{ $souvenirs->links() }}
            </div>
        </x-ui.card>
    </div>
</x-admin-layout>
