@extends('layouts.admin')

@section('title', 'Order ' . $order->order_number)

@section('content')
    <a href="{{ route('admin.orders.index') }}" class="back-link">← All orders</a>
    <div class="admin-head">
        <div>
            <h1 style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
                {{ $order->order_number }} <x-order-status :order="$order" />
            </h1>
            <p>Placed {{ $order->created_at->format('d M Y \a\t H:i') }} · {{ $order->created_at->diffForHumans() }}</p>
        </div>
    </div>

    <div class="form-layout">
        <div style="display:grid;gap:24px">
            <section class="card">
                <div class="card-head"><h2>Books ordered</h2></div>
                <div class="table-wrap">
                    <table class="table order-lines">
                        <thead>
                        <tr><th>Book</th><th class="num">Price</th><th class="num">Qty</th><th class="num">Total</th></tr>
                        </thead>
                        <tbody>
                        @foreach ($order->items as $item)
                            <tr>
                                <td>
                                    <div class="book-cell">
                                        @if ($item->book)
                                            <x-book-cover :book="$item->book" size="xs" />
                                        @endif
                                        <div>
                                            @if ($item->book)
                                                <a class="row-link" href="{{ route('admin.books.edit', $item->book) }}">{{ $item->title }}</a>
                                            @else
                                                <strong>{{ $item->title }}</strong>
                                                <span class="muted small">(book since deleted)</span>
                                            @endif
                                            <div class="muted">{{ $item->author }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="num">{{ money($item->unit_price) }}</td>
                                <td class="num">{{ $item->quantity }}</td>
                                <td class="num">{{ money($item->line_total) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-pad" style="border-top:1px solid var(--rule);max-width:340px;margin-left:auto">
                    <div class="summary-row"><span>Subtotal</span><span>{{ money($order->subtotal) }}</span></div>
                    <div class="summary-row"><span>Shipping</span><span>{{ $order->shipping_fee > 0 ? money($order->shipping_fee) : 'Free' }}</span></div>
                    <div class="summary-row total"><span>Total</span><span>{{ money($order->total) }}</span></div>
                    <p class="muted small" style="margin-top:6px">{{ $order->paymentLabel() }}</p>
                </div>
            </section>

            @if ($order->notes)
                <section class="card card-pad">
                    <h2 class="section-title">Customer notes</h2>
                    <p style="white-space:pre-line">{{ $order->notes }}</p>
                </section>
            @endif
        </div>

        <aside>
            <section class="card card-pad">
                <h2 class="section-title">Status</h2>
                <form method="post" action="{{ route('admin.orders.status', $order) }}" x-data="{ status: @js($order->status), original: @js($order->status) }"
                      @submit="if (status === 'cancelled' && !confirm('Cancel this order? Its copies will go back into stock.')) $event.preventDefault()">
                    @csrf @method('patch')
                    <div class="field">
                        <label for="status" class="visually-hidden">Order status</label>
                        <select id="status" name="status" class="select" x-model="status">
                            @foreach (\App\Models\Order::STATUSES as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <p class="hint small muted" style="margin-top:8px" x-show="status === 'cancelled' && original !== 'cancelled'" x-cloak>Stock will be restored.</p>
                    <p class="hint small muted" style="margin-top:8px" x-show="original === 'cancelled' && status !== 'cancelled'" x-cloak>Stock will be taken again.</p>
                    <button type="submit" class="btn btn-block" style="margin-top:12px" :disabled="status === original">Update status</button>
                </form>
            </section>

            <section class="card card-pad">
                <h2 class="section-title">Customer</h2>
                <dl class="kv">
                    <div><dt>Name</dt><dd>{{ $order->customer_name }}</dd></div>
                    <div><dt>Email</dt><dd><a href="mailto:{{ $order->customer_email }}">{{ $order->customer_email }}</a></dd></div>
                    <div><dt>Phone</dt><dd><a href="tel:{{ preg_replace('/[^0-9+]/', '', $order->customer_phone) }}">{{ $order->customer_phone }}</a></dd></div>
                    <div>
                        <dt>Delivery address</dt>
                        <dd>
                            {{ $order->shipping_address }}<br>
                            {{ $order->shipping_city }}{{ $order->shipping_postcode ? ' ' . $order->shipping_postcode : '' }}
                        </dd>
                    </div>
                </dl>
            </section>
        </aside>
    </div>
@endsection
