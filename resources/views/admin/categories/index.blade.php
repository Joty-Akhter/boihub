@extends('layouts.admin')

@section('title', 'Categories')

@section('content')
    <div class="admin-head">
        <div>
            <h1>Categories</h1>
            <p>Shelves that group books in the shop. Hidden categories also hide their books.</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-accent">+ Add category</a>
    </div>

    <div class="card">
        @if ($categories->isEmpty())
            <div class="card-pad muted">No categories yet. <a href="{{ route('admin.categories.create') }}">Add the first one</a>.</div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th class="num">Books</th>
                        <th class="num">Order</th>
                        <th>Status</th>
                        <th class="actions"><span class="visually-hidden">Actions</span></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($categories as $category)
                        <tr>
                            <td>
                                <a class="row-link" href="{{ route('admin.categories.edit', $category) }}">{{ $category->name }}</a>
                                @if ($category->description)
                                    <div class="muted small">{{ Str::limit($category->description, 70) }}</div>
                                @endif
                            </td>
                            <td class="muted">{{ $category->slug }}</td>
                            <td class="num">
                                <a href="{{ route('admin.books.index', ['category' => $category->id]) }}" class="row-link">{{ $category->books_count }}</a>
                            </td>
                            <td class="num">{{ $category->sort_order }}</td>
                            <td>
                                <form method="post" action="{{ route('admin.categories.toggle', $category) }}">
                                    @csrf @method('patch')
                                    <button type="submit" class="badge {{ $category->is_active ? 'badge-ok' : 'badge-off' }}" style="border:0;cursor:pointer"
                                            title="Click to {{ $category->is_active ? 'hide' : 'show' }}">
                                        {{ $category->is_active ? 'Active' : 'Hidden' }}
                                    </button>
                                </form>
                            </td>
                            <td class="actions">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="icon-action" title="Edit" aria-label="Edit {{ $category->name }}">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16v4Z"/></svg>
                                </a>
                                <form method="post" action="{{ route('admin.categories.destroy', $category) }}"
                                      onsubmit="return confirm('Delete the “{{ addslashes($category->name) }}” category?')">
                                    @csrf @method('delete')
                                    <button type="submit" class="icon-action danger" title="{{ $category->books_count ? 'Has books — move them first' : 'Delete' }}" aria-label="Delete {{ $category->name }}">
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
@endsection
