<?php

namespace App\Support;

use App\Models\Book;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Collection;

/**
 * Session-backed cart. Stores only [book_id => quantity]; prices and
 * availability are always re-read from the database.
 */
class Cart
{
    private const KEY = 'cart';

    public function __construct(private Session $session)
    {
    }

    /** @return array<int, int> */
    public function raw(): array
    {
        return $this->session->get(self::KEY, []);
    }

    public function add(Book $book, int $quantity = 1): int
    {
        $items = $this->raw();
        $items[$book->id] = $this->clamp($book, ($items[$book->id] ?? 0) + $quantity);
        $this->session->put(self::KEY, $items);

        return $items[$book->id];
    }

    public function set(Book $book, int $quantity): int
    {
        if ($quantity <= 0) {
            $this->remove($book->id);

            return 0;
        }

        $items = $this->raw();
        $items[$book->id] = $this->clamp($book, $quantity);
        $this->session->put(self::KEY, $items);

        return $items[$book->id];
    }

    public function remove(int $bookId): void
    {
        $items = $this->raw();
        unset($items[$bookId]);
        $this->session->put(self::KEY, $items);
    }

    public function clear(): void
    {
        $this->session->forget(self::KEY);
    }

    public function count(): int
    {
        return array_sum($this->raw());
    }

    /**
     * Cart lines with fresh book data. Lines for books that can no longer be
     * bought are dropped, and quantities are capped at current stock.
     *
     * @return Collection<int, object>
     */
    public function lines(): Collection
    {
        $raw = $this->raw();

        if ($raw === []) {
            return collect();
        }

        $books = Book::visible()->with('category')->whereKey(array_keys($raw))->get()->keyBy('id');
        $cleaned = [];
        $lines = collect();

        foreach ($raw as $bookId => $quantity) {
            $book = $books->get($bookId);

            if (! $book || ! $book->isInStock()) {
                continue;
            }

            $quantity = $this->clamp($book, $quantity);
            $cleaned[$bookId] = $quantity;
            $lines->push((object) [
                'book' => $book,
                'quantity' => $quantity,
                'line_total' => round((float) $book->price * $quantity, 2),
            ]);
        }

        if ($cleaned !== $raw) {
            $this->session->put(self::KEY, $cleaned);
        }

        return $lines;
    }

    /** @return array{subtotal: float, shipping: float, total: float} */
    public function totals(Collection $lines): array
    {
        $subtotal = round((float) $lines->sum('line_total'), 2);
        $freeOver = (float) config('shop.shipping.free_over');
        $shipping = ($subtotal <= 0 || ($freeOver > 0 && $subtotal >= $freeOver))
            ? 0.0
            : (float) config('shop.shipping.flat_rate');

        return [
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => round($subtotal + $shipping, 2),
        ];
    }

    private function clamp(Book $book, int $quantity): int
    {
        return max(1, min($quantity, $book->stock, config('shop.max_quantity_per_item')));
    }
}
