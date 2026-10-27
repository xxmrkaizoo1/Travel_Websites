<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GroupTourDestination;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

/**
 * Adds, edits and removes a single state card of a destination page, from buttons shown on the page itself.
 */
class GroupTourDestinationStateController extends Controller
{
    private const STATE_FIELDS = ['name', 'region', 'capital', 'tagline', 'image', 'alt'];

    public function create(Request $request, GroupTourDestination $destination): View|RedirectResponse
    {
        if (count($destination->states ?? []) >= GroupTourDestination::MAX_STATES) {
            return redirect()->route('group-tours.destination', $destination);
        }

        $region = (string) $request->query('region');
        $regions = $destination->regionOptions();

        return view('Admin.group-tours.state-form', [
            'destination' => $destination,
            'state' => [...array_fill_keys(self::STATE_FIELDS, ''), 'region' => array_key_exists($region, $regions) ? $region : array_key_first($regions)],
            'stateKey' => null,
        ]);
    }

    public function store(Request $request, GroupTourDestination $destination): RedirectResponse
    {
        $states = $destination->states ?? [];

        if (count($states) >= GroupTourDestination::MAX_STATES) {
            return back()->withInput()->withErrors(['states' => 'This page already has the maximum of '.GroupTourDestination::MAX_STATES.' states.']);
        }

        return $this->saveState($request, $destination, null);
    }

    public function edit(GroupTourDestination $destination, string $state): View
    {
        $index = $this->stateIndex($destination, $state);

        return view('Admin.group-tours.state-form', [
            'destination' => $destination,
            'state' => $destination->states[$index],
            'stateKey' => $state,
        ]);
    }

    public function update(Request $request, GroupTourDestination $destination, string $state): RedirectResponse
    {
        return $this->saveState($request, $destination, $this->stateIndex($destination, $state));
    }

    public function destroy(GroupTourDestination $destination, string $state): RedirectResponse
    {
        $index = $this->stateIndex($destination, $state);
        $storedPaths = $destination->storedImagePaths();
        $states = $destination->states;
        $removed = $states[$index];
        array_splice($states, $index, 1);

        $destination->update(['states' => $states]);
        Storage::disk('public')->delete(array_values(array_diff($storedPaths, $destination->fresh()->storedImagePaths())));

        return redirect()->to(route('group-tours.destination', $destination).'#region-'.$removed['region'])
            ->with('status', "{$removed['name']} removed.");
    }

    /**
     * Validates one state and stores it at $index, or appends it when $index is null.
     */
    private function saveState(Request $request, GroupTourDestination $destination, ?int $index): RedirectResponse
    {
        $states = $destination->states ?? [];
        $storedPaths = $destination->storedImagePaths();
        $otherNames = array_map(
            static fn (array $state): string => Str::slug($state['name']),
            array_values(array_filter($states, static fn (int $key): bool => $key !== $index, ARRAY_FILTER_USE_KEY)),
        );

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:60', function (string $attribute, mixed $value, Closure $fail) use ($otherNames): void {
                if (in_array(Str::slug((string) $value), $otherNames, true)) {
                    $fail('Another state on this page already has that name.');
                }
            }],
            'region' => ['required', Rule::in(array_keys($destination->regionOptions()))],
            'capital' => ['nullable', 'string', 'max:60'],
            'tagline' => ['nullable', 'string', 'max:160'],
            'image' => ['nullable', 'string', 'max:2048', $this->imageSourceRule($storedPaths)],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'alt' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            if ($request->hasFile('image_file')) {
                $validated['image'] = $this->storeUpload($request->file('image_file'));
            }
        } catch (RuntimeException $exception) {
            return back()->withInput()->withErrors(['image_file' => $exception->getMessage()]);
        }

        $state = array_map(static fn (mixed $value): string => (string) $value, array_intersect_key($validated, array_flip(self::STATE_FIELDS)) + array_fill_keys(self::STATE_FIELDS, ''));

        if ($index === null) {
            $states[] = $state;
        } else {
            $states[$index] = $state;
        }

        $destination->update(['states' => array_values($states)]);
        Storage::disk('public')->delete(array_values(array_diff($storedPaths, $destination->fresh()->storedImagePaths())));

        return redirect()->to(route('group-tours.destination', $destination).'#'.GroupTourDestination::stateAnchor($state['name']))
            ->with('status', $index === null ? "{$state['name']} added." : "{$state['name']} updated.");
    }

    private function stateIndex(GroupTourDestination $destination, string $stateSlug): int
    {
        foreach ($destination->states ?? [] as $index => $state) {
            if (Str::slug($state['name']) === $stateSlug) {
                return $index;
            }
        }

        abort(404);
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
