<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['is_admin' => true])->save();

        return $user;
    }

    private function category(array $attrs = []): Category
    {
        return Category::create($attrs + ['name' => 'Poetry', 'slug' => 'poetry-' . uniqid(), 'is_active' => true]);
    }

    private function book(array $attrs = []): Book
    {
        return Book::create($attrs + [
            'category_id' => $attrs['category_id'] ?? $this->category()->id,
            'title' => 'Gitanjali',
            'slug' => 'gitanjali-' . uniqid(),
            'author' => 'Rabindranath Tagore',
            'price' => 300,
            'stock' => 10,
        ]);
    }

    /** A real 1x1 PNG, since GD isn't needed to validate it. */
    private function png(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('cover.png', base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='
        ));
    }

    public function test_guests_and_customers_cannot_reach_admin(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/books')->assertRedirect('/admin/login');

        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
    }

    public function test_login_requires_admin_flag(): void
    {
        $customer = User::factory()->create(['password' => 'secret123']);
        $this->post('/admin/login', ['email' => $customer->email, 'password' => 'secret123'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $admin = $this->admin();
        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'password'])->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_admin_pages_render(): void
    {
        $book = $this->book();
        $order = Order::create([
            'order_number' => 'Q-TEST-1', 'customer_name' => 'Rina', 'customer_email' => 'r@example.com',
            'customer_phone' => '0171', 'shipping_address' => 'Road 1', 'shipping_city' => 'Dhaka',
            'subtotal' => 300, 'shipping_fee' => 60, 'total' => 360,
        ]);
        $order->items()->create(['book_id' => $book->id, 'title' => $book->title, 'author' => $book->author, 'unit_price' => 300, 'quantity' => 1, 'line_total' => 300]);

        $this->actingAs($this->admin());
        $this->get('/admin')->assertOk()->assertSee('Q-TEST-1');
        $this->get('/admin/books')->assertOk()->assertSee('Gitanjali');
        $this->get('/admin/books?status=low&q=Tagore')->assertOk();
        $this->get('/admin/books/create')->assertOk();
        $this->get(route('admin.books.edit', $book))->assertOk();
        $this->get('/admin/categories')->assertOk();
        $this->get('/admin/categories/create')->assertOk();
        $this->get(route('admin.categories.edit', $book->category))->assertOk();
        $this->get('/admin/orders')->assertOk()->assertSee('Rina');
        $this->get('/admin/orders?status=pending&q=Rina')->assertOk()->assertSee('Q-TEST-1');
        $this->get(route('admin.orders.show', $order))->assertOk()->assertSee('r@example.com')->assertSee('Road 1');
    }

    public function test_admin_can_create_update_and_delete_a_book_with_cover(): void
    {
        Storage::fake('public');
        $category = $this->category();
        $this->actingAs($this->admin());

        $this->post('/admin/books', [
            'title' => 'Leaves of Grass', 'author' => 'Walt Whitman', 'category_id' => $category->id,
            'price' => '420.50', 'stock' => 7, 'is_featured' => '1', 'is_active' => '1', 'cover' => $this->png(),
        ])->assertRedirect('/admin/books')->assertSessionHasNoErrors();

        $book = Book::sole();
        $this->assertSame('leaves-of-grass', $book->slug);
        $this->assertTrue($book->is_featured);
        $this->assertEquals(420.50, $book->price);
        Storage::disk('public')->assertExists($book->cover_path);
        $oldCover = $book->cover_path;

        $this->put(route('admin.books.update', $book), [
            'title' => 'Leaves of Grass', 'author' => 'Walt Whitman', 'category_id' => $category->id,
            'price' => 450, 'stock' => 3, 'cover' => $this->png(),
        ])->assertRedirect('/admin/books')->assertSessionHasNoErrors();

        $book->refresh();
        $this->assertFalse($book->is_featured);
        $this->assertFalse($book->is_active);
        $this->assertSame(3, $book->stock);
        Storage::disk('public')->assertMissing($oldCover);
        Storage::disk('public')->assertExists($book->cover_path);

        $this->patch(route('admin.books.toggle', $book), ['field' => 'is_active']);
        $this->assertTrue($book->fresh()->is_active);
        $this->patch(route('admin.books.toggle', $book), ['field' => 'price'])->assertSessionHasErrors('field');

        $cover = $book->fresh()->cover_path;
        $this->delete(route('admin.books.destroy', $book))->assertRedirect('/admin/books');
        $this->assertSame(0, Book::count());
        Storage::disk('public')->assertMissing($cover);
    }

    public function test_book_validation(): void
    {
        $this->actingAs($this->admin());

        $this->post('/admin/books', ['title' => '', 'price' => -1, 'stock' => 'x', 'category_id' => 999])
            ->assertSessionHasErrors(['title', 'author', 'price', 'stock', 'category_id']);
    }

    public function test_category_crud_and_delete_protection(): void
    {
        $this->actingAs($this->admin());

        $this->post('/admin/categories', ['name' => 'Science & Nature', 'is_active' => '1'])->assertRedirect('/admin/categories');
        $category = Category::sole();
        $this->assertSame('science-nature', $category->slug);

        $this->put(route('admin.categories.update', $category), ['name' => 'Science', 'slug' => 'science'])->assertRedirect('/admin/categories');
        $this->assertFalse($category->fresh()->is_active);
        $this->assertSame('science', $category->fresh()->slug);

        $this->book(['category_id' => $category->id]);
        $this->delete(route('admin.categories.destroy', $category))->assertSessionHas('error');
        $this->assertNotNull($category->fresh());

        $empty = $this->category();
        $this->delete(route('admin.categories.destroy', $empty))->assertSessionHas('success');
        $this->assertNull($empty->fresh());
    }

    public function test_cancelling_an_order_restores_stock_and_reopening_takes_it(): void
    {
        $book = $this->book(['stock' => 4]);
        $order = Order::create([
            'order_number' => 'Q-TEST-2', 'customer_name' => 'Rina', 'customer_email' => 'r@example.com',
            'customer_phone' => '0171', 'shipping_address' => 'Road 1', 'shipping_city' => 'Dhaka',
            'subtotal' => 600, 'shipping_fee' => 60, 'total' => 660,
        ]);
        $order->items()->create(['book_id' => $book->id, 'title' => $book->title, 'author' => $book->author, 'unit_price' => 300, 'quantity' => 2, 'line_total' => 600]);

        $this->actingAs($this->admin());

        $this->patch(route('admin.orders.status', $order), ['status' => 'processing']);
        $this->assertSame('processing', $order->fresh()->status);
        $this->assertSame(4, $book->fresh()->stock);

        $this->patch(route('admin.orders.status', $order), ['status' => 'cancelled']);
        $this->assertSame(6, $book->fresh()->stock);

        $book->update(['stock' => 1]);
        $this->patch(route('admin.orders.status', $order), ['status' => 'pending'])->assertSessionHas('error');
        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(1, $book->fresh()->stock);

        $book->update(['stock' => 5]);
        $this->patch(route('admin.orders.status', $order), ['status' => 'pending']);
        $this->assertSame('pending', $order->fresh()->status);
        $this->assertSame(3, $book->fresh()->stock);

        $this->patch(route('admin.orders.status', $order), ['status' => 'lost'])->assertSessionHasErrors('status');
    }
}
