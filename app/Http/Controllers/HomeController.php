<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('shop.home', [
            'featured' => Book::visible()->where('is_featured', true)->with('category')->latest()->take(8)->get(),
            'newArrivals' => Book::visible()->with('category')->latest()->take(8)->get(),
            'categories' => Category::active()->ordered()
                ->withCount(['books' => fn ($q) => $q->where('is_active', true)])
                ->get(),
        ]);
    }
}
