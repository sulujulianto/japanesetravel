@extends('layouts.site')

@section('title', $place->name . ' · ' . __('Japan Travel'))

@section('content')
    @php
        $ratingValue = number_format($place->reviews_avg_rating ?? 0, 1);
        $reviewCount = $place->reviews_count ?? 0;
        $travelWhatsappNumber = preg_replace('/\D+/', '', (string) config('services.travel.whatsapp_number'));
        $travelWhatsappUrl = $travelWhatsappNumber !== '' ? 'https://wa.me/'.$travelWhatsappNumber : null;
        $placeImage = $place->image_url ?: asset('demo/place-placeholder.svg');
    @endphp

    <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
        <a href="{{ route('places.index') }}" class="inline-flex items-center justify-center rounded-full border border-[#DDD6CC] bg-white px-4 py-2 text-sm font-semibold text-[#374151] transition hover:border-[#B33A3A] hover:text-[#8F2E2E] dark:border-[#2A333D] dark:bg-[#161B22] dark:text-[#D8DEE8] dark:hover:border-[#D96B6B] dark:hover:text-[#D96B6B]">
            {{ __('Katalog destinasi') }}
        </a>

        <div class="mt-6 overflow-hidden rounded-[28px] border border-[#E7E3DC] bg-[#F1EEE8] shadow-sm dark:border-[#2A333D] dark:bg-[#1F2630]">
            <img src="{{ $placeImage }}" alt="{{ $place->name }}" class="aspect-[16/11] w-full object-cover sm:aspect-[16/8] lg:aspect-[16/7]">
        </div>

        <header class="mt-8 max-w-4xl">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#526071] dark:text-[#AEB8C7]">{{ __('Destinasi') }}</p>
            <h1 class="mt-3 text-4xl font-semibold leading-tight text-[#1F2937] dark:text-[#F4F1ED] sm:text-5xl">{{ $place->name }}</h1>
            <p class="mt-4 max-w-2xl text-base leading-7 text-[#374151] dark:text-[#D8DEE8]">{{ $place->address ?? __('Lokasi belum ditambahkan') }}</p>

            <div class="mt-6 flex flex-wrap gap-3">
                <span class="inline-flex items-center rounded-full border border-[#DDD6CC] bg-white px-4 py-2 text-sm font-semibold text-[#1F2937] dark:border-[#2A333D] dark:bg-[#161B22] dark:text-[#F4F1ED]">
                    {{ __('Rating') }} {{ $ratingValue }}
                </span>
                <span class="inline-flex items-center rounded-full border border-[#DDD6CC] bg-white px-4 py-2 text-sm font-semibold text-[#526071] dark:border-[#2A333D] dark:bg-[#161B22] dark:text-[#D8DEE8]">
                    {{ $reviewCount }} {{ __('ulasan') }}
                </span>
                @if($place->open_days || $place->open_hours)
                    <span class="inline-flex items-center rounded-full border border-[#DDD6CC] bg-white px-4 py-2 text-sm font-semibold text-[#526071] dark:border-[#2A333D] dark:bg-[#161B22] dark:text-[#D8DEE8]">
                        {{ $place->open_days ?? __('Jadwal fleksibel') }} · {{ $place->open_hours ?? __('Jam belum tersedia') }}
                    </span>
                @endif
            </div>
        </header>

        <div class="mt-6 grid gap-6 lg:grid-cols-12 lg:items-start lg:gap-8">
            <div class="space-y-6 lg:col-span-8">
                <article class="rounded-[24px] border border-[#E7E3DC] bg-white p-6 shadow-sm dark:border-[#2A333D] dark:bg-[#161B22] sm:p-8">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#526071] dark:text-[#AEB8C7]">{{ __('Tentang Destinasi') }}</p>
                    <h2 class="mt-3 text-2xl font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ $place->name }}</h2>
                    <p class="mt-5 whitespace-pre-line text-base leading-8 text-[#374151] dark:text-[#D8DEE8]">{{ $place->description }}</p>
                </article>

                <section class="rounded-[24px] border border-[#E7E3DC] bg-white p-6 shadow-sm dark:border-[#2A333D] dark:bg-[#161B22] sm:p-8">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#526071] dark:text-[#AEB8C7]">{{ __('Informasi Kunjungan') }}</p>
                            <h2 class="mt-3 text-2xl font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Sebelum berangkat') }}</h2>
                        </div>
                        @if($place->open_days || $place->open_hours)
                            <p class="text-sm font-semibold text-[#526071] dark:text-[#AEB8C7]">{{ $place->open_days ?? '-' }} · {{ $place->open_hours ?? '-' }}</p>
                        @endif
                    </div>

                    <div class="mt-6 space-y-5">
                        <div>
                            <h3 class="text-sm font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Fasilitas') }}</h3>
                            @if($place->facilities)
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach(explode(',', $place->facilities) as $facility)
                                        <span class="rounded-full border border-[#DDD6CC] bg-[#FAF9F6] px-3 py-1.5 text-xs font-semibold text-[#2F5D50] dark:border-[#2A333D] dark:bg-[#1F2630] dark:text-[#8AB7A4]">{{ trim($facility) }}</span>
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-3 text-sm leading-6 text-[#526071] dark:text-[#D8DEE8]">{{ __('Belum ada data fasilitas.') }}</p>
                            @endif
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl border border-[#E7E3DC] bg-[#FAF9F6] p-5 dark:border-[#2A333D] dark:bg-[#1F2630]">
                                <h3 class="text-sm font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Lokasi') }}</h3>
                                <p class="mt-3 text-sm leading-6 text-[#526071] dark:text-[#D8DEE8]">{{ $place->address ?? __('Alamat belum tersedia.') }}</p>
                            </div>
                            <div class="rounded-2xl border border-[#E7E3DC] bg-[#FAF9F6] p-5 dark:border-[#2A333D] dark:bg-[#1F2630]">
                                <h3 class="text-sm font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Peta') }}</h3>
                                <p class="mt-3 text-sm leading-6 text-[#526071] dark:text-[#D8DEE8]">{{ __('Peta interaktif belum tersedia.') }}</p>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <aside class="space-y-5 lg:col-span-4 lg:sticky lg:top-24">
                <section class="rounded-[24px] border border-[#E7E3DC] bg-white p-5 shadow-sm dark:border-[#2A333D] dark:bg-[#161B22] sm:p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#B33A3A] dark:text-[#D96B6B]">{{ __('Travel inquiry') }}</p>
                    <h2 class="mt-3 text-xl font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Butuh bantuan menyusun perjalanan?') }}</h2>
                    <p class="mt-3 text-sm leading-6 text-[#374151] dark:text-[#D8DEE8]">
                        {{ __('Hubungi kami untuk bertanya tentang rute, waktu kunjungan, atau kebutuhan travel.') }}
                    </p>

                    <div class="mt-5 space-y-3">
                        @if($travelWhatsappUrl)
                            <a href="{{ $travelWhatsappUrl }}" target="_blank" rel="noopener noreferrer" class="flex w-full items-center justify-center rounded-full bg-[#B33A3A] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#8F2E2E] dark:bg-[#D96B6B] dark:text-[#0E1116] dark:hover:bg-[#E18484]">
                                {{ __('Konsultasi via WhatsApp') }}
                            </a>
                        @else
                            <div aria-disabled="true" class="flex w-full items-center justify-center rounded-full border border-[#DDD6CC] bg-[#F1EEE8] px-5 py-3 text-sm font-semibold text-[#526071] dark:border-[#2A333D] dark:bg-[#1F2630] dark:text-[#AEB8C7]">
                                {{ __('WhatsApp belum tersedia') }}
                            </div>
                        @endif

                        <a href="{{ route('shop.index') }}" class="flex w-full items-center justify-center rounded-full border border-[#B33A3A] bg-white px-5 py-3 text-sm font-semibold text-[#8F2E2E] transition hover:bg-[#FFF5F3] dark:border-[#D96B6B] dark:bg-[#161B22] dark:text-[#D96B6B] dark:hover:bg-[#241F20]">
                            {{ __('Lihat katalog oleh-oleh') }}
                        </a>
                    </div>
                </section>

                <section class="rounded-[24px] border border-[#E7E3DC] bg-white p-5 shadow-sm dark:border-[#2A333D] dark:bg-[#161B22] sm:p-6">
                    <h3 class="text-base font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Ringkasan destinasi') }}</h3>
                    <dl class="mt-4 divide-y divide-[#E7E3DC] border-y border-[#E7E3DC] text-sm dark:divide-[#2A333D] dark:border-[#2A333D]">
                        <div class="grid grid-cols-[96px,1fr] gap-4 py-3">
                            <dt class="font-semibold text-[#526071] dark:text-[#AEB8C7]">{{ __('Rating') }}</dt>
                            <dd class="text-[#1F2937] dark:text-[#F4F1ED]">{{ $ratingValue }} · {{ $reviewCount }} {{ __('ulasan') }}</dd>
                        </div>
                        <div class="grid grid-cols-[96px,1fr] gap-4 py-3">
                            <dt class="font-semibold text-[#526071] dark:text-[#AEB8C7]">{{ __('Lokasi') }}</dt>
                            <dd class="text-[#374151] dark:text-[#D8DEE8]">{{ $place->address ?? __('Belum tersedia') }}</dd>
                        </div>
                        <div class="grid grid-cols-[96px,1fr] gap-4 py-3">
                            <dt class="font-semibold text-[#526071] dark:text-[#AEB8C7]">{{ __('Jam') }}</dt>
                            <dd class="text-[#374151] dark:text-[#D8DEE8]">{{ $place->open_days ?? '-' }} · {{ $place->open_hours ?? '-' }}</dd>
                        </div>
                        <div class="grid grid-cols-[96px,1fr] gap-4 py-3">
                            <dt class="font-semibold text-[#526071] dark:text-[#AEB8C7]">{{ __('Kurator') }}</dt>
                            <dd class="text-[#374151] dark:text-[#D8DEE8]">{{ $place->author->username ?? __('Admin') }}</dd>
                        </div>
                    </dl>
                    <p class="mt-4 rounded-2xl border border-[#DDD6CC] bg-[#FAF9F6] p-4 text-sm leading-6 text-[#526071] dark:border-[#2A333D] dark:bg-[#1F2630] dark:text-[#D8DEE8]">
                        {{ __('Tidak ada transaksi jasa travel langsung di halaman ini.') }}
                    </p>
                </section>
            </aside>

            <div class="space-y-6 lg:col-span-8">
                <section class="rounded-[24px] border border-[#E7E3DC] bg-white p-6 shadow-sm dark:border-[#2A333D] dark:bg-[#161B22] sm:p-8">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#526071] dark:text-[#AEB8C7]">{{ __('Ulasan Pengunjung') }}</p>
                            <h2 class="mt-2 text-2xl font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Pengalaman dari pengunjung') }}</h2>
                        </div>
                        <span class="inline-flex w-fit rounded-full border border-[#DDD6CC] bg-[#FAF9F6] px-3 py-1.5 text-sm font-semibold text-[#526071] dark:border-[#2A333D] dark:bg-[#1F2630] dark:text-[#AEB8C7]">
                            {{ $reviewCount }} {{ __('ulasan') }}
                        </span>
                    </div>

                    <div class="mt-6 space-y-4">
                        @forelse($reviews as $review)
                            <article class="rounded-[20px] border border-[#E7E3DC] bg-[#FAF9F6] p-4 dark:border-[#2A333D] dark:bg-[#1F2630] sm:p-5">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#F1EEE8] text-sm font-semibold text-[#B33A3A] dark:bg-[#0E1116] dark:text-[#D96B6B]">
                                            {{ strtoupper(substr($review->user->username, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ $review->user->username }}</p>
                                            <p class="text-xs font-medium text-[#667085] dark:text-[#AEB8C7]">{{ $review->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                    <div class="shrink-0 text-sm tracking-wide text-[#8A6A2F] dark:text-[#D2B16F]">
                                        @for($i = 0; $i < 5; $i++)
                                            <span class="{{ $i < $review->rating ? '' : 'text-[#C7CED8] dark:text-[#46515D]' }}">★</span>
                                        @endfor
                                    </div>
                                </div>
                                <p class="mt-4 text-sm leading-6 text-[#374151] dark:text-[#D8DEE8]">{{ $review->comment }}</p>
                            </article>
                        @empty
                            <div class="rounded-[20px] border border-dashed border-[#DDD6CC] bg-[#FAF9F6] p-6 text-center text-sm leading-6 text-[#526071] dark:border-[#2A333D] dark:bg-[#1F2630] dark:text-[#D8DEE8]">
                                {{ __('Belum ada ulasan. Jadilah yang pertama!') }}
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-6">
                        {{ $reviews->links() }}
                    </div>
                </section>

                <section class="rounded-[24px] border border-[#E7E3DC] bg-white p-6 shadow-sm dark:border-[#2A333D] dark:bg-[#161B22] sm:p-8">
                    <h2 class="text-xl font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Tulis Ulasan') }}</h2>
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
                                <x-ui.textarea name="comment" rows="4" placeholder="{{ __('Ceritakan pengalamanmu di sini...') }}"></x-ui.textarea>
                            </div>
                            <x-ui.button type="submit" class="w-full sm:w-auto">{{ __('Kirim Ulasan') }}</x-ui.button>
                        </form>
                    @else
                        <div class="mt-5 rounded-2xl border border-[#D2B16F] bg-[#FFF8E6] p-4 text-sm leading-6 text-[#6D541F] dark:border-[#8A6A2F] dark:bg-[#241F14] dark:text-[#D2B16F]">
                            <a href="{{ route('login') }}" class="font-semibold underline">{{ __('Masuk') }}</a>
                            {{ __('untuk menulis ulasan.') }}
                        </div>
                    @endauth
                </section>
            </div>
        </div>
    </section>
@endsection
