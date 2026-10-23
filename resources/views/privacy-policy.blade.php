<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="How Smart Muslim by Roystar Travel &amp; Tours collects, uses and protects your personal data.">
    <title>Privacy Policy | Smart Muslim by Roystar Travel &amp; Tours</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .privacy-hero { padding: 5rem 1.5rem 3.5rem; text-align: center; color: #fff; background: linear-gradient(135deg, #0b2a4a 0%, #1e4fa8 60%, #3e5dd2 100%); }
        .privacy-hero span { display: inline-block; margin-bottom: 1rem; padding: .4rem .9rem; border-radius: 999px; background: rgba(255, 255, 255, .14); font-size: .78rem; font-weight: 700; letter-spacing: .22em; }
        .privacy-hero h1 { margin: 0 0 .75rem; font-size: clamp(2rem, 4.5vw, 3.2rem); font-weight: 800; }
        .privacy-hero p { max-width: 560px; margin: 0 auto; color: rgba(255, 255, 255, .85); line-height: 1.6; }
        .privacy-wrap { display: grid; grid-template-columns: 250px minmax(0, 1fr); gap: 2.5rem; max-width: 1100px; margin: 0 auto; padding: 3.5rem 1.5rem 5rem; }
        .privacy-toc { position: sticky; top: 100px; align-self: start; padding: 1.25rem; border: 1px solid #e6eaf2; border-radius: 16px; background: #f6f8fb; }
        .privacy-toc h2 { margin: 0 0 .75rem; font-size: .8rem; letter-spacing: .18em; color: #3e5dd2; }
        .privacy-toc ol { display: grid; gap: .35rem; margin: 0; padding: 0; list-style: none; }
        .privacy-toc a { display: block; padding: .4rem .6rem; border-radius: 8px; color: #3f536b; font-size: .9rem; line-height: 1.35; text-decoration: none; }
        .privacy-toc a:hover { background: #e4ecfb; color: #1e4fa8; }
        .privacy-body { min-width: 0; color: #3f4a5c; line-height: 1.75; }
        .privacy-updated { margin: 0 0 2rem; color: #7a8699; font-size: .9rem; }
        .privacy-body section { margin-bottom: 2.25rem; padding-bottom: 2rem; border-bottom: 1px solid #edf0f6; scroll-margin-top: 100px; }
        .privacy-body h2 { margin: 0 0 .75rem; font-size: 1.35rem; color: #14213d; }
        .privacy-body p { margin: 0 0 .85rem; }
        .privacy-body ul { margin: 0 0 .85rem; padding-left: 1.25rem; }
        .privacy-body li { margin-bottom: .4rem; }
        .privacy-body a { color: #1e4fa8; overflow-wrap: anywhere; }
        .privacy-contact { padding: 1.5rem; border-radius: 16px; background: #f6f8fb; }
        @media (max-width: 860px) { .privacy-wrap { grid-template-columns: 1fr; padding-top: 2rem; } .privacy-toc { position: static; } }
    </style>
</head>
<body class="island-page">
    <a class="skip-link" href="#main">Skip to content</a>

    @include('partials.site-header')

    <main id="main">
        <header class="privacy-hero">
            <span>LEGAL</span>
            <h1>Privacy Policy</h1>
            <p>How Roystar Travel &amp; Tours Sdn Bhd (Smart Muslim / Smart Holidays) collects, uses and protects your personal data.</p>
        </header>

        <div class="privacy-wrap">
            <nav class="privacy-toc" aria-label="Privacy policy sections">
                <h2>ON THIS PAGE</h2>
                <ol>
                    <li><a href="#collect">Information we collect</a></li>
                    <li><a href="#use">How we use it</a></li>
                    <li><a href="#share">Sharing your data</a></li>
                    <li><a href="#cookies">Cookies</a></li>
                    <li><a href="#security">Security &amp; retention</a></li>
                    <li><a href="#rights">Your rights</a></li>
                    <li><a href="#changes">Changes to this policy</a></li>
                    <li><a href="#contact">Contact us</a></li>
                </ol>
            </nav>

            <article class="privacy-body">
                <p class="privacy-updated">Last updated: {{ date('j F Y') }}</p>

                <section id="intro">
                    <p>Roystar Travel &amp; Tours Sdn Bhd (“we”, “us”) operates Smart Muslim and Smart Holidays. We respect your privacy and handle your personal data in line with Malaysia’s Personal Data Protection Act 2010 (PDPA). This policy explains what we collect when you use our website or enquire about our tours, and how we look after it.</p>
                </section>

                <section id="collect">
                    <h2>1. Information we collect</h2>
                    <ul>
                        <li><strong>Contact details</strong> such as your name, phone number, email address and company name when you contact us or request a quotation.</li>
                        <li><strong>Travel details</strong> such as passport information, travel dates, group size, dietary needs and special requests, when you book or ask us to plan a trip.</li>
                        <li><strong>Account details</strong> if you sign in, including your name, email address and an encrypted password.</li>
                        <li><strong>Technical data</strong> such as your browser, device and pages visited, collected through cookies and server logs.</li>
                    </ul>
                </section>

                <section id="use">
                    <h2>2. How we use your information</h2>
                    <ul>
                        <li>To reply to enquiries and prepare quotations and itineraries.</li>
                        <li>To make and manage bookings with airlines, hotels, transport and other suppliers.</li>
                        <li>To send booking confirmations, travel updates and important service notices.</li>
                        <li>To improve our website, tours and customer service.</li>
                        <li>To meet legal and regulatory obligations.</li>
                    </ul>
                </section>

                <section id="share">
                    <h2>3. Sharing your data</h2>
                    <p>We do not sell your personal data. We share it only when needed to deliver your trip, for example with airlines, hotels, tour guides, visa and insurance providers, or when the law requires it. These parties are expected to protect your data and use it only for the purpose we shared it.</p>
                    <p>Some suppliers are located outside Malaysia, so your data may be transferred overseas for your booking.</p>
                </section>

                <section id="cookies">
                    <h2>4. Cookies</h2>
                    <p>Our website uses cookies to keep you signed in, remember preferences and understand how the site is used. You can block or delete cookies in your browser settings, but some parts of the site may not work properly without them.</p>
                </section>

                <section id="security">
                    <h2>5. Security and retention</h2>
                    <p>We use reasonable technical and organisational measures to protect your data against loss, misuse and unauthorised access. We keep personal data only as long as needed for the purposes above or as required by law, then delete or anonymise it.</p>
                </section>

                <section id="rights">
                    <h2>6. Your rights</h2>
                    <p>You may ask to access or correct the personal data we hold about you, withdraw your consent, or ask us to stop using your data for marketing. Contact us using the details below and we will respond as soon as reasonably possible.</p>
                </section>

                <section id="changes">
                    <h2>7. Changes to this policy</h2>
                    <p>We may update this policy from time to time. The latest version is always published on this page with its date.</p>
                </section>

                <section id="contact">
                    <h2>8. Contact us</h2>
                    <div class="privacy-contact">
                        <p><strong>Roystar Travel &amp; Tours Sdn Bhd</strong><br>
                            No.16A &amp; 16B, Lorong Brunei 3, Off Jalan Pudu,<br>55100 Kuala Lumpur, WPKL, Malaysia.</p>
                        <p>Tel: <a href="tel:+60321430188">+603 2143 0188</a> / <a href="tel:+60321430288">0288</a><br>
                            Email: <a href="mailto:smart_muslim@roystartravel.com.my">smart_muslim@roystartravel.com.my</a></p>
                    </div>
                </section>
            </article>
        </div>
    </main>

    <footer class="site-footer">
        <div class="footer-inner">
            <a class="footer-logo" href="{{ route('home') }}" aria-label="Smart Holidays home">
                <img src="{{ asset('images/smart-holidays-logo.png') }}" alt="Smart Holidays">
            </a>
            <div class="footer-company">
                <h2>ROYSTAR TRAVEL &amp; TOURS SDN BHD</h2>
                <p>No.16A &amp; 16B, Lorong Brunei 3,<br>Off Jalan Pudu, 55100 Kuala Lumpur,<br>WPKL, Malaysia.</p>
                <a href="tel:+60321430188">Tour: +603 2143 0188 / 0288</a>
                <a href="mailto:smart_muslim@roystartravel.com.my">smart_muslim@roystartravel.com.my</a>
            </div>
            <div class="footer-links">
                <h2>Quick Links</h2>
                <a href="{{ route('smart-muslim') }}">Smart Muslim Home</a>
                <a href="{{ route('china-muslim') }}">China Muslim</a>
                <a href="{{ route('tour-package-private') }}">Private Tours</a>
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
