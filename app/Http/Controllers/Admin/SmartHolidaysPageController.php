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

    public function edit(): View
    {
        return view('Admin.smart-holidays.edit', [
            'page' => SmartHolidaysPage::query()->firstOrFail(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $page = SmartHolidaysPage::query()->firstOrFail();
        $slides = $this->filledSlides($request);
        $storedPaths = $page->storedImagePaths();

        $validated = Validator::make([...$request->except('journey_slides'), 'journey_slides' => $slides], [
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
            'journey_slides.*.thumb' => ['required', 'string', 'max:40'],
            'popular_eyebrow' => ['required', 'string', 'max:120'],
            'popular_heading' => ['required', 'string', 'max:160'],
            'popular_body' => ['required', 'string', 'max:500'],
            'popular_video' => ['nullable', 'string', 'max:2048', $this->imageSourceRule($storedPaths, 'video')],
            'popular_video_file' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm', 'max:'.SmartHolidaysPage::MAX_VIDEO_KILOBYTES],
            'brands_eyebrow' => ['required', 'string', 'max:120'],
            'brands_heading' => ['required', 'string', 'max:160'],
            'cta_heading' => ['required', 'string', 'max:160'],
            'cta_body' => ['required', 'string', 'max:500'],
            'cta_label' => ['required', 'string', 'max:60'],
            'whatsapp_number' => ['required', 'string', 'max:20', 'regex:/^[0-9]+$/'],
        ], [
            'journey_slides.*.image.required_without' => 'Each slide needs an image URL or an uploaded image.',
            'popular_video_file.mimetypes' => 'The background video must be an MP4 or WebM file.',
            'journey_slides.*.video_file.mimetypes' => 'Each slide video must be an MP4 or WebM file.',
        ])->validate();

        $newPaths = [];

        try {
            unset($validated['hero_image'], $validated['popular_video_file']);
            $validated['popular_video'] ??= null;

            if ($request->hasFile('hero_image')) {
                $validated['hero_image'] = $newPaths[] = $this->storeUpload($request->file('hero_image'));
            }

            if ($request->hasFile('popular_video_file')) {
                $validated['popular_video'] = $newPaths[] = $this->storeUpload($request->file('popular_video_file'));
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

            $page->update($validated);
        } catch (RuntimeException $exception) {
            Storage::disk('public')->delete($newPaths);

            return back()->withInput()->withErrors(['images' => $exception->getMessage()]);
        }

        Storage::disk('public')->delete(array_values(array_diff($storedPaths, $page->fresh()->storedImagePaths())));

        return redirect()->route('admin.smart-holidays.edit')->with('status', 'Smart Holidays homepage updated.');
    }

    /**
     * Slide rows with at least one value, keeping each row's uploaded file.
     *
     * @return array<int, array<string, mixed>>
     */
    private function filledSlides(Request $request): array
    {
        $slides = [];

        foreach ((array) $request->input('journey_slides', []) as $index => $slide) {
            if (! is_array($slide)) {
                continue;
            }

            $slide = array_intersect_key($slide, array_flip(self::SLIDE_FIELDS));
            $files = array_filter([
                'image_file' => $request->file("journey_slides.$index.image_file"),
                'video_file' => $request->file("journey_slides.$index.video_file"),
            ]);
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
