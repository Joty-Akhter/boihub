@extends('layouts.admin')

@section('title', 'Orders')

@section('content')
    <div class="admin-head">
        <div>
            <h1>Orders</h1>
            <p>{{ $counts->sum() }} {{ Str::plural('order', $counts->sum()) }} in total</p>
        </div>
    </div>

    <div class="card">
        <nav class="tabs" aria-label="Filter by status">
            <a href="{{ route('admin.orders.index', request()->only('q')) }}" @class(['active' => ! isset(\App\Models\Order::STATUSES[$status])])>
                All <span class="n">{{ $counts->sum() }}</span>
            </a>
            @foreach (\App\Models\Order::STATUSES as $key => $label)
                <a href="{{ route('admin.orders.index', ['status' => $key] + request()->only('q')) }}" @class(['active' => $status === $key])>
                    {{ $label }} <span class="n">{{ $counts[$key] ?? 0 }}</span>
                </a>
            @endforeach
        </nav>

        <form method="get" class="filters-bar">
            @if (isset(\App\Models\Order::STATUSES[$status]))
                <input type="hidden" name="status" value="{{ $status }}">
            @endif
            <input type="search" name="q" value="{{ request('q') }}" class="input" placeholder="Order number, customer name, email or phone" aria-label="Search orders">
            <button type="submit" class="btn btn-ghost btn-sm">Search</button>
            @if (request('q'))
                <a href="{{ route('admin.orders.index', array_filter(['status' => $status])) }}" class="link-btn">Clear</a>
            @endif
        </form>

        @if ($orders->isEmpty())
            <div class="card-pad muted">No orders found.</div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Order</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th class="num">Items</th>
                        <th class="num">Total</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <td><a class="row-link" href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></td>
                            <td class="muted" style="white-space:nowrap">{{ $order->created_at->format('d M Y, H:i') }}</td>
                            <td>
                                {{ $order->customer_name }}
                                <div class="muted small">{{ $order->customer_phone }}</div>
                            </td>
                            <td class="num">{{ $order->items_count }}</td>
                            <td class="num">{{ money($order->total) }}</td>
                            <td><x-order-status :order="$order" /></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{ $orders->links() }}
@endsection
