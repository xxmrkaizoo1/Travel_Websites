<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomepageCampaign;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class HomepageCampaignController extends Controller
{
    public function index(): View
    {
        return view('Admin.homepage.index', [
            'campaigns' => HomepageCampaign::query()->orderBy('position')->get(),
        ]);
    }

    public function edit(HomepageCampaign $campaign): View
    {
        return view('Admin.homepage.edit', compact('campaign'));
    }

    public function create(): View
    {
        return view('Admin.homepage.edit', ['campaign' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCampaign($request, true);
        $disk = Storage::disk('public');
        $storedPaths = [];

        if (HomepageCampaign::query()->count() >= 255) {
            return back()->withInput()->withErrors([
                'campaign' => 'The Smart Muslim homepage supports a maximum of 255 campaigns.',
            ]);
        }

        foreach (['image_main', 'image_top', 'image_bottom'] as $field) {
            if (! $request->hasFile($field)) {
                continue;
            }

            $path = $request->file($field)->store('homepage-campaigns', 'public');

            if (! is_string($path)) {
                $disk->delete(array_values($storedPaths));

                return back()
                    ->withInput()
                    ->withErrors([$field => 'The image could not be saved. Please try again.']);
            }

            $storedPaths[$field] = $path;
        }

        foreach (['image_top', 'image_bottom', 'sticker', 'ribbon', 'brand_top', 'brand_bottom'] as $field) {
            $validated[$field] = $storedPaths[$field] ?? $validated[$field] ?? '';
        }

        try {
            $campaign = DB::transaction(function () use ($validated, $storedPaths): HomepageCampaign {
                $positions = HomepageCampaign::query()
                    ->pluck('position')
                    ->map(fn (mixed $position): int => (int) $position)
                    ->all();
                $position = 1;

                while (in_array($position, $positions, true)) {
                    $position++;
                }

                if ($position > 255) {
                    throw ValidationException::withMessages([
                        'campaign' => 'The Smart Muslim homepage supports a maximum of 255 campaigns.',
                    ]);
                }

                $baseSlug = Str::slug($validated['kicker'].'-'.$validated['headline']) ?: 'campaign';
                $slug = $baseSlug;
                $suffix = 2;

                while (HomepageCampaign::query()->where('slug', $slug)->exists()) {
                    $slug = $baseSlug.'-'.$suffix++;
                }

                return HomepageCampaign::query()->create([
                    ...$validated,
                    ...$storedPaths,
                    'slug' => $slug,
                    'position' => $position,
                ]);
            });
        } catch (Throwable $exception) {
            $disk->delete(array_values($storedPaths));

            throw $exception;
        }

        return redirect()->route('admin.homepage')
            ->with('status', 'Homepage campaign added successfully.');
    }

    public function update(Request $request, HomepageCampaign $campaign): RedirectResponse
    {
        $validated = $this->validateCampaign($request, false, $campaign);
        $disk = Storage::disk('public');
        $storedPaths = [];
        $oldPaths = [];

        foreach (['sticker', 'ribbon', 'brand_top', 'brand_bottom'] as $field) {
            $validated[$field] = $validated[$field] ?? '';
        }

        $updates = Arr::only($validated, [
            'layout',
            'kicker',
            'headline',
            'headline_highlight',
            'body',
            'cta_label',
            'image_alt',
            'sticker',
            'ribbon',
            'brand_top',
            'brand_bottom',
            'design_key',
        ]);

        foreach (['image_main', 'image_top', 'image_bottom'] as $field) {
            if (! $request->hasFile($field)) {
                continue;
            }

            $path = $request->file($field)->store('homepage-campaigns', 'public');

            if (! is_string($path)) {
                $disk->delete($storedPaths);

                return back()
                    ->withInput()
                    ->withErrors([$field => 'The image could not be saved. Please try again.']);
            }

            $storedPaths[] = $path;
            $oldPaths[] = $campaign->{$field};
            $updates[$field] = $path;
        }

        try {
            DB::transaction(fn () => $campaign->update($updates));
        } catch (Throwable $exception) {
            $disk->delete($storedPaths);

            throw $exception;
        }

        foreach ($oldPaths as $oldPath) {
            $scheme = parse_url((string) $oldPath, PHP_URL_SCHEME);

            if ($oldPath === '' || $scheme !== null || ! $disk->exists($oldPath)) {
                continue;
            }

            if (! $disk->delete($oldPath)) {
                Log::warning('Could not remove a replaced Smart Muslim homepage campaign image.', [
                    'path' => $oldPath,
                    'campaign' => $campaign->slug,
                ]);
            }
        }

        return redirect()
            ->route('admin.homepage.edit', $campaign)
            ->with('status', 'Homepage campaign updated successfully.');
    }

    public function destroy(HomepageCampaign $campaign): RedirectResponse
    {
        if (HomepageCampaign::query()->count() <= 1) {
            return redirect()->route('admin.homepage')
                ->withErrors(['campaign' => 'At least one Smart Muslim homepage campaign must remain.']);
        }

        $imagePaths = Arr::only($campaign->getAttributes(), [
            'image_main',
            'image_top',
            'image_bottom',
        ]);

        $campaign->delete();

        $disk = Storage::disk('public');

        foreach ($imagePaths as $path) {
            if ($path === '' || parse_url($path, PHP_URL_SCHEME) !== null || ! $disk->exists($path)) {
                continue;
            }

            if (! $disk->delete($path)) {
                Log::warning('Could not remove an image for a deleted Smart Muslim homepage campaign.', [
                    'path' => $path,
                    'campaign' => $campaign->slug,
                ]);
            }
        }

        return redirect()->route('admin.homepage')
            ->with('status', 'Homepage campaign deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateCampaign(Request $request, bool $creating, ?HomepageCampaign $campaign = null): array
    {
        $request->mergeIfMissing(['layout' => 'collage']);
        $isCollage = $request->input('layout') === 'collage';
        $needsImage = fn (string $field): bool => $isCollage && ($creating || blank($campaign?->{$field}));
        $collageText = fn (int $max): array => [Rule::requiredIf($isCollage), 'nullable', 'string', 'max:'.$max];

        return $request->validate([
            'kicker' => ['required', 'string', 'max:100'],
            'headline' => ['required', 'string', 'max:100'],
            'headline_highlight' => ['required', 'string', 'max:100'],
            'body' => ['required', 'string', 'max:500'],
            'cta_label' => ['required', 'string', 'max:100'],
            'design_key' => ['required', 'in:fair,muslim,group'],
            'layout' => ['required', 'in:collage,banner'],
            'image_alt' => ['required', 'string', 'max:255'],
            'sticker' => $collageText(100),
            'ribbon' => $collageText(100),
            'brand_top' => $collageText(50),
            'brand_bottom' => $collageText(50),
            'image_main' => [$creating ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'image_top' => [Rule::requiredIf($needsImage('image_top')), 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'image_bottom' => [Rule::requiredIf($needsImage('image_bottom')), 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
    }
}
