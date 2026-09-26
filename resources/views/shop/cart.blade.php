@extends('layouts.shop')

@section('title', 'Your basket')

@section('content')
    <div class="wrap">
        <header class="page-head">
            <h1>Your basket</h1>
        </header>

        @if ($lines->isEmpty())
            <div class="empty">
                <h3>Your basket is empty</h3>
                <p>Plenty of good books are waiting on the shelves.</p>
                <a href="{{ route('books.index') }}" class="btn btn-accent">Start browsing</a>
            </div>
        @else
            <div class="cart-layout">
                <ul class="cart-lines">
                    @foreach ($lines as $line)
                        @php($max = min($line->book->stock, config('shop.max_quantity_per_item')))
                        <li class="cart-line">
                            <a href="{{ route('books.show', $line->book) }}" class="cover-link" tabindex="-1" aria-hidden="true">
                                <x-book-cover :book="$line->book" />
                            </a>
                            <div class="info">
                                <a href="{{ route('books.show', $line->book) }}" class="title">{{ $line->book->title }}</a>
                                <span class="muted small">{{ $line->book->author }} · {{ money($line->book->price) }} each</span>
                                <div class="controls">
                                    <form method="post" action="{{ route('cart.update', $line->book) }}"
                                          x-data="{ qty: {{ $line->quantity }}, max: {{ $max }} }">
                                        @csrf
                                        @method('patch')
                                        <div class="stepper sm">
                                            <button type="button" :disabled="qty <= 1" @click="qty--; $nextTick(() => $root.submit())" aria-label="Fewer">−</button>
                                            <input type="number" name="quantity" x-model.number="qty" min="1" max="{{ $max }}"
                                                   @change="$root.submit()" aria-label="Quantity of {{ $line->book->title }}">
                                            <button type="button" :disabled="qty >= max" @click="qty++; $nextTick(() => $root.submit())" aria-label="More">+</button>
                                        </div>
                                        <noscript><button type="submit" class="link-btn">Update</button></noscript>
                                    </form>
                                    <form method="post" action="{{ route('cart.destroy', $line->book) }}">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="link-btn">Remove</button>
                                    </form>
                                    <span class="line-total">{{ money($line->line_total) }}</span>
                                </div>
                                @if ($line->book->stock <= config('shop.max_quantity_per_item') && $line->quantity >= $line->book->stock)
                                    <span class="small" style="color:var(--amber)">Only {{ $line->book->stock }} in stock</span>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>

                <aside class="summary">
                    <h2>Summary</h2>
                    @include('shop.partials.totals')
                    <a href="{{ route('checkout.create') }}" class="btn btn-accent btn-block" style="margin-top:16px">Checkout</a>
                    <a href="{{ route('books.index') }}" class="btn btn-ghost btn-block" style="margin-top:8px">Keep browsing</a>
                </aside>
            </div>
        @endif
    </div>
@endsection
