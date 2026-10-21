<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TourPackagePage;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class TourPackagePageController extends Controller
{
    public const MAX_SLIDES = 12;

    private const VIRTUAL_PARENT_PAGES = [
        'island-honeymoon' => 'Island & Honeymoon',
    ];

    public function create(Request $request): View
    {
        $kind = $request->query('kind');
        $parent = $this->findParent($request->query('parent'));

        return view('Admin.tour-packages.create', [
            'parent' => $parent,
            'kind' => in_array($kind, [TourPackagePage::KIND_INTRODUCTION, TourPackagePage::KIND_PACKAGE], true)
                ? $kind
                : TourPackagePage::KIND_INTRODUCTION,
        ]);
    }

    private function findParent(mixed $slug): ?TourPackagePage
    {
        if (is_string($slug) && isset(self::VIRTUAL_PARENT_PAGES[$slug])) {
            return (new TourPackagePage)->forceFill([
                'slug' => $slug,
                'name' => self::VIRTUAL_PARENT_PAGES[$slug],
            ]);
        }

        return is_string($slug) && $slug !== ''
            ? TourPackagePage::query()->where('slug', $slug)->first()
            : null;
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'kind' => ['required', Rule::in([TourPackagePage::KIND_INTRODUCTION, TourPackagePage::KIND_PACKAGE])],
            'parent_slug' => ['nullable', 'string', $this->parentSlugRule()],
            'template' => ['nullable', 'string', Rule::in(array_keys(TourPackagePage::TEMPLATES[$request->input('kind')] ?? []))],
        ]);

        $page = DB::transaction(function () use ($validated): TourPackagePage {
            $baseSlug = Str::slug($validated['name']) ?: 'tour';
            $slug = $baseSlug;
            $suffix = 2;

            while (TourPackagePage::query()->where('slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.$suffix++;
            }

            $page = new TourPackagePage(TourPackagePage::starterContent($validated['kind'], trim($validated['name']), $validated['template'] ?? null));
            $page->slug = $slug;
            $page->parent_slug = $validated['parent_slug'] ?? null;
            $page->is_custom = true;
            $page->save();

            return $page;
        });

        return redirect()->route('admin.tour-packages.edit', $page)
            ->with('status', $page->name.' page created. Edit the starter content below.');
    }

    public function edit(TourPackagePage $page): View
    {
        return view('Admin.tour-packages.edit', [
            'page' => $page,
            'slides' => $page->slides ?? [],
            'package' => $page->package_data ?? [],
            'maxSlides' => self::MAX_SLIDES,
            'parentOptions' => $page->is_custom ? $this->parentOptions($page) : [],
        ]);
    }

    public function move(Request $request, TourPackagePage $page): JsonResponse
    {
        abort_unless($page->is_custom, 404);

        $validated = $request->validate([
            'parent_slug' => ['present', 'nullable', 'string', $this->parentSlugRule($page)],
        ]);

        $page->update(['parent_slug' => $validated['parent_slug']]);

        return response()->json(['parent_slug' => $page->parent_slug]);
    }

    public function update(Request $request, TourPackagePage $page): RedirectResponse
    {
        $this->prepareInput($request, $page);
        $validated = $this->validatePage($request, $page);

        $disk = Storage::disk('public');
        $newPaths = [];

        try {
            foreach (['hero_image', 'intro_image'] as $field) {
                unset($validated[$field]);

                if ($request->hasFile($field)) {
                    $validated[$field] = $newPaths[] = $this->storeImage($request->file($field));
                }
            }

            if ($page->isPackage()) {
                $validated['package_data'] = $this->normalisePackage($request, $validated['package'], $newPaths);
                $validated['package_data']['template'] = $page->package_data['template'] ?? TourPackagePage::defaultTemplate(TourPackagePage::KIND_PACKAGE);
                unset($validated['package']);
            } elseif ($page->isSeries()) {
                $validated['package_data'] = ['series' => $this->normaliseSeries($request, $validated['series'], $newPaths)];
                $validated['slides'] = [];
                unset($validated['series']);
            } else {
                $validated['slides'] = $this->resolveImageRows($request, $validated['slides'], 'slides', $newPaths, [
                    'alt', 'eyebrow', 'title', 'body', 'link_label', 'link_url',
                ]);
            }

            $previousPaths = $page->storedImagePaths();

            DB::transaction(fn () => $page->update($validated));
        } catch (Throwable $exception) {
            $disk->delete($newPaths);

            if ($exception instanceof RuntimeException) {
                return back()->withInput()->withErrors(['images' => $exception->getMessage()]);
            }

            throw $exception;
        }

        $this->deleteImages(array_diff($previousPaths, $page->storedImagePaths()), $page);

        return redirect()->route('admin.tour-packages.edit', $page)
            ->with('status', $page->name.' page updated successfully.');
    }

    public function destroy(TourPackagePage $page): RedirectResponse
    {
        abort_unless($page->is_custom, 403);

        $paths = $page->storedImagePaths();
        TourPackagePage::query()->where('parent_slug', $page->slug)->update(['parent_slug' => $page->parent_slug]);
        $page->delete();
        $this->deleteImages($paths, $page);

        return redirect()->route('admin.dashboard')
            ->with('status', $page->name.' page deleted.');
    }

    /**
     * @param  array<int, string>  $paths
     */
    private function deleteImages(array $paths, TourPackagePage $page): void
    {
        $disk = Storage::disk('public');

        foreach ($paths as $path) {
            if (! $disk->delete($path) && $disk->exists($path)) {
                Log::warning('Could not remove an unused tour package image.', [
                    'path' => $path,
                    'page' => $page->slug,
                ]);
            }
        }
    }

    private function storeImage(UploadedFile $file): string
    {
        $path = $file->store('tour-packages', 'public');

        if (! is_string($path)) {
            throw new RuntimeException('An image could not be saved. Please try again.');
        }

        return $path;
    }

    /**
     * Replace uploaded files with stored paths and keep only the allowed keys of each row.
     *
     * @param  array<int|string, array<string, mixed>>  $rows
     * @param  array<int, string>  $newPaths
     * @param  array<int, string>  $keys
     * @return array<int, array<string, mixed>>
     */
    private function resolveImageRows(Request $request, array $rows, string $inputKey, array &$newPaths, array $keys): array
    {
        $resolved = [];

        foreach ($rows as $index => $row) {
            $file = $request->file("$inputKey.$index.image_file");
            $item = ['image' => $file instanceof UploadedFile ? $newPaths[] = $this->storeImage($file) : $row['image']];

            foreach ($keys as $key) {
                $item[$key] = $row[$key] ?? '';
            }

            $resolved[] = $item;
        }

        return $resolved;
    }

    /**
     * @param  array<string, mixed>  $package
     * @param  array<int, string>  $newPaths
     * @return array<string, mixed>
     */
    private function normalisePackage(Request $request, array $package, array &$newPaths): array
    {
        $itineraries = [];

        foreach ($package['itineraries'] as $itinerary) {
            $itineraries[] = [
                'duration' => $itinerary['duration'],
                'title' => $itinerary['title'],
                'summary' => $itinerary['summary'],
                'featured' => filter_var($itinerary['featured'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'days' => array_values(array_map(fn (array $day): array => [
                    'title' => $day['title'],
                    'body' => $day['body'],
                    'meals' => $day['meals'] ?? '',
                ], $itinerary['days'])),
                'cta_label' => $itinerary['cta_label'],
                'cta_url' => $itinerary['cta_url'],
            ];
        }

        return [
            'itineraries_eyebrow' => $package['itineraries_eyebrow'],
            'itineraries_headline' => $package['itineraries_headline'],
            'itineraries_body' => $package['itineraries_body'],
            'itineraries' => $itineraries,
            'gallery_eyebrow' => $package['gallery_eyebrow'],
            'gallery_headline' => $package['gallery_headline'],
            'gallery_body' => $package['gallery_body'],
            'gallery' => $this->resolveImageRows($request, $package['gallery'] ?? [], 'package.gallery', $newPaths, ['alt', 'eyebrow', 'title']),
            'details_eyebrow' => $package['details_eyebrow'],
            'details_headline' => $package['details_headline'],
            'details_body' => $package['details_body'],
            'inclusions' => array_values($package['inclusions']),
            'exclusions' => array_values($package['exclusions']),
            'accommodation_eyebrow' => $package['accommodation_eyebrow'],
            'accommodation_headline' => $package['accommodation_headline'],
            'accommodation_body' => $package['accommodation_body'],
            'hotels' => array_values(array_map(fn (array $hotel): array => [
                'label' => $hotel['label'],
                'name' => $hotel['name'],
                'address' => $hotel['address'],
                'phone' => $hotel['phone'] ?? '',
            ], $package['hotels'] ?? [])),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $series
     * @param  array<int, string>  $newPaths
     * @return array<int, array<string, mixed>>
     */
    private function normaliseSeries(Request $request, array $series, array &$newPaths): array
    {
        $destinations = [];

        foreach ($series as $index => $destination) {
            $flagFile = $request->file("series.$index.flag_file");

            $destinations[] = [
                'name' => $destination['name'],
                'country' => $destination['country'],
                'flag' => $flagFile instanceof UploadedFile ? $newPaths[] = $this->storeImage($flagFile) : ($destination['flag'] ?? ''),
                'tagline' => $destination['tagline'],
                'intro' => $destination['intro'],
                'advantages' => array_values(array_map(fn (array $advantage): array => [
                    'icon' => $advantage['icon'] ?? '',
                    'title' => $advantage['title'],
                    'body' => $advantage['body'],
                ], $destination['advantages'])),
                'gallery' => $this->resolveImageRows($request, $destination['gallery'] ?? [], "series.$index.gallery", $newPaths, ['alt', 'caption']),
            ];
        }

        return $destinations;
    }

    private function prepareInput(Request $request, TourPackagePage $page): void
    {
        if ($page->isSeries()) {
            $series = $request->input('series');

            if (is_array($series)) {
                foreach ($series as $index => $destination) {
                    if (! is_array($destination) || trim((string) ($destination['name'] ?? '')) === '' && trim((string) ($destination['country'] ?? '')) === '') {
                        unset($series[$index]);

                        continue;
                    }

                    $series[$index]['advantages'] = $this->pruneRows($request, $destination['advantages'] ?? [], "series.$index.advantages");
                    $series[$index]['gallery'] = $this->pruneRows($request, $destination['gallery'] ?? [], "series.$index.gallery");
                }

                $request->merge(['series' => $series]);
            }

            return;
        }

        if (! $page->isPackage()) {
            $request->merge(['slides' => $this->pruneRows($request, $request->input('slides'), 'slides')]);

            return;
        }

        $package = $request->input('package');

        if (! is_array($package)) {
            return;
        }

        foreach (['inclusions', 'exclusions'] as $list) {
            $package[$list] = is_array($package[$list] ?? null)
                ? array_values(array_filter(
                    $package[$list],
                    fn (mixed $item): bool => is_string($item) && trim($item) !== '',
                ))
                : [];
        }

        $package['gallery'] = $this->pruneRows($request, $package['gallery'] ?? [], 'package.gallery');
        $package['hotels'] = $this->pruneRows($request, $package['hotels'] ?? [], 'package.hotels');

        $itineraries = [];

        foreach (is_array($package['itineraries'] ?? null) ? $package['itineraries'] : [] as $index => $itinerary) {
            if (! is_array($itinerary)) {
                continue;
            }

            $itinerary['days'] = $this->pruneRows($request, $itinerary['days'] ?? [], "package.itineraries.$index.days");

            if ($itinerary['days'] === [] && ! $this->hasContent($itinerary, ['featured', 'days'])) {
                continue;
            }

            $itineraries[$index] = $itinerary;
        }

        $package['itineraries'] = $itineraries;
        $request->merge(['package' => $package]);
    }

    /**
     * @return array<int|string, array<string, mixed>>
     */
    private function pruneRows(Request $request, mixed $rows, string $path): array
    {
        if (! is_array($rows)) {
            return [];
        }

        return array_filter(
            $rows,
            fn (mixed $row, mixed $index): bool => is_array($row)
                && ($request->hasFile("$path.$index.image_file") || $this->hasContent($row)),
            ARRAY_FILTER_USE_BOTH,
        );
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<int, string>  $ignore
     */
    private function hasContent(array $row, array $ignore = []): bool
    {
        return collect($row)->except($ignore)->contains(
            fn (mixed $value): bool => is_scalar($value) && trim((string) $value) !== '',
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePage(Request $request, TourPackagePage $page): array
    {
        $storedPaths = $page->storedImagePaths();

        $isHttpUrl = fn (mixed $value): bool => filter_var($value, FILTER_VALIDATE_URL) !== false
            && in_array(parse_url((string) $value, PHP_URL_SCHEME), ['http', 'https'], true);

        $imageSource = function (string $attribute, mixed $value, Closure $fail) use ($storedPaths, $isHttpUrl): void {
            if (! $isHttpUrl($value) && ! in_array($value, $storedPaths, true)) {
                $fail('The image must be a valid http(s) URL or an uploaded image.');
            }
        };

        $linkTarget = function (string $attribute, mixed $value, Closure $fail) use ($isHttpUrl): void {
            $isSitePath = str_starts_with((string) $value, '/') && ! str_starts_with((string) $value, '//');

            if (! $isSitePath && ! $isHttpUrl($value)) {
                $fail('The link must be a site path such as /bali or a full http(s) URL.');
            }
        };

        $imageUpload = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];

        $rules = [
            'meta_title' => ['required', 'string', 'max:160'],
            'meta_description' => ['required', 'string', 'max:255'],
            'hero_eyebrow' => ['required', 'string', 'max:120'],
            'hero_headline' => ['required', 'string', 'max:120'],
            'hero_highlight' => ['required', 'string', 'max:160'],
            'hero_body' => ['required', 'string', 'max:1500'],
            'hero_cta_label' => ['required', 'string', 'max:100'],
            'hero_note' => ['required', 'string', 'max:120'],
            'hero_image' => $imageUpload,
            'intro_eyebrow' => ['required', 'string', 'max:120'],
            'intro_headline' => ['required', 'string', 'max:160'],
            'intro_highlight' => ['required', 'string', 'max:100'],
            'intro_body' => ['required', 'string', 'max:1500'],
            'intro_link_label' => ['required', 'string', 'max:120'],
            'intro_image' => $imageUpload,
            'intro_image_alt' => ['required', 'string', 'max:255'],
            'intro_image_caption' => ['required', 'string', 'max:120'],
            'highlights_eyebrow' => ['required', 'string', 'max:120'],
            'highlights_headline' => ['required', 'string', 'max:160'],
            'highlights_body' => ['required', 'string', 'max:255'],
            'highlights' => ['required', 'array', 'size:3'],
            'highlights.*.title' => ['required', 'string', 'max:100'],
            'highlights.*.body' => ['required', 'string', 'max:500'],
            'contact_eyebrow' => ['required', 'string', 'max:120'],
            'contact_headline' => ['required', 'string', 'max:160'],
            'contact_body' => ['required', 'string', 'max:1500'],
            'contact_cta_label' => ['required', 'string', 'max:100'],
            'contact_href' => ['required', 'url:http,https', 'max:2048'],
        ];

        if ($page->is_custom) {
            $rules['name'] = ['required', 'string', 'max:60'];
            $rules['parent_slug'] = ['nullable', 'string', $this->parentSlugRule($page)];
        }

        $slideRules = fn (string $prefix, bool $withBody, bool $withLink): array => array_filter([
            "$prefix.*.image" => ['nullable', 'string', 'max:2048', "required_without:$prefix.*.image_file", $imageSource],
            "$prefix.*.image_file" => $imageUpload,
            "$prefix.*.alt" => ['required', 'string', 'max:255'],
            "$prefix.*.eyebrow" => ['required', 'string', 'max:120'],
            "$prefix.*.title" => ['required', 'string', 'max:120'],
            "$prefix.*.body" => $withBody ? ['required', 'string', 'max:500'] : null,
            "$prefix.*.link_label" => $withLink ? ['nullable', 'string', 'max:120', "required_with:$prefix.*.link_url"] : null,
            "$prefix.*.link_url" => $withLink ? ['nullable', 'string', 'max:2048', "required_with:$prefix.*.link_label", $linkTarget] : null,
        ]);

        if ($page->isSeries()) {
            $seriesImage = function (string $attribute, mixed $value, Closure $fail) use ($storedPaths, $isHttpUrl): void {
                $isBundled = preg_match('#^images/[A-Za-z0-9/_.-]+$#', (string) $value) === 1 && ! str_contains((string) $value, '..');

                if (! $isHttpUrl($value) && ! $isBundled && ! in_array($value, $storedPaths, true)) {
                    $fail('The image must be a valid http(s) URL or an uploaded image.');
                }
            };

            $rules['highlights'] = ['required', 'array', 'min:3', 'max:6'];
            $rules += [
                'destinations_eyebrow' => ['required', 'string', 'max:120'],
                'destinations_headline' => ['required', 'string', 'max:160'],
                'destinations_body' => ['required', 'string', 'max:255'],
                'series' => ['required', 'array', 'min:1', 'max:6'],
                'series.*.name' => ['required', 'string', 'max:60'],
                'series.*.country' => ['required', 'string', 'max:60'],
                'series.*.tagline' => ['required', 'string', 'max:255'],
                'series.*.intro' => ['required', 'string', 'max:600'],
                'series.*.flag' => ['nullable', 'string', 'max:2048', $seriesImage],
                'series.*.flag_file' => $imageUpload,
                'series.*.advantages' => ['required', 'array', 'min:1', 'max:6'],
                'series.*.advantages.*.icon' => ['nullable', 'string', 'max:16'],
                'series.*.advantages.*.title' => ['required', 'string', 'max:120'],
                'series.*.advantages.*.body' => ['required', 'string', 'max:500'],
                'series.*.gallery' => ['nullable', 'array', 'max:8'],
                'series.*.gallery.*.image' => ['nullable', 'string', 'max:2048', 'required_without:series.*.gallery.*.image_file', $seriesImage],
                'series.*.gallery.*.image_file' => $imageUpload,
                'series.*.gallery.*.alt' => ['required', 'string', 'max:255'],
                'series.*.gallery.*.caption' => ['required', 'string', 'max:120'],
            ];

            return $request->validate($rules);
        }

        if (! $page->isPackage()) {
            $rules += [
                'destinations_eyebrow' => ['required', 'string', 'max:120'],
                'destinations_headline' => ['required', 'string', 'max:160'],
                'destinations_body' => ['required', 'string', 'max:255'],
                'slides' => ['required', 'array', 'min:1', 'max:'.self::MAX_SLIDES],
                ...$slideRules('slides', true, true),
            ];

            return $request->validate($rules);
        }

        $rules += [
            'package' => ['required', 'array'],
            'package.itineraries_eyebrow' => ['required', 'string', 'max:120'],
            'package.itineraries_headline' => ['required', 'string', 'max:160'],
            'package.itineraries_body' => ['required', 'string', 'max:255'],
            'package.itineraries' => ['required', 'array', 'min:1', 'max:8'],
            'package.itineraries.*.duration' => ['required', 'string', 'max:60'],
            'package.itineraries.*.title' => ['required', 'string', 'max:120'],
            'package.itineraries.*.summary' => ['required', 'string', 'max:255'],
            'package.itineraries.*.featured' => ['nullable', 'boolean'],
            'package.itineraries.*.days' => ['required', 'array', 'min:1', 'max:15'],
            'package.itineraries.*.days.*.title' => ['required', 'string', 'max:120'],
            'package.itineraries.*.days.*.body' => ['required', 'string', 'max:1000'],
            'package.itineraries.*.days.*.meals' => ['nullable', 'string', 'max:120'],
            'package.itineraries.*.cta_label' => ['required', 'string', 'max:120'],
            'package.itineraries.*.cta_url' => ['required', 'url:http,https', 'max:2048'],
            'package.gallery_eyebrow' => ['required', 'string', 'max:120'],
            'package.gallery_headline' => ['required', 'string', 'max:160'],
            'package.gallery_body' => ['required', 'string', 'max:255'],
            'package.gallery' => ['nullable', 'array', 'max:'.self::MAX_SLIDES],
            ...$slideRules('package.gallery', false, false),
            'package.details_eyebrow' => ['required', 'string', 'max:120'],
            'package.details_headline' => ['required', 'string', 'max:160'],
            'package.details_body' => ['required', 'string', 'max:255'],
            'package.inclusions' => ['required', 'array', 'min:1', 'max:20'],
            'package.inclusions.*' => ['required', 'string', 'max:255'],
            'package.exclusions' => ['required', 'array', 'min:1', 'max:20'],
            'package.exclusions.*' => ['required', 'string', 'max:255'],
            'package.accommodation_eyebrow' => ['required', 'string', 'max:120'],
            'package.accommodation_headline' => ['required', 'string', 'max:160'],
            'package.accommodation_body' => ['required', 'string', 'max:255'],
            'package.hotels' => ['nullable', 'array', 'max:5'],
            'package.hotels.*.label' => ['required', 'string', 'max:120'],
            'package.hotels.*.name' => ['required', 'string', 'max:120'],
            'package.hotels.*.address' => ['required', 'string', 'max:255'],
            'package.hotels.*.phone' => ['nullable', 'string', 'max:40'],
        ];

        return $request->validate($rules);
    }

    /**
     * @return array<string, string>
     */
    private function parentOptions(TourPackagePage $page): array
    {
        $excludedSlugs = [$page->slug, ...$this->descendantSlugs($page)];
        $pages = TourPackagePage::query()
            ->where(function ($query): void {
                $query->where('is_custom', true)
                    ->orWhereIn('slug', TourPackagePage::SLUGS);
            })
            ->whereNotIn('slug', $excludedSlugs)
            ->orderBy('name')
            ->get(['slug', 'name', 'parent_slug']);
        $namesBySlug = $pages->pluck('name', 'slug')->all();
        $options = ['' => 'Top-level destination', ...self::VIRTUAL_PARENT_PAGES];

        foreach ($pages as $candidate) {
            $parentName = $candidate->parent_slug === 'island-honeymoon'
                ? self::VIRTUAL_PARENT_PAGES['island-honeymoon']
                : ($namesBySlug[$candidate->parent_slug] ?? null);
            $options[$candidate->slug] = $parentName
                ? "{$parentName} / {$candidate->name}"
                : $candidate->name;
        }

        return $options;
    }

    /**
     * @return array<int, string>
     */
    private function descendantSlugs(TourPackagePage $page): array
    {
        $pages = TourPackagePage::query()->get(['slug', 'parent_slug']);
        $descendants = collect();
        $parentSlugs = collect([$page->slug]);

        while ($parentSlugs->isNotEmpty()) {
            $children = $pages->whereIn('parent_slug', $parentSlugs)
                ->pluck('slug')
                ->diff([$page->slug, ...$descendants->all()])
                ->values();

            $descendants = $descendants->merge($children);
            $parentSlugs = $children;
        }

        return $descendants->all();
    }

    private function parentSlugRule(?TourPackagePage $page = null): Closure
    {
        $excludedSlugs = $page
            ? [$page->slug, ...$this->descendantSlugs($page)]
            : [];

        return function (string $attribute, mixed $value, Closure $fail) use ($excludedSlugs): void {
            if ($value === null || $value === '') {
                return;
            }

            if (! is_string($value)) {
                $fail('The selected parent page is invalid.');

                return;
            }

            $isVirtualParent = array_key_exists($value, self::VIRTUAL_PARENT_PAGES);
            $parentExists = $isVirtualParent || TourPackagePage::query()->where('slug', $value)->exists();

            if (! $parentExists) {
                $fail('The selected parent page is invalid.');

                return;
            }

            if (in_array($value, $excludedSlugs, true)) {
                $fail('A page cannot be placed inside itself or one of its own destinations.');
            }
        };
    }
}
