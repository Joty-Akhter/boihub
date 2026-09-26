@extends('layouts.shop')

@section('title', $category?->name ?? (request('q') ? 'Search' : 'All books'))

@section('content')
    <div class="wrap">
        <header class="page-head">
            <p class="eyebrow">{{ $category ? 'Shelf' : 'Catalogue' }}</p>
            <h1 style="margin-top:8px">
                @if (request('q'))
                    Results for “{{ request('q') }}”
                @else
                    {{ $category?->name ?? 'All books' }}
                @endif
            </h1>
            @if ($category?->description)
                <p>{{ $category->description }}</p>
            @endif
        </header>

        <div class="catalog">
            <aside class="filters">
                <div>
                    <h3>Shelves</h3>
                    <ul class="cat-list">
                        <li><a href="{{ route('books.index', request()->only('q', 'sort')) }}" @class(['active' => ! $category])>All books</a></li>
                        @foreach ($categories as $cat)
                            <li>
                                <a href="{{ route('books.index', ['category' => $cat->slug] + request()->only('q', 'sort')) }}"
                                   @class(['active' => $category?->is($cat)])>{{ $cat->name }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </aside>

            <section>
                <form method="get" action="{{ route('books.index') }}" class="toolbar" x-data>
                    @if ($category)
                        <input type="hidden" name="category" value="{{ $category->slug }}">
                    @endif
                    @if (request('q'))
                        <input type="hidden" name="q" value="{{ request('q') }}">
                    @endif
                    <span class="muted">{{ $books->total() }} {{ Str::plural('book', $books->total()) }}</span>
                    <div style="display:flex;gap:14px;align-items:center;flex-wrap:wrap">
                        <label class="check">
                            <input type="checkbox" name="in_stock" value="1" @checked(request()->boolean('in_stock')) @change="$root.submit()">
                            In stock only
                        </label>
                        <label for="sort" class="visually-hidden">Sort by</label>
                        <select id="sort" name="sort" class="select" @change="$root.submit()">
                            @foreach (['newest' => 'Newest first', 'price_asc' => 'Price: low to high', 'price_desc' => 'Price: high to low', 'title' => 'Title A–Z'] as $value => $label)
                                <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <noscript><button class="btn btn-sm" type="submit">Apply</button></noscript>
                    </div>
                </form>

                @if ($books->isEmpty())
                    <div class="empty">
                        <h3>Nothing on this shelf yet</h3>
                        <p>Try a different search, or browse everything we have.</p>
                        <a href="{{ route('books.index') }}" class="btn btn-ghost">View all books</a>
                    </div>
                @else
                    <div class="book-grid">
                        @foreach ($books as $book)
                            <x-book-card :book="$book" />
                        @endforeach
                    </div>
                    {{ $books->links() }}
                @endif
            </section>
        </div>
    </div>
@endsection
