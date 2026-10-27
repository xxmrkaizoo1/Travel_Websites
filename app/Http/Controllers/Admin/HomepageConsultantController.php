<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomepageConsultant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class HomepageConsultantController extends Controller
{
    private const WHATSAPP_RULE = ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9\s\-()]{8,30}$/'];

    public function index(): View
    {
        return view('Admin.homepage.consultants.index', [
            'consultants' => HomepageConsultant::query()->orderBy('position')->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (HomepageConsultant::query()->count() >= HomepageConsultant::MAX_CONSULTANTS) {
            return back()->withInput()->withErrors(['new_name' => 'The section holds up to '.HomepageConsultant::MAX_CONSULTANTS.' consultants. Remove one first.']);
        }

        $validated = $request->validate([
            'new_name' => ['required', 'string', 'max:60'],
            'new_role' => ['nullable', 'string', 'max:80'],
            'new_whatsapp' => self::WHATSAPP_RULE,
            'new_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], $this->messages('new_whatsapp'));

        HomepageConsultant::query()->create([
            'position' => (HomepageConsultant::query()->max('position') ?? 0) + 1,
            'name' => $validated['new_name'],
            'role' => $validated['new_role'] ?? null,
            'whatsapp' => $validated['new_whatsapp'] ?? null,
            'photo_path' => $request->file('new_photo')?->store('homepage-consultants', 'public'),
        ]);

        return redirect()->route('admin.homepage.consultants')->with('status', "{$validated['new_name']} added to the team.");
    }

    /**
     * Saves every consultant on the page at once: details, order, photo changes and removals.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'consultants' => ['required', 'array'],
            'consultants.*.name' => ['required', 'string', 'max:60'],
            'consultants.*.role' => ['nullable', 'string', 'max:80'],
            'consultants.*.whatsapp' => self::WHATSAPP_RULE,
            'consultants.*.position' => ['required', 'integer', 'min:1', 'max:99'],
            'consultants.*.photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'consultants.*.remove_photo' => ['sometimes', 'boolean'],
            'consultants.*.remove' => ['sometimes', 'boolean'],
        ], $this->messages('consultants.*.whatsapp'));

        $consultants = HomepageConsultant::query()->whereKey(array_keys($validated['consultants']))->get()->keyBy('id');
        $unusedFiles = [];

        foreach ($validated['consultants'] as $id => $fields) {
            $consultant = $consultants->get($id);

            if ($consultant === null) {
                continue;
            }

            if (! empty($fields['remove'])) {
                $unusedFiles[] = $consultant->photo_path;
                $consultant->delete();

                continue;
            }

            $photoPath = $consultant->photo_path;

            if ($request->hasFile("consultants.{$id}.photo")) {
                $photoPath = $request->file("consultants.{$id}.photo")->store('homepage-consultants', 'public');
            } elseif (! empty($fields['remove_photo'])) {
                $photoPath = null;
            }

            if ($photoPath !== $consultant->photo_path) {
                $unusedFiles[] = $consultant->photo_path;
            }

            $consultant->update([
                'name' => $fields['name'],
                'role' => $fields['role'] ?? null,
                'whatsapp' => $fields['whatsapp'] ?? null,
                'position' => $fields['position'],
                'photo_path' => $photoPath,
            ]);
        }

        Storage::disk('public')->delete(array_values(array_filter(
            $unusedFiles,
            fn (?string $path): bool => filled($path) && ! str_starts_with($path, 'images/'),
        )));

        return redirect()->route('admin.homepage.consultants')->with('status', 'Team saved.');
    }

    /**
     * @return array<string, string>
     */
    private function messages(string $whatsappField): array
    {
        return [
            "{$whatsappField}.regex" => 'Enter a WhatsApp number using digits only, for example 016-550 0590 or +6016 550 0590.',
        ];
    }
}
