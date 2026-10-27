<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="{{ $page->meta_description }}">
    <title>{{ $page->meta_title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="ticketing-page">
    <a class="skip-link" href="#main">Skip to content</a>

    @include('partials.site-header')

    <main id="main">
        <section class="tk-hero" aria-labelledby="ticketing-title">
            <img class="tk-hero__image" src="{{ $page->heroImageUrl() }}" alt="" aria-hidden="true" fetchpriority="high">
            <div class="tk-hero__inner">
                <span class="tk-hero__eyebrow"><span aria-hidden="true">✈</span> {{ $page->hero_eyebrow }}</span>
                <h1 id="ticketing-title">{{ $page->hero_title }} <strong>{{ $page->hero_highlight }}</strong></h1>
                <p>{{ $page->hero_body }}</p>
                <div class="tk-hero__actions">
                    <a class="tk-button tk-button--light" href="#enquiry">Enquire about a ticket <span aria-hidden="true">↓</span></a>
                    @if ($page->promotions !== [])
                        <a class="tk-button tk-button--ghost" href="#promotions">See fare promotions</a>
                    @endif
                </div>
            </div>
        </section>

        @if ($page->airlines !== [])
            <section class="tk-airlines" aria-labelledby="ticketing-airlines-title">
                <div class="tk-heading">
                    <span class="tk-label">{{ $page->airlines_label }}</span>
                    <h2 id="ticketing-airlines-title">{{ $page->airlines_heading }}</h2>
                </div>
                <div class="tk-airlines__marquee">
                    <ul class="tk-airlines__track">
                        @foreach ([1, 2] as $copy)
                            @foreach ($page->airlines as $airline)
                                <li class="tk-airline" @if ($copy === 2) aria-hidden="true" @endif>
                                    <img src="{{ $page->imageUrl($airline['logo']) }}" alt="{{ $copy === 1 ? $airline['name'] : '' }}" loading="lazy">
                                </li>
                            @endforeach
                        @endforeach
                    </ul>
                </div>
            </section>
        @endif

        @if ($page->promotions !== [])
            <section class="tk-promotions" id="promotions" aria-labelledby="ticketing-promotions-title">
                <div class="tk-heading">
                    <span class="tk-label">{{ $page->promotions_label }}</span>
                    <h2 id="ticketing-promotions-title">{{ $page->promotions_heading }}</h2>
                    @if (filled($page->promotions_body))
                        <p>{{ $page->promotions_body }}</p>
                    @endif
                </div>
                <div class="tk-promotion-grid">
                    @foreach ($page->promotions as $promotion)
                        <article class="tk-promotion">
                            <a class="tk-promotion__poster" href="{{ $page->imageUrl($promotion['image']) }}" target="_blank" rel="noreferrer" aria-label="Open the {{ $promotion['title'] }} poster">
                                <img src="{{ $page->imageUrl($promotion['image']) }}" alt="{{ $promotion['alt'] }}" loading="lazy" decoding="async">
                            </a>
                            <div class="tk-promotion__body">
                                <div class="tk-promotion__top">
                                    <span class="tk-promotion__airline">{{ $promotion['airline'] }}</span>
                                    @if (filled($promotion['fare']))
                                        <span class="tk-promotion__fare">@if (filled($promotion['fare_note']))<small>{{ $promotion['fare_note'] }}</small>@endif<strong>{{ $promotion['fare'] }}</strong></span>
                                    @endif
                                </div>
                                <h3>{{ $promotion['title'] }}</h3>
                                @if (filled($promotion['destinations']))
                                    <p class="tk-promotion__route">@if (filled($promotion['origin']))<span>From {{ $promotion['origin'] }} ✈</span>@endif {{ $promotion['destinations'] }}</p>
                                @endif
                                @if (filled($promotion['booking_period']) || filled($promotion['travel_period']))
                                    <dl class="tk-promotion__periods">
                                        @if (filled($promotion['booking_period']))
                                            <div><dt>Booking</dt><dd>{{ $promotion['booking_period'] }}</dd></div>
                                        @endif
                                        @if (filled($promotion['travel_period']))
                                            <div><dt>Travel</dt><dd>{{ $promotion['travel_period'] }}</dd></div>
                                        @endif
                                    </dl>
                                @endif
                                <a class="tk-promotion__cta" href="{{ $page->promotionEnquiryUrl($promotion) }}" target="_blank" rel="noreferrer">Ask about this fare <span aria-hidden="true">↗</span></a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="tk-enquiry" id="enquiry" aria-labelledby="ticketing-enquiry-title">
            <div class="tk-enquiry__inner">
                <div class="tk-enquiry__intro">
                    <span class="tk-label tk-label--light">{{ $page->enquiry_label }}</span>
                    <h2 id="ticketing-enquiry-title">{{ $page->enquiry_heading }}</h2>
                    <p>{{ $page->enquiry_body }}</p>
                    <ul class="tk-enquiry__contacts">
                        <li><span aria-hidden="true">💬</span><a href="{{ $page->whatsappUrl() }}" target="_blank" rel="noreferrer">WhatsApp +{{ $page->whatsapp_number }}</a></li>
                        <li><span aria-hidden="true">✉️</span><a href="mailto:{{ $page->email }}">{{ $page->email }}</a></li>
                        <li><span aria-hidden="true">📞</span><a href="tel:{{ preg_replace('/[^0-9+]/', '', $page->phone) }}">{{ $page->phone }}</a></li>
                    </ul>
                </div>
                <form class="tk-enquiry__form" action="{{ $page->whatsappUrl() }}" target="_blank" data-ticket-enquiry data-whatsapp="{{ $page->whatsapp_number }}" data-enquiry-url="{{ route('enquiries.store') }}">
                    @csrf
                    <fieldset class="tk-trip-type">
                        <legend>Trip type</legend>
                        <label><input type="radio" name="trip" value="Return" checked data-trip-type> Return</label>
                        <label><input type="radio" name="trip" value="One way" data-trip-type> One way</label>
                    </fieldset>
                    <label class="tk-field"><span>Your name</span><input name="name" required maxlength="80" autocomplete="name"></label>
                    <label class="tk-field"><span>Phone</span><input name="phone" type="tel" required maxlength="30" autocomplete="tel"></label>
                    <label class="tk-field"><span>From</span><input name="from" required maxlength="60" value="Kuala Lumpur"></label>
                    <label class="tk-field"><span>To</span><input name="to" required maxlength="60" placeholder="e.g. Bangkok"></label>
                    <label class="tk-field"><span>Departure date</span><input name="depart" type="date" required></label>
                    <label class="tk-field" data-return-field><span>Return date</span><input name="return" type="date" required></label>
                    <label class="tk-field"><span>Passengers</span><input name="passengers" type="number" min="1" max="50" value="1" required></label>
                    <label class="tk-field"><span>Class</span>
                        <select name="class">
                            <option>Economy</option>
                            <option>Premium Economy</option>
                            <option>Business</option>
                            <option>First</option>
                        </select>
                    </label>
                    <label class="tk-field tk-field--wide"><span>Preferred airline <small>Optional</small></span>
                        <select name="airline">
                            <option value="">Any airline</option>
                            @foreach ($page->airlines as $airline)
                                <option>{{ $airline['name'] }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="tk-field tk-field--wide"><span>Notes <small>Optional</small></span><textarea name="notes" maxlength="500" rows="3" placeholder="Baggage, flexible dates, special requests…"></textarea></label>
                    <button class="tk-button tk-button--primary tk-field--wide" type="submit">Send enquiry on WhatsApp <span aria-hidden="true">↗</span></button>
                </form>
            </div>
        </section>
    </main>

    <footer class="site-footer" id="contact">
        <div class="footer-inner">
            <a class="footer-logo" href="{{ route('home') }}" aria-label="Smart Holidays home">
                <img src="{{ asset('images/smart-holidays-logo.png') }}" alt="Smart Holidays">
            </a>
            <div class="footer-company">
                <h2>ROYSTAR TRAVEL &amp; TOURS SDN BHD</h2>
                <p>No.16A &amp; 16B, Lorong Brunei 3,<br>Off Jalan Pudu, 55100 Kuala Lumpur,<br>WPKL, Malaysia.</p>
                <a href="tel:+60321430188">Tour: +603 2143 0188 / 0288</a>
                <a href="https://wa.me/60165500590" target="_blank" rel="noreferrer">WhatsApp: +6016 550 0590</a>
                <a href="mailto:tour@roystartravel.com.my">tour@roystartravel.com.my</a>
            </div>
            <div class="footer-links">
                <h2>Quick Links</h2>
                <a href="{{ route('home') }}">Smart Holidays Home</a>
                <a href="{{ route('group-tours') }}">Group Tours</a>
                <a href="{{ route('ground-tours') }}">Ground Tours</a>
                <a href="{{ route('domestic-tours') }}">Domestic Tours</a>
                <a href="{{ route('smart-muslim') }}">Smart Muslim Tour</a>
                <a href="{{ route('privacy-policy') }}">Privacy Policy</a>
            </div>
            <div class="footer-brands" aria-label="Our travel brands">
                <h2>Our brands</h2>
                <a href="{{ route('smart-muslim') }}" class="footer-brand-badge"><strong>SMART</strong><span>MUSLIM</span></a>
                <a href="{{ route('home') }}" class="footer-brand-badge footer-brand-main"><strong>SMART</strong><span>HOLIDAYS</span></a>
            </div>
        </div>
        <div class="footer-bottom">
            © {{ date('Y') }} Smart Holidays. All rights reserved.
        </div>
    </footer>
</body>
</html>
