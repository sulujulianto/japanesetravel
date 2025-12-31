@extends('layouts.site')

@section('title', $place->name . ' · ' . __('Japan Travel'))

@section('content')
    <section class="relative">
        <div class="h-80 w-full overflow-hidden bg-slate-200 dark:bg-slate-800">
            @if($place->image)
                <img src="{{ asset('storage/' . $place->image) }}" alt="{{ $place->name }}" class="h-full w-full object-cover">
            @else
                <img src="{{ asset('demo/place-placeholder.svg') }}" alt="{{ $place->name }}" class="h-full w-full object-cover">
            @endif
        </div>
        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-950/80 to-transparent">
            <div class="mx-auto max-w-7xl px-4 pb-8 sm:px-6 lg:px-8">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-200">{{ __('Destinasi') }}</p>
                <h1 class="mt-2 font-display text-3xl font-semibold text-white sm:text-4xl">{{ $place->name }}</h1>
                <p class="mt-2 text-sm text-slate-200">📍 {{ $place->address ?? __('Lokasi belum ditambahkan') }}</p>
            </div>
        </div>
    </section>

    <section class="mx-auto -mt-10 max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-4 md:grid-cols-3">
            <x-ui.card>
                <p class="text-xs uppercase tracking-wider text-slate-400">{{ __('Rating') }}</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ number_format($place->reviews_avg_rating ?? 0, 1) }}</p>
                <p class="text-sm text-slate-500">{{ $place->reviews_count }} {{ __('ulasan') }}</p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-xs uppercase tracking-wider text-slate-400">{{ __('Jam Operasional') }}</p>
                <p class="mt-2 text-base font-semibold text-slate-900 dark:text-white">{{ $place->open_days ?? '-' }}</p>
                <p class="text-sm text-slate-500">{{ $place->open_hours ?? '-' }}</p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-xs uppercase tracking-wider text-slate-400">{{ __('Ditambahkan Oleh') }}</p>
                <p class="mt-2 text-base font-semibold text-slate-900 dark:text-white">{{ $place->author->username ?? __('Admin') }}</p>
                <p class="text-sm text-slate-500">{{ $place->created_at->format('d M Y') }}</p>
            </x-ui.card>
        </div>
    </section>

    <section class="mx-auto mt-12 max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-3">
            <div class="space-y-8 lg:col-span-2">
                <x-ui.card>
                    <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Tentang Destinasi') }}</h2>
                    <p class="mt-4 text-sm text-slate-600 dark:text-slate-300 whitespace-pre-line">{{ $place->description }}</p>
                </x-ui.card>

                <x-ui.card>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Fasilitas') }}</h3>
                    @if($place->facilities)
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach(explode(',', $place->facilities) as $facility)
                                <x-ui.badge variant="info">{{ trim($facility) }}</x-ui.badge>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-4 text-sm text-slate-500">{{ __('Belum ada data fasilitas.') }}</p>
                    @endif
                </x-ui.card>

                <x-ui.card>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Lokasi') }}</h3>
                    <p class="mt-2 text-sm text-slate-500">{{ $place->address ?? __('Alamat belum disetting untuk peta.') }}</p>
                    <div class="mt-4 rounded-2xl border border-dashed border-slate-200 p-6 text-sm text-slate-500 dark:border-slate-700">
                        {{ __('Peta interaktif akan segera tersedia. Gunakan alamat di atas untuk navigasi langsung.') }}
                    </div>
                </x-ui.card>
            </div>

            <div class="space-y-6">
                <x-ui.card>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Ringkasan Rating') }}</h3>
                    <div class="mt-4 space-y-2 text-sm text-slate-500">
                        <p>{{ __('Rata-rata') }}: <span class="font-semibold text-slate-900 dark:text-white">{{ number_format($place->reviews_avg_rating ?? 0, 1) }}</span></p>
                        <p>{{ __('Total ulasan') }}: <span class="font-semibold text-slate-900 dark:text-white">{{ $place->reviews_count }}</span></p>
                    </div>
                </x-ui.card>
                <x-ui.card>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Tips Berkunjung') }}</h3>
                    <p class="mt-2 text-sm text-slate-500">{{ __('Datang lebih pagi untuk menikmati suasana yang lebih tenang dan pencahayaan terbaik untuk foto.') }}</p>
                </x-ui.card>
            </div>
        </div>
    </section>

    <section class="mx-auto mt-12 max-w-7xl px-4 pb-16 sm:px-6 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <x-ui.card>
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Ulasan Pengunjung') }}</h3>
                        <span class="text-sm text-slate-500">{{ $place->reviews_count }} {{ __('ulasan') }}</span>
                    </div>
                    <div class="mt-6 space-y-4">
                        @forelse($reviews as $review)
                            <div class="rounded-2xl border border-slate-200/70 bg-white/70 p-4 dark:border-slate-800 dark:bg-slate-950/40">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-rose-100 text-sm font-semibold text-rose-600 dark:bg-rose-500/20 dark:text-rose-200">
                                            {{ strtoupper(substr($review->user->username, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $review->user->username }}</p>
                                            <p class="text-xs text-slate-400">{{ $review->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                    <div class="text-xs text-amber-400">
                                        @for($i = 0; $i < 5; $i++)
                                            <span class="{{ $i < $review->rating ? '' : 'text-slate-300 dark:text-slate-700' }}">★</span>
                                        @endfor
                                    </div>
                                </div>
                                <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">{{ $review->comment }}</p>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-200 p-6 text-center text-sm text-slate-500 dark:border-slate-700">
                                {{ __('Belum ada ulasan. Jadilah yang pertama!') }}
                            </div>
                        @endforelse
                    </div>
                    <div class="mt-6">
                        {{ $reviews->links() }}
                    </div>
                </x-ui.card>
            </div>

            <div>
                <x-ui.card>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Tulis Ulasan') }}</h3>
                    @auth
                        <form action="{{ route('review.store', $place->id) }}" method="POST" class="mt-4 space-y-4">
                            @csrf
                            <div>
                                <x-ui.label value="{{ __('Rating') }}" />
                                <x-ui.select name="rating">
                                    <option value="5">{{ __('⭐⭐⭐⭐⭐ (Sempurna)') }}</option>
                                    <option value="4">{{ __('⭐⭐⭐⭐ (Bagus)') }}</option>
                                    <option value="3">{{ __('⭐⭐⭐ (Biasa)') }}</option>
                                    <option value="2">{{ __('⭐⭐ (Buruk)') }}</option>
                                    <option value="1">{{ __('⭐ (Sangat Buruk)') }}</option>
                                </x-ui.select>
                            </div>
                            <div>
                                <x-ui.label value="{{ __('Komentar') }}" />
                                <x-ui.textarea name="comment" rows="4" placeholder="{{ __('Ceritakan pengalamanmu di sini...') }}"></x-ui.textarea>
                            </div>
                            <x-ui.button type="submit">{{ __('Kirim Ulasan') }}</x-ui.button>
                        </form>
                    @else
                        <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-700">
                            <a href="{{ route('login') }}" class="font-semibold underline">{{ __('Masuk') }}</a>
                            {{ __('untuk menulis ulasan.') }}
                        </div>
                    @endauth
                </x-ui.card>
            </div>
        </div>
    </section>
@endsection
