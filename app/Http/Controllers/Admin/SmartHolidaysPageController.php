<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmartHolidaysPage;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use RuntimeException;

class SmartHolidaysPageController extends Controller
{
    private const SLIDE_FIELDS = ['image', 'video', 'alt', 'eyebrow', 'title', 'body', 'link_label', 'link_url', 'thumb'];

    private const POSTER_FIELDS = ['image', 'alt', 'label', 'title', 'price', 'link_url'];

    private const REEL_FIELDS = ['video', 'image', 'label', 'title', 'link_url'];

    public function edit(): View
    {
        return view('Admin.smart-holidays.edit', [
            'page' => SmartHolidaysPage::query()->firstOrFail(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $page = SmartHolidaysPage::query()->firstOrFail();
        $slides = $this->filledSlides($request, 'journey_slides', self::SLIDE_FIELDS, ['image_file', 'video_file']);
        $posters = $this->filledSlides($request, 'poster_slides', self::POSTER_FIELDS, ['image_file']);
        $reels = $this->filledSlides($request, 'reel_slides', self::REEL_FIELDS, ['video_file', 'image_file']);
        $storedPaths = $page->storedImagePaths();

        $validated = Validator::make([...$request->except('journey_slides', 'poster_slides', 'reel_slides'), 'journey_slides' => $slides, 'poster_slides' => $posters, 'reel_slides' => $reels], [
            'meta_title' => ['required', 'string', 'max:120'],
            'meta_description' => ['required', 'string', 'max:500'],
            'hero_eyebrow' => ['required', 'string', 'max:120'],
            'hero_title' => ['required', 'string', 'max:160'],
            'hero_body' => ['required', 'string', 'max:1500'],
            'hero_cta_label' => ['required', 'string', 'max:60'],
            'hero_secondary_label' => ['required', 'string', 'max:60'],
            'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'hero_image_alt' => ['required', 'string', 'max:255'],
            'stats' => ['required', 'array', 'size:'.SmartHolidaysPage::STAT_COUNT],
            'stats.*.title' => ['required', 'string', 'max:40'],
            'stats.*.body' => ['required', 'string', 'max:80'],
            'services_eyebrow' => ['required', 'string', 'max:120'],
            'services_heading' => ['required', 'string', 'max:160'],
            'services' => ['required', 'array', 'size:'.SmartHolidaysPage::SERVICE_COUNT],
            'services.*.title' => ['required', 'string', 'max:80'],
            'services.*.body' => ['required', 'string', 'max:300'],
            'journey_slides' => ['required', 'array', 'min:1', 'max:'.SmartHolidaysPage::MAX_JOURNEY_SLIDES],
            'journey_slides.*.image' => ['nullable', 'string', 'max:2048', 'required_without:journey_slides.*.image_file', $this->imageSourceRule($storedPaths)],
            'journey_slides.*.image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'journey_slides.*.video' => ['nullable', 'string', 'max:2048', $this->imageSourceRule($storedPaths, 'video')],
            'journey_slides.*.video_file' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm', 'max:'.SmartHolidaysPage::MAX_VIDEO_KILOBYTES],
            'journey_slides.*.alt' => ['required', 'string', 'max:255'],
            'journey_slides.*.eyebrow' => ['required', 'string', 'max:120'],
            'journey_slides.*.title' => ['required', 'string', 'max:120'],
            'journey_slides.*.body' => ['required', 'string', 'max:500'],
            'journey_slides.*.link_label' => ['required', 'string', 'max:60'],
            'journey_slides.*.link_url' => ['required', 'string', 'max:2048', $this->linkTargetRule()],
            'journey_slides.*.thumb' => ['nullable', 'string', 'max:40'],
            'posters_eyebrow' => ['required', 'string', 'max:120'],
            'posters_heading' => ['required', 'string', 'max:160'],
            'poster_slides' => ['array', 'max:'.SmartHolidaysPage::MAX_POSTER_SLIDES],
            'poster_slides.*.image' => ['nullable', 'string', 'max:2048', 'required_without:poster_slides.*.image_file', $this->imageSourceRule($storedPaths)],
            'poster_slides.*.image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'poster_slides.*.alt' => ['required', 'string', 'max:255'],
            'poster_slides.*.label' => ['nullable', 'string', 'max:40'],
            'poster_slides.*.title' => ['required', 'string', 'max:120'],
            'poster_slides.*.price' => ['nullable', 'string', 'max:40'],
            'poster_slides.*.link_url' => ['required', 'string', 'max:2048', $this->linkTargetRule()],
            'reels_eyebrow' => ['required', 'string', 'max:120'],
            'reels_heading' => ['required', 'string', 'max:160'],
            'reel_slides' => ['array', 'max:'.SmartHolidaysPage::MAX_REEL_SLIDES],
            'reel_slides.*.video' => ['nullable', 'string', 'max:2048', 'required_without:reel_slides.*.video_file', $this->imageSourceRule($storedPaths, 'video')],
            'reel_slides.*.video_file' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm', 'max:'.SmartHolidaysPage::MAX_VIDEO_KILOBYTES],
            'reel_slides.*.image' => ['nullable', 'string', 'max:2048', $this->imageSourceRule($storedPaths)],
            'reel_slides.*.image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'reel_slides.*.label' => ['nullable', 'string', 'max:40'],
            'reel_slides.*.title' => ['required', 'string', 'max:120'],
            'reel_slides.*.link_url' => ['nullable', 'string', 'max:2048', $this->linkTargetRule()],
            'popular_eyebrow' => ['required', 'string', 'max:120'],
            'popular_heading' => ['required', 'string', 'max:160'],
            'popular_body' => ['required', 'string', 'max:500'],
            'popular_video' => ['nullable', 'string', 'max:2048', $this->imageSourceRule($storedPaths, 'video')],
            'popular_video_file' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm', 'max:'.SmartHolidaysPage::MAX_VIDEO_KILOBYTES],
            'holidays_popular_video' => ['nullable', 'string', 'max:2048', $this->imageSourceRule($storedPaths, 'video')],
            'holidays_popular_video_file' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm', 'max:'.SmartHolidaysPage::MAX_VIDEO_KILOBYTES],
            'holidays_popular_image' => ['nullable', 'string', 'max:2048', $this->imageSourceRule($storedPaths)],
            'holidays_popular_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'brands_eyebrow' => ['required', 'string', 'max:120'],
            'brands_heading' => ['required', 'string', 'max:160'],
            'cta_heading' => ['required', 'string', 'max:160'],
            'cta_body' => ['required', 'string', 'max:500'],
            'cta_label' => ['required', 'string', 'max:60'],
            'whatsapp_number' => ['required', 'string', 'max:20', 'regex:/^[0-9]+$/'],
        ], [
            'journey_slides.*.image.required_without' => 'Each slide needs an image URL or an uploaded image.',
            'poster_slides.*.image.required_without' => 'Each poster needs an image URL or an uploaded image.',
            'reel_slides.*.video.required_without' => 'Each short video needs a video URL or an uploaded video.',
            'reel_slides.*.video_file.mimetypes' => 'Each short video must be an MP4 or WebM file.',
            'popular_video_file.mimetypes' => 'The background video must be an MP4 or WebM file.',
            'holidays_popular_video_file.mimetypes' => 'The background video must be an MP4 or WebM file.',
            'journey_slides.*.video_file.mimetypes' => 'Each slide video must be an MP4 or WebM file.',
        ])->validate();

        $newPaths = [];

        try {
            unset($validated['hero_image'], $validated['popular_video_file'], $validated['holidays_popular_video_file'], $validated['holidays_popular_image_file']);
            $validated['popular_video'] ??= null;
            $validated['holidays_popular_video'] ??= null;
            $validated['holidays_popular_image'] ??= null;

            if ($request->hasFile('hero_image')) {
                $validated['hero_image'] = $newPaths[] = $this->storeUpload($request->file('hero_image'));
            }

            if ($request->hasFile('popular_video_file')) {
                $validated['popular_video'] = $newPaths[] = $this->storeUpload($request->file('popular_video_file'));
            }

            if ($request->hasFile('holidays_popular_video_file')) {
                $validated['holidays_popular_video'] = $newPaths[] = $this->storeUpload($request->file('holidays_popular_video_file'));
            }

            if ($request->hasFile('holidays_popular_image_file')) {
                $validated['holidays_popular_image'] = $newPaths[] = $this->storeUpload($request->file('holidays_popular_image_file'));
            }

            $validated['journey_slides'] = array_map(function (array $slide) use (&$newPaths): array {
                if (($slide['image_file'] ?? null) instanceof UploadedFile) {
                    $slide['image'] = $newPaths[] = $this->storeUpload($slide['image_file']);
                }

                if (($slide['video_file'] ?? null) instanceof UploadedFile) {
                    $slide['video'] = $newPaths[] = $this->storeUpload($slide['video_file']);
                }

                return array_map(static fn (mixed $value): string => (string) $value, array_intersect_key($slide, array_flip(self::SLIDE_FIELDS)) + array_fill_keys(self::SLIDE_FIELDS, ''));
            }, $validated['journey_slides']);

            $validated['poster_slides'] = array_map(function (array $poster) use (&$newPaths): array {
                if (($poster['image_file'] ?? null) instanceof UploadedFile) {
                    $poster['image'] = $newPaths[] = $this->storeUpload($poster['image_file']);
                }

                return array_map(static fn (mixed $value): string => (string) $value, array_intersect_key($poster, array_flip(self::POSTER_FIELDS)) + array_fill_keys(self::POSTER_FIELDS, ''));
            }, $validated['poster_slides'] ?? []);

            $validated['reel_slides'] = array_map(function (array $reel) use (&$newPaths): array {
                if (($reel['video_file'] ?? null) instanceof UploadedFile) {
                    $reel['video'] = $newPaths[] = $this->storeUpload($reel['video_file']);
                }

                if (($reel['image_file'] ?? null) instanceof UploadedFile) {
                    $reel['image'] = $newPaths[] = $this->storeUpload($reel['image_file']);
                }

                return array_map(static fn (mixed $value): string => (string) $value, array_intersect_key($reel, array_flip(self::REEL_FIELDS)) + array_fill_keys(self::REEL_FIELDS, ''));
            }, $validated['reel_slides'] ?? []);

            $page->update($validated);
        } catch (RuntimeException $exception) {
            Storage::disk('public')->delete($newPaths);

            return back()->withInput()->withErrors(['images' => $exception->getMessage()]);
        }

        Storage::disk('public')->delete(array_values(array_diff($storedPaths, $page->fresh()->storedImagePaths())));

        return redirect()->route('admin.smart-holidays.edit')->with('status', 'Smart Holidays homepage updated.');
    }

    /**
     * Slide rows with at least one value, keeping each row's uploaded files.
     *
     * @param  array<int, string>  $fields
     * @param  array<int, string>  $fileFields
     * @return array<int, array<string, mixed>>
     */
    private function filledSlides(Request $request, string $input, array $fields, array $fileFields): array
    {
        $slides = [];

        foreach ((array) $request->input($input, []) as $index => $slide) {
            if (! is_array($slide)) {
                continue;
            }

            $slide = array_intersect_key($slide, array_flip($fields));
            $files = array_filter(array_combine($fileFields, array_map(
                static fn (string $fileField): mixed => $request->file("$input.$index.$fileField"),
                $fileFields,
            )));
            $slide = [...$slide, ...$files];

            if ($files !== [] || array_filter($slide, static fn (mixed $value): bool => filled($value)) !== []) {
                $slides[] = $slide;
            }
        }

        return $slides;
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
        $path = $file->store('smart-holidays', 'public');

        if (! is_string($path)) {
            throw new RuntimeException('A file could not be saved. Please try again.');
        }

        return $path;
    }
}
