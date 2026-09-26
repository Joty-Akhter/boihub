<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Order;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $threshold = config('shop.low_stock_threshold');

        return view('admin.dashboard', [
            'stats' => [
                'books' => Book::count(),
                'pending' => Order::where('status', 'pending')->count(),
                'orders_30d' => Order::where('created_at', '>=', now()->subDays(30))->count(),
                'revenue_30d' => Order::where('created_at', '>=', now()->subDays(30))
                    ->where('status', '!=', 'cancelled')->sum('total'),
            ],
            'recentOrders' => Order::withCount('items')->latest()->take(8)->get(),
            'lowStock' => Book::where('is_active', true)->where('stock', '<=', $threshold)
                ->orderBy('stock')->take(8)->get(),
        ]);
    }
}
