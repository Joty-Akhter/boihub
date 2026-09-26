@props(['book', 'size' => null])

{{-- Uploaded cover if there is one, otherwise a typeset cover in one of eight palettes. --}}
<div {{ $attributes->class(['cover', 'cover-lg' => $size === 'lg', 'cover-xs' => $size === 'xs']) }}>
    @if ($url = $book->coverUrl())
        <img src="{{ $url }}" alt="Cover of {{ $book->title }}" loading="lazy">
    @else
        <div class="cover-gen cv-{{ crc32($book->title ?? '') % 8 }}" role="img" aria-label="Cover of {{ $book->title }}">
            <div class="cg-rule"></div>
            <div class="cg-title">{{ $book->title }}</div>
            <div class="cg-author">{{ $book->author }}</div>
        </div>
    @endif
</div>
