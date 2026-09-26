@extends('layouts.shop')

@section('title', 'Checkout')

@section('content')
    <div class="wrap">
        <header class="page-head">
            <p class="eyebrow"><a href="{{ route('cart.index') }}" style="text-decoration:none">← Back to basket</a></p>
            <h1 style="margin-top:8px">Checkout</h1>
        </header>

        <form method="post" action="{{ route('checkout.store') }}" class="cart-layout" x-data="{ busy: false }" @submit="busy = true">
            @csrf
            <div>
                @error('cart')
                    <div class="alert" role="alert">{{ $message }}</div>
                @enderror

                <fieldset class="fieldset">
                    <legend>Your details</legend>
                    <div class="form-grid cols-2">
                        <div class="field span-all">
                            <label for="customer_name">Full name</label>
                            <input id="customer_name" name="customer_name" class="input @error('customer_name') is-invalid @enderror" value="{{ old('customer_name') }}" autocomplete="name" required>
                            @error('customer_name')<span class="error">{{ $message }}</span>@enderror
                        </div>
                        <div class="field">
                            <label for="customer_email">Email</label>
                            <input id="customer_email" type="email" name="customer_email" class="input @error('customer_email') is-invalid @enderror" value="{{ old('customer_email') }}" autocomplete="email" required>
                            @error('customer_email')<span class="error">{{ $message }}</span>@enderror
                        </div>
                        <div class="field">
                            <label for="customer_phone">Phone</label>
                            <input id="customer_phone" type="tel" name="customer_phone" class="input @error('customer_phone') is-invalid @enderror" value="{{ old('customer_phone') }}" autocomplete="tel" required>
                            @error('customer_phone')<span class="error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </fieldset>

                <fieldset class="fieldset">
                    <legend>Delivery address</legend>
                    <div class="form-grid cols-2">
                        <div class="field span-all">
                            <label for="shipping_address">Street address</label>
                            <input id="shipping_address" name="shipping_address" class="input @error('shipping_address') is-invalid @enderror" value="{{ old('shipping_address') }}" autocomplete="street-address" required>
                            @error('shipping_address')<span class="error">{{ $message }}</span>@enderror
                        </div>
                        <div class="field">
                            <label for="shipping_city">City</label>
                            <input id="shipping_city" name="shipping_city" class="input @error('shipping_city') is-invalid @enderror" value="{{ old('shipping_city') }}" autocomplete="address-level2" required>
                            @error('shipping_city')<span class="error">{{ $message }}</span>@enderror
                        </div>
                        <div class="field">
                            <label for="shipping_postcode">Postcode <span class="muted" style="font-weight:400">(optional)</span></label>
                            <input id="shipping_postcode" name="shipping_postcode" class="input @error('shipping_postcode') is-invalid @enderror" value="{{ old('shipping_postcode') }}" autocomplete="postal-code">
                            @error('shipping_postcode')<span class="error">{{ $message }}</span>@enderror
                        </div>
                        <div class="field span-all">
                            <label for="notes">Delivery notes <span class="muted" style="font-weight:400">(optional)</span></label>
                            <textarea id="notes" name="notes" class="textarea" rows="3" placeholder="Landmark, best time to call…">{{ old('notes') }}</textarea>
                            @error('notes')<span class="error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </fieldset>

                <fieldset class="fieldset">
                    <legend>Payment</legend>
                    <label class="pay-option">
                        <input type="radio" checked disabled style="margin-top:4px;accent-color:var(--accent)">
                        <span>
                            <strong>Cash on delivery</strong><br>
                            <span class="muted small">Pay the courier when your books arrive.</span>
                        </span>
                    </label>
                </fieldset>
            </div>

            <aside class="summary">
                <h2>Your order</h2>
                <ul class="mini-lines">
                    @foreach ($lines as $line)
                        <li>
                            <x-book-cover :book="$line->book" />
                            <div class="grow">
                                <strong>{{ $line->book->title }}</strong>
                                <span class="muted">Qty {{ $line->quantity }}</span>
                            </div>
                            <span>{{ money($line->line_total) }}</span>
                        </li>
                    @endforeach
                </ul>
                @include('shop.partials.totals')
                <button type="submit" class="btn btn-accent btn-block" style="margin-top:16px" :disabled="busy">
                    <span x-show="!busy">Place order · {{ money($totals['total']) }}</span>
                    <span x-show="busy" x-cloak>Placing order…</span>
                </button>
            </aside>
        </form>
    </div>
@endsection
