{{-- Line icon for the client portal. Expects $name. --}}
<svg class="cp-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('home')
            <path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V20h5v-6h4v6h5V9.5"/>
            @break
        @case('plane')
            <path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.2c.4-.3.6-.7.5-1.2z"/>
            @break
        @case('chat')
            <path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.6A8 8 0 1 1 21 12z"/><path d="M8.5 11h7M8.5 14.5h4"/>
            @break
        @case('user')
            <circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>
            @break
        @case('compass')
            <circle cx="12" cy="12" r="9"/><path d="m15.5 8.5-2 5-5 2 2-5z"/>
            @break
        @case('calendar')
            <rect x="3.5" y="5" width="17" height="15.5" rx="2.5"/><path d="M3.5 10h17M8 3v4M16 3v4"/>
            @break
        @case('users')
            <circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 4.6a3.5 3.5 0 0 1 0 6.8M18 14a6.5 6.5 0 0 1 3.5 6"/>
            @break
        @case('suitcase')
            <rect x="3" y="7" width="18" height="13" rx="2.5"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2M3 12.5h18"/>
            @break
        @case('whatsapp')
            <path d="M20 11.6A8.2 8.2 0 0 1 8 18.9L4 20l1.1-3.9A8.2 8.2 0 1 1 20 11.6z"/><path d="M9.2 8.6c.3 2.6 2.4 5 5.3 5.6l.9-1.3-1.7-.9-.8.6a4.5 4.5 0 0 1-2-2l.6-.8-.8-1.8z"/>
            @break
        @case('logout')
            <path d="M15 17l5-5-5-5M20 12H9M12 21H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h6"/>
            @break
        @case('arrow')
            <path d="M5 12h14M13 6l6 6-6 6"/>
            @break
        @case('plus')
            <path d="M12 5v14M5 12h14"/>
            @break
        @case('shield')
            <path d="M12 3 4.5 6v5.5c0 4.6 3.2 8.4 7.5 9.5 4.3-1.1 7.5-4.9 7.5-9.5V6z"/><path d="m9 12 2 2 4-4"/>
            @break
    @endswitch
</svg>
