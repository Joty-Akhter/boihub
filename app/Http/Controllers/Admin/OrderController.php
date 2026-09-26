<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->queryText('status');
        $term = $request->queryText('q');

        $orders = Order::withCount('items')
            ->when(isset(Order::STATUSES[$status]), fn ($q) => $q->where('status', $status))
            ->when($term !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('order_number', 'like', "%{$term}%")
                ->orWhere('customer_name', 'like', "%{$term}%")
                ->orWhere('customer_email', 'like', "%{$term}%")
                ->orWhere('customer_phone', 'like', "%{$term}%")))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = Order::select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status');

        return view('admin.orders.index', compact('orders', 'counts', 'status'));
    }

    public function show(Order $order): View
    {
        return view('admin.orders.show', ['order' => $order->load('items.book')]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $new = $request->validate([
            'status' => ['required', Rule::in(array_keys(Order::STATUSES))],
        ])['status'];

        $old = $order->status;

        if ($new === $old) {
            return back();
        }

        DB::transaction(function () use ($order, $old, $new) {
            $order->load('items');

            // Cancelling puts copies back on the shelf; reopening takes them again.
            if ($new === 'cancelled') {
                foreach ($order->items as $item) {
                    if ($item->book_id) {
                        Book::whereKey($item->book_id)->increment('stock', $item->quantity);
                    }
                }
            } elseif ($old === 'cancelled') {
                $books = Book::whereKey($order->items->pluck('book_id')->filter())->lockForUpdate()->get()->keyBy('id');

                foreach ($order->items as $item) {
                    $book = $books->get($item->book_id);

                    if ($book && $book->stock < $item->quantity) {
                        abort(back()->with('error', "Can't reopen: only {$book->stock} copies of “{$book->title}” left."));
                    }
                }

                foreach ($order->items as $item) {
                    $books->get($item->book_id)?->decrement('stock', $item->quantity);
                }
            }

            $order->update(['status' => $new]);
        });

        return back()->with('success', "Order {$order->order_number} is now " . Order::STATUSES[$new] . '.');
    }
}
