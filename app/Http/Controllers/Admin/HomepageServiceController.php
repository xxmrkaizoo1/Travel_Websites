<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomepageService;
use App\Models\HomepageSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class HomepageServiceController extends Controller
{
    public function index(): View
    {
        return view('Admin.homepage.services.index', [
            'services' => HomepageService::query()->orderBy('position')->get(),
            'heading' => HomepageSetting::query()
                ->where('key', 'services_heading')
                ->firstOrFail()
                ->value,
        ]);
    }

    public function edit(HomepageService $service): View
    {
        return view('Admin.homepage.services.edit', compact('service'));
    }

    public function update(Request $request, HomepageService $service): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'overlay_label' => ['required', 'string', 'max:100'],
            'button_label' => ['required', 'string', 'max:100'],
            'href' => [
                'required',
                'string',
                'max:2048',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    // A page on this site, such as /china-muslim ("//" would point at another host).
                    if (preg_match('#^/(?!/)[^\s\\\\]*$#', $value) === 1) {
                        return;
                    }

                    $scheme = parse_url($value, PHP_URL_SCHEME);
                    $valid = match (strtolower((string) $scheme)) {
                        'http', 'https' => filter_var($value, FILTER_VALIDATE_URL) !== false,
                        'mailto' => filter_var(parse_url($value, PHP_URL_PATH), FILTER_VALIDATE_EMAIL) !== false,
                        'tel' => preg_match('/^\+?[0-9().\-\s]{5,30}$/', parse_url($value, PHP_URL_PATH)) === 1,
                        default => false,
                    };

                    if (! $valid) {
                        $fail('Enter a site page such as /china-muslim, or a valid HTTP, HTTPS, email, or telephone link.');
                    }
                },
            ],
            'image_alt' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $disk = Storage::disk('public');
        $oldImage = $service->image_path;
        $storedImage = null;
        $updates = [
            'title' => $validated['title'],
            'overlay_label' => $validated['overlay_label'],
            'button_label' => $validated['button_label'],
            'href' => $validated['href'],
            'image_alt' => $validated['image_alt'],
        ];

        if ($request->hasFile('image')) {
            $storedImage = $request->file('image')->store('homepage-services', 'public');

            if (! is_string($storedImage)) {
                throw ValidationException::withMessages([
                    'image' => 'The image could not be saved. Please try again.',
                ]);
            }

            $updates['image_path'] = $storedImage;
        }

        try {
            DB::transaction(fn () => $service->update($updates));
        } catch (Throwable $exception) {
            if ($storedImage !== null) {
                $disk->delete($storedImage);
            }

            throw $exception;
        }

        if ($storedImage !== null && parse_url($oldImage, PHP_URL_SCHEME) === null && $disk->exists($oldImage)) {
            if (! $disk->delete($oldImage)) {
                Log::warning('Could not remove a replaced Smart Muslim homepage service image.', [
                    'path' => $oldImage,
                    'service' => $service->slug,
                ]);
            }
        }

        return redirect()
            ->route('admin.homepage.services.edit', $service)
            ->with('status', 'Homepage service updated successfully.');
    }

    public function updateHeading(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'heading' => ['required', 'string', 'max:100'],
        ]);

        HomepageSetting::query()
            ->where('key', 'services_heading')
            ->firstOrFail()
            ->update(['value' => $validated['heading']]);

        return redirect()
            ->route('admin.homepage.services')
            ->with('status', 'Services heading updated successfully.');
    }
}
