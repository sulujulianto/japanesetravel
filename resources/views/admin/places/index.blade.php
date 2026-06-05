<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-5 rounded-2xl border border-[#E7E3DC] bg-white p-5 sm:p-6 lg:flex-row lg:items-center lg:justify-between dark:border-[#2A333D] dark:bg-[#161B22]">
            <div class="max-w-2xl">
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#667085] dark:text-[#AEB8C7]">{{ __('Master Data') }}</p>
                <h1 class="mt-2 text-2xl font-semibold tracking-tight text-[#1F2937] dark:text-[#F4F1ED] sm:text-3xl">{{ __('Kelola Destinasi Wisata') }}</h1>
                <p class="mt-2 text-sm leading-6 text-[#526071] dark:text-[#AEB8C7]">{{ __('Kelola informasi destinasi, detail kunjungan, dan media yang tampil di katalog publik.') }}</p>
            </div>
            <a href="{{ route('admin.places.create') }}" class="inline-flex min-h-10 items-center justify-center rounded-xl bg-[#B33A3A] px-4 text-sm font-semibold text-white transition hover:bg-[#8F2E2E] dark:bg-[#D96B6B] dark:text-[#0E1116] dark:hover:bg-[#E18484]">
                + {{ __('Tambah Destinasi') }}
            </a>
        </div>
    </x-slot>

    @if(session('success'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('success') }}
        </x-ui.alert>
    @endif

    <section class="rounded-2xl border border-[#E7E3DC] bg-white dark:border-[#2A333D] dark:bg-[#161B22]" aria-labelledby="places-list-heading">
        <div class="border-b border-[#E7E3DC] px-5 py-4 dark:border-[#2A333D] sm:px-6">
            <h2 id="places-list-heading" class="text-base font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Daftar Destinasi') }}</h2>
            <p class="mt-1 text-sm text-[#526071] dark:text-[#AEB8C7]">{{ __('Menampilkan destinasi terbaru lebih dahulu.') }}</p>
        </div>

        <div class="hidden overflow-x-auto md:block">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-[#E7E3DC] text-left text-[11px] font-semibold uppercase tracking-[0.12em] text-[#667085] dark:border-[#2A333D] dark:text-[#AEB8C7]">
                        <th class="px-6 py-3">{{ __('Gambar') }}</th>
                        <th class="px-4 py-3">{{ __('Nama Destinasi') }}</th>
                        <th class="px-4 py-3">{{ __('Alamat') }}</th>
                        <th class="px-6 py-3 text-right">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E7E3DC] dark:divide-[#2A333D]">
                    @forelse ($places as $place)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="h-16 w-20 overflow-hidden rounded-xl border border-[#E7E3DC] bg-[#F1EEE8] dark:border-[#2A333D] dark:bg-[#0E1116]">
                                    <img src="{{ $place->image_url ?: asset('demo/place-placeholder.svg') }}" alt="{{ $place->name }}" class="h-full w-full object-cover">
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <p class="font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ $place->name }}</p>
                                <p class="mt-1 text-xs text-[#526071] dark:text-[#AEB8C7]">#{{ $place->id }}</p>
                            </td>
                            <td class="max-w-md px-4 py-4 text-[#526071] dark:text-[#AEB8C7]">
                                {{ Str::limit($place->address ?: __('Alamat belum diisi.'), 70) }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-3 text-sm font-semibold">
                                    <a href="{{ route('admin.places.edit', $place->id) }}" class="text-[#526071] transition hover:text-[#8F2E2E] dark:text-[#AEB8C7] dark:hover:text-[#D96B6B]">{{ __('Edit') }}</a>
                                    <form action="{{ route('admin.places.destroy', $place->id) }}" method="POST" onsubmit="return confirm({{ Illuminate\Support\Js::from(__('Yakin ingin menghapus :name? Data tidak bisa dikembalikan.', ['name' => $place->name])) }});">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-[#9F2A2A] transition hover:text-[#7A1F1F] dark:text-[#F0A0A0] dark:hover:text-[#F7B8B8]">{{ __('Hapus') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center">
                                <p class="text-sm font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Belum ada data destinasi wisata.') }}</p>
                                <p class="mt-1 text-sm text-[#526071] dark:text-[#AEB8C7]">{{ __('Tambahkan destinasi pertama untuk mulai mengisi katalog.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="space-y-3 p-4 md:hidden">
            @forelse ($places as $place)
                <article class="rounded-xl border border-[#E7E3DC] bg-[#FAF8F3] p-4 dark:border-[#2A333D] dark:bg-[#0E1116]">
                    <div class="flex items-start gap-3">
                        <div class="h-20 w-24 shrink-0 overflow-hidden rounded-xl border border-[#E7E3DC] bg-white dark:border-[#2A333D] dark:bg-[#161B22]">
                            <img src="{{ $place->image_url ?: asset('demo/place-placeholder.svg') }}" alt="{{ $place->name }}" class="h-full w-full object-cover">
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ $place->name }}</p>
                            <p class="mt-1 line-clamp-2 text-sm leading-5 text-[#526071] dark:text-[#AEB8C7]">{{ $place->address ?: __('Alamat belum diisi.') }}</p>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-end gap-4 border-t border-[#E7E3DC] pt-3 text-sm font-semibold dark:border-[#2A333D]">
                        <a href="{{ route('admin.places.edit', $place->id) }}" class="text-[#526071] dark:text-[#AEB8C7]">{{ __('Edit') }}</a>
                        <form action="{{ route('admin.places.destroy', $place->id) }}" method="POST" onsubmit="return confirm({{ Illuminate\Support\Js::from(__('Yakin ingin menghapus :name? Data tidak bisa dikembalikan.', ['name' => $place->name])) }});">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-[#9F2A2A] dark:text-[#F0A0A0]">{{ __('Hapus') }}</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="rounded-xl border border-dashed border-[#E7E3DC] p-6 text-center dark:border-[#2A333D]">
                    <p class="text-sm font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Belum ada data destinasi wisata.') }}</p>
                    <p class="mt-1 text-sm text-[#526071] dark:text-[#AEB8C7]">{{ __('Tambahkan destinasi pertama untuk mulai mengisi katalog.') }}</p>
                </div>
            @endforelse
        </div>

        @if($places->hasPages())
            <div class="border-t border-[#E7E3DC] px-4 py-4 dark:border-[#2A333D] sm:px-6">
                {{ $places->links() }}
            </div>
        @endif
    </section>
</x-admin-layout>
