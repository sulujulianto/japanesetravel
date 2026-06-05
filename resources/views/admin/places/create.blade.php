<x-admin-layout>
    <x-slot name="header">
        <div class="rounded-2xl border border-[#E7E3DC] bg-white p-5 sm:p-6 dark:border-[#2A333D] dark:bg-[#161B22]">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#667085] dark:text-[#AEB8C7]">{{ __('Destinasi') }}</p>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-[#1F2937] dark:text-[#F4F1ED] sm:text-3xl">{{ __('Tambah Destinasi Baru') }}</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-[#526071] dark:text-[#AEB8C7]">{{ __('Lengkapi konten bilingual, informasi kunjungan, dan gambar destinasi untuk katalog publik.') }}</p>
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

    <form action="{{ route('admin.places.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <section class="{{ $sectionClass }}" aria-labelledby="place-id-content">
            <div class="border-b border-[#E7E3DC] pb-4 dark:border-[#2A333D]">
                <h2 id="place-id-content" class="text-lg font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Konten Bahasa Indonesia') }}</h2>
                <p class="mt-1 text-sm text-[#526071] dark:text-[#AEB8C7]">{{ __('Informasi utama yang dibaca pengunjung berbahasa Indonesia.') }}</p>
            </div>
            <div class="mt-5 grid gap-5">
                <div>
                    <label for="name_id" class="{{ $labelClass }}">{{ __('Nama Destinasi (ID)') }}</label>
                    <input id="name_id" name="name_id" value="{{ old('name_id') }}" required class="{{ $inputClass }}">
                    @error('name_id')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="description_id" class="{{ $labelClass }}">{{ __('Deskripsi (ID)') }}</label>
                    <textarea id="description_id" name="description_id" rows="6" class="{{ $inputClass }}">{{ old('description_id') }}</textarea>
                    <p class="{{ $helpClass }}">{{ __('Jelaskan karakter, suasana, dan informasi penting destinasi secara ringkas.') }}</p>
                    @error('description_id')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <section class="{{ $sectionClass }}" aria-labelledby="place-en-content">
            <div class="border-b border-[#E7E3DC] pb-4 dark:border-[#2A333D]">
                <h2 id="place-en-content" class="text-lg font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Konten Bahasa Inggris') }}</h2>
                <p class="mt-1 text-sm text-[#526071] dark:text-[#AEB8C7]">{{ __('Gunakan terjemahan yang natural dan konsisten dengan konten Indonesia.') }}</p>
            </div>
            <div class="mt-5 grid gap-5">
                <div>
                    <label for="name_en" class="{{ $labelClass }}">{{ __('Nama Destinasi (EN)') }}</label>
                    <input id="name_en" name="name_en" value="{{ old('name_en') }}" required class="{{ $inputClass }}">
                    @error('name_en')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="description_en" class="{{ $labelClass }}">{{ __('Deskripsi (EN)') }}</label>
                    <textarea id="description_en" name="description_en" rows="6" class="{{ $inputClass }}">{{ old('description_en') }}</textarea>
                    @error('description_en')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <section class="{{ $sectionClass }}" aria-labelledby="place-visit-information">
            <div class="border-b border-[#E7E3DC] pb-4 dark:border-[#2A333D]">
                <h2 id="place-visit-information" class="text-lg font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Informasi Kunjungan') }}</h2>
                <p class="mt-1 text-sm text-[#526071] dark:text-[#AEB8C7]">{{ __('Data operasional yang membantu pengunjung memahami lokasi dan waktu kunjungan.') }}</p>
            </div>
            <div class="mt-5 grid gap-5 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="address" class="{{ $labelClass }}">{{ __('Alamat') }}</label>
                    <input id="address" name="address" value="{{ old('address') }}" class="{{ $inputClass }}">
                    @error('address')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label for="facilities" class="{{ $labelClass }}">{{ __('Fasilitas (pisahkan dengan koma)') }}</label>
                    <input id="facilities" name="facilities" value="{{ old('facilities') }}" class="{{ $inputClass }}">
                    <p class="{{ $helpClass }}">{{ __('Contoh: WiFi, Restoran, Area parkir.') }}</p>
                    @error('facilities')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="open_days" class="{{ $labelClass }}">{{ __('Hari Buka') }}</label>
                    <input id="open_days" name="open_days" value="{{ old('open_days') }}" class="{{ $inputClass }}">
                    @error('open_days')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="open_hours" class="{{ $labelClass }}">{{ __('Jam Buka') }}</label>
                    <input id="open_hours" name="open_hours" value="{{ old('open_hours') }}" class="{{ $inputClass }}">
                    @error('open_hours')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <section class="{{ $sectionClass }}" aria-labelledby="place-media">
            <div class="border-b border-[#E7E3DC] pb-4 dark:border-[#2A333D]">
                <h2 id="place-media" class="text-lg font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Media') }}</h2>
                <p class="mt-1 text-sm text-[#526071] dark:text-[#AEB8C7]">{{ __('Unggah gambar utama yang representatif dan jelas.') }}</p>
            </div>
            <div class="mt-5 rounded-xl border border-dashed border-[#DDD6CC] bg-[#FAF8F3] p-4 dark:border-[#2A333D] dark:bg-[#0E1116]">
                <label for="image" class="{{ $labelClass }}">{{ __('Upload Gambar') }}</label>
                <input id="image" type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp" class="{{ $inputClass }} file:mr-4 file:rounded-lg file:border-0 file:bg-[#F1EEE8] file:px-3 file:py-2 file:text-xs file:font-semibold file:text-[#374151] dark:file:bg-[#1F2630] dark:file:text-[#D8DEE8]">
                <p class="{{ $helpClass }}">{{ __('Format gambar yang didukung: JPG, PNG, GIF, atau WebP. Maksimal 2 MB.') }}</p>
                @error('image')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
            </div>
        </section>

        <div class="flex flex-col-reverse gap-3 rounded-2xl border border-[#E7E3DC] bg-white p-4 sm:flex-row sm:items-center sm:justify-end dark:border-[#2A333D] dark:bg-[#161B22]">
            <a href="{{ route('admin.places.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-[#E7E3DC] px-4 text-sm font-semibold text-[#526071] transition hover:border-[#B33A3A] hover:text-[#8F2E2E] dark:border-[#2A333D] dark:text-[#AEB8C7] dark:hover:border-[#D96B6B] dark:hover:text-[#D96B6B]">{{ __('Batal') }}</a>
            <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-xl bg-[#B33A3A] px-5 text-sm font-semibold text-white transition hover:bg-[#8F2E2E] focus:outline-none focus:ring-2 focus:ring-[#B33A3A]/30 dark:bg-[#D96B6B] dark:text-[#0E1116] dark:hover:bg-[#E18484]">{{ __('Simpan') }}</button>
        </div>
    </form>
</x-admin-layout>
