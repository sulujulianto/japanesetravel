<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Kelola Destinasi Wisata') }}
            </h2>
            <a href="{{ route('admin.places.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                + Tambah Destinasi
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Berhasil!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Gambar</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nama Destinasi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Alamat</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($places as $place)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($place->image)
                                            <img src="{{ asset('storage/' . $place->image) }}" alt="" class="w-16 h-16 object-cover rounded border border-gray-200">
                                        @else
                                            <span class="text-gray-400 text-xs italic">No Image</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap font-bold">{{ $place->name }}</td>
                                    <td class="px-6 py-4 text-sm">{{ Str::limit($place->address, 50) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('admin.places.edit', $place->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-3 font-bold">Edit</a>
                                        
                                        <form action="{{ route('admin.places.destroy', $place->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus {{ $place->name }}? Data tidak bisa dikembalikan.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-bold">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                        <p class="text-lg">Belum ada data destinasi wisata.</p>
                                        <p class="text-sm mt-1">Silakan klik tombol "Tambah Destinasi" di atas.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $places->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>