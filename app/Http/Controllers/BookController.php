<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookController extends Controller
{
    private const SORTS = [
        'newest' => ['created_at', 'desc'],
        'price_asc' => ['price', 'asc'],
        'price_desc' => ['price', 'desc'],
        'title' => ['title', 'asc'],
    ];

    public function index(Request $request): View
    {
        $categories = Category::active()->ordered()->get();
        $category = $request->filled('category')
            ? $categories->firstWhere('slug', $request->queryText('category'))
            : null;

        $sort = $request->queryText('sort');
        $sort = isset(self::SORTS[$sort]) ? $sort : 'newest';
        [$column, $direction] = self::SORTS[$sort];

        $books = Book::visible()
            ->with('category')
            ->search($request->queryText('q'))
            ->when($category, fn ($q) => $q->where('category_id', $category->id))
            ->when($request->boolean('in_stock'), fn ($q) => $q->where('stock', '>', 0))
            ->orderBy($column, $direction)
            ->paginate(12)
            ->withQueryString();

        return view('shop.books.index', compact('books', 'categories', 'category', 'sort'));
    }

    public function show(Book $book): View
    {
        abort_unless(Book::visible()->whereKey($book->id)->exists(), 404);

        $book->load('category');

        $related = Book::visible()
            ->where('category_id', $book->category_id)
            ->whereKeyNot($book->id)
            ->with('category')
            ->inRandomOrder()
            ->take(4)
            ->get();

        return view('shop.books.show', compact('book', 'related'));
    }
}
