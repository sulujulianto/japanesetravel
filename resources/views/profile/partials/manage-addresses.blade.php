@php
    $addressFields = [
        'label',
        'recipient_name',
        'recipient_phone',
        'address_line_1',
        'address_line_2',
        'city',
        'province',
        'postal_code',
        'country_code',
        'is_default',
    ];
    $creatingAddress = old('address_id') === null && $errors->hasAny($addressFields);
@endphp

<div>
    <header class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#2F5D50] dark:text-[#8AB7A4]">{{ __('Alamat pengiriman') }}</p>
            <h2 class="mt-2 text-xl font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Alamat tersimpan') }}</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-[var(--public-muted)]">{{ __('Simpan alamat Indonesia yang dapat dipilih untuk pengiriman pesanan oleh-oleh.') }}</p>
        </div>

        @if (session('status') && str_starts_with((string) session('status'), 'address-'))
            <p class="rounded-full bg-emerald-50 px-3 py-1.5 text-sm font-semibold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                {{ __('Perubahan alamat berhasil disimpan.') }}
            </p>
        @endif
    </header>

    <details class="mt-6 rounded-2xl border border-[var(--public-border)] bg-[var(--public-surface-muted)] p-4" @if ($creatingAddress) open @endif>
        <summary class="cursor-pointer list-none text-sm font-semibold text-[var(--public-accent)] focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--public-accent)]">
            <span aria-hidden="true">+</span> {{ __('Tambah alamat baru') }}
        </summary>

        <form method="post" action="{{ route('profile.addresses.store') }}" class="mt-6 space-y-5 border-t border-[var(--public-border)] pt-6">
            @csrf

            @include('profile.partials.address-fields', ['address' => null])

            <label class="flex items-start gap-3 text-sm text-[var(--public-muted)]">
                <input name="is_default" type="checkbox" value="1" class="mt-1 rounded border-[var(--public-border)] text-[var(--public-accent)] focus:ring-[var(--public-accent)]" @checked(old('is_default'))>
                <span>
                    <strong class="block text-[var(--public-ink)]">{{ __('Jadikan alamat utama') }}</strong>
                    {{ __('Alamat pertama otomatis menjadi alamat utama.') }}
                </span>
            </label>

            <x-primary-button>{{ __('Simpan alamat') }}</x-primary-button>
        </form>
    </details>

    <div class="mt-6 space-y-4">
        @forelse ($addresses as $address)
            <article class="rounded-2xl border border-[var(--public-border)] bg-[var(--public-surface-muted)] p-4 sm:p-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="font-semibold text-[var(--public-ink)]">{{ $address->label }}</h3>
                            @if ($address->is_default)
                                <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-200">{{ __('Alamat utama') }}</span>
                            @endif
                        </div>
                        <p class="mt-3 font-semibold text-[var(--public-ink)]">{{ $address->recipient_name }}</p>
                        <p class="mt-1 text-sm text-[var(--public-muted)]">{{ $address->recipient_phone }}</p>
                        <address class="mt-3 text-sm not-italic leading-6 text-[var(--public-muted)]">
                            {{ $address->address_line_1 }}
                            @if ($address->address_line_2)
                                <br>{{ $address->address_line_2 }}
                            @endif
                            <br>{{ $address->city }}, {{ $address->province }} {{ $address->postal_code }}
                            <br>{{ __('Indonesia') }}
                        </address>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        @unless ($address->is_default)
                            <form method="post" action="{{ route('profile.addresses.default', $address) }}">
                                @csrf
                                @method('patch')
                                <x-secondary-button type="submit">{{ __('Atur sebagai utama') }}</x-secondary-button>
                            </form>
                        @endunless

                        <form method="post" action="{{ route('profile.addresses.destroy', $address) }}" onsubmit="return confirm(@js(__('Hapus alamat ini?')))">
                            @csrf
                            @method('delete')
                            <x-danger-button>{{ __('Hapus alamat') }}</x-danger-button>
                        </form>
                    </div>
                </div>

                <details class="mt-5 border-t border-[var(--public-border)] pt-4" @if ((string) old('address_id') === (string) $address->getKey() && $errors->hasAny($addressFields)) open @endif>
                    <summary class="cursor-pointer list-none text-sm font-semibold text-[var(--public-accent)] focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--public-accent)]">{{ __('Ubah alamat') }}</summary>

                    <form method="post" action="{{ route('profile.addresses.update', $address) }}" class="mt-5 space-y-5">
                        @csrf
                        @method('patch')

                        @include('profile.partials.address-fields', ['address' => $address])

                        <x-primary-button>{{ __('Simpan perubahan alamat') }}</x-primary-button>
                    </form>
                </details>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-[var(--public-border)] px-5 py-10 text-center">
                <p class="font-semibold text-[var(--public-ink)]">{{ __('Belum ada alamat tersimpan') }}</p>
                <p class="mt-2 text-sm text-[var(--public-muted)]">{{ __('Tambahkan alamat pertama untuk menyiapkan pengiriman pesanan.') }}</p>
            </div>
        @endforelse
    </div>
</div>
