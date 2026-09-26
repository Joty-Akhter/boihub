@props(['book'])

<article class="book-card">
    <a href="{{ route('books.show', $book) }}" class="cover-link" tabindex="-1" aria-hidden="true">
        <x-book-cover :book="$book" />
    </a>
    <div class="meta">
        <a href="{{ route('books.show', $book) }}" class="title">{{ $book->title }}</a>
        <span class="author">{{ $book->author }}</span>
    </div>
    <div class="buy">
        <span class="price">{{ money($book->price) }}</span>
        @if ($book->isInStock())
            <form method="post" action="{{ route('cart.store') }}">
                @csrf
                <input type="hidden" name="book_id" value="{{ $book->id }}">
                <button class="btn btn-ghost btn-sm" type="submit" aria-label="Add {{ $book->title }} to basket">Add</button>
            </form>
        @else
            <span class="oos">Out of stock</span>
        @endif
    </div>
</article>
