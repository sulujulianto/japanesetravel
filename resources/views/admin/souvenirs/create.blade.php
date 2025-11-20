<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tambah Barang Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-xl sm:rounded-2xl overflow-hidden">
                
                <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 border-b border-gray-200 dark:border-gray-600">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Informasi Produk</h3>
                    <p class="text-sm text-gray-500">Lengkapi data barang yang akan dijual.</p>
                </div>

                <div class="p-8">
                    <form action="{{ route('admin.souvenirs.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-6">
                                <div>
                                    <x-input-label for="name" :value="__('Nama Barang')" />
                                    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus placeholder="Contoh: Kimono Batik" />
                                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="description" :value="__('Deskripsi')" />
                                    <textarea name="description" rows="4" class="block mt-1 w-full border-gray-300 dark:bg-gray-900 dark:text-white rounded-md shadow-sm focus:border-sky-500 focus:ring-sky-500" placeholder="Jelaskan detail barang...">{{ old('description') }}</textarea>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="price" :value="__('Harga (Rp)')" />
                                        <div class="relative mt-1">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">Rp</span>
                                            </div>
                                            <x-text-input id="price" class="block w-full pl-10" type="number" name="price" :value="old('price')" required placeholder="0" />
                                        </div>
                                    </div>
                                    <div>
                                        <x-input-label for="stock" :value="__('Stok Awal')" />
                                        <x-text-input id="stock" class="block mt-1 w-full" type="number" name="stock" :value="old('stock')" required placeholder="0" />
                                    </div>
                                </div>

                                <div>
                                    <x-input-label for="image" :value="__('Foto Barang')" />
                                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:bg-gray-50 transition cursor-pointer relative">
                                        <div class="space-y-1 text-center">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <div class="flex text-sm text-gray-600 justify-center">
                                                <label for="image" class="relative cursor-pointer bg-white rounded-md font-medium text-sky-600 hover:text-sky-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-sky-500">
                                                    <span>Upload file</span>
                                                    <input id="image" name="image" type="file" class="sr-only">
                                                </label>
                                                <p class="pl-1">atau drag ke sini</p>
                                            </div>
                                            <p class="text-xs text-gray-500">PNG, JPG, GIF up to 2MB</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8 pt-6 border-t border-gray-200 dark:border-gray-700 gap-4">
                            <a href="{{ route('admin.souvenirs.index') }}" class="text-gray-600 hover:text-gray-900 font-medium">Batal</a>
                            <x-primary-button class="bg-sky-600 hover:bg-sky-700">Simpan Barang</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>