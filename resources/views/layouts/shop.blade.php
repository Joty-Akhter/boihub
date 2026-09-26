<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Inline @section values are already escaped by Blade. --}}
    <title>{!! $__env->hasSection('title') ? $__env->yieldContent('title') . ' — ' : '' !!}{{ config('shop.name') }}</title>
    <meta name="description" content="@yield('description', config('shop.tagline'))">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300..700;1,9..144,300..700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
</head>
<body>
<header class="site-header" x-data="{ open: false }">
    <div class="wrap bar">
        <a href="{{ route('home') }}" class="logo">{{ config('shop.name') }}<span class="dot">.</span></a>

        <nav class="main-nav" aria-label="Main">
            <a href="{{ route('books.index') }}" @class(['active' => request()->routeIs('books.index') && ! request('category')])>All books</a>
            @foreach ($navCategories->take(4) as $navCategory)
                <a href="{{ route('books.index', ['category' => $navCategory->slug]) }}"
                   @class(['active' => request('category') === $navCategory->slug])>{{ $navCategory->name }}</a>
            @endforeach
        </nav>

        <form action="{{ route('books.index') }}" method="get" class="header-search" role="search">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
            <label for="site-search" class="visually-hidden">Search books</label>
            <input id="site-search" class="input" type="search" name="q" value="{{ request('q') }}" placeholder="Search title, author, ISBN">
        </form>

        <div class="header-actions">
            <a href="{{ route('cart.index') }}" class="icon-btn" aria-label="Basket, {{ $cartCount }} {{ Str::plural('item', $cartCount) }}">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M5 8h14l-1.2 11.1a2 2 0 0 1-2 1.9H8.2a2 2 0 0 1-2-1.9L5 8Z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg>
                @if ($cartCount > 0)
                    <span class="basket-count">{{ $cartCount }}</span>
                @endif
            </a>
            <button type="button" class="icon-btn menu-toggle" @click="open = !open" :aria-expanded="open" aria-label="Menu">
                <svg x-show="!open" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                <svg x-show="open" x-cloak width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
            </button>
        </div>
    </div>

    <div class="wrap mobile-menu" x-show="open" x-cloak x-transition.opacity @click.outside="open = false">
        <form action="{{ route('books.index') }}" method="get" role="search">
            <input class="input" type="search" name="q" value="{{ request('q') }}" placeholder="Search title, author, ISBN" aria-label="Search books">
        </form>
        <a href="{{ route('books.index') }}">All books</a>
        @foreach ($navCategories as $navCategory)
            <a href="{{ route('books.index', ['category' => $navCategory->slug]) }}">{{ $navCategory->name }}</a>
        @endforeach
        <a href="{{ route('cart.index') }}">Basket ({{ $cartCount }})</a>
    </div>
</header>

@include('partials.flash')

<main>
    @yield('content')
</main>

<footer class="site-footer">
    <div class="wrap footer-grid">
        <div>
            <a href="{{ route('home') }}" class="logo">{{ config('shop.name') }}<span class="dot">.</span></a>
            <p style="margin-top:12px;max-width:340px">{{ config('shop.tagline') }} An independent bookshop. We pick every title by hand and ship it to your door.</p>
        </div>
        <div>
            <h4>Browse</h4>
            <ul>
                <li><a href="{{ route('books.index') }}">All books</a></li>
                @foreach ($navCategories->take(4) as $navCategory)
                    <li><a href="{{ route('books.index', ['category' => $navCategory->slug]) }}">{{ $navCategory->name }}</a></li>
                @endforeach
            </ul>
        </div>
        <div>
            <h4>Shop</h4>
            <ul>
                <li><a href="{{ route('cart.index') }}">Your basket</a></li>
                <li>Cash on delivery</li>
                <li>Free shipping over {{ money(config('shop.shipping.free_over')) }}</li>
            </ul>
        </div>
    </div>
    <div class="wrap small" style="margin-top:40px;color:var(--ink-3)">© {{ date('Y') }} {{ config('shop.name') }} Books.</div>
</footer>
</body>
</html>
