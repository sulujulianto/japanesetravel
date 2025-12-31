<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">{{ __('Souvenir') }}</p>
            <h2 class="text-2xl font-semibold text-slate-900 dark:text-white">{{ __('Edit Souvenir') }}</h2>
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

        <form action="{{ route('admin.souvenirs.update', $souvenir->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <x-ui.label value="{{ __('Nama Produk (ID)') }}" />
                    <x-ui.input name="name_id" value="{{ old('name_id', $souvenir->getTranslation('name', 'id')) }}" required />
                </div>
                <div>
                    <x-ui.label value="{{ __('Nama Produk (EN)') }}" />
                    <x-ui.input name="name_en" value="{{ old('name_en', $souvenir->getTranslation('name', 'en')) }}" required />
                </div>
                <div>
                    <x-ui.label value="{{ __('Deskripsi (ID)') }}" />
                    <x-ui.textarea name="description_id" rows="4">{{ old('description_id', $souvenir->getTranslation('description', 'id')) }}</x-ui.textarea>
                </div>
                <div>
                    <x-ui.label value="{{ __('Deskripsi (EN)') }}" />
                    <x-ui.textarea name="description_en" rows="4">{{ old('description_en', $souvenir->getTranslation('description', 'en')) }}</x-ui.textarea>
                </div>
                <div>
                    <x-ui.label value="{{ __('Harga') }}" />
                    <x-ui.input type="number" name="price" value="{{ old('price', $souvenir->price) }}" required />
                </div>
                <div>
                    <x-ui.label value="{{ __('Stok') }}" />
                    <x-ui.input type="number" name="stock" value="{{ old('stock', $souvenir->stock) }}" required />
                </div>
                <div>
                    <x-ui.label value="{{ __('Upload Gambar') }}" />
                    <x-ui.input type="file" name="image" />
                </div>
            </div>

            <div class="flex gap-3">
                <x-ui.button type="submit">{{ __('Simpan Perubahan') }}</x-ui.button>
                <a href="{{ route('admin.souvenirs.index') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-900 dark:text-slate-300">{{ __('Batal') }}</a>
            </div>
        </form>
    </x-ui.card>
</x-admin-layout>
