@extends('layouts.shop')

@section('content')
    <section class="hero">
        <div class="wrap hero-grid">
            <div>
                <p class="eyebrow">An independent bookshop</p>
                <h1 style="margin-top:14px">Stories worth <em>keeping</em> on the shelf.</h1>
                <p class="lede">Every title here is chosen by hand, from new fiction to old favourites. We wrap it with care and bring it to your door.</p>
                <div class="hero-actions">
                    <a href="{{ route('books.index') }}" class="btn btn-accent">Browse the shelves</a>
                    @if ($featured->isNotEmpty())
                        <a href="#featured" class="btn btn-ghost">Staff picks</a>
                    @endif
                </div>
            </div>

            @php($stack = ($featured->count() >= 3 ? $featured : $newArrivals)->take(3))
            @if ($stack->count() === 3)
                <div class="hero-stack" aria-hidden="true">
                    @foreach ($stack as $book)
                        <x-book-cover :book="$book" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <div class="wrap">
        @if ($featured->isNotEmpty())
            <section class="section" id="featured">
                <div class="section-head">
                    <div>
                        <p class="eyebrow">Staff picks</p>
                        <h2 style="margin-top:6px">Featured this season</h2>
                    </div>
                    <a href="{{ route('books.index') }}">See all →</a>
                </div>
                <div class="book-grid">
                    @foreach ($featured as $book)
                        <x-book-card :book="$book" />
                    @endforeach
                </div>
            </section>
        @endif

        @if ($categories->isNotEmpty())
            <section class="section">
                <div class="section-head">
                    <div>
                        <p class="eyebrow">Wander the shop</p>
                        <h2 style="margin-top:6px">Browse by shelf</h2>
                    </div>
                </div>
                <div class="cat-grid">
                    @foreach ($categories as $category)
                        <a href="{{ route('books.index', ['category' => $category->slug]) }}" class="cat-tile">
                            <h3>{{ $category->name }}</h3>
                            <span>{{ $category->books_count }} {{ Str::plural('title', $category->books_count) }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="section">
            <div class="section-head">
                <div>
                    <p class="eyebrow">Just in</p>
                    <h2 style="margin-top:6px">New arrivals</h2>
                </div>
                <a href="{{ route('books.index', ['sort' => 'newest']) }}">See all →</a>
            </div>
            @if ($newArrivals->isEmpty())
                <div class="empty">
                    <h3>The shelves are being stocked</h3>
                    <p>New books will appear here soon.</p>
                </div>
            @else
                <div class="book-grid">
                    @foreach ($newArrivals as $book)
                        <x-book-card :book="$book" />
                    @endforeach
                </div>
            @endif
        </section>

        <section class="band">
            <div>
                <h2>Ordering from {{ config('shop.name') }}</h2>
                <p style="margin-top:12px">No account and no card needed. Add books to your basket, tell us where to send them, and pay when they arrive.</p>
            </div>
            <ul class="band-points">
                <li>Cash on delivery, nationwide</li>
                <li>Free shipping on orders over {{ money(config('shop.shipping.free_over')) }}</li>
                <li>Every book checked and wrapped by hand</li>
            </ul>
        </section>
    </div>
@endsection
