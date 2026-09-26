<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Support\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private Cart $cart)
    {
    }

    public function index(): View
    {
        $lines = $this->cart->lines();

        return view('shop.cart', [
            'lines' => $lines,
            'totals' => $this->cart->totals($lines),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'book_id' => ['required', 'integer'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:' . config('shop.max_quantity_per_item')],
        ]);

        $book = Book::visible()->findOrFail($data['book_id']);

        if (! $book->isInStock()) {
            return back()->with('error', "“{$book->title}” is out of stock.");
        }

        $this->cart->add($book, $data['quantity'] ?? 1);

        return back()->with('success', "Added “{$book->title}” to your basket.");
    }

    public function update(Request $request, Book $book): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:' . config('shop.max_quantity_per_item')],
        ]);

        $this->cart->set($book, $data['quantity']);

        return redirect()->route('cart.index');
    }

    public function destroy(Book $book): RedirectResponse
    {
        $this->cart->remove($book->id);

        return redirect()->route('cart.index')->with('success', "Removed “{$book->title}”.");
    }
}
