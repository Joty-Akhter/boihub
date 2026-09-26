@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="admin-head">
        <div>
            <h1>Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 18 ? 'afternoon' : 'evening') }}</h1>
            <p>Here's what is happening at {{ config('shop.name') }}.</p>
        </div>
        <a href="{{ route('admin.books.create') }}" class="btn btn-accent">+ Add book</a>
    </div>

    <div class="stats">
        <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="card stat" style="text-decoration:none">
            <div class="label">Pending orders</div>
            <div class="value accent">{{ $stats['pending'] }}</div>
        </a>
        <div class="card stat">
            <div class="label">Orders · 30 days</div>
            <div class="value">{{ $stats['orders_30d'] }}</div>
        </div>
        <div class="card stat">
            <div class="label">Revenue · 30 days</div>
            <div class="value">{{ money($stats['revenue_30d']) }}</div>
        </div>
        <a href="{{ route('admin.books.index') }}" class="card stat" style="text-decoration:none">
            <div class="label">Books</div>
            <div class="value">{{ $stats['books'] }}</div>
        </a>
    </div>

    <div class="dash-grid">
        <section class="card">
            <div class="card-head">
                <h2>Recent orders</h2>
                <a href="{{ route('admin.orders.index') }}">All orders →</a>
            </div>
            @if ($recentOrders->isEmpty())
                <p class="card-pad muted">No orders yet.</p>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead><tr><th>Order</th><th>Customer</th><th>Status</th><th class="num">Total</th></tr></thead>
                        <tbody>
                        @foreach ($recentOrders as $order)
                            <tr>
                                <td>
                                    <a class="row-link" href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a>
                                    <div class="muted small">{{ $order->created_at->diffForHumans() }}</div>
                                </td>
                                <td>{{ $order->customer_name }}</td>
                                <td><x-order-status :order="$order" /></td>
                                <td class="num">{{ money($order->total) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="card">
            <div class="card-head">
                <h2>Running low</h2>
                <a href="{{ route('admin.books.index', ['status' => 'low']) }}">View all →</a>
            </div>
            @if ($lowStock->isEmpty())
                <p class="card-pad muted">Every active book is well stocked.</p>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <tbody>
                        @foreach ($lowStock as $book)
                            <tr>
                                <td>
                                    <div class="book-cell" style="min-width:0">
                                        <x-book-cover :book="$book" size="xs" />
                                        <div>
                                            <a class="row-link" href="{{ route('admin.books.edit', $book) }}">{{ $book->title }}</a>
                                            <div class="muted">{{ $book->author }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="num"><span class="{{ $book->stock === 0 ? 'stock-out' : 'stock-low' }}">{{ $book->stock }}</span></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
@endsection
