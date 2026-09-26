<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BookRequest;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BookController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->queryText('status');

        $books = Book::with('category')
            ->search($request->queryText('q'))
            ->when($request->filled('category'), fn ($q) => $q->where('category_id', $request->integer('category')))
            ->when($status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($status === 'featured', fn ($q) => $q->where('is_featured', true))
            ->when($status === 'low', fn ($q) => $q->where('stock', '<=', config('shop.low_stock_threshold')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.books.index', [
            'books' => $books,
            'categories' => Category::ordered()->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.books.form', [
            'book' => new Book(['is_active' => true, 'stock' => 0]),
            'categories' => Category::ordered()->get(),
        ]);
    }

    public function store(BookRequest $request): RedirectResponse
    {
        $data = $this->payload($request);
        $data['slug'] = Book::uniqueSlug(($data['slug'] ?? null) ?: $data['title']);

        if ($request->hasFile('cover')) {
            $data['cover_path'] = $request->file('cover')->store('covers', 'public');
        }

        $book = Book::create($data);

        return redirect()->route('admin.books.index')->with('success', "“{$book->title}” was added.");
    }

    public function edit(Book $book): View
    {
        return view('admin.books.form', [
            'book' => $book,
            'categories' => Category::ordered()->get(),
        ]);
    }

    public function update(BookRequest $request, Book $book): RedirectResponse
    {
        $data = $this->payload($request);
        $data['slug'] = Book::uniqueSlug(($data['slug'] ?? null) ?: $data['title'], $book->id);

        if ($request->hasFile('cover') || $request->boolean('remove_cover')) {
            $this->deleteCover($book);
            $data['cover_path'] = $request->hasFile('cover')
                ? $request->file('cover')->store('covers', 'public')
                : null;
        }

        $book->update($data);

        return redirect()->route('admin.books.index')->with('success', "“{$book->title}” was updated.");
    }

    /** Quick toggles from the list: active / featured. */
    public function toggle(Request $request, Book $book): RedirectResponse
    {
        $field = $request->validate(['field' => ['required', 'in:is_active,is_featured']])['field'];
        $book->update([$field => ! $book->{$field}]);

        return back()->with('success', "“{$book->title}” updated.");
    }

    public function destroy(Book $book): RedirectResponse
    {
        // Past orders keep their own title/price snapshot, so deleting is safe.
        $this->deleteCover($book);
        $book->delete();

        return redirect()->route('admin.books.index')->with('success', "“{$book->title}” was deleted.");
    }

    private function payload(BookRequest $request): array
    {
        return collect($request->validated())->except(['cover', 'remove_cover'])->all();
    }

    private function deleteCover(Book $book): void
    {
        if ($book->cover_path) {
            Storage::disk('public')->delete($book->cover_path);
        }
    }
}
