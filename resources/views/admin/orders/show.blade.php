<x-admin-layout>
    <x-slot name="header">
        <div class="rounded-2xl border border-[#E7E3DC] bg-white p-5 dark:border-[#2A333D] dark:bg-[#161B22] sm:p-6">
            <a href="{{ route('admin.orders.index') }}" class="text-sm font-semibold text-[#526071] transition hover:text-[#8F2E2E] dark:text-[#AEB8C7] dark:hover:text-[#D96B6B]">
                &larr; {{ __('Kembali ke daftar pesanan') }}
            </a>
            <p class="mt-5 text-[11px] font-semibold uppercase tracking-[0.18em] text-[#667085] dark:text-[#AEB8C7]">{{ __('Detail Pesanan') }}</p>
            <div class="mt-2 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-[#1F2937] dark:text-[#F4F1ED] sm:text-3xl">#ORDER-{{ $order->id }}</h1>
                    <p class="mt-2 text-sm text-[#526071] dark:text-[#AEB8C7]">{{ __('Dibuat pada') }} {{ $order->created_at->format('d M Y H:i') }}</p>
                </div>
                <p class="text-2xl font-semibold tracking-tight text-[#1F2937] dark:text-[#F4F1ED]">Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('success') }}
        </x-ui.alert>
    @endif

    @if(session('error'))
        <x-ui.alert variant="danger" class="mb-6">
            {{ session('error') }}
        </x-ui.alert>
    @endif

    @if ($errors->any())
        <x-ui.alert variant="danger" class="mb-6">
            <p class="font-semibold">{{ __('Periksa kembali perubahan status pesanan.') }}</p>
            <ul class="mt-2 list-disc space-y-1 pl-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-ui.alert>
    @endif

    @php
        $sectionClass = 'rounded-2xl border border-[#E7E3DC] bg-white p-5 dark:border-[#2A333D] dark:bg-[#161B22] sm:p-6';
        $statusVariants = [
            'pending' => 'warning',
            'processing' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger',
        ];
        $paymentVariants = [
            'pending' => 'warning',
            'paid' => 'success',
            'failed' => 'danger',
            'expired' => 'danger',
            'refunded' => 'info',
        ];
        $selectedStatus = old('status', $order->status);
    @endphp

    <div class="grid items-start gap-6 lg:grid-cols-12">
        <div class="space-y-6 lg:col-span-8">
            <section class="{{ $sectionClass }}" aria-labelledby="order-summary-heading">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 id="order-summary-heading" class="text-lg font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Ringkasan Pesanan') }}</h2>
                        <p class="mt-1 text-sm text-[#526071] dark:text-[#AEB8C7]">{{ __('Status operasional dan pembayaran terkini untuk pesanan ini.') }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <x-ui.badge variant="{{ $statusVariants[$order->status] ?? 'default' }}">
                            {{ __(strtoupper($order->status)) }}
                        </x-ui.badge>
                        @if($order->payment)
                            <x-ui.badge variant="{{ $paymentVariants[$order->payment->status] ?? 'default' }}">
                                {{ strtoupper($order->payment->provider) }} · {{ __(strtoupper($order->payment->status)) }}
                            </x-ui.badge>
                        @else
                            <x-ui.badge variant="default">{{ __('Belum ada pembayaran') }}</x-ui.badge>
                        @endif
                    </div>
                </div>

                <dl class="mt-5 grid gap-4 border-t border-[#E7E3DC] pt-5 sm:grid-cols-2 dark:border-[#2A333D]">
                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-[0.12em] text-[#667085] dark:text-[#AEB8C7]">{{ __('Pelanggan') }}</dt>
                        <dd class="mt-2 font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ $order->user?->username ?: __('Pengguna tidak tersedia') }}</dd>
                        <dd class="mt-1 break-all text-sm text-[#526071] dark:text-[#AEB8C7]">{{ $order->user?->email ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-[0.12em] text-[#667085] dark:text-[#AEB8C7]">{{ __('Waktu Pesanan') }}</dt>
                        <dd class="mt-2 font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ $order->created_at->format('d M Y H:i') }}</dd>
                        <dd class="mt-1 text-sm text-[#526071] dark:text-[#AEB8C7]">{{ __('Total') }}: Rp {{ number_format($order->total_price, 0, ',', '.') }}</dd>
                    </div>
                </dl>

                @if($order->note || $order->admin_note)
                    <div class="mt-5 grid gap-4 border-t border-[#E7E3DC] pt-5 sm:grid-cols-2 dark:border-[#2A333D]">
                        @if($order->note)
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-[#667085] dark:text-[#AEB8C7]">{{ __('Catatan Pesanan') }}</p>
                                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-[#374151] dark:text-[#D8DEE8]">{{ $order->note }}</p>
                            </div>
                        @endif
                        @if($order->admin_note)
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-[#667085] dark:text-[#AEB8C7]">{{ __('Catatan Admin') }}</p>
                                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-[#374151] dark:text-[#D8DEE8]">{{ $order->admin_note }}</p>
                            </div>
                        @endif
                    </div>
                @endif
            </section>

            <section class="{{ $sectionClass }}" aria-labelledby="order-items-heading">
                <div>
                    <h2 id="order-items-heading" class="text-lg font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Item Pesanan') }}</h2>
                    <p class="mt-1 text-sm text-[#526071] dark:text-[#AEB8C7]">{{ __('Produk, jumlah, harga satuan, dan subtotal yang tercatat pada pesanan.') }}</p>
                </div>

                <div class="mt-5 space-y-3">
                    @foreach($order->items as $item)
                        @php
                            $product = $item->product;
                            $productName = $product?->name ?? $item->product_name ?? __('Produk tidak tersedia');
                        @endphp
                        <article class="rounded-xl border border-[#E7E3DC] bg-[#FAF8F3] p-4 dark:border-[#2A333D] dark:bg-[#0E1116]">
                            <div class="flex items-start gap-3 sm:items-center sm:gap-4">
                                <div class="h-16 w-16 shrink-0 overflow-hidden rounded-xl border border-[#E7E3DC] bg-white dark:border-[#2A333D] dark:bg-[#161B22]">
                                    @if($item->resolved_image_url)
                                        <img src="{{ $item->resolved_image_url }}" alt="{{ $productName }}" class="h-full w-full object-cover">
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ $productName }}</p>
                                    <p class="mt-1 text-sm text-[#526071] dark:text-[#AEB8C7]">{{ $item->quantity }} &times; Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                                </div>
                                <p class="hidden shrink-0 text-sm font-semibold text-[#1F2937] dark:text-[#F4F1ED] sm:block">
                                    Rp {{ number_format($item->quantity * $item->price, 0, ',', '.') }}
                                </p>
                            </div>
                            <div class="mt-3 flex items-center justify-between border-t border-[#E7E3DC] pt-3 text-sm sm:hidden dark:border-[#2A333D]">
                                <span class="text-[#526071] dark:text-[#AEB8C7]">{{ __('Subtotal') }}</span>
                                <span class="font-semibold text-[#1F2937] dark:text-[#F4F1ED]">Rp {{ number_format($item->quantity * $item->price, 0, ',', '.') }}</span>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="{{ $sectionClass }}" aria-labelledby="payment-history-heading">
                <div>
                    <h2 id="payment-history-heading" class="text-lg font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Riwayat Pembayaran') }}</h2>
                    <p class="mt-1 text-sm text-[#526071] dark:text-[#AEB8C7]">{{ __('Catatan transaksi dari provider pembayaran yang terhubung dengan pesanan.') }}</p>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse($order->payments as $payment)
                        <article class="rounded-xl border border-[#E7E3DC] bg-[#FAF8F3] p-4 dark:border-[#2A333D] dark:bg-[#0E1116]">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ strtoupper($payment->provider) }}</p>
                                        <x-ui.badge variant="{{ $paymentVariants[$payment->status] ?? 'default' }}">{{ __(strtoupper($payment->status)) }}</x-ui.badge>
                                    </div>
                                    <p class="mt-2 break-all text-xs text-[#526071] dark:text-[#AEB8C7]">{{ $payment->provider_ref ?: __('Referensi belum tersedia') }}</p>
                                </div>
                                <div class="sm:text-right">
                                    <p class="font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ $payment->currency }} {{ number_format($payment->amount, 2, '.', ',') }}</p>
                                    <p class="mt-1 text-xs text-[#526071] dark:text-[#AEB8C7]">{{ $payment->paid_at?->format('d M Y H:i') ?? __('Belum dibayar') }}</p>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-xl border border-dashed border-[#E7E3DC] p-6 text-center dark:border-[#2A333D]">
                            <p class="text-sm font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Belum ada pembayaran untuk pesanan ini.') }}</p>
                            <p class="mt-1 text-sm text-[#526071] dark:text-[#AEB8C7]">{{ __('Riwayat akan tampil setelah proses pembayaran dibuat oleh sistem.') }}</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        <aside class="lg:col-span-4">
            <section class="{{ $sectionClass }} lg:sticky lg:top-24" aria-labelledby="update-status-heading">
                <h2 id="update-status-heading" class="text-lg font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Update Status') }}</h2>
                <p class="mt-1 text-sm leading-6 text-[#526071] dark:text-[#AEB8C7]">{{ __('Pilih status berikutnya sesuai progres operasional pesanan.') }}</p>

                <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="mt-5 space-y-5">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="status" class="block text-[11px] font-semibold uppercase tracking-[0.12em] text-[#667085] dark:text-[#AEB8C7]">{{ __('Status Pesanan') }}</label>
                        <select id="status" name="status" class="mt-2 w-full rounded-xl border border-[#DDD6CC] bg-white px-3.5 py-2.5 text-sm text-[#1F2937] outline-none transition focus:border-[#B33A3A] focus:ring-2 focus:ring-[#B33A3A]/15 dark:border-[#2A333D] dark:bg-[#0E1116] dark:text-[#F4F1ED] dark:focus:border-[#D96B6B] dark:focus:ring-[#D96B6B]/20">
                            <option value="pending" @selected($selectedStatus === 'pending')>{{ __('Menunggu') }}</option>
                            <option value="processing" @selected($selectedStatus === 'processing')>{{ __('Diproses') }}</option>
                            <option value="completed" @selected($selectedStatus === 'completed')>{{ __('Selesai') }}</option>
                            <option value="cancelled" @selected($selectedStatus === 'cancelled')>{{ __('Dibatalkan') }}</option>
                        </select>
                        @error('status')
                            <p class="mt-2 text-sm font-medium text-[#9F2A2A] dark:text-[#F0A0A0]">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="admin_note" class="block text-[11px] font-semibold uppercase tracking-[0.12em] text-[#667085] dark:text-[#AEB8C7]">{{ __('Catatan Admin') }}</label>
                        <textarea id="admin_note" name="admin_note" rows="5" class="mt-2 w-full rounded-xl border border-[#DDD6CC] bg-white px-3.5 py-2.5 text-sm leading-6 text-[#1F2937] outline-none transition placeholder:text-[#667085] focus:border-[#B33A3A] focus:ring-2 focus:ring-[#B33A3A]/15 dark:border-[#2A333D] dark:bg-[#0E1116] dark:text-[#F4F1ED] dark:placeholder:text-[#AEB8C7] dark:focus:border-[#D96B6B] dark:focus:ring-[#D96B6B]/20">{{ old('admin_note', $order->admin_note) }}</textarea>
                        <p class="mt-2 text-xs leading-5 text-[#526071] dark:text-[#AEB8C7]">{{ __('Catatan bersifat internal dan membantu pelacakan proses pesanan.') }}</p>
                        @error('admin_note')
                            <p class="mt-2 text-sm font-medium text-[#9F2A2A] dark:text-[#F0A0A0]">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-[#B33A3A] px-4 text-sm font-semibold text-white transition hover:bg-[#8F2E2E] dark:bg-[#D96B6B] dark:text-[#0E1116] dark:hover:bg-[#E18484]">
                        {{ __('Simpan Perubahan') }}
                    </button>
                </form>
            </section>
        </aside>
    </div>
</x-admin-layout>
