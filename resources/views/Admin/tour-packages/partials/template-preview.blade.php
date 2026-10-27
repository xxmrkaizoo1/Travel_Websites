{{-- Miniature page drawing for a template. Sections unique to the template are highlighted; shared sections are muted. --}}
<div class="template-preview tp-template-{{ $key }}" role="img" aria-label="Example layout of the {{ $name }} template">
    <div class="tp-browser" aria-hidden="true"><span></span><span></span><span></span></div>
    <div class="tp-wire" aria-hidden="true">
        <div class="tp-block tp-hero">
            <em class="tp-label">Hero banner</em>
            <i></i><b></b><u></u>
        </div>
        <div class="tp-block tp-intro">
            <em class="tp-label">Introduction</em>
            <span><i></i><i></i><i></i></span><s></s>
        </div>

        @switch($key)
            @case('slideshow')
                <div class="tp-block tp-highlights">
                    <em class="tp-label">3 highlights</em>
                    <s>★</s><s>★</s><s>★</s>
                </div>
                <div class="tp-block tp-key tp-carousel">
                    <em class="tp-label">Destination slideshow</em>
                    <span class="tp-arrow">‹</span><s></s><s></s><s></s><span class="tp-arrow">›</span>
                    <span class="tp-dots"><i></i><i></i><i></i></span>
                </div>
                @break
            @case('series')
                <div class="tp-block tp-key tp-tabs">
                    <em class="tp-label">Country tabs</em>
                    <u>China</u><u>Korea</u><u>Türkiye</u>
                </div>
                <div class="tp-block tp-key tp-series">
                    <em class="tp-label">Advantages + flag card</em>
                    <span><i>✓</i><i>✓</i><i>✓</i><i>✓</i></span><b class="tp-flag">★</b>
                </div>
                <div class="tp-block tp-key tp-photos">
                    <em class="tp-label">5-photo gallery</em>
                    <s></s><s></s><s></s><s></s><s></s>
                </div>
                @break
            @case('standard')
                <div class="tp-block tp-key tp-itinerary">
                    <em class="tp-label">Day-by-day itinerary</em>
                    @foreach (['A', 'B'] as $itinerary)
                        <s><b>Package {{ $itinerary }}</b><i>Day 1</i><i>Day 2</i><i>Day 3</i></s>
                    @endforeach
                </div>
                @include('Admin.tour-packages.partials.template-preview-package-extras')
                @break
            @case('compact')
                <div class="tp-block tp-key tp-itinerary tp-itinerary-compact">
                    <em class="tp-label">Summary cards only</em>
                    @foreach (['A', 'B', 'C'] as $itinerary)
                        <s><b>Package {{ $itinerary }}</b><i></i></s>
                    @endforeach
                </div>
                @include('Admin.tour-packages.partials.template-preview-package-extras')
                @break
        @endswitch

        <div class="tp-block tp-contact">
            <em class="tp-label">Contact</em>
            <i></i><b>WhatsApp</b>
        </div>
    </div>
    <p class="tp-legend"><span aria-hidden="true"></span> Highlighted = what makes this template different</p>
</div>
