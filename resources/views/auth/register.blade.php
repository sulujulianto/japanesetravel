<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Buat Akun Baru 🚀</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">Bergabunglah dengan komunitas traveler Jepang.</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <x-input-label for="username" :value="__('Username')" class="font-semibold"/>
            <x-text-input id="username" class="block mt-1 w-full border-gray-300 focus:border-sky-500 focus:ring-sky-500 rounded-lg py-3" type="text" name="username" :value="old('username')" required autofocus autocomplete="username" placeholder="Username unik" />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" class="font-semibold"/>
            <x-text-input id="email" class="block mt-1 w-full border-gray-300 focus:border-sky-500 focus:ring-sky-500 rounded-lg py-3" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="nama@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" class="font-semibold"/>
            <x-text-input id="password" class="block mt-1 w-full border-gray-300 focus:border-sky-500 focus:ring-sky-500 rounded-lg py-3"
                            type="password"
                            name="password"
                            required autocomplete="new-password"
                            placeholder="Minimal 8 karakter" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" class="font-semibold"/>
            <x-text-input id="password_confirmation" class="block mt-1 w-full border-gray-300 focus:border-sky-500 focus:ring-sky-500 rounded-lg py-3"
                            type="password"
                            name="password_confirmation"
                            required autocomplete="new-password"
                            placeholder="Ulangi password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-6">
            <x-primary-button class="w-full justify-center py-3 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-lg transition transform active:scale-95">
                {{ __('Daftar') }}
            </x-primary-button>
        </div>

        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">Sudah punya akun? 
                <a href="{{ route('login') }}" class="font-bold text-sky-600 hover:underline">Masuk di sini</a>
            </p>
        </div>
    </form>
</x-guest-layout>