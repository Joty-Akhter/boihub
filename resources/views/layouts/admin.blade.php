<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{!! $__env->hasSection('title') ? $__env->yieldContent('title') . ' — ' : '' !!}{{ config('shop.name') }} Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400..700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
</head>
<body class="admin-body">
@auth
    @php($pendingCount = \App\Models\Order::where('status', 'pending')->count())
    <div class="admin-shell" x-data="{ nav: false }" @keydown.escape.window="nav = false">
        <aside class="sidebar" :class="{ open: nav }">
            <a href="{{ route('admin.dashboard') }}" class="logo">{{ config('shop.name') }}<span class="dot" style="color:var(--accent)">.</span><small>Admin</small></a>
            <nav class="side-nav" aria-label="Admin">
                <a href="{{ route('admin.dashboard') }}" @class(['active' => request()->routeIs('admin.dashboard')])>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('admin.orders.index') }}" @class(['active' => request()->routeIs('admin.orders.*')])>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 8h14l-1.2 11.1a2 2 0 0 1-2 1.9H8.2a2 2 0 0 1-2-1.9L5 8Z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg>
                    Orders
                    @if ($pendingCount)<span class="count">{{ $pendingCount }}</span>@endif
                </a>
                <a href="{{ route('admin.books.index') }}" @class(['active' => request()->routeIs('admin.books.*')])>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 19.5V5a2 2 0 0 1 2-2h13v16H6a2 2 0 0 0-2 2Zm0 0A2 2 0 0 0 6 21.5h13"/></svg>
                    Books
                </a>
                <a href="{{ route('admin.categories.index') }}" @class(['active' => request()->routeIs('admin.categories.*')])>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/></svg>
                    Categories
                </a>
            </nav>
            <div class="side-foot">
                <a href="{{ route('home') }}" target="_blank" rel="noopener">View shop ↗</a>
                <form method="post" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit">Sign out</button>
                </form>
            </div>
        </aside>
        <div class="scrim" x-show="nav" x-cloak x-transition.opacity @click="nav = false"></div>

        <div class="admin-main">
            <div class="admin-top">
                <button type="button" class="icon-btn menu-toggle" @click="nav = true" aria-label="Open menu">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                </button>
                <span class="user">Signed in as <strong>{{ auth()->user()->name }}</strong></span>
            </div>
            @include('partials.flash')
            <main class="admin-content">
                @yield('content')
            </main>
        </div>
    </div>
@else
    @yield('content')
@endauth
</body>
</html>
