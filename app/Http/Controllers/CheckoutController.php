<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Book;
use App\Models\Order;
use App\Support\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(private Cart $cart)
    {
    }

    public function create(): View|RedirectResponse
    {
        $lines = $this->cart->lines();

        if ($lines->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your basket is empty.');
        }

        return view('shop.checkout', [
            'lines' => $lines,
            'totals' => $this->cart->totals($lines),
        ]);
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        $wanted = $this->cart->raw();

        if ($wanted === []) {
            return redirect()->route('cart.index')->with('error', 'Your basket is empty.');
        }

        $order = DB::transaction(function () use ($request, $wanted) {
            // Lock the rows so two shoppers can't both buy the last copy.
            $books = Book::visible()->whereKey(array_keys($wanted))->lockForUpdate()->get()->keyBy('id');
            $lines = collect();

            foreach ($wanted as $bookId => $quantity) {
                $book = $books->get($bookId);

                if (! $book || $book->stock < $quantity) {
                    $title = $book?->title ?? 'A book in your basket';
                    throw ValidationException::withMessages([
                        'cart' => "“{$title}” no longer has enough copies in stock. Please review your basket.",
                    ]);
                }

                $lines->push((object) [
                    'book' => $book,
                    'quantity' => $quantity,
                    'line_total' => round((float) $book->price * $quantity, 2),
                ]);
            }

            $totals = $this->cart->totals($lines);

            $order = Order::create($request->validated() + [
                'order_number' => Order::generateNumber(),
                'status' => 'pending',
                'payment_method' => 'cod',
                'subtotal' => $totals['subtotal'],
                'shipping_fee' => $totals['shipping'],
                'total' => $totals['total'],
            ]);

            foreach ($lines as $line) {
                $order->items()->create([
                    'book_id' => $line->book->id,
                    'title' => $line->book->title,
                    'author' => $line->book->author,
                    'unit_price' => $line->book->price,
                    'quantity' => $line->quantity,
                    'line_total' => $line->line_total,
                ]);
                $line->book->decrement('stock', $line->quantity);
            }

            return $order;
        });

        $this->cart->clear();
        $request->session()->put('last_order', $order->order_number);

        return redirect()->route('checkout.success', $order);
    }

    public function success(Order $order): View
    {
        // Only the shopper who just placed the order may view this page.
        abort_unless(session('last_order') === $order->order_number, 404);

        return view('shop.order-success', ['order' => $order->load('items')]);
    }
}
