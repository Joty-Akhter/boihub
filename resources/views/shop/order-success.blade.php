@extends('layouts.shop')

@section('title', 'Order placed')

@section('content')
    <div class="wrap">
        <div class="success-card">
            <div class="seal" aria-hidden="true">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg>
            </div>
            <h1>Thank you, {{ Str::before($order->customer_name, ' ') }}.</h1>
            <p class="muted" style="margin-top:12px">
                Your order <strong style="color:var(--ink)">{{ $order->order_number }}</strong> is in. We'll call
                {{ $order->customer_phone }} to confirm delivery. Payment is due when your books arrive.
            </p>

            <div class="receipt">
                <ul class="mini-lines">
                    @foreach ($order->items as $item)
                        <li>
                            <div class="grow">
                                <strong>{{ $item->title }}</strong>
                                <span class="muted">{{ $item->author }} · Qty {{ $item->quantity }}</span>
                            </div>
                            <span>{{ money($item->line_total) }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="summary-row"><span>Subtotal</span><span>{{ money($order->subtotal) }}</span></div>
                <div class="summary-row"><span>Shipping</span><span>{{ $order->shipping_fee > 0 ? money($order->shipping_fee) : 'Free' }}</span></div>
                <div class="summary-row total"><span>Total due on delivery</span><span>{{ money($order->total) }}</span></div>
                <p class="note small muted" style="margin-top:14px">
                    Delivering to {{ $order->shipping_address }}, {{ $order->shipping_city }}{{ $order->shipping_postcode ? ' ' . $order->shipping_postcode : '' }}
                </p>
            </div>

            <a href="{{ route('books.index') }}" class="btn btn-ghost" style="margin-top:28px">Continue browsing</a>
        </div>
    </div>
@endsection
