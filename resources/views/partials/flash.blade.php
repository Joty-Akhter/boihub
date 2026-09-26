@if (session('success') || session('error'))
    <div class="flash-stack" aria-live="polite">
        @foreach (['success' => '', 'error' => 'error-flash'] as $key => $class)
            @if (session($key))
                <div class="flash {{ $class }}" x-data="{ show: true }" x-show="show" x-transition.opacity.duration.300ms
                     x-init="setTimeout(() => show = false, 5000)" role="status">
                    <span>{{ session($key) }}</span>
                    <button type="button" @click="show = false" aria-label="Dismiss">&times;</button>
                </div>
            @endif
        @endforeach
    </div>
@endif
