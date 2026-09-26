@extends('layouts.admin')

@php($editing = $book->exists)

@section('title', $editing ? 'Edit ' . $book->title : 'Add book')

@section('content')
    <a href="{{ route('admin.books.index') }}" class="back-link">← All books</a>
    <div class="admin-head">
        <h1>{{ $editing ? 'Edit book' : 'Add a book' }}</h1>
    </div>

    @if ($categories->isEmpty())
        <div class="alert">You need a category before adding books. <a href="{{ route('admin.categories.create') }}">Create one</a>.</div>
    @endif

    <form method="post" enctype="multipart/form-data"
          action="{{ $editing ? route('admin.books.update', $book) : route('admin.books.store') }}"
          x-data="{
              preview: null,
              removeCover: false,
              pick(e) {
                  const file = e.target.files[0];
                  this.preview = file ? URL.createObjectURL(file) : null;
                  if (file) this.removeCover = false;
              }
          }">
        @csrf
        @if ($editing) @method('put') @endif

        <div class="form-layout">
            <div class="card card-pad">
                <h2 class="section-title">Details</h2>
                <div class="form-grid cols-2">
                    <div class="field span-all">
                        <label for="title">Title</label>
                        <input id="title" name="title" class="input @error('title') is-invalid @enderror" value="{{ old('title', $book->title) }}" required>
                        @error('title')<span class="error">{{ $message }}</span>@enderror
                    </div>
                    <div class="field">
                        <label for="author">Author</label>
                        <input id="author" name="author" class="input @error('author') is-invalid @enderror" value="{{ old('author', $book->author) }}" required>
                        @error('author')<span class="error">{{ $message }}</span>@enderror
                    </div>
                    <div class="field">
                        <label for="category_id">Category</label>
                        <select id="category_id" name="category_id" class="select @error('category_id') is-invalid @enderror" required>
                            <option value="">Choose…</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) old('category_id', $book->category_id) === (string) $category->id)>
                                    {{ $category->name }}{{ $category->is_active ? '' : ' (hidden)' }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')<span class="error">{{ $message }}</span>@enderror
                    </div>
                    <div class="field span-all">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="textarea @error('description') is-invalid @enderror" rows="7">{{ old('description', $book->description) }}</textarea>
                        <span class="hint">Leave a blank line between paragraphs.</span>
                        @error('description')<span class="error">{{ $message }}</span>@enderror
                    </div>
                    <div class="field">
                        <label for="publisher">Publisher</label>
                        <input id="publisher" name="publisher" class="input" value="{{ old('publisher', $book->publisher) }}">
                        @error('publisher')<span class="error">{{ $message }}</span>@enderror
                    </div>
                    <div class="field">
                        <label for="isbn">ISBN</label>
                        <input id="isbn" name="isbn" class="input" value="{{ old('isbn', $book->isbn) }}">
                        @error('isbn')<span class="error">{{ $message }}</span>@enderror
                    </div>
                    <div class="field">
                        <label for="published_year">Year published</label>
                        <input id="published_year" type="number" name="published_year" class="input @error('published_year') is-invalid @enderror" value="{{ old('published_year', $book->published_year) }}">
                        @error('published_year')<span class="error">{{ $message }}</span>@enderror
                    </div>
                    <div class="field">
                        <label for="pages">Pages</label>
                        <input id="pages" type="number" name="pages" min="1" class="input @error('pages') is-invalid @enderror" value="{{ old('pages', $book->pages) }}">
                        @error('pages')<span class="error">{{ $message }}</span>@enderror
                    </div>
                    <div class="field">
                        <label for="language">Language</label>
                        <input id="language" name="language" class="input" value="{{ old('language', $book->language) }}">
                        @error('language')<span class="error">{{ $message }}</span>@enderror
                    </div>
                    <div class="field">
                        <label for="slug">URL slug</label>
                        <input id="slug" name="slug" class="input @error('slug') is-invalid @enderror" value="{{ old('slug', $book->slug) }}" placeholder="Generated from the title">
                        @error('slug')<span class="error">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            <aside>
                <div class="card card-pad">
                    <h2 class="section-title">Cover</h2>
                    <div class="cover-drop">
                        <template x-if="preview">
                            <div class="cover"><img :src="preview" alt="New cover preview"></div>
                        </template>
                        <div x-show="!preview" :style="removeCover ? 'opacity:.35' : ''">
                            <x-book-cover :book="$book->exists ? $book : new \App\Models\Book(['title' => 'Your book', 'author' => 'Author'])" />
                        </div>
                        <input type="file" name="cover" accept="image/jpeg,image/png,image/webp" @change="pick($event)" aria-label="Upload cover">
                        <span class="hint">JPG, PNG or WebP, up to 4 MB. Without a cover, a typeset one is shown.</span>
                        @error('cover')<span class="error">{{ $message }}</span>@enderror
                        @if ($book->cover_path)
                            <label class="check"><input type="checkbox" name="remove_cover" value="1" x-model="removeCover"> Remove current cover</label>
                        @endif
                    </div>
                </div>

                <div class="card card-pad">
                    <h2 class="section-title">Price & stock</h2>
                    <div class="form-grid">
                        <div class="field">
                            <label for="price">Price</label>
                            <div class="price-input">
                                <span>{{ config('shop.currency') }}</span>
                                <input id="price" type="number" step="0.01" min="0" name="price" class="input @error('price') is-invalid @enderror" value="{{ old('price', $book->price) }}" required>
                            </div>
                            @error('price')<span class="error">{{ $message }}</span>@enderror
                        </div>
                        <div class="field">
                            <label for="stock">Copies in stock</label>
                            <input id="stock" type="number" min="0" name="stock" class="input @error('stock') is-invalid @enderror" value="{{ old('stock', $book->stock) }}" required>
                            @error('stock')<span class="error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>

                <div class="card card-pad">
                    <h2 class="section-title">Visibility</h2>
                    <div class="form-grid">
                        <label class="switch">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $book->is_active))>
                            <span class="track"></span> Active (visible in shop)
                        </label>
                        <label class="switch">
                            <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $book->is_featured))>
                            <span class="track"></span> Featured on home page
                        </label>
                    </div>
                </div>
            </aside>
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.books.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-accent">{{ $editing ? 'Save changes' : 'Add book' }}</button>
        </div>
    </form>
@endsection
