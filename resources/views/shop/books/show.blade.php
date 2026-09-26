@extends('layouts.shop')

@section('title', $book->title)
@section('description', Str::limit(strip_tags($book->description ?? ''), 155))

@section('content')
    <div class="wrap">
        <nav class="crumbs" aria-label="Breadcrumb">
            <a href="{{ route('books.index') }}">Books</a> <span>/</span>
            <a href="{{ route('books.index', ['category' => $book->category->slug]) }}">{{ $book->category->name }}</a> <span>/</span>
            <span>{{ $book->title }}</span>
        </nav>

        <div class="book-detail">
            <div class="cover-col">
                <x-book-cover :book="$book" size="lg" />
            </div>

            <div>
                <p class="eyebrow">{{ $book->category->name }}</p>
                <h1 style="margin-top:10px">{{ $book->title }}</h1>
                <p class="by">by <strong>{{ $book->author }}</strong></p>

                <div class="price-row">
                    <span class="price">{{ money($book->price) }}</span>
                    @if (! $book->isInStock())
                        <span class="badge badge-off">Out of stock</span>
                    @elseif ($book->isLowStock())
                        <span class="badge badge-warn">Only {{ $book->stock }} left</span>
                    @else
                        <span class="badge badge-ok">In stock</span>
                    @endif
                </div>

                @if ($book->isInStock())
                    @php($max = min($book->stock, config('shop.max_quantity_per_item')))
                    <form method="post" action="{{ route('cart.store') }}" class="buy-box" x-data="{ qty: 1, max: {{ $max }} }">
                        @csrf
                        <input type="hidden" name="book_id" value="{{ $book->id }}">
                        <div class="stepper">
                            <button type="button" @click="qty = Math.max(1, qty - 1)" :disabled="qty <= 1" aria-label="Fewer">−</button>
                            <input type="number" name="quantity" x-model.number="qty" min="1" max="{{ $max }}" aria-label="Quantity">
                            <button type="button" @click="qty = Math.min(max, qty + 1)" :disabled="qty >= max" aria-label="More">+</button>
                        </div>
                        <button type="submit" class="btn btn-accent">Add to basket</button>
                    </form>
                @endif

                @if ($book->description)
                    <div class="prose">
                        @foreach (preg_split('/\R{2,}/', trim($book->description)) as $paragraph)
                            <p>{!! nl2br(e($paragraph)) !!}</p>
                        @endforeach
                    </div>
                @endif

                <dl class="specs">
                    @foreach ([
                        'Publisher' => $book->publisher,
                        'Published' => $book->published_year,
                        'Pages' => $book->pages,
                        'Language' => $book->language,
                        'ISBN' => $book->isbn,
                    ] as $label => $value)
                        @if ($value)
                            <div><dt>{{ $label }}</dt><dd>{{ $value }}</dd></div>
                        @endif
                    @endforeach
                </dl>
            </div>
        </div>

        @if ($related->isNotEmpty())
            <section class="section">
                <div class="section-head">
                    <h2>More from {{ $book->category->name }}</h2>
                    <a href="{{ route('books.index', ['category' => $book->category->slug]) }}">View shelf →</a>
                </div>
                <div class="book-grid">
                    @foreach ($related as $item)
                        <x-book-card :book="$item" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
