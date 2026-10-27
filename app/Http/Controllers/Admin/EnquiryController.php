<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EnquirySource;
use App\Enums\EnquiryStatus;
use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Models\User;
use App\Support\DestinationCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EnquiryController extends Controller
{
    private const TABS = ['pending', 'closed', 'all'];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'tab' => ['nullable', Rule::in(self::TABS)],
        ]);
        $tab = $filters['tab'] ?? 'pending';

        $enquiries = Enquiry::query()
            ->with(['client:id,name', 'booking:id,reference'])
            ->search($filters['search'] ?? null)
            ->when($tab === 'pending', fn ($query) => $query->pending())
            ->when($tab === 'closed', fn ($query) => $query->where('status', EnquiryStatus::Closed->value))
            ->latest()
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('Admin.enquiries.index', [
            'enquiries' => $enquiries,
            'filters' => $filters,
            'tab' => $tab,
            'counts' => [
                'pending' => Enquiry::query()->pending()->count(),
                'closed' => Enquiry::query()->where('status', EnquiryStatus::Closed->value)->count(),
                'all' => Enquiry::query()->count(),
            ],
        ]);
    }

    public function create(DestinationCatalog $catalog): View
    {
        return view('Admin.enquiries.create', ['destinations' => $catalog->names()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255', 'required_without:phone'],
            'phone' => ['nullable', 'string', 'max:30', 'required_without:email'],
            'destination' => ['nullable', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:2000'],
            'source' => ['required', Rule::enum(EnquirySource::class)],
        ], [
            'email.required_without' => 'Add an email address or a phone number so the team can reply.',
            'phone.required_without' => 'Add a phone number or an email address so the team can reply.',
        ]);

        $enquiry = new Enquiry($validated);
        $enquiry->client()->associate(filled($validated['email'] ?? null) ? User::query()->clients()->where('email', $validated['email'])->first() : null);
        $enquiry->save();

        return redirect()->route('admin.enquiries.index')->with('status', "Enquiry from {$enquiry->name} logged.");
    }

    public function update(Request $request, Enquiry $enquiry): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(EnquiryStatus::class)],
        ]);

        $enquiry->update($validated);

        return back()->with('status', "Enquiry from {$enquiry->name} marked as ".$enquiry->status->label().'.');
    }

    public function destroy(Enquiry $enquiry): RedirectResponse
    {
        $enquiry->delete();

        return back()->with('status', "Enquiry from {$enquiry->name} deleted.");
    }
}
