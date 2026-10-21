<div class="template-preview" role="img" aria-label="Example layout of the {{ $name }} template">
    <div class="tp-wire">
        <div class="tp-hero"><i></i><b></b><u></u></div>
        <div class="tp-intro"><span><i></i><i></i><i></i></span><em></em></div>
        <div class="tp-cards tp-cards-3"><s></s><s></s><s></s></div>

        @switch($key)
            @case('slideshow')
                <div class="tp-slides"><s></s><s></s><s></s><span>‹ ›</span></div>
                @break
            @case('series')
                <div class="tp-tabs"><u></u><u></u><u></u></div>
                <div class="tp-series"><span><i></i><i></i><i></i><i></i></span><em>🏳</em></div>
                <div class="tp-photos"><s></s><s></s><s></s><s></s><s></s></div>
                @break
            @case('standard')
                <div class="tp-itinerary"><s><i></i><i></i><i></i><i></i></s><s><i></i><i></i><i></i><i></i></s></div>
                <div class="tp-slides"><s></s><s></s><s></s></div>
                <div class="tp-lists"><s></s><s></s></div>
                @break
            @case('compact')
                <div class="tp-itinerary tp-itinerary-compact"><s><i></i></s><s><i></i></s><s><i></i></s></div>
                <div class="tp-slides"><s></s><s></s><s></s></div>
                <div class="tp-lists"><s></s><s></s></div>
                @break
        @endswitch

        <div class="tp-contact"><i></i><b></b></div>
    </div>
    <small>{{ $name }}</small>
</div>
