{{-- Floating language switcher on every public page; resources/js/app.js translates the page with Google Translate and remembers the choice in the "googtrans" cookie. --}}
<div class="language-switcher notranslate" translate="no" data-language-switcher>
    <button class="language-switcher-button" type="button" aria-haspopup="true" aria-expanded="false" aria-controls="language-switcher-menu" aria-label="Change language">
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18ZM3.6 9h16.8M3.6 15h16.8M12 3c2.3 2.5 3.4 5.5 3.4 9s-1.1 6.5-3.4 9c-2.3-2.5-3.4-5.5-3.4-9S9.7 5.5 12 3Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span data-language-current>EN</span>
    </button>
    <div class="language-switcher-menu" id="language-switcher-menu" role="menu" hidden>
        <button type="button" role="menuitemradio" aria-checked="true" data-language="en" data-language-short="EN" lang="en"><span>EN</span>English</button>
        <button type="button" role="menuitemradio" aria-checked="false" data-language="zh-CN" data-language-short="中文" lang="zh-CN"><span>中</span>中文</button>
        <button type="button" role="menuitemradio" aria-checked="false" data-language="ms" data-language-short="BM" lang="ms"><span>BM</span>Bahasa Melayu</button>
    </div>
</div>
<div id="google-translate-element" hidden></div>
