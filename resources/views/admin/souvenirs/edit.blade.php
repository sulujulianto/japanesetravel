<x-admin-layout>
    <x-slot name="header">
        <div class="rounded-2xl border border-[#E7E3DC] bg-white p-5 sm:p-6 dark:border-[#2A333D] dark:bg-[#161B22]">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#667085] dark:text-[#AEB8C7]">{{ __('Souvenir') }}</p>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-[#1F2937] dark:text-[#F4F1ED] sm:text-3xl">{{ __('Edit Souvenir') }}</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-[#526071] dark:text-[#AEB8C7]">{{ __('Perbarui informasi produk, stok, harga, atau gambar yang tampil di toko.') }}</p>
        </div>
    </x-slot>

    @php
        $sectionClass = 'rounded-2xl border border-[#E7E3DC] bg-white p-5 dark:border-[#2A333D] dark:bg-[#161B22] sm:p-6';
        $labelClass = 'block text-sm font-semibold text-[#374151] dark:text-[#D8DEE8]';
        $inputClass = 'mt-2 w-full rounded-xl border border-[#DDD6CC] bg-white px-4 py-2.5 text-sm text-[#1F2937] outline-none transition placeholder:text-[#667085] focus:border-[#B33A3A] focus:ring-2 focus:ring-[#B33A3A]/20 dark:border-[#2A333D] dark:bg-[#0E1116] dark:text-[#F4F1ED] dark:placeholder:text-[#AEB8C7] dark:focus:border-[#D96B6B] dark:focus:ring-[#D96B6B]/20';
        $errorClass = 'mt-2 text-sm font-medium text-[#9F2A2A] dark:text-[#F0A0A0]';
        $helpClass = 'mt-2 text-xs leading-5 text-[#526071] dark:text-[#AEB8C7]';
    @endphp

    @if ($errors->any())
        <x-ui.alert variant="danger" class="mb-6">
            <p class="font-semibold">{{ __('Periksa kembali data yang belum valid.') }}</p>
            <ul class="mt-2 list-disc space-y-1 pl-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-ui.alert>
    @endif

    <form action="{{ route('admin.souvenirs.update', $souvenir->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <section class="{{ $sectionClass }}" aria-labelledby="souvenir-id-content">
            <div class="border-b border-[#E7E3DC] pb-4 dark:border-[#2A333D]">
                <h2 id="souvenir-id-content" class="text-lg font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Konten Bahasa Indonesia') }}</h2>
                <p class="mt-1 text-sm text-[#526071] dark:text-[#AEB8C7]">{{ __('Nama dan deskripsi utama untuk pelanggan berbahasa Indonesia.') }}</p>
            </div>
            <div class="mt-5 grid gap-5">
                <div>
                    <label for="name_id" class="{{ $labelClass }}">{{ __('Nama Produk (ID)') }}</label>
                    <input id="name_id" name="name_id" value="{{ old('name_id', $souvenir->getTranslation('name', 'id')) }}" required class="{{ $inputClass }}">
                    @error('name_id')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="description_id" class="{{ $labelClass }}">{{ __('Deskripsi (ID)') }}</label>
                    <textarea id="description_id" name="description_id" rows="6" class="{{ $inputClass }}">{{ old('description_id', $souvenir->getTranslation('description', 'id')) }}</textarea>
                    <p class="{{ $helpClass }}">{{ __('Jelaskan karakter produk, bahan, atau asalnya tanpa klaim promosi yang tidak tersedia.') }}</p>
                    @error('description_id')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <section class="{{ $sectionClass }}" aria-labelledby="souvenir-en-content">
            <div class="border-b border-[#E7E3DC] pb-4 dark:border-[#2A333D]">
                <h2 id="souvenir-en-content" class="text-lg font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Konten Bahasa Inggris') }}</h2>
                <p class="mt-1 text-sm text-[#526071] dark:text-[#AEB8C7]">{{ __('Gunakan terjemahan yang natural dan konsisten dengan informasi produk.') }}</p>
            </div>
            <div class="mt-5 grid gap-5">
                <div>
                    <label for="name_en" class="{{ $labelClass }}">{{ __('Nama Produk (EN)') }}</label>
                    <input id="name_en" name="name_en" value="{{ old('name_en', $souvenir->getTranslation('name', 'en')) }}" required class="{{ $inputClass }}">
                    @error('name_en')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="description_en" class="{{ $labelClass }}">{{ __('Deskripsi (EN)') }}</label>
                    <textarea id="description_en" name="description_en" rows="6" class="{{ $inputClass }}">{{ old('description_en', $souvenir->getTranslation('description', 'en')) }}</textarea>
                    @error('description_en')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <section class="{{ $sectionClass }}" aria-labelledby="souvenir-commerce">
            <div class="border-b border-[#E7E3DC] pb-4 dark:border-[#2A333D]">
                <h2 id="souvenir-commerce" class="text-lg font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Informasi Penjualan') }}</h2>
                <p class="mt-1 text-sm text-[#526071] dark:text-[#AEB8C7]">{{ __('Perubahan harga dan stok akan digunakan langsung oleh alur toko dan keranjang.') }}</p>
            </div>
            <div class="mt-5 grid gap-5 md:grid-cols-2">
                <div>
                    <label for="price" class="{{ $labelClass }}">{{ __('Harga') }}</label>
                    <input id="price" type="number" name="price" value="{{ old('price', $souvenir->price) }}" min="0" step="any" required class="{{ $inputClass }}">
                    <p class="{{ $helpClass }}">{{ __('Masukkan nilai harga tanpa pemisah ribuan.') }}</p>
                    @error('price')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="stock" class="{{ $labelClass }}">{{ __('Stok') }}</label>
                    <input id="stock" type="number" name="stock" value="{{ old('stock', $souvenir->stock) }}" min="0" step="1" required class="{{ $inputClass }}">
                    <p class="{{ $helpClass }}">{{ __('Stok nol akan ditampilkan sebagai habis di katalog.') }}</p>
                    @error('stock')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <section class="{{ $sectionClass }}" aria-labelledby="souvenir-media">
            <div class="border-b border-[#E7E3DC] pb-4 dark:border-[#2A333D]">
                <h2 id="souvenir-media" class="text-lg font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Media') }}</h2>
                <p class="mt-1 text-sm text-[#526071] dark:text-[#AEB8C7]">{{ __('Gambar baru akan menggantikan gambar yang sedang digunakan.') }}</p>
            </div>
            <div class="mt-5 grid gap-5 lg:grid-cols-[12rem_minmax(0,1fr)] lg:items-start">
                <div>
                    <p class="{{ $labelClass }}">{{ __('Gambar Saat Ini') }}</p>
                    <div class="mt-2 aspect-square overflow-hidden rounded-xl border border-[#E7E3DC] bg-[#FAF8F3] dark:border-[#2A333D] dark:bg-[#0E1116]">
                        <img src="{{ $souvenir->image_url ?: asset('demo/souvenir-placeholder.svg') }}" alt="{{ $souvenir->name }}" class="h-full w-full object-cover">
                    </div>
                </div>
                <div class="rounded-xl border border-dashed border-[#DDD6CC] bg-[#FAF8F3] p-4 dark:border-[#2A333D] dark:bg-[#0E1116]">
                    <label for="image" class="{{ $labelClass }}">{{ __('Upload Gambar') }}</label>
                    <input id="image" type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp" class="{{ $inputClass }} file:mr-4 file:rounded-lg file:border-0 file:bg-[#F1EEE8] file:px-3 file:py-2 file:text-xs file:font-semibold file:text-[#374151] dark:file:bg-[#1F2630] dark:file:text-[#D8DEE8]">
                    <p class="{{ $helpClass }}">{{ __('Kosongkan jika tidak ingin mengganti gambar. Format JPG, PNG, GIF, atau WebP; maksimal 2 MB.') }}</p>
                    @error('image')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <div class="flex flex-col-reverse gap-3 rounded-2xl border border-[#E7E3DC] bg-white p-4 sm:flex-row sm:items-center sm:justify-end dark:border-[#2A333D] dark:bg-[#161B22]">
            <a href="{{ route('admin.souvenirs.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-[#E7E3DC] px-4 text-sm font-semibold text-[#526071] transition hover:border-[#B33A3A] hover:text-[#8F2E2E] dark:border-[#2A333D] dark:text-[#AEB8C7] dark:hover:border-[#D96B6B] dark:hover:text-[#D96B6B]">{{ __('Batal') }}</a>
            <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-xl bg-[#B33A3A] px-5 text-sm font-semibold text-white transition hover:bg-[#8F2E2E] focus:outline-none focus:ring-2 focus:ring-[#B33A3A]/30 dark:bg-[#D96B6B] dark:text-[#0E1116] dark:hover:bg-[#E18484]">{{ __('Simpan Perubahan') }}</button>
        </div>
    </form>
</x-admin-layout>
