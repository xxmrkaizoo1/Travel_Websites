<svg data-menu-icon="{{ $iconName }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" focusable="false" aria-hidden="true">
    @switch($iconName)
        @case('mosque')
            <path d="M3 20h18M5 20V11h14v9M4 11l8-5 8 5M9 11V9m6 2V9M12 3v3M10 5h4M8 14v2m4-2v2m4-2v2"/>
            @break
        @case('kaaba')
            <path d="m4 7 8-4 8 4v10l-8 4-8-4V7Z"/>
            <path d="m4 7 8 4 8-4M12 11v10M4 10h16"/>
            @break
        @case('crescent')
            <path d="M19.8 15.6A8.5 8.5 0 0 1 8.4 4.2 8.5 8.5 0 1 0 19.8 15.6Z"/>
            <path d="m17 4 .6 1.4L19 6l-1.4.6L17 8l-.6-1.4L15 6l1.4-.6L17 4Z"/>
            @break
        @case('plane')
            <path d="m21 3-7.2 18-3.6-7.2L3 10.2 21 3Z"/>
            <path d="M10.2 13.8 15 9"/>
            @break
        @case('globe')
            <circle cx="12" cy="12" r="9"/>
            <path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>
            @break
        @case('beach')
            <circle cx="17" cy="6" r="2.5"/>
            <path d="M3 13c2-1.3 4-1.3 6 0s4 1.3 6 0 4-1.3 6 0M3 18c2-1.3 4-1.3 6 0s4 1.3 6 0 4-1.3 6 0M6 8c1.2-1.2 2.5-1.8 4-1.8"/>
            @break
        @case('luggage')
            <rect x="4" y="8" width="16" height="13" rx="2"/>
            <path d="M9 8V5a3 3 0 0 1 6 0v3M8 12v5m4-5v5m4-5v5M2 12v5m20-5v5"/>
            @break
        @case('ship')
            <path d="M3 14h18l-2 5H6l-3-5ZM7 14V7h10v7M10 7V4h4v3M2 20c1.5 1 3 1 4.5 0s3-1 4.5 0 3 1 4.5 0 3-1 4.5 0 3 1 4.5 0"/>
            @break
        @case('hotel')
            <path d="M4 21V5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v16M2 21h20M8 7h2m4 0h2M8 11h2m4 0h2M8 15h2m4 0h2m-5 6v-4h4v4"/>
            @break
        @case('star')
            <path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9L12 3Z"/>
            @break
        @case('shopping')
            <path d="M4 8h16l-1 13H5L4 8Z"/>
            <path d="M9 9V6a3 3 0 0 1 6 0v3"/>
            @break
        @default
            <circle cx="12" cy="12" r="9"/>
            <path d="m14.8 9.2-1.7 4-4 1.6 1.7-4 4-1.6Z"/>
    @endswitch
</svg>
