@foreach ($customTourPagesByParent->get($parentSlug, collect()) as $childPage)
    <a class="admin-nav-link admin-subnav-link admin-subnav-child" style="--nest: {{ $depth }}" data-context-menu="tour-package-menu" data-page-name="{{ $childPage->name }}" data-page-slug="{{ $childPage->slug }}" data-edit-url="{{ route('admin.tour-packages.edit', $childPage->slug) }}" data-view-url="{{ route('tour-package.show', $childPage->slug) }}" data-delete-url="{{ route('admin.tour-packages.destroy', $childPage->slug) }}" href="{{ route('admin.tour-packages.edit', $childPage->slug) }}">{{ $childPage->name }} <small>{{ $childPage->kind === 'package' ? 'Package' : 'Intro' }}</small></a>
    @include('Admin.partials.tour-page-children', ['parentSlug' => $childPage->slug, 'depth' => $depth + 1])
@endforeach
