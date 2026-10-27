<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrivateToursPage;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use RuntimeException;

class PrivateToursPageController extends Controller
{
    private const PERK_FIELDS = ['icon', 'title', 'body'];

    private const DESTINATION_FIELDS = ['name', 'tagline', 'image', 'alt', 'link_url', 'link_label'];

    public function edit(): View
    {
        return view('Admin.private-tours.edit', [
            'page' => PrivateToursPage::query()->firstOrFail(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $page = PrivateToursPage::query()->firstOrFail();
        $storedPaths = $page->storedImagePaths();
        $perks = $this->filledRows($request, 'perks', self::PERK_FIELDS);
        $destinations = $this->filledRows($request, 'destinations', self::DESTINATION_FIELDS, 'image_file');
        $imageRules = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];

        $validated = Validator::make([...$request->except(['perks', 'destinations']), 'perks' => $perks, 'destinations' => $destinations], [
            'meta_title' => ['required', 'string', 'max:120'],
            'meta_description' => ['required', 'string', 'max:500'],
            'hero_eyebrow' => ['required', 'string', 'max:60'],
            'hero_title' => ['required', 'string', 'max:80'],
            'hero_highlight' => ['required', 'string', 'max:80'],
            'hero_body' => ['required', 'string', 'max:500'],
            'hero_cta_label' => ['required', 'string', 'max:40'],
            'hero_image' => ['nullable', 'string', 'max:2048', $this->imageSourceRule($storedPaths)],
            'hero_image_file' => $imageRules,
            'hero_note' => ['nullable', 'string', 'max:80'],
            'perks' => ['array', 'max:'.PrivateToursPage::MAX_PERKS],
            'perks.*.icon' => ['nullable', 'string', 'max:8'],
            'perks.*.title' => ['required', 'string', 'max:60'],
            'perks.*.body' => ['nullable', 'string', 'max:160'],
            'intro_label' => ['required', 'string', 'max:60'],
            'intro_heading' => ['required', 'string', 'max:120'],
            'intro_highlight' => ['nullable', 'string', 'max:40'],
            'intro_body' => ['required', 'string', 'max:1000'],
            'intro_link_label' => ['required', 'string', 'max:40'],
            'intro_image' => ['nullable', 'string', 'max:2048', $this->imageSourceRule($storedPaths)],
            'intro_image_file' => $imageRules,
            'intro_image_alt' => ['nullable', 'string', 'max:255'],
            'intro_caption' => ['nullable', 'string', 'max:80'],
            'destinations_label' => ['required', 'string', 'max:60'],
            'destinations_heading' => ['required', 'string', 'max:120'],
            'destinations_body' => ['nullable', 'string', 'max:500'],
            'destinations' => ['required', 'array', 'min:1', 'max:'.PrivateToursPage::MAX_DESTINATIONS],
            'destinations.*.name' => ['required', 'string', 'max:40'],
            'destinations.*.tagline' => ['nullable', 'string', 'max:160'],
            'destinations.*.link_url' => ['required', 'string', 'max:2048', $this->linkTargetRule()],
            'destinations.*.link_label' => ['nullable', 'string', 'max:40'],
            'destinations.*.image' => ['nullable', 'string', 'max:2048', 'required_without:destinations.*.image_file', $this->imageSourceRule($storedPaths)],
            'destinations.*.image_file' => $imageRules,
            'destinations.*.alt' => ['required', 'string', 'max:255'],
            'contact_label' => ['required', 'string', 'max:60'],
            'contact_heading' => ['required', 'string', 'max:120'],
            'contact_body' => ['required', 'string', 'max:500'],
            'contact_cta_label' => ['required', 'string', 'max:40'],
        ], [
            'destinations.*.image.required_without' => 'Each destination card needs an image URL or an uploaded image.',
        ])->validate();

        $newPaths = [];

        try {
            unset($validated['hero_image_file'], $validated['intro_image_file']);
            $validated['hero_image'] ??= null;
            $validated['intro_image'] ??= null;

            foreach (['hero_image', 'intro_image'] as $field) {
                if ($request->hasFile("{$field}_file")) {
                    $validated[$field] = $newPaths[] = $this->storeUpload($request->file("{$field}_file"));
                }
            }

            $validated['perks'] = array_map(fn (array $perk): array => $this->onlyFields($perk, self::PERK_FIELDS), $validated['perks'] ?? []);
            $validated['destinations'] = array_map(function (array $destination) use (&$newPaths): array {
                if (($destination['image_file'] ?? null) instanceof UploadedFile) {
                    $destination['image'] = $newPaths[] = $this->storeUpload($destination['image_file']);
                }

                return $this->onlyFields($destination, self::DESTINATION_FIELDS);
            }, $validated['destinations']);

            $page->update($validated);
        } catch (RuntimeException $exception) {
            Storage::disk('public')->delete($newPaths);

            return back()->withInput()->withErrors(['images' => $exception->getMessage()]);
        }

        Storage::disk('public')->delete(array_values(array_diff($storedPaths, $page->fresh()->storedImagePaths())));

        return redirect()->route('admin.private-tours.edit')->with('status', 'Private tours page updated.');
    }

    /**
     * Rows of a repeatable section with at least one value, keeping each row's uploaded image.
     *
     * @param  array<int, string>  $fields
     * @return array<int, array<string, mixed>>
     */
    private function filledRows(Request $request, string $section, array $fields, ?string $fileField = null): array
    {
        $rows = [];

        foreach ((array) $request->input($section, []) as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $row = array_intersect_key($row, array_flip($fields));
            $file = $fileField === null ? null : $request->file("{$section}.{$index}.{$fileField}");

            if ($file !== null) {
                $row[$fileField] = $file;
            }

            if ($file !== null || array_filter($row, static fn (mixed $value): bool => filled($value)) !== []) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<int, string>  $fields
     * @return array<string, string>
     */
    private function onlyFields(array $row, array $fields): array
    {
        return array_map(static fn (mixed $value): string => (string) $value, array_intersect_key($row, array_flip($fields)) + array_fill_keys($fields, ''));
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
        $path = $file->store('private-tours', 'public');

        if (! is_string($path)) {
            throw new RuntimeException('A file could not be saved. Please try again.');
        }

        return $path;
    }
}
