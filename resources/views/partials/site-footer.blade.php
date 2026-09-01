@php($copy = \App\Support\PublicShell::copy())

<footer data-public-footer class="public-footer relative mt-16 shrink-0 border-t">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-10 sm:px-6 lg:grid-cols-4 lg:px-8">
        <div class="lg:col-span-2">
            <a href="{{ route('home') }}" class="flex w-fit items-center gap-2 text-lg font-semibold text-[var(--public-ink)]">
                <span class="brand-mark" aria-hidden="true">{{ \App\Support\Brand::mark() }}</span>
                <span>{{ \App\Support\Brand::name() }}</span>
            </a>
            <p class="mt-4 max-w-md text-sm leading-6 text-[var(--public-muted)]">
                {{ $copy['footerDescription'] }}
            </p>
        </div>
        <div>
            <h4 class="text-sm font-semibold uppercase tracking-wider text-[var(--public-muted)]">{{ $copy['navigation'] }}</h4>
            <ul class="mt-4 space-y-2 text-sm text-[var(--public-muted)]">
                <li><a href="{{ route('places.index') }}" class="hover:text-[var(--public-accent)]">{{ $copy['destinations'] }}</a></li>
                <li><a href="{{ route('shop.index') }}" class="hover:text-[var(--public-accent)]">{{ $copy['souvenirs'] }}</a></li>
                @auth
                    <li><a href="{{ route('dashboard') }}" class="hover:text-[var(--public-accent)]">{{ $copy['dashboard'] }}</a></li>
                @else
                    <li><a href="{{ route('login') }}" class="hover:text-[var(--public-accent)]">{{ $copy['login'] }}</a></li>
                @endauth
            </ul>
        </div>
        <div>
            <h4 class="text-sm font-semibold uppercase tracking-wider text-[var(--public-muted)]">{{ $copy['contact'] }}</h4>
            <p class="mt-4 text-sm leading-6 text-[var(--public-muted)]">
                {{ $copy['contactDescription'] }}
            </p>
        </div>
    </div>
    <div class="mx-auto flex max-w-7xl flex-col items-center gap-2 border-t border-[var(--public-border)] px-4 py-5 text-xs text-[var(--public-muted)] sm:px-6 lg:px-8">
        <span>© {{ date('Y') }} {{ $copy['footerProject'] }}</span>
        <span>{{ $copy['footerTechnology'] }}</span>
    </div>
</footer>
