<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">{{ __('Destinasi') }}</p>
            <h2 class="text-2xl font-semibold text-slate-900 dark:text-white">{{ __('Tambah Destinasi Baru') }}</h2>
        </div>
    </x-slot>

    <x-ui.card>
        @if ($errors->any())
            <x-ui.alert variant="danger" class="mb-6">
                <ul class="list-disc space-y-1 pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        <form action="{{ route('admin.places.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <x-ui.label value="{{ __('Nama Destinasi (ID)') }}" />
                    <x-ui.input name="name_id" value="{{ old('name_id') }}" required />
                </div>
                <div>
                    <x-ui.label value="{{ __('Nama Destinasi (EN)') }}" />
                    <x-ui.input name="name_en" value="{{ old('name_en') }}" required />
                </div>
                <div>
                    <x-ui.label value="{{ __('Deskripsi (ID)') }}" />
                    <x-ui.textarea name="description_id" rows="4">{{ old('description_id') }}</x-ui.textarea>
                </div>
                <div>
                    <x-ui.label value="{{ __('Deskripsi (EN)') }}" />
                    <x-ui.textarea name="description_en" rows="4">{{ old('description_en') }}</x-ui.textarea>
                </div>
                <div class="md:col-span-2">
                    <x-ui.label value="{{ __('Alamat') }}" />
                    <x-ui.input name="address" value="{{ old('address') }}" />
                </div>
                <div>
                    <x-ui.label value="{{ __('Fasilitas (pisahkan dengan koma)') }}" />
                    <x-ui.input name="facilities" value="{{ old('facilities') }}" />
                </div>
                <div>
                    <x-ui.label value="{{ __('Hari Buka') }}" />
                    <x-ui.input name="open_days" value="{{ old('open_days') }}" />
                </div>
                <div>
                    <x-ui.label value="{{ __('Jam Buka') }}" />
                    <x-ui.input name="open_hours" value="{{ old('open_hours') }}" />
                </div>
                <div>
                    <x-ui.label value="{{ __('Upload Gambar') }}" />
                    <x-ui.input type="file" name="image" />
                </div>
            </div>

            <div class="flex gap-3">
                <x-ui.button type="submit">{{ __('Simpan') }}</x-ui.button>
                <a href="{{ route('admin.places.index') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-900 dark:text-slate-300">{{ __('Batal') }}</a>
            </div>
        </form>
    </x-ui.card>
</x-admin-layout>
