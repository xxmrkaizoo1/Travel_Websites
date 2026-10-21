<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IslandDestination;
use App\Models\IslandHoneymoonSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class IslandHoneymoonController extends Controller
{
    public function index(): View
    {
        return view('Admin.island-honeymoon.index', [
            'settings' => IslandHoneymoonSetting::query()->firstOrFail(),
            'destinations' => IslandDestination::query()->orderBy('position')->get(),
        ]);
    }

    public function updatePage(Request $request): RedirectResponse
    {
        $settings = IslandHoneymoonSetting::query()->firstOrFail();
        $validated = $request->validate([
            'hero_eyebrow' => ['required', 'string', 'max:120'],
            'hero_headline' => ['required', 'string', 'max:120'],
            'hero_highlight' => ['required', 'string', 'max:160'],
            'hero_body' => ['required', 'string', 'max:1500'],
            'hero_cta_label' => ['required', 'string', 'max:100'],
            'hero_note' => ['required', 'string', 'max:120'],
            'intro_eyebrow' => ['required', 'string', 'max:120'],
            'intro_headline' => ['required', 'string', 'max:160'],
            'intro_highlight' => ['required', 'string', 'max:100'],
            'intro_body' => ['required', 'string', 'max:1500'],
            'intro_link_label' => ['required', 'string', 'max:120'],
            'intro_link_href' => ['required', 'url:http,https', 'max:2048'],
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
            'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'intro_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $disk = Storage::disk('public');
        $storedPaths = [];
        $oldPaths = [];

        foreach (['hero_image', 'intro_image'] as $field) {
            unset($validated[$field]);

            if (! $request->hasFile($field)) {
                continue;
            }

            $path = $request->file($field)->store('island-honeymoon', 'public');

            if (! is_string($path)) {
                $disk->delete($storedPaths);

                return back()->withInput()->withErrors([
                    $field => 'The image could not be saved. Please try again.',
                ]);
            }

            $storedPaths[] = $path;
            $oldPaths[] = $settings->{$field};
            $validated[$field] = $path;
        }

        try {
            DB::transaction(fn () => $settings->update($validated));
        } catch (Throwable $exception) {
            $disk->delete($storedPaths);

            throw $exception;
        }

        foreach ($oldPaths as $oldPath) {
            if (parse_url($oldPath, PHP_URL_SCHEME) === null && $disk->exists($oldPath) && ! $disk->delete($oldPath)) {
                Log::warning('Could not remove a replaced Island & Honeymoon page image.', [
                    'path' => $oldPath,
                ]);
            }
        }

        return redirect()->route('admin.island-honeymoon')
            ->with('status', 'Island & Honeymoon page updated successfully.');
    }

    public function createDestination(): View
    {
        return view('Admin.island-honeymoon.destination-form', [
            'destination' => null,
            'benefits' => [],
            'gallery' => [],
        ]);
    }

    public function storeDestination(Request $request): RedirectResponse
    {
        $validated = $this->validateDestination($request, true);
        $image = $request->file('feature_image')->store('island-destinations', 'public');

        if (! is_string($image)) {
            return back()->withInput()->withErrors([
                'feature_image' => 'The image could not be saved. Please try again.',
            ]);
        }

        try {
            $destination = DB::transaction(function () use ($validated, $image): IslandDestination {
                $position = (IslandDestination::query()->max('position') ?? 0) + 1;
                $slug = Str::slug($validated['name']) ?: 'destination';
                $baseSlug = $slug;
                $suffix = 2;

                while (IslandDestination::query()->where('slug', $slug)->exists()) {
                    $slug = $baseSlug.'-'.$suffix++;
                }

                unset($validated['feature_image']);

                return IslandDestination::query()->create([
                    ...$validated,
                    'slug' => $slug,
                    'position' => $position,
                    'feature_image' => $image,
                ]);
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($image);

            throw $exception;
        }

        return redirect()->route('admin.island-honeymoon')
            ->with('status', $destination->name.' destination spotlight added.');
    }

    public function editDestination(IslandDestination $destination): View
    {
        return view('Admin.island-honeymoon.destination-form', [
            'destination' => $destination,
            'benefits' => $destination->benefits,
            'gallery' => $destination->gallery,
        ]);
    }

    public function updateDestination(Request $request, IslandDestination $destination): RedirectResponse
    {
        $validated = $this->validateDestination($request, false);
        $disk = Storage::disk('public');
        $oldImage = $destination->feature_image;
        $newImage = null;

        if ($request->hasFile('feature_image')) {
            $newImage = $request->file('feature_image')->store('island-destinations', 'public');

            if (! is_string($newImage)) {
                return back()->withInput()->withErrors([
                    'feature_image' => 'The image could not be saved. Please try again.',
                ]);
            }
        }

        unset($validated['feature_image']);
        if ($newImage !== null) {
            $validated['feature_image'] = $newImage;
        }

        try {
            DB::transaction(fn () => $destination->update($validated));
        } catch (Throwable $exception) {
            if ($newImage !== null) {
                $disk->delete($newImage);
            }

            throw $exception;
        }

        if ($newImage !== null && parse_url($oldImage, PHP_URL_SCHEME) === null && $disk->exists($oldImage) && ! $disk->delete($oldImage)) {
            Log::warning('Could not remove a replaced destination spotlight image.', [
                'path' => $oldImage,
                'destination' => $destination->slug,
            ]);
        }

        return redirect()->route('admin.island-honeymoon')
            ->with('status', $destination->name.' destination spotlight updated.');
    }

    public function destroyDestination(IslandDestination $destination): RedirectResponse
    {
        $oldImage = $destination->feature_image;
        $destination->delete();

        if (parse_url($oldImage, PHP_URL_SCHEME) === null && Storage::disk('public')->exists($oldImage) && ! Storage::disk('public')->delete($oldImage)) {
            Log::warning('Could not remove a deleted destination spotlight image.', [
                'path' => $oldImage,
                'destination' => $destination->slug,
            ]);
        }

        return redirect()->route('admin.island-honeymoon')
            ->with('status', $destination->name.' destination spotlight deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateDestination(Request $request, bool $creating): array
    {
        $rawBenefits = $request->input('benefits', []);
        $benefits = is_array($rawBenefits) ? array_values(array_filter(
            $rawBenefits,
            fn (mixed $benefit): bool => is_array($benefit)
                && collect($benefit)->contains(fn (mixed $value): bool => is_scalar($value)
                    && trim((string) $value) !== ''),
        )) : [];
        $rawGallery = $request->input('gallery', []);
        $gallery = is_array($rawGallery) ? array_values(array_filter(
            $rawGallery,
            fn (mixed $slide): bool => is_array($slide)
                && collect($slide)->contains(fn (mixed $value): bool => is_scalar($value)
                    && trim((string) $value) !== ''),
        )) : [];
        $request->merge(['benefits' => $benefits, 'gallery' => $gallery]);

        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'country' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:2000'],
            'known_as' => ['required', 'string', 'max:120'],
            'perfect_for' => ['required', 'string', 'max:160'],
            'benefits_heading' => ['required', 'string', 'max:160'],
            'benefits' => ['required', 'array', 'min:1', 'max:5'],
            'benefits.*.icon' => ['required', 'string', 'max:12'],
            'benefits.*.title' => ['required', 'string', 'max:120'],
            'benefits.*.body' => ['required', 'string', 'max:500'],
            'feature_image' => [$creating ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'feature_image_alt' => ['required', 'string', 'max:255'],
            'feature_caption' => ['required', 'string', 'max:120'],
            'gallery' => ['required', 'array', 'min:1', 'max:5'],
            'gallery.*.image' => ['required', 'url:http,https', 'max:2048'],
            'gallery.*.alt' => ['required', 'string', 'max:255'],
            'gallery.*.eyebrow' => ['required', 'string', 'max:120'],
            'gallery.*.title' => ['required', 'string', 'max:120'],
        ]);
    }
}
