{{-- Love button for a Smart Muslim package. Expects $likeKey (see App\Support\PackageLikes) and $likeLabel; optional $likeClass. Counts load from JS. --}}
<button type="button" class="package-like {{ $likeClass ?? '' }}" data-like-key="{{ $likeKey }}" aria-pressed="false" aria-label="Love {{ $likeLabel }}" title="Love this package">
    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20.6s-7.6-4.6-9.4-9.3C1.4 8 3.4 4.4 7 4.4c2 0 3.4 1.1 5 3 1.6-1.9 3-3 5-3 3.6 0 5.6 3.6 4.4 6.9-1.8 4.7-9.4 9.3-9.4 9.3Z"/></svg>
    <span class="package-like-count" data-like-count aria-live="polite"></span>
</button>
