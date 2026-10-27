<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketingPage;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use RuntimeException;

class TicketingPageController extends Controller
{
    private const AIRLINE_FIELDS = ['name', 'logo'];

    private const PROMOTION_FIELDS = ['title', 'airline', 'origin', 'destinations', 'fare', 'fare_note', 'booking_period', 'travel_period', 'image', 'alt'];

    public function edit(): View
    {
        return view('Admin.ticketing.edit', [
            'page' => TicketingPage::query()->firstOrFail(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $page = TicketingPage::query()->firstOrFail();
        $storedPaths = $page->storedImagePaths();
        $airlines = $this->filledRows($request, 'airlines', self::AIRLINE_FIELDS, 'logo_file');
        $promotions = $this->filledRows($request, 'promotions', self::PROMOTION_FIELDS, 'image_file');
        $imageUpload = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];

        $validated = Validator::make([...$request->except(['airlines', 'promotions']), 'airlines' => $airlines, 'promotions' => $promotions], [
            'meta_title' => ['required', 'string', 'max:120'],
            'meta_description' => ['required', 'string', 'max:500'],
            'hero_eyebrow' => ['required', 'string', 'max:60'],
            'hero_title' => ['required', 'string', 'max:80'],
            'hero_highlight' => ['required', 'string', 'max:80'],
            'hero_body' => ['required', 'string', 'max:500'],
            'hero_image' => ['nullable', 'string', 'max:2048', $this->imageSourceRule($storedPaths)],
            'hero_image_file' => $imageUpload,
            'airlines_label' => ['required', 'string', 'max:60'],
            'airlines_heading' => ['required', 'string', 'max:120'],
            'airlines' => ['array', 'max:'.TicketingPage::MAX_AIRLINES],
            'airlines.*.name' => ['required', 'string', 'max:60'],
            'airlines.*.logo' => ['nullable', 'string', 'max:2048', 'required_without:airlines.*.logo_file', $this->imageSourceRule($storedPaths)],
            'airlines.*.logo_file' => $imageUpload,
            'promotions_label' => ['required', 'string', 'max:60'],
            'promotions_heading' => ['required', 'string', 'max:120'],
            'promotions_body' => ['nullable', 'string', 'max:500'],
            'promotions' => ['array', 'max:'.TicketingPage::MAX_PROMOTIONS],
            'promotions.*.title' => ['required', 'string', 'max:60'],
            'promotions.*.airline' => ['required', 'string', 'max:60'],
            'promotions.*.origin' => ['nullable', 'string', 'max:60'],
            'promotions.*.destinations' => ['nullable', 'string', 'max:200'],
            'promotions.*.fare' => ['nullable', 'string', 'max:20'],
            'promotions.*.fare_note' => ['nullable', 'string', 'max:40'],
            'promotions.*.booking_period' => ['nullable', 'string', 'max:60'],
            'promotions.*.travel_period' => ['nullable', 'string', 'max:60'],
            'promotions.*.image' => ['nullable', 'string', 'max:2048', 'required_without:promotions.*.image_file', $this->imageSourceRule($storedPaths)],
            'promotions.*.image_file' => $imageUpload,
            'promotions.*.alt' => ['required', 'string', 'max:255'],
            'enquiry_label' => ['required', 'string', 'max:60'],
            'enquiry_heading' => ['required', 'string', 'max:120'],
            'enquiry_body' => ['required', 'string', 'max:500'],
            'whatsapp_number' => ['required', 'regex:/^[0-9]{8,15}$/'],
            'email' => ['required', 'email', 'max:120'],
            'phone' => ['required', 'string', 'max:40'],
        ], [
            'airlines.*.logo.required_without' => 'Each airline needs a logo URL or an uploaded logo.',
            'promotions.*.image.required_without' => 'Each promotion needs a poster URL or an uploaded poster.',
            'whatsapp_number.regex' => 'The WhatsApp number must be digits only, with the country code, e.g. 60165500590.',
        ])->validate();

        $newPaths = [];

        try {
            unset($validated['hero_image_file']);
            $validated['hero_image'] ??= null;

            if ($request->hasFile('hero_image_file')) {
                $validated['hero_image'] = $newPaths[] = $this->storeUpload($request->file('hero_image_file'));
            }

            $validated['airlines'] = $this->resolveRows($validated['airlines'] ?? [], self::AIRLINE_FIELDS, 'logo', $newPaths);
            $validated['promotions'] = $this->resolveRows($validated['promotions'] ?? [], self::PROMOTION_FIELDS, 'image', $newPaths);

            $page->update($validated);
        } catch (RuntimeException $exception) {
            Storage::disk('public')->delete($newPaths);

            return back()->withInput()->withErrors(['images' => $exception->getMessage()]);
        }

        Storage::disk('public')->delete(array_values(array_diff($storedPaths, $page->fresh()->storedImagePaths())));

        return redirect()->route('admin.ticketing.edit')->with('status', 'Ticketing page updated.');
    }

    /**
     * Stores each row's uploaded image in its image field and keeps only the known fields.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<int, string>  $fields
     * @param  array<int, string>  $newPaths
     * @return array<int, array<string, string>>
     */
    private function resolveRows(array $rows, array $fields, string $imageField, array &$newPaths): array
    {
        return array_map(function (array $row) use ($fields, $imageField, &$newPaths): array {
            if (($row["{$imageField}_file"] ?? null) instanceof UploadedFile) {
                $row[$imageField] = $newPaths[] = $this->storeUpload($row["{$imageField}_file"]);
            }

            return array_map(static fn (mixed $value): string => (string) $value, array_intersect_key($row, array_flip($fields)) + array_fill_keys($fields, ''));
        }, $rows);
    }

    /**
     * Rows of a repeatable section with at least one value, keeping each row's uploaded image.
     *
     * @param  array<int, string>  $fields
     * @return array<int, array<string, mixed>>
     */
    private function filledRows(Request $request, string $section, array $fields, string $fileField): array
    {
        $rows = [];

        foreach ((array) $request->input($section, []) as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $row = array_intersect_key($row, array_flip($fields));
            $file = $request->file("{$section}.{$index}.{$fileField}");

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

    private function storeUpload(UploadedFile $file): string
    {
        $path = $file->store('ticketing', 'public');

        if (! is_string($path)) {
            throw new RuntimeException('A file could not be saved. Please try again.');
        }

        return $path;
    }
}
