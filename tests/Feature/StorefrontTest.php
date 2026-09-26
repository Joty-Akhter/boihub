<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    private function book(array $attrs = [], array $categoryAttrs = []): Book
    {
        $category = Category::create($categoryAttrs + ['name' => 'Fiction', 'slug' => 'fiction-' . uniqid(), 'is_active' => true]);

        return Book::create($attrs + [
            'category_id' => $category->id,
            'title' => 'Test Book',
            'slug' => 'test-book-' . uniqid(),
            'author' => 'A. Writer',
            'price' => 500,
            'stock' => 5,
            'is_active' => true,
        ]);
    }

    private function checkoutData(array $overrides = []): array
    {
        return $overrides + [
            'customer_name' => 'Rina Das',
            'customer_email' => 'rina@example.com',
            'customer_phone' => '+880 1711 000000',
            'shipping_address' => '12 Lake Road',
            'shipping_city' => 'Dhaka',
        ];
    }

    public function test_public_pages_render(): void
    {
        $book = $this->book(['is_featured' => true]);

        $this->get('/')->assertOk()->assertSee('Test Book');
        $this->get('/books')->assertOk()->assertSee('Test Book');
        $this->get('/books?q=Writer&sort=price_desc&in_stock=1')->assertOk()->assertSee('Test Book');
        $this->get('/books?sort[]=x')->assertOk();
        $this->get(route('books.show', $book))->assertOk()->assertSee('A. Writer');
        $this->get('/basket')->assertOk()->assertSee('Your basket is empty');
    }

    public function test_inactive_books_and_hidden_categories_are_not_visible(): void
    {
        $inactive = $this->book(['title' => 'Hidden One', 'is_active' => false]);
        $hiddenCat = $this->book(['title' => 'Hidden Two'], ['is_active' => false]);

        $this->get(route('books.show', $inactive))->assertNotFound();
        $this->get(route('books.show', $hiddenCat))->assertNotFound();
        $this->get('/books')->assertDontSee('Hidden One')->assertDontSee('Hidden Two');
        $this->post('/basket', ['book_id' => $inactive->id])->assertNotFound();
    }

    public function test_cart_quantity_is_capped_by_stock(): void
    {
        $book = $this->book(['stock' => 2]);

        $this->post('/basket', ['book_id' => $book->id, 'quantity' => 5])->assertRedirect();
        $this->assertSame([$book->id => 2], session('cart'));

        $this->patch(route('cart.update', $book), ['quantity' => 1]);
        $this->assertSame([$book->id => 1], session('cart'));

        $this->delete(route('cart.destroy', $book));
        $this->assertSame([], session('cart'));
    }

    public function test_checkout_creates_order_and_decrements_stock(): void
    {
        $book = $this->book(['price' => 400, 'stock' => 5]);
        $this->post('/basket', ['book_id' => $book->id, 'quantity' => 2]);

        $this->get('/checkout')->assertOk()->assertSee('Place order');
        $response = $this->post('/checkout', $this->checkoutData());

        $order = Order::with('items')->sole();
        $response->assertRedirect(route('checkout.success', $order));
        $this->assertSame('pending', $order->status);
        $this->assertEquals(800, $order->subtotal);
        $this->assertEquals(config('shop.shipping.flat_rate'), $order->shipping_fee);
        $this->assertEquals(800 + config('shop.shipping.flat_rate'), $order->total);
        $this->assertSame(2, $order->items->first()->quantity);
        $this->assertSame('Test Book', $order->items->first()->title);
        $this->assertSame(3, $book->fresh()->stock);
        $this->assertNull(session('cart'));

        $this->get(route('checkout.success', $order))->assertOk()->assertSee($order->order_number);
    }

    public function test_free_shipping_over_threshold(): void
    {
        $book = $this->book(['price' => config('shop.shipping.free_over'), 'stock' => 5]);
        $this->post('/basket', ['book_id' => $book->id]);
        $this->post('/checkout', $this->checkoutData());

        $this->assertEquals(0, Order::sole()->shipping_fee);
    }

    public function test_checkout_fails_when_stock_ran_out(): void
    {
        $book = $this->book(['stock' => 2]);
        $this->post('/basket', ['book_id' => $book->id, 'quantity' => 2]);
        $book->update(['stock' => 1]);

        $this->from('/checkout')->post('/checkout', $this->checkoutData())
            ->assertRedirect('/checkout')
            ->assertSessionHasErrors('cart');

        $this->assertSame(0, Order::count());
        $this->assertSame(1, $book->fresh()->stock);
    }

    public function test_checkout_validates_customer_details(): void
    {
        $book = $this->book();
        $this->post('/basket', ['book_id' => $book->id]);

        $this->post('/checkout', $this->checkoutData(['customer_email' => "a@b.com\r\nBcc: x@y.com", 'customer_phone' => 'call me']))
            ->assertSessionHasErrors(['customer_email', 'customer_phone']);
    }

    public function test_empty_cart_cannot_check_out(): void
    {
        $this->get('/checkout')->assertRedirect('/basket');
        $this->post('/checkout', $this->checkoutData())->assertRedirect('/basket');
    }

    public function test_success_page_is_private_to_the_shopper(): void
    {
        $book = $this->book();
        $this->post('/basket', ['book_id' => $book->id]);
        $this->post('/checkout', $this->checkoutData());
        $order = Order::sole();

        $this->flushSession();
        $this->get(route('checkout.success', $order))->assertNotFound();
    }
}
