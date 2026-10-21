@foreach ($customTourPagesByParent->get($parentSlug, collect()) as $childPage)
    @php($childPages = $customTourPagesByParent->get($childPage->slug, collect()))
    @if ($childPages->isEmpty())
        <a class="{{ request()->is('tour/'.$childPage->slug) ? 'is-current' : '' }}" href="{{ route('tour-package.show', $childPage->slug) }}">{{ $childPage->name }}</a>
    @else
        <div class="nav-subitem has-submenu destination-nav">
            <a class="destination-link{{ request()->is('tour/'.$childPage->slug) ? ' is-current' : '' }}" href="{{ route('tour-package.show', $childPage->slug) }}">{{ $childPage->name }}</a>
            <button class="submenu-trigger destination-toggle" type="button" aria-label="Show {{ $childPage->name }} destinations" aria-expanded="false" aria-controls="{{ $childPage->slug }}-destination-menu">
                <span class="submenu-arrow" aria-hidden="true">›</span>
            </button>
            <div class="submenu-menu" id="{{ $childPage->slug }}-destination-menu">
                @include('partials.tour-package-submenu', ['parentSlug' => $childPage->slug])
            </div>
        </div>
    @endif
@endforeach
