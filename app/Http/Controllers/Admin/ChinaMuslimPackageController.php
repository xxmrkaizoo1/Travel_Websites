<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChinaAboutPage;
use App\Models\ChinaMuslimPackage;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ChinaMuslimPackageController extends Controller
{
    public function index(): View
    {
        return view('Admin.china-muslim.index', [
            'packages' => ChinaMuslimPackage::query()->orderBy('position')->orderBy('id')->get(),
            'about' => ChinaAboutPage::query()->first(),
        ]);
    }

    /**
     * Updates the traveller photos slideshow: captions, removals and new uploads.
     */
    public function updateTravellerPhotos(Request $request): RedirectResponse
    {
        $about = ChinaAboutPage::query()->firstOrFail();
        $current = collect($about->traveller_photos ?? [])->keyBy('image');

        $validated = $request->validate([
            'photos' => ['nullable', 'array'],
            'photos.*.image' => ['required', 'string'],
            'photos.*.caption' => ['nullable', 'string', 'max:80'],
            'photos.*.remove' => ['nullable', 'boolean'],
            'new_photos' => ['nullable', 'array', 'max:12'],
            'new_photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'new_photos.max' => 'Upload up to 12 photos at a time.',
        ]);

        $kept = [];
        $removed = [];

        foreach ($validated['photos'] ?? [] as $photo) {
            if (! $current->has($photo['image'])) {
                continue;
            }

            if (! empty($photo['remove'])) {
                $removed[] = $photo['image'];

                continue;
            }

            $kept[] = ['image' => $photo['image'], 'caption' => trim((string) ($photo['caption'] ?? ''))];
        }

        $room = ChinaAboutPage::MAX_TRAVELLER_PHOTOS - count($kept);

        if (count($request->file('new_photos', [])) > $room) {
            return back()->withInput()->withErrors(['new_photos' => 'The slideshow holds up to '.ChinaAboutPage::MAX_TRAVELLER_PHOTOS." photos. Remove some or upload fewer (room for {$room} more)."]);
        }

        foreach ($request->file('new_photos', []) as $upload) {
            $kept[] = ['image' => $upload->store('china-travellers', 'public'), 'caption' => 'Smart Muslim travellers'];
        }

        $about->update(['traveller_photos' => $kept]);

        Storage::disk('public')->delete(array_filter($removed, fn (string $path): bool => ! str_starts_with($path, 'images/')));

        return redirect()->route('admin.china-muslim')->with('status', 'Traveller photos updated.');
    }

    public function updateAbout(Request $request): RedirectResponse
    {
        $about = ChinaAboutPage::query()->firstOrFail();
        $currentGallery = $about->gallery ?? [];

        $validated = $request->validate([
            'badge' => ['required', 'string', 'max:60'],
            'title' => ['required', 'string', 'max:120'],
            'intro' => ['required', 'string', 'max:1500'],
            'facts' => ['required', 'array', 'size:4'],
            'facts.*.label' => ['required', 'string', 'max:40'],
            'facts.*.value' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:1500'],
            'note' => ['required', 'string', 'max:300'],
            'new_photos' => ['nullable', 'array', 'max:8'],
            'new_photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_photos' => ['nullable', 'array'],
            'remove_photos.*' => ['string'],
        ]);

        $removed = array_values(array_intersect($currentGallery, $validated['remove_photos'] ?? []));
        $gallery = array_values(array_diff($currentGallery, $removed));
        $stored = [];

        foreach ($request->file('new_photos', []) as $photo) {
            $path = $photo->store('china-about', 'public');

            if (! is_string($path)) {
                Storage::disk('public')->delete($stored);

                return back()->withInput()->withErrors(['new_photos' => 'A photo could not be saved. Please try again.']);
            }

            $stored[] = $path;
        }

        $gallery = array_slice([...$gallery, ...$stored], 0, 12);

        $about->update([
            'badge' => $validated['badge'],
            'title' => $validated['title'],
            'intro' => $validated['intro'],
            'facts' => array_values($validated['facts']),
            'body' => $validated['body'],
            'note' => $validated['note'],
            'gallery' => $gallery,
        ]);

        Storage::disk('public')->delete($removed);

        return redirect()->to(url()->previous(route('admin.china-muslim')))->with('status', 'About this place popup updated.');
    }

    public function create(): View
    {
        return view('Admin.china-muslim.form', ['package' => null, 'about' => ChinaAboutPage::query()->firstOrFail()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePackage($request, true);
        $image = $request->file('image')->store('china-muslim', 'public');

        if (! is_string($image)) {
            return back()->withInput()->withErrors([
                'image' => 'The image could not be saved. Please try again.',
            ]);
        }

        if ($request->hasFile('about_video')) {
            $video = $request->file('about_video')->store('china-muslim-videos', 'public');

            if (! is_string($video)) {
                Storage::disk('public')->delete($image);

                return back()->withInput()->withErrors(['about_video' => 'The video could not be saved. Please try again.']);
            }

            $validated['about_video_path'] = $video;
        }

        ChinaMuslimPackage::query()->create([
            ...$validated,
            'image_path' => $image,
            'is_hot' => $request->boolean('is_hot'),
            'position' => (ChinaMuslimPackage::query()->max('position') ?? 0) + 1,
        ]);

        return redirect()->route('admin.china-muslim')
            ->with('status', 'China Muslim package card created.');
    }

    public function edit(ChinaMuslimPackage $package): View
    {
        return view('Admin.china-muslim.form', ['package' => $package, 'about' => ChinaAboutPage::query()->firstOrFail()]);
    }

    public function update(Request $request, ChinaMuslimPackage $package): RedirectResponse
    {
        $validated = $this->validatePackage($request, false);
        $oldImage = $package->image_path;

        if ($request->hasFile('image')) {
            $image = $request->file('image')->store('china-muslim', 'public');

            if (! is_string($image)) {
                return back()->withInput()->withErrors([
                    'image' => 'The image could not be saved. Please try again.',
                ]);
            }

            $validated['image_path'] = $image;
        }

        $oldVideo = $package->about_video_path;

        if ($request->hasFile('about_video')) {
            $video = $request->file('about_video')->store('china-muslim-videos', 'public');

            if (! is_string($video)) {
                return back()->withInput()->withErrors(['about_video' => 'The video could not be saved. Please try again.']);
            }

            $validated['about_video_path'] = $video;
        } elseif ($request->boolean('remove_about_video')) {
            $validated['about_video_path'] = null;
        }

        $package->update([...$validated, 'is_hot' => $request->boolean('is_hot')]);

        if (isset($validated['image_path'])) {
            Storage::disk('public')->delete($oldImage);
        }

        if ($oldVideo !== null && array_key_exists('about_video_path', $validated)) {
            Storage::disk('public')->delete($oldVideo);
        }

        return redirect()->route('admin.china-muslim')
            ->with('status', 'China Muslim package card updated.');
    }

    public function destroy(ChinaMuslimPackage $package): RedirectResponse
    {
        $files = array_filter([$package->image_path, $package->about_video_path]);
        $package->delete();
        Storage::disk('public')->delete($files);

        return redirect()->route('admin.china-muslim')
            ->with('status', 'China Muslim package card deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePackage(Request $request, bool $imageRequired): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'price' => ['required', 'integer', 'min:0', 'max:9999999'],
            'days' => ['required', 'integer', 'min:1', 'max:60'],
            'nights' => ['required', 'integer', 'min:0', 'max:60'],
            'link' => ['nullable', 'url:http,https', 'max:2048'],
            'slides' => ['nullable', 'array', 'max:60'],
            'slides.*.title' => ['nullable', 'string', 'max:160'],
            'slides.*.meals' => ['nullable', 'string', 'max:40'],
            'slides.*.items' => ['nullable', 'string', 'max:4000'],
            'sections' => ['nullable', 'array'],
            'sections.*' => ['nullable', 'string', 'max:4000'],
            'note' => ['nullable', 'string', 'max:1000'],
            'is_hot' => ['sometimes', 'boolean'],
            'image' => [$imageRequired ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'about_video' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime', 'max:102400'],
            'about_video_url' => [
                'nullable',
                'url:http,https',
                'max:2048',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (ChinaMuslimPackage::youtubeId($value) === null) {
                        $fail('Paste a YouTube video link, for example https://www.youtube.com/watch?v=…');
                    }
                },
            ],
            'remove_about_video' => ['sometimes', 'boolean'],
        ], [
            'about_video.mimetypes' => 'The video must be an MP4, WebM or MOV file.',
            'about_video.max' => 'The video must be 100 MB or smaller. For longer videos, upload to YouTube and paste the link instead.',
        ]);

        $validated['details'] = ChinaMuslimPackage::composeDetails(
            array_values($validated['slides'] ?? []),
            $validated['sections'] ?? [],
            $validated['note'] ?? null,
        );

        unset($validated['image'], $validated['about_video'], $validated['remove_about_video'], $validated['is_hot'],$validated['slides'], $validated['sections'], $validated['note']);

        return $validated;
    }
}
