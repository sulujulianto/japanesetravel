<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tambah Destinasi Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <form action="{{ route('admin.places.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf <div class="mb-4">
                            <x-input-label for="name" :value="__('Nama Destinasi')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="description" :value="__('Deskripsi Singkat')" />
                            <textarea name="description" id="description" rows="4" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="address" :value="__('Alamat Lengkap')" />
                            <x-text-input id="address" class="block mt-1 w-full" type="text" name="address" :value="old('address')" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="facilities" :value="__('Fasilitas (Pisahkan dengan koma)')" />
                            <x-text-input id="facilities" class="block mt-1 w-full" type="text" name="facilities" :value="old('facilities')" placeholder="WiFi, Toilet, Parkir" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="image" :value="__('Gambar Utama')" />
                            <input type="file" name="image" id="image" class="block mt-1 w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400">
                            <p class="mt-1 text-sm text-gray-500">JPG, PNG, GIF (Max. 2MB).</p>
                            <x-input-error :messages="$errors->get('image')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <x-input-label for="open_days" :value="__('Hari Buka (Contoh: Senin-Jumat)')" />
                                <x-text-input id="open_days" class="block mt-1 w-full" type="text" name="open_days" :value="old('open_days')" />
                            </div>
                            <div>
                                <x-input-label for="open_hours" :value="__('Jam Buka (Contoh: 09:00 - 17:00)')" />
                                <x-text-input id="open_hours" class="block mt-1 w-full" type="text" name="open_hours" :value="old('open_hours')" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.places.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Batal</a>
                            <x-primary-button>
                                {{ __('Simpan Data') }}
                            </x-primary-button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>