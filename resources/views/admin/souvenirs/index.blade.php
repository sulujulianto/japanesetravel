<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">{{ __('Master Data') }}</p>
                <h2 class="text-2xl font-semibold text-slate-900 dark:text-white">{{ __('Kelola Souvenir') }}</h2>
            </div>
            <a href="{{ route('admin.souvenirs.create') }}" class="inline-flex items-center gap-2 rounded-full bg-rose-500 px-5 py-2 text-sm font-semibold text-white hover:bg-rose-400">
                + {{ __('Tambah Souvenir') }}
            </a>
        </div>
    </x-slot>

    @if(session('success'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('success') }}
        </x-ui.alert>
    @endif

    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wider text-slate-400">
                        <th class="pb-3">{{ __('Gambar') }}</th>
                        <th class="pb-3">{{ __('Nama Produk') }}</th>
                        <th class="pb-3">{{ __('Harga') }}</th>
                        <th class="pb-3">{{ __('Stok') }}</th>
                        <th class="pb-3">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/60 dark:divide-slate-800">
                    @forelse ($souvenirs as $souvenir)
                        <tr>
                            <td class="py-3">
                                <div class="h-14 w-14 overflow-hidden rounded-xl bg-slate-200 dark:bg-slate-800">
                                    @if($souvenir->image)
                                        <img src="{{ asset('storage/' . $souvenir->image) }}" alt="{{ $souvenir->name }}" class="h-full w-full object-cover">
                                    @else
                                        <img src="{{ asset('demo/souvenir-placeholder.svg') }}" alt="{{ $souvenir->name }}" class="h-full w-full object-cover">
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 font-semibold text-slate-900 dark:text-white">{{ $souvenir->name }}</td>
                            <td class="py-3 text-slate-600 dark:text-slate-300">Rp {{ number_format($souvenir->price, 0, ',', '.') }}</td>
                            <td class="py-3">
                                <x-ui.badge variant="{{ $souvenir->stock <= 5 ? 'warning' : 'success' }}">
                                    {{ $souvenir->stock }}
                                </x-ui.badge>
                            </td>
                            <td class="py-3">
                                <div class="flex items-center gap-3 text-sm font-semibold">
                                    <a href="{{ route('admin.souvenirs.edit', $souvenir->id) }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-300">{{ __('Edit') }}</a>
                                    <form action="{{ route('admin.souvenirs.destroy', $souvenir->id) }}" method="POST" onsubmit="return confirm({{ Illuminate\Support\Js::from(__('Yakin ingin menghapus :name? Data tidak bisa dikembalikan.', ['name' => $souvenir->name])) }});">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-500 hover:text-rose-400">{{ __('Hapus') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-sm text-slate-500">
                                {{ __('Belum ada data souvenir.') }}
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
</x-admin-layout>
