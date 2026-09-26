@extends('layouts.admin')

@section('title', 'Books')

@section('content')
    <div class="admin-head">
        <div>
            <h1>Books</h1>
            <p>{{ $books->total() }} {{ Str::plural('book', $books->total()) }}</p>
        </div>
        <a href="{{ route('admin.books.create') }}" class="btn btn-accent">+ Add book</a>
    </div>

    <div class="card">
        <form method="get" class="filters-bar" x-data>
            <input type="search" name="q" value="{{ request('q') }}" class="input" placeholder="Search title, author, ISBN" aria-label="Search">
            <select name="category" class="select" @change="$root.submit()" aria-label="Category">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <select name="status" class="select" @change="$root.submit()" aria-label="Status">
                @foreach (['' => 'Any status', 'active' => 'Active', 'inactive' => 'Inactive', 'featured' => 'Featured', 'low' => 'Low stock'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('status', '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-ghost btn-sm">Filter</button>
            @if (request()->hasAny(['q', 'category', 'status']))
                <a href="{{ route('admin.books.index') }}" class="link-btn">Clear</a>
            @endif
        </form>

        @if ($books->isEmpty())
            <div class="card-pad muted">No books match. <a href="{{ route('admin.books.create') }}">Add one</a>.</div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Book</th>
                        <th>Category</th>
                        <th class="num">Price</th>
                        <th class="num">Stock</th>
                        <th>Featured</th>
                        <th>Status</th>
                        <th class="actions"><span class="visually-hidden">Actions</span></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($books as $book)
                        <tr>
                            <td>
                                <div class="book-cell">
                                    <x-book-cover :book="$book" size="xs" />
                                    <div>
                                        <a class="row-link" href="{{ route('admin.books.edit', $book) }}">{{ $book->title }}</a>
                                        <div class="muted">{{ $book->author }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $book->category->name }}</td>
                            <td class="num">{{ money($book->price) }}</td>
                            <td class="num">
                                <span @class(['stock-out' => $book->stock === 0, 'stock-low' => $book->isLowStock()])>{{ $book->stock }}</span>
                            </td>
                            <td>
                                <form method="post" action="{{ route('admin.books.toggle', $book) }}">
                                    @csrf @method('patch')
                                    <input type="hidden" name="field" value="is_featured">
                                    <button type="submit" @class(['toggle', 'on' => $book->is_featured])
                                            title="{{ $book->is_featured ? 'Remove from featured' : 'Mark as featured' }}"
                                            aria-label="{{ $book->is_featured ? 'Remove from featured' : 'Mark as featured' }}">★</button>
                                </form>
                            </td>
                            <td>
                                <form method="post" action="{{ route('admin.books.toggle', $book) }}">
                                    @csrf @method('patch')
                                    <input type="hidden" name="field" value="is_active">
                                    <button type="submit" class="badge {{ $book->is_active ? 'badge-ok' : 'badge-off' }}" style="border:0;cursor:pointer"
                                            title="Click to {{ $book->is_active ? 'deactivate' : 'activate' }}">
                                        {{ $book->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td class="actions">
                                @if ($book->is_active && $book->category->is_active)
                                    <a href="{{ route('books.show', $book) }}" target="_blank" rel="noopener" class="icon-action" title="View in shop" aria-label="View in shop">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/></svg>
                                    </a>
                                @endif
                                <a href="{{ route('admin.books.edit', $book) }}" class="icon-action" title="Edit" aria-label="Edit {{ $book->title }}">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16v4Z"/></svg>
                                </a>
                                <form method="post" action="{{ route('admin.books.destroy', $book) }}"
                                      onsubmit="return confirm('Delete “{{ addslashes($book->title) }}” permanently? Past orders keep their record. To just hide it, deactivate instead.')">
                                    @csrf @method('delete')
                                    <button type="submit" class="icon-action danger" title="Delete" aria-label="Delete {{ $book->title }}">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div style="padding-bottom:8px">{{ $books->links() }}</div>
@endsection
