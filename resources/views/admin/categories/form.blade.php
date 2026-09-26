@extends('layouts.admin')

@php($editing = $category->exists)

@section('title', $editing ? 'Edit ' . $category->name : 'Add category')

@section('content')
    <a href="{{ route('admin.categories.index') }}" class="back-link">← All categories</a>
    <div class="admin-head">
        <h1>{{ $editing ? 'Edit category' : 'Add a category' }}</h1>
    </div>

    <form method="post" action="{{ $editing ? route('admin.categories.update', $category) : route('admin.categories.store') }}"
          class="card card-pad" style="max-width:640px"
          x-data="{
              name: @js(old('name', $category->name ?? '')),
              slug: @js(old('slug', $category->slug ?? '')),
              touched: {{ $editing ? 'true' : 'false' }},
              slugify(v) { return v.toLowerCase().normalize('NFKD').replace(/[̀-ͯ]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, ''); }
          }">
        @csrf
        @if ($editing) @method('put') @endif

        <div class="form-grid cols-2">
            <div class="field span-all">
                <label for="name">Name</label>
                <input id="name" name="name" class="input @error('name') is-invalid @enderror" x-model="name"
                       @input="if (!touched) slug = slugify(name)" required>
                @error('name')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="field">
                <label for="slug">URL slug</label>
                <input id="slug" name="slug" class="input @error('slug') is-invalid @enderror" x-model="slug" @input="touched = true">
                <span class="hint">Used in links: /books?category=<span x-text="slug || '…'"></span></span>
                @error('slug')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="field">
                <label for="sort_order">Display order</label>
                <input id="sort_order" type="number" min="0" name="sort_order" class="input" value="{{ old('sort_order', $category->sort_order ?? 0) }}">
                <span class="hint">Lower numbers appear first.</span>
                @error('sort_order')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="field span-all">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="textarea" rows="4">{{ old('description', $category->description) }}</textarea>
                @error('description')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="span-all">
                <label class="switch">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active))>
                    <span class="track"></span> Active (shown in the shop)
                </label>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.categories.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-accent">{{ $editing ? 'Save changes' : 'Add category' }}</button>
        </div>
    </form>
@endsection
