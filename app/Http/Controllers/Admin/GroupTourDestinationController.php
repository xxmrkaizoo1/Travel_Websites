<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GroupTourDestination;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class GroupTourDestinationController extends Controller
{
    private const STATE_FIELDS = ['name', 'region', 'capital', 'tagline', 'image', 'alt'];

    public function create(): View
    {
        return view('Admin.group-tours.destination-form', [
            'destination' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'slug' => ['nullable', 'string', 'max:60', 'alpha_dash:ascii'],
        ]);
        $slug = Str::slug(($validated['slug'] ?? '') ?: $validated['name']);

        Validator::make(['slug' => $slug], [
            'slug' => ['required', Rule::notIn(GroupTourDestination::RESERVED_SLUGS), Rule::unique('group_tour_destinations', 'slug')],
        ], [
            'slug.not_in' => 'That web address is already used by another Group Tours page.',
            'slug.unique' => 'A destination page with that web address already exists.',
        ])->validate();

        $destination = GroupTourDestination::query()->create([
            'slug' => $slug,
            'name' => $validated['name'],
            'meta_description' => "Explore group tours in {$validated['name']} with Smart Holidays by Roystar Travel & Tours.",
            'hero_eyebrow' => 'GROUP TOURS · '.Str::upper($validated['name']),
            'heading' => "Discover {$validated['name']}",
            'hero_image' => 'images/placeholder-photo.svg',
            'states_heading' => 'Explore the destinations',
            'regions' => GroupTourDestination::regionList(GroupTourDestination::DEFAULT_REGIONS),
            'place_term' => 'destination',
            'states' => [],
            'position' => (int) GroupTourDestination::query()->max('position') + 1,
        ]);

        return redirect()->route('admin.group-tour-destinations.edit', $destination)
            ->with('status', 'Destination page created. Add its banner and states, then link it from a tour card.');
    }

    public function edit(GroupTourDestination $destination): View
    {
        return view('Admin.group-tours.destination-form', [
            'destination' => $destination,
        ]);
    }

    public function update(Request $request, GroupTourDestination $destination): RedirectResponse
    {
        $states = $this->filledStates($request);
        $storedPaths = $destination->storedImagePaths();
        $regions = $this->submittedRegions($request, $destination);

        $validated = Validator::make([...$request->except('states'), 'states' => $states, 'region_list' => $regions], [
            'name' => ['required', 'string', 'max:60'],
            'meta_description' => ['required', 'string', 'max:500'],
            'hero_eyebrow' => ['required', 'string', 'max:60'],
            'heading' => ['required', 'string', 'max:120'],
            'intro' => ['nullable', 'string', 'max:500'],
            'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'states_heading' => ['required', 'string', 'max:120'],
            'place_term' => ['sometimes', 'required', 'string', 'max:30', 'regex:/^[A-Za-z ]+$/'],
            'region_list' => ['array', 'min:1', 'max:'.GroupTourDestination::MAX_REGIONS],
            'region_list.*' => ['string', 'max:40'],
            'states' => ['array', 'max:'.GroupTourDestination::MAX_STATES],
            'states.*.name' => ['required', 'string', 'max:60'],
            'states.*.region' => ['required', Rule::in(array_keys($regions))],
            'states.*.capital' => ['nullable', 'string', 'max:60'],
            'states.*.tagline' => ['nullable', 'string', 'max:160'],
            'states.*.image' => ['nullable', 'string', 'max:2048', $this->imageSourceRule($storedPaths)],
            'states.*.image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'states.*.alt' => ['nullable', 'string', 'max:255'],
            'region_images' => ['nullable', 'array:'.implode(',', array_unique([...array_keys($destination->regionOptions()), ...array_keys($regions)]))],
            'region_images.*.image' => ['nullable', 'string', 'max:2048', $this->imageSourceRule($storedPaths)],
            'region_images.*.image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'region_images.*.alt' => ['nullable', 'string', 'max:255'],
        ], [
            'region_list.min' => 'Keep at least one region.',
            'region_list.max' => 'A page can have up to '.GroupTourDestination::MAX_REGIONS.' regions.',
            'place_term.regex' => 'Use letters only for the place word, e.g. state or destination.',
            'states.*.region.in' => 'Each place must use one of the regions on this page. Move places out of a region before removing it.',
        ])->validate();

        unset($validated['region_list']);
        $validated['regions'] = GroupTourDestination::regionList($regions);

        if (isset($validated['place_term'])) {
            $validated['place_term'] = Str::lower(trim($validated['place_term']));
        }

        $newPaths = [];

        try {
            unset($validated['hero_image']);

            if ($request->hasFile('hero_image')) {
                $validated['hero_image'] = $newPaths[] = $this->storeUpload($request->file('hero_image'));
            }

            $validated['states'] = array_map(function (array $state) use (&$newPaths): array {
                if (($state['image_file'] ?? null) instanceof UploadedFile) {
                    $state['image'] = $newPaths[] = $this->storeUpload($state['image_file']);
                }

                return array_map(static fn (mixed $value): string => (string) $value, array_intersect_key($state, array_flip(self::STATE_FIELDS)) + array_fill_keys(self::STATE_FIELDS, ''));
            }, $validated['states'] ?? []);

            if ($request->has('region_images')) {
                $validated['region_images'] = [];

                foreach (array_keys($regions) as $regionKey) {
                    $file = $request->file("region_images.$regionKey.image_file");
                    $image = $file instanceof UploadedFile
                        ? $newPaths[] = $this->storeUpload($file)
                        : (string) ($request->input("region_images.$regionKey.image") ?? '');

                    $validated['region_images'][$regionKey] = ['image' => $image, 'alt' => (string) ($request->input("region_images.$regionKey.alt") ?? '')];
                }
            }

            $destination->update($validated);
        } catch (RuntimeException $exception) {
            Storage::disk('public')->delete($newPaths);

            return back()->withInput()->withErrors(['images' => $exception->getMessage()]);
        }

        Storage::disk('public')->delete(array_values(array_diff($storedPaths, $destination->fresh()->storedImagePaths())));

        return redirect()->route('admin.group-tour-destinations.edit', $destination)->with('status', "{$destination->name} page updated.");
    }

    /**
     * Regions after this save: kept regions by key (renaming keeps the key, an empty name removes it),
     * then newly named regions. Without region fields in the request the current regions stay.
     *
     * @return array<string, string>
     */
    private function submittedRegions(Request $request, GroupTourDestination $destination): array
    {
        if (! $request->has('regions') && ! $request->has('new_regions')) {
            return $destination->regionOptions();
        }

        $current = $destination->regionOptions();
        $regions = [];

        foreach ((array) $request->input('regions', []) as $key => $label) {
            if (array_key_exists($key, $current) && is_string($label) && filled($label)) {
                $regions[$key] = trim($label);
            }
        }

        foreach ((array) $request->input('new_regions', []) as $label) {
            if (! is_string($label) || blank($label)) {
                continue;
            }

            $baseKey = Str::slug($label) ?: 'region';
            $key = $baseKey;

            for ($suffix = 2; array_key_exists($key, $regions); $suffix++) {
                $key = "{$baseKey}-{$suffix}";
            }

            $regions[$key] = trim($label);
        }

        return $regions;
    }

    /**
     * State rows with at least one value, keeping each row's uploaded image.
     *
     * @return array<int, array<string, mixed>>
     */
    private function filledStates(Request $request): array
    {
        $states = [];

        foreach ((array) $request->input('states', []) as $index => $state) {
            if (! is_array($state)) {
                continue;
            }

            $state = array_intersect_key($state, array_flip(self::STATE_FIELDS));
            $file = $request->file("states.$index.image_file");

            if ($file !== null) {
                $state['image_file'] = $file;
            }

            $hasText = array_filter(array_diff_key($state, ['region' => true]), static fn (mixed $value): bool => filled($value)) !== [];

            if ($file !== null || $hasText) {
                $states[] = $state;
            }
        }

        return $states;
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
        $path = $file->store('group-tours', 'public');

        if (! is_string($path)) {
            throw new RuntimeException('A file could not be saved. Please try again.');
        }

        return $path;
    }
}
