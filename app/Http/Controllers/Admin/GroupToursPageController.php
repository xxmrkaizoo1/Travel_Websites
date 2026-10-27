<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GroupToursPage;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use RuntimeException;

class GroupToursPageController extends Controller
{
    private const TOUR_FIELDS = ['name', 'tagline', 'category', 'image', 'alt', 'link_url'];

    /**
     * Edit the Group Tours or Ground Tours page; the route sets $kind.
     */
    public function edit(string $kind = GroupToursPage::KIND_GROUP): View
    {
        return view('Admin.group-tours.edit', [
            'page' => GroupToursPage::forKind($kind),
        ]);
    }

    public function update(Request $request, string $kind = GroupToursPage::KIND_GROUP): RedirectResponse
    {
        $page = GroupToursPage::forKind($kind);
        $tours = $this->filledTours($request);
        $storedPaths = $page->storedImagePaths();

        $validated = Validator::make([...$request->except('tours'), 'tours' => $tours], [
            'meta_title' => ['required', 'string', 'max:120'],
            'meta_description' => ['required', 'string', 'max:500'],
            'hero_eyebrow' => ['required', 'string', 'max:60'],
            'heading' => ['required', 'string', 'max:120'],
            'intro' => ['nullable', 'string', 'max:500'],
            'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'hero_video' => ['nullable', 'string', 'max:2048', $this->imageSourceRule($storedPaths, 'video')],
            'hero_video_file' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm', 'max:'.GroupToursPage::MAX_VIDEO_KILOBYTES],
            'cta_heading' => ['required', 'string', 'max:120'],
            'cta_body' => ['required', 'string', 'max:500'],
            'cta_label' => ['required', 'string', 'max:60'],
            'tours' => ['required', 'array', 'min:1', 'max:'.GroupToursPage::MAX_TOURS],
            'tours.*.name' => ['required', 'string', 'max:40'],
            'tours.*.tagline' => ['nullable', 'string', 'max:120'],
            'tours.*.category' => ['nullable', 'string', 'max:40'],
            'tours.*.image' => ['nullable', 'string', 'max:2048', 'required_without:tours.*.image_file', $this->imageSourceRule($storedPaths)],
            'tours.*.image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'tours.*.alt' => ['required', 'string', 'max:255'],
            'tours.*.link_url' => ['nullable', 'string', 'max:2048', $this->linkTargetRule()],
        ], [
            'tours.*.image.required_without' => 'Each tour card needs an image URL or an uploaded image.',
            'hero_video_file.mimetypes' => 'The banner video must be an MP4 or WebM file.',
        ])->validate();

        $newPaths = [];

        try {
            unset($validated['hero_image'], $validated['hero_video_file']);
            $validated['hero_video'] ??= null;

            if ($request->hasFile('hero_image')) {
                $validated['hero_image'] = $newPaths[] = $this->storeUpload($request->file('hero_image'));
            }

            if ($request->hasFile('hero_video_file')) {
                $validated['hero_video'] = $newPaths[] = $this->storeUpload($request->file('hero_video_file'));
            }

            $validated['tours'] = array_map(function (array $tour) use (&$newPaths): array {
                if (($tour['image_file'] ?? null) instanceof UploadedFile) {
                    $tour['image'] = $newPaths[] = $this->storeUpload($tour['image_file']);
                }

                return array_map(static fn (mixed $value): string => (string) $value, array_intersect_key($tour, array_flip(self::TOUR_FIELDS)) + array_fill_keys(self::TOUR_FIELDS, ''));
            }, $validated['tours']);

            $page->update($validated);
        } catch (RuntimeException $exception) {
            Storage::disk('public')->delete($newPaths);

            return back()->withInput()->withErrors(['images' => $exception->getMessage()]);
        }

        Storage::disk('public')->delete(array_values(array_diff($storedPaths, $page->fresh()->storedImagePaths())));

        return redirect()->route($page->adminRoute('edit'))->with('status', "{$page->label()} page updated.");
    }

    public function editBookingConditions(): View
    {
        return view('Admin.group-tours.booking-conditions', [
            'page' => GroupToursPage::forKind(GroupToursPage::KIND_GROUP),
        ]);
    }

    public function updateBookingConditions(Request $request): RedirectResponse
    {
        $conditions = array_values(array_filter(
            array_map(
                static fn (mixed $condition): array => is_array($condition) ? array_intersect_key($condition, array_flip(['title', 'body'])) : [],
                (array) $request->input('booking_conditions', []),
            ),
            static fn (array $condition): bool => array_filter($condition, static fn (mixed $value): bool => filled($value)) !== [],
        ));

        $validated = Validator::make([...$request->except('booking_conditions'), 'booking_conditions' => $conditions], [
            'booking_heading' => ['required', 'string', 'max:120'],
            'booking_intro' => ['nullable', 'string', 'max:500'],
            'booking_conditions' => ['required', 'array', 'min:1', 'max:'.GroupToursPage::MAX_BOOKING_CONDITIONS],
            'booking_conditions.*.title' => ['required', 'string', 'max:120'],
            'booking_conditions.*.body' => ['required', 'string', 'max:2000'],
        ])->validate();

        GroupToursPage::forKind(GroupToursPage::KIND_GROUP)->update($validated);

        return redirect()->route('admin.group-tours.booking-conditions.edit')->with('status', 'Booking conditions updated.');
    }

    /**
     * Tour rows with at least one value, keeping each row's uploaded image.
     *
     * @return array<int, array<string, mixed>>
     */
    private function filledTours(Request $request): array
    {
        $tours = [];

        foreach ((array) $request->input('tours', []) as $index => $tour) {
            if (! is_array($tour)) {
                continue;
            }

            $tour = array_intersect_key($tour, array_flip(self::TOUR_FIELDS));
            $file = $request->file("tours.$index.image_file");

            if ($file !== null) {
                $tour['image_file'] = $file;
            }

            if ($file !== null || array_filter($tour, static fn (mixed $value): bool => filled($value)) !== []) {
                $tours[] = $tour;
            }
        }

        return $tours;
    }

    /**
     * @param  array<int, string>  $storedPaths
     */
    private function imageSourceRule(array $storedPaths, string $mediaLabel = 'image'): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($storedPaths, $mediaLabel): void {
            $value = (string) $value;
            $isHttpUrl = filter_var($value, FILTER_VALIDATE_URL) !== false && in_array(parse_url($value, PHP_URL_SCHEME), ['http', 'https'], true);
            $isBundled = preg_match('#^images/[A-Za-z0-9/_.-]+$#', $value) === 1 && ! str_contains($value, '..');

            if (! $isHttpUrl && ! $isBundled && ! in_array($value, $storedPaths, true)) {
                $fail("The {$mediaLabel} must be a valid http(s) URL or an uploaded {$mediaLabel}.");
            }
        };
    }

    private function linkTargetRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $value = (string) $value;
            $isSitePath = str_starts_with($value, '/') && ! str_starts_with($value, '//');
            $isHttpUrl = filter_var($value, FILTER_VALIDATE_URL) !== false && in_array(parse_url($value, PHP_URL_SCHEME), ['http', 'https'], true);

            if (! $isSitePath && ! $isHttpUrl) {
                $fail('The link must be a site path such as /vietnam or a full http(s) URL.');
            }
        };
    }

    private function storeUpload(UploadedFile $file): string
    {
        $path = $file->store('group-tours', 'public');

        if (! is_string($path)) {
            throw new RuntimeException('A file could not be saved. Please try again.');
        }

        return $path;
    }
}
