<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactPage;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use RuntimeException;

class ContactPageController extends Controller
{
    private const COMPANY_FIELDS = ['name', 'logo', 'address', 'phone', 'whatsapp', 'email', 'website'];

    public function edit(): View
    {
        return view('Admin.contact.edit', [
            'page' => ContactPage::query()->firstOrFail(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $page = ContactPage::query()->firstOrFail();
        $storedPaths = $page->storedImagePaths();
        $companies = $this->filledCompanies($request);
        $imageUpload = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];

        $validated = Validator::make([...$request->except('companies'), 'companies' => $companies], [
            'meta_title' => ['required', 'string', 'max:120'],
            'meta_description' => ['required', 'string', 'max:500'],
            'hero_eyebrow' => ['required', 'string', 'max:60'],
            'hero_title' => ['required', 'string', 'max:120'],
            'hero_body' => ['required', 'string', 'max:500'],
            'hero_image' => ['nullable', 'string', 'max:2048', $this->imageSourceRule($storedPaths)],
            'hero_image_file' => $imageUpload,
            'companies_heading' => ['required', 'string', 'max:120'],
            'companies' => ['required', 'array', 'max:'.ContactPage::MAX_COMPANIES],
            'companies.*.name' => ['required', 'string', 'max:120'],
            'companies.*.logo' => ['nullable', 'string', 'max:2048', $this->imageSourceRule($storedPaths)],
            'companies.*.logo_file' => $imageUpload,
            'companies.*.address' => ['required', 'string', 'max:255'],
            'companies.*.phone' => ['nullable', 'string', 'max:120', 'regex:/^[0-9\s\/()+-]+$/'],
            'companies.*.whatsapp' => ['nullable', 'string', 'max:120', 'regex:/^[0-9\s\/()+-]+$/'],
            'companies.*.email' => ['nullable', 'email', 'max:120'],
            'companies.*.website' => ['nullable', 'string', 'max:120', 'regex:#^(https?://)?[A-Za-z0-9.-]+\.[A-Za-z]{2,}(/\S*)?$#'],
            'map_query' => ['required', 'string', 'max:255'],
        ], [
            'companies.required' => 'Add at least one company.',
            'companies.*.phone.regex' => 'Phone numbers may only use digits, spaces, dashes and "/" between numbers.',
            'companies.*.whatsapp.regex' => 'WhatsApp numbers may only use digits, spaces, dashes and "/" between numbers.',
            'companies.*.website.regex' => 'The website must look like www.example.com.',
        ])->validate();

        $newPaths = [];

        try {
            unset($validated['hero_image_file']);
            $validated['hero_image'] ??= null;

            if ($request->hasFile('hero_image_file')) {
                $validated['hero_image'] = $newPaths[] = $this->storeUpload($request->file('hero_image_file'));
            }

            $validated['companies'] = array_map(function (array $company) use (&$newPaths): array {
                if (($company['logo_file'] ?? null) instanceof UploadedFile) {
                    $company['logo'] = $newPaths[] = $this->storeUpload($company['logo_file']);
                }

                return array_map(static fn (mixed $value): string => (string) $value, array_intersect_key($company, array_flip(self::COMPANY_FIELDS)) + array_fill_keys(self::COMPANY_FIELDS, ''));
            }, $validated['companies']);

            $page->update($validated);
        } catch (RuntimeException $exception) {
            Storage::disk('public')->delete($newPaths);

            return back()->withInput()->withErrors(['images' => $exception->getMessage()]);
        }

        Storage::disk('public')->delete(array_values(array_diff($storedPaths, $page->fresh()->storedImagePaths())));

        return redirect()->route('admin.contact.edit')->with('status', 'Contact page updated.');
    }

    /**
     * Company rows with at least one value, keeping each row's uploaded logo.
     *
     * @return array<int, array<string, mixed>>
     */
    private function filledCompanies(Request $request): array
    {
        $rows = [];

        foreach ((array) $request->input('companies', []) as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $row = array_intersect_key($row, array_flip(self::COMPANY_FIELDS));
            $file = $request->file("companies.{$index}.logo_file");

            if ($file !== null) {
                $row['logo_file'] = $file;
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
        $path = $file->store('contact', 'public');

        if (! is_string($path)) {
            throw new RuntimeException('A file could not be saved. Please try again.');
        }

        return $path;
    }
}
