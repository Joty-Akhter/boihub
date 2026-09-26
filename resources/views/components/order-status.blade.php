@props(['order'])

<span {{ $attributes->class(['badge', 'badge-' . $order->status]) }}>{{ $order->statusLabel() }}</span>
