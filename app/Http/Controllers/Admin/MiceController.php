<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MiceClient;
use App\Models\MiceGalleryImage;
use App\Models\MicePage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MiceController extends Controller
{
    public function index(): View
    {
        return view('Admin.mice.index', [
            'page' => MicePage::query()->firstOrFail(),
            'clients' => MiceClient::query()->orderBy('position')->orderBy('id')->get(),
            'galleryImages' => MiceGalleryImage::query()->orderBy('position')->orderBy('id')->get(),
        ]);
    }

    public function updatePage(Request $request): RedirectResponse
    {
        $page = MicePage::query()->firstOrFail();
        $validated = $request->validate([
            'hero_eyebrow' => ['required', 'string', 'max:120'],
            'hero_title' => ['required', 'string', 'max:120'],
            'hero_highlight' => ['required', 'string', 'max:120'],
            'hero_body' => ['required', 'string', 'max:1500'],
            'hero_cta_label' => ['required', 'string', 'max:100'],
            'intro_eyebrow' => ['required', 'string', 'max:120'],
            'intro_heading' => ['required', 'string', 'max:160'],
            'intro_highlight' => ['required', 'string', 'max:100'],
            'intro_body' => ['required', 'string', 'max:3000'],
            'intro_image_alt' => ['required', 'string', 'max:255'],
            'intro_image_caption' => ['required', 'string', 'max:120'],
            'why_eyebrow' => ['required', 'string', 'max:120'],
            'why_heading' => ['required', 'string', 'max:160'],
            'highlights' => ['required', 'array', 'size:4'],
            'highlights.*.title' => ['required', 'string', 'max:100'],
            'highlights.*.body' => ['required', 'string', 'max:500'],
            'contact_eyebrow' => ['required', 'string', 'max:120'],
            'contact_heading' => ['required', 'string', 'max:160'],
            'contact_body' => ['required', 'string', 'max:1500'],
            'phone_primary' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s]+$/'],
            'phone_secondary' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s]+$/'],
            'email' => ['required', 'email', 'max:255'],
            'whatsapp_number' => ['required', 'string', 'max:20', 'regex:/^[0-9]+$/'],
            'clientele_eyebrow' => ['required', 'string', 'max:120'],
            'clientele_heading' => ['required', 'string', 'max:160'],
            'clientele_body' => ['required', 'string', 'max:500'],
            'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'intro_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $oldPaths = [];

        foreach (['hero_image', 'intro_image'] as $field) {
            unset($validated[$field]);

            if (! $request->hasFile($field)) {
                continue;
            }

            $path = $request->file($field)->store('mice', 'public');

            if (! is_string($path)) {
                return back()->withInput()->withErrors([$field => 'The image could not be saved. Please try again.']);
            }

            $oldPaths[] = $page->{$field};
            $validated[$field] = $path;
        }

        $page->update($validated);

        $this->deleteStoredImages($oldPaths);

        return redirect()->route('admin.mice')->with('status', 'Company Trip (MICE) page updated.');
    }

    public function storeClient(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'logo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $path = $request->file('logo')->store('mice/clients', 'public');

        if (! is_string($path)) {
            return back()->withInput()->withErrors(['logo' => 'The logo could not be saved. Please try again.']);
        }

        MiceClient::query()->create([
            'name' => $validated['name'],
            'logo' => $path,
            'position' => (int) MiceClient::query()->max('position') + 1,
        ]);

        return redirect()->route('admin.mice')->with('status', 'Client logo added.');
    }

    public function destroyClient(MiceClient $client): RedirectResponse
    {
        $logo = $client->logo;
        $client->delete();

        $this->deleteStoredImages([$logo]);

        return redirect()->route('admin.mice')->with('status', 'Client logo removed.');
    }

    public function storeGalleryImage(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'caption' => ['nullable', 'string', 'max:120'],
            'gallery_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $path = $request->file('gallery_image')->store('mice/gallery', 'public');

        if (! is_string($path)) {
            return back()->withInput()->withErrors(['gallery_image' => 'The photo could not be saved. Please try again.']);
        }

        MiceGalleryImage::query()->create([
            'image' => $path,
            'caption' => $validated['caption'] ?? '',
            'position' => (int) MiceGalleryImage::query()->max('position') + 1,
        ]);

        return redirect()->route('admin.mice')->with('status', 'Gallery photo added.');
    }

    public function destroyGalleryImage(MiceGalleryImage $image): RedirectResponse
    {
        $path = $image->image;
        $image->delete();

        $this->deleteStoredImages([$path]);

        return redirect()->route('admin.mice')->with('status', 'Gallery photo removed.');
    }

    /**
     * @param  array<int, string>  $paths
     */
    private function deleteStoredImages(array $paths): void
    {
        $uploaded = array_filter($paths, static fn (string $path): bool => ! str_starts_with($path, 'images/') && ! preg_match('#^https?://#', $path));

        Storage::disk('public')->delete(array_values($uploaded));
    }
}
