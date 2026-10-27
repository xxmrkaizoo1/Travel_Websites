<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryPage;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use RuntimeException;

class GalleryPageController extends Controller
{
    private const DESTINATION_FIELDS = ['country', 'flag', 'photo', 'photo_alt'];

    private const IMAGE_FIELDS = ['flag', 'photo'];

    public function edit(): View
    {
        return view('Admin.gallery.edit', [
            'page' => GalleryPage::query()->firstOrFail(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $page = GalleryPage::query()->firstOrFail();
        $destinations = $this->filledDestinations($request);
        $storedPaths = $page->storedImagePaths();
        $imageUpload = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];

        $validated = Validator::make([...$request->except('destinations'), 'destinations' => $destinations], [
            'meta_title' => ['required', 'string', 'max:120'],
            'meta_description' => ['required', 'string', 'max:500'],
            'hero_eyebrow' => ['required', 'string', 'max:120'],
            'hero_title' => ['required', 'string', 'max:160'],
            'hero_body' => ['required', 'string', 'max:500'],
            'hero_image' => $imageUpload,
            'moments_eyebrow' => ['required', 'string', 'max:120'],
            'moments_heading' => ['required', 'string', 'max:160'],
            'moments_body' => ['required', 'string', 'max:500'],
            'moments_card_label' => ['required', 'string', 'max:60'],
            'moments_card_eyebrow' => ['required', 'string', 'max:40'],
            'destinations' => ['required', 'array', 'min:1', 'max:'.GalleryPage::MAX_DESTINATIONS],
            'destinations.*.country' => ['required', 'string', 'max:60'],
            'destinations.*.flag' => ['nullable', 'string', 'max:2048', 'required_without:destinations.*.flag_file', $this->imageSourceRule($storedPaths)],
            'destinations.*.flag_file' => $imageUpload,
            'destinations.*.photo' => ['nullable', 'string', 'max:2048', 'required_without:destinations.*.photo_file', $this->imageSourceRule($storedPaths)],
            'destinations.*.photo_file' => $imageUpload,
            'destinations.*.photo_alt' => ['required', 'string', 'max:255'],
            'destinations.*.photos' => ['array', 'max:'.GalleryPage::MAX_ALBUM_PHOTOS],
            'destinations.*.photos.*.image' => ['nullable', 'string', 'max:2048', 'required_without:destinations.*.photos.*.image_file', $this->imageSourceRule($storedPaths)],
            'destinations.*.photos.*.image_file' => $imageUpload,
            'destinations.*.photos.*.caption' => ['nullable', 'string', 'max:80'],
        ], [
            'destinations.*.flag.required_without' => 'Each destination needs a flag image URL or an uploaded flag.',
            'destinations.*.photo.required_without' => 'Each destination needs a photo URL or an uploaded photo.',
            'destinations.*.photos.*.image.required_without' => 'Each popup photo needs an image URL or an uploaded image.',
        ])->validate();

        $newPaths = [];

        try {
            unset($validated['hero_image']);

            if ($request->hasFile('hero_image')) {
                $validated['hero_image'] = $newPaths[] = $this->storeImage($request->file('hero_image'));
            }

            $validated['destinations'] = array_map(function (array $destination) use (&$newPaths): array {
                foreach (self::IMAGE_FIELDS as $field) {
                    if (($destination["{$field}_file"] ?? null) instanceof UploadedFile) {
                        $destination[$field] = $newPaths[] = $this->storeImage($destination["{$field}_file"]);
                    }
                }

                $photos = array_map(function (array $photo) use (&$newPaths): array {
                    $image = ($photo['image_file'] ?? null) instanceof UploadedFile
                        ? $newPaths[] = $this->storeImage($photo['image_file'])
                        : (string) $photo['image'];

                    return ['image' => $image, 'caption' => (string) ($photo['caption'] ?? '')];
                }, $destination['photos'] ?? []);

                return [
                    ...array_map(static fn (mixed $value): string => (string) $value, array_intersect_key($destination, array_flip(self::DESTINATION_FIELDS)) + array_fill_keys(self::DESTINATION_FIELDS, '')),
                    'photos' => $photos,
                ];
            }, $validated['destinations']);

            $page->update($validated);
        } catch (RuntimeException $exception) {
            Storage::disk('public')->delete($newPaths);

            return back()->withInput()->withErrors(['images' => $exception->getMessage()]);
        }

        Storage::disk('public')->delete(array_values(array_diff($storedPaths, $page->fresh()->storedImagePaths())));

        return redirect()->route('admin.gallery.edit')->with('status', 'Smart Muslim gallery updated.');
    }

    /**
     * Destination rows with at least one value, keeping each row's uploaded files and filled album photos.
     *
     * @return array<int, array<string, mixed>>
     */
    private function filledDestinations(Request $request): array
    {
        $destinations = [];

        foreach ((array) $request->input('destinations', []) as $index => $destination) {
            if (! is_array($destination)) {
                continue;
            }

            $photos = $this->filledPhotos($request, $destination['photos'] ?? [], "destinations.$index.photos");
            $destination = array_intersect_key($destination, array_flip(self::DESTINATION_FIELDS));
            $hasFile = false;

            foreach (self::IMAGE_FIELDS as $field) {
                $file = $request->file("destinations.$index.{$field}_file");

                if ($file !== null) {
                    $destination["{$field}_file"] = $file;
                    $hasFile = true;
                }
            }

            if ($hasFile || $photos !== [] || array_filter($destination, static fn (mixed $value): bool => filled($value)) !== []) {
                $destinations[] = [...$destination, 'photos' => $photos];
            }
        }

        return $destinations;
    }

    /**
     * Album photo rows that have an image, an upload or a caption.
     *
     * @return array<int, array<string, mixed>>
     */
    private function filledPhotos(Request $request, mixed $photos, string $inputKey): array
    {
        $filled = [];

        foreach (is_array($photos) ? $photos : [] as $index => $photo) {
            if (! is_array($photo)) {
                continue;
            }

            $photo = array_intersect_key($photo, array_flip(['image', 'caption']));
            $file = $request->file("$inputKey.$index.image_file");

            if ($file !== null) {
                $photo['image_file'] = $file;
            }

            if ($file !== null || array_filter($photo, static fn (mixed $value): bool => filled($value)) !== []) {
                $filled[] = $photo;
            }
        }

        return $filled;
    }

    /**
     * @param  array<int, string>  $storedPaths
     */
    private function imageSourceRule(array $storedPaths): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($storedPaths): void {
            $value = (string) $value;
            $isHttpUrl = filter_var($value, FILTER_VALIDATE_URL) !== false && in_array(parse_url($value, PHP_URL_SCHEME), ['http', 'https'], true);
            $isBundled = preg_match('#^images/[A-Za-z0-9/_.-]+$#', $value) === 1 && ! str_contains($value, '..');

            if (! $isHttpUrl && ! $isBundled && ! in_array($value, $storedPaths, true)) {
                $fail('The image must be a valid http(s) URL or an uploaded image.');
            }
        };
    }

    private function storeImage(UploadedFile $file): string
    {
        $path = $file->store('gallery', 'public');

        if (! is_string($path)) {
            throw new RuntimeException('An image could not be saved. Please try again.');
        }

        return $path;
    }
}
