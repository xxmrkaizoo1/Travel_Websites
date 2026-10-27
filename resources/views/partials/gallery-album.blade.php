{{-- Postcard album popup shared by the public gallery page and the admin gallery preview. Filled by the gallery album script. --}}
<dialog class="gallery-album" id="gallery-album" aria-labelledby="gallery-album-title">
    <div class="gallery-album__panel">
        <header class="gallery-album__header">
            <span class="gallery-album__stamp" aria-hidden="true"><img data-album-flag src="" alt=""></span>
            <div class="gallery-album__heading">
                <span class="gallery-album__eyebrow">{{ $eyebrow }}</span>
                <h2 id="gallery-album-title" data-album-country></h2>
            </div>
            <span class="gallery-album__counter" data-album-counter aria-live="polite"></span>
            <button class="gallery-album__close" type="button" data-album-close aria-label="Close gallery">×</button>
        </header>

        <div class="gallery-album__stage" data-album-stage>
            <img class="gallery-album__image" data-album-image src="" alt="">
            <p class="gallery-album__caption" data-album-caption></p>
            <button class="gallery-album__arrow gallery-album__arrow--prev" type="button" data-album-step="-1" aria-label="Previous photo">←</button>
            <button class="gallery-album__arrow gallery-album__arrow--next" type="button" data-album-step="1" aria-label="Next photo">→</button>
        </div>

        <div class="gallery-album__thumbs" data-album-thumbs role="group" aria-label="Choose a photo"></div>

        <footer class="gallery-album__footer">
            <button class="gallery-album__destination" type="button" data-album-destination="-1"><span aria-hidden="true">‹</span> <span data-album-destination-label></span></button>
            <span class="gallery-album__footer-note">{{ $footerNote }}</span>
            <button class="gallery-album__destination gallery-album__destination--next" type="button" data-album-destination="1"><span data-album-destination-label></span> <span aria-hidden="true">›</span></button>
        </footer>
    </div>
</dialog>
