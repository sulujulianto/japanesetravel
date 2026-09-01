@extends('layouts.site')

@section('title', $place->name . ' · ' . \App\Support\Brand::name())

@section('content')
    @php
        $ratingValue = \App\Support\Format::rating($place->reviews_avg_rating ?? 0);
        $reviewCount = $place->reviews_count ?? 0;
        $travelWhatsappNumber = preg_replace('/\D+/', '', (string) config('services.travel.whatsapp_number'));
        $travelWhatsappUrl = $travelWhatsappNumber !== '' ? 'https://wa.me/'.$travelWhatsappNumber : null;
        $placeImage = $place->image_url ?: asset('demo/place-placeholder.svg');
    @endphp

    <section data-place-detail-layout class="ui-reveal mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
        <a href="{{ route('places.index') }}" class="ui-button-quiet px-4 py-2 text-sm">
            <span aria-hidden="true" class="mr-2">←</span>{{ __('Katalog destinasi') }}
        </a>

        <div class="ui-surface mt-6 overflow-hidden rounded-[24px]">
            <img src="{{ $placeImage }}" alt="{{ $place->name }}" class="aspect-[16/11] w-full object-cover sm:aspect-[16/8] lg:aspect-[16/7]" decoding="async" fetchpriority="high">
        </div>

        <header class="mt-8 max-w-4xl">
            <p class="ui-eyebrow">{{ __('Destinasi') }}</p>
            <h1 class="ui-heading mt-3 text-4xl leading-tight sm:text-5xl">{{ $place->name }}</h1>
            <p class="ui-copy mt-4 max-w-2xl text-base">{{ $place->address ?? __('Lokasi belum ditambahkan') }}</p>

            <div class="mt-6 flex flex-wrap gap-3">
                <span class="inline-flex items-center rounded-lg border border-[var(--public-border)] bg-[var(--public-accent-soft)] px-4 py-2 text-sm font-semibold text-[var(--public-ink)]">
                    {{ __('Rating') }} {{ $ratingValue }}
                </span>
                <span class="inline-flex items-center rounded-lg border border-[var(--public-border)] bg-[var(--public-secondary-soft)] px-4 py-2 text-sm font-semibold text-[var(--public-muted)]">
                    {{ trans_choice('Jumlah ulasan', $reviewCount, ['count' => $reviewCount]) }}
                </span>
                @if($place->open_days || $place->open_hours)
                    <span class="inline-flex items-center rounded-lg border border-[var(--public-border)] bg-[var(--public-surface)] px-4 py-2 text-sm font-semibold text-[var(--public-muted)]">
                        {{ $place->open_days ?? __('Jadwal fleksibel') }} · {{ $place->open_hours ?? __('Jam belum tersedia') }}
                    </span>
                @endif
            </div>
        </header>

        <div class="mt-8 grid gap-6 lg:grid-cols-12 lg:items-start lg:gap-8">
            <div data-place-detail-main class="space-y-6 lg:col-span-8">
                <article class="ui-surface rounded-[20px] p-6 sm:p-8">
                    <p class="ui-eyebrow">{{ __('Tentang Destinasi') }}</p>
                    <h2 class="ui-heading mt-3 text-2xl">{{ $place->name }}</h2>
                    <p class="ui-copy mt-5 whitespace-pre-line text-base leading-8">{{ $place->description }}</p>
                </article>

                <section class="ui-surface rounded-[20px] p-6 sm:p-8">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="ui-eyebrow text-[var(--public-secondary)]">{{ __('Informasi Kunjungan') }}</p>
                            <h2 class="ui-heading mt-3 text-2xl">{{ __('Sebelum berangkat') }}</h2>
                        </div>
                        @if($place->open_days || $place->open_hours)
                            <p class="text-sm font-semibold text-[var(--public-muted)]">{{ $place->open_days ?? '-' }} · {{ $place->open_hours ?? '-' }}</p>
                        @endif
                    </div>

                    <div class="mt-6 space-y-5">
                        <div>
                            <h3 class="text-sm font-semibold text-[var(--public-ink)]">{{ __('Fasilitas') }}</h3>
                            @if($place->facilities)
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach(explode(',', $place->facilities) as $facility)
                                        <span class="rounded-lg border border-[var(--public-border)] bg-[var(--public-secondary-soft)] px-3 py-1.5 text-xs font-semibold text-[var(--public-secondary)]">{{ trim($facility) }}</span>
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-3 text-sm leading-6 text-[var(--public-muted)]">{{ __('Belum ada data fasilitas.') }}</p>
                            @endif
                        </div>

                        <div class="ui-surface-muted rounded-2xl p-5">
                            <h3 class="text-sm font-semibold text-[var(--public-ink)]">{{ __('Lokasi') }}</h3>
                            <p class="mt-3 text-sm leading-6 text-[var(--public-muted)]">{{ $place->address ?? __('Alamat belum tersedia.') }}</p>
                        </div>
                    </div>
                </section>
                <section class="ui-surface rounded-[20px] p-6 sm:p-8">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="ui-eyebrow">{{ __('Ulasan Pengunjung') }}</p>
                            <h2 class="ui-heading mt-2 text-2xl">{{ __('Pengalaman dari pengunjung') }}</h2>
                        </div>
                        <span class="inline-flex w-fit rounded-lg border border-[var(--public-border)] bg-[var(--public-surface-muted)] px-3 py-1.5 text-sm font-semibold text-[var(--public-muted)]">
                            {{ trans_choice('Jumlah ulasan', $reviewCount, ['count' => $reviewCount]) }}
                        </span>
                    </div>

                    <div class="mt-6 space-y-4">
                        @forelse($reviews as $review)
                            <article class="ui-surface-muted rounded-[16px] p-4 sm:p-5">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--public-accent-soft)] text-sm font-semibold text-[var(--public-accent)]">
                                            {{ strtoupper(substr($review->user->username, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-[var(--public-ink)]">{{ $review->user->username }}</p>
                                            <p class="mt-1 text-xs font-medium text-[var(--public-muted)]">{{ \App\Support\Format::relative($review->created_at) }}</p>
                                        </div>
                                    </div>
                                    <div class="shrink-0 text-sm tracking-wide text-[var(--public-warning)]">
                                        @for($i = 0; $i < 5; $i++)
                                            <span class="{{ $i < $review->rating ? '' : 'opacity-30' }}">★</span>
                                        @endfor
                                    </div>
                                </div>
                                <p class="mt-4 text-sm leading-6 text-[var(--public-muted)]">{{ $review->comment }}</p>
                            </article>
                        @empty
                            <div class="ui-surface-muted rounded-[16px] border-dashed p-6 text-center text-sm leading-6 text-[var(--public-muted)]">
                                {{ __('Belum ada ulasan. Jadilah yang pertama!') }}
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-6">
                        {{ $reviews->links() }}
                    </div>
                </section>

                <section class="ui-surface rounded-[20px] p-6 sm:p-8">
                    <h2 class="ui-heading text-xl">{{ __('Tulis Ulasan') }}</h2>
                    @auth
                        <form action="{{ route('review.store', $place->id) }}" method="POST" class="mt-5 space-y-4">
                            @csrf
                            <div>
                                <x-ui.label value="{{ __('Rating') }}" />
                                <x-ui.select name="rating">
                                    <option value="5">{{ __('5 - Sempurna') }}</option>
                                    <option value="4">{{ __('4 - Bagus') }}</option>
                                    <option value="3">{{ __('3 - Biasa') }}</option>
                                    <option value="2">{{ __('2 - Buruk') }}</option>
                                    <option value="1">{{ __('1 - Sangat Buruk') }}</option>
                                </x-ui.select>
                            </div>
                            <div>
                                <x-ui.label value="{{ __('Komentar') }}" />
                                <x-ui.textarea name="comment" rows="4" placeholder="{{ __('Ceritakan pengalaman Anda di sini...') }}"></x-ui.textarea>
                            </div>
                            <x-ui.button type="submit" class="w-full sm:w-auto">{{ __('Kirim Ulasan') }}</x-ui.button>
                        </form>
                    @else
                        <div class="mt-5 rounded-xl border border-[var(--public-warning)] bg-[var(--public-surface-muted)] p-4 text-sm leading-6 text-[var(--public-muted)]">
                            <a href="{{ route('login') }}" class="font-semibold text-[var(--public-accent)] underline">{{ __('Masuk') }}</a>
                            {{ __('untuk menulis ulasan.') }}
                        </div>
                    @endauth
                </section>
            </div>

            <aside class="space-y-5 lg:col-span-4 lg:self-start">
                <section class="rounded-[20px] border border-[var(--public-border)] bg-[var(--public-secondary-soft)] p-5 sm:p-6">
                    <p class="ui-eyebrow text-[var(--public-secondary)]">{{ __('Travel inquiry') }}</p>
                    <h2 class="ui-heading mt-3 text-xl">{{ __('Butuh bantuan menyusun perjalanan?') }}</h2>
                    <p class="ui-copy mt-3 text-sm">
                        {{ __('Hubungi kami untuk bertanya tentang rute, waktu kunjungan, atau kebutuhan travel.') }}
                    </p>

                    <div class="mt-5 space-y-3">
                        @if($travelWhatsappUrl)
                            <a href="{{ $travelWhatsappUrl }}" target="_blank" rel="noopener noreferrer" class="ui-button-secondary w-full px-5 py-3 text-sm">
                                {{ __('Konsultasi via WhatsApp') }}
                            </a>
                        @else
                            <div aria-disabled="true" class="ui-button-quiet w-full cursor-not-allowed px-5 py-3 text-sm opacity-70">
                                {{ __('WhatsApp belum tersedia') }}
                            </div>
                        @endif

                        <a href="{{ route('shop.index') }}" class="ui-button-quiet w-full px-5 py-3 text-sm">
                            {{ __('Lihat katalog oleh-oleh') }}
                        </a>
                    </div>
                </section>

                <section class="ui-surface rounded-[20px] p-5 sm:p-6">
                    <h3 class="text-base font-semibold text-[var(--public-ink)]">{{ __('Ringkasan destinasi') }}</h3>
                    <dl class="mt-4 divide-y divide-[var(--public-border)] border-y border-[var(--public-border)] text-sm">
                        <div class="grid grid-cols-[96px,1fr] gap-4 py-3">
                            <dt class="font-semibold text-[var(--public-muted)]">{{ __('Rating') }}</dt>
                            <dd class="text-[var(--public-ink)]">{{ $ratingValue }} · {{ trans_choice('Jumlah ulasan', $reviewCount, ['count' => $reviewCount]) }}</dd>
                        </div>
                        <div class="grid grid-cols-[96px,1fr] gap-4 py-3">
                            <dt class="font-semibold text-[var(--public-muted)]">{{ __('Lokasi') }}</dt>
                            <dd class="text-[var(--public-ink)]">{{ $place->address ?? __('Belum tersedia') }}</dd>
                        </div>
                        <div class="grid grid-cols-[96px,1fr] gap-4 py-3">
                            <dt class="font-semibold text-[var(--public-muted)]">{{ __('Jam') }}</dt>
                            <dd class="text-[var(--public-ink)]">{{ $place->open_days ?? '-' }} · {{ $place->open_hours ?? '-' }}</dd>
                        </div>
                        <div class="grid grid-cols-[96px,1fr] gap-4 py-3">
                            <dt class="font-semibold text-[var(--public-muted)]">{{ __('Kurator') }}</dt>
                            <dd class="text-[var(--public-ink)]">{{ $place->author->username ?? __('Admin') }}</dd>
                        </div>
                    </dl>
                    <p class="ui-surface-muted mt-4 rounded-xl p-4 text-sm leading-6 text-[var(--public-muted)]">
                        {{ __('Tidak ada transaksi jasa travel langsung di halaman ini.') }}
                    </p>
                </section>
            </aside>

        </div>
    </section>
@endsection
