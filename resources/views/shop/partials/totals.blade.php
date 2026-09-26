<div class="summary-row"><span>Subtotal</span><span>{{ money($totals['subtotal']) }}</span></div>
<div class="summary-row">
    <span>Shipping</span>
    <span>{{ $totals['shipping'] > 0 ? money($totals['shipping']) : 'Free' }}</span>
</div>
<div class="summary-row total"><span>Total</span><span>{{ money($totals['total']) }}</span></div>
@php($freeOver = config('shop.shipping.free_over'))
@if ($totals['shipping'] > 0 && $freeOver > 0)
    <p class="note">Add {{ money($freeOver - $totals['subtotal']) }} more for free shipping.</p>
@endif
