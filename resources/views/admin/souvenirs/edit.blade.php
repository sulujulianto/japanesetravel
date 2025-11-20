<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Barang') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('admin.souvenirs.update', $souvenir->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf @method('PUT')
                        
                        <div class="mb-4">
                            <x-input-label for="name" :value="__('Nama Barang')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $souvenir->name)" required />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="description" :value="__('Deskripsi')" />
                            <textarea name="description" rows="3" class="block mt-1 w-full border-gray-300 dark:bg-gray-900 dark:text-white rounded-md shadow-sm">{{ old('description', $souvenir->description) }}</textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <x-input-label for="price" :value="__('Harga (Rp)')" />
                                <x-text-input id="price" class="block mt-1 w-full" type="number" name="price" :value="old('price', $souvenir->price)" required />
                            </div>
                            <div>
                                <x-input-label for="stock" :value="__('Stok')" />
                                <x-text-input id="stock" class="block mt-1 w-full" type="number" name="stock" :value="old('stock', $souvenir->stock)" required />
                            </div>
                        </div>

                        <div class="mb-4">
                            <x-input-label for="image" :value="__('Ganti Foto (Opsional)')" />
                            @if($souvenir->image)
                                <img src="{{ asset('storage/' . $souvenir->image) }}" class="w-20 h-20 object-cover mb-2 rounded">
                            @endif
                            <input type="file" name="image" class="block mt-1 w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-700 dark:text-white">
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.souvenirs.index') }}" class="text-gray-600 mr-4">Batal</a>
                            <x-primary-button>Update Barang</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>