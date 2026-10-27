<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ClientController extends Controller
{
    private const FILTERS = ['active', 'suspended', 'unverified'];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'filter' => ['nullable', Rule::in(self::FILTERS)],
        ]);
        $search = trim($filters['search'] ?? '');

        $clients = User::query()
            ->clients()
            ->withCount('bookings')
            ->withSum(['bookings as revenue' => fn (Builder $query) => $query->whereIn('status', BookingStatus::revenueValues())], 'amount')
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")))
            ->when($filters['filter'] ?? null, fn (Builder $query, string $filter) => match ($filter) {
                'active' => $query->whereNull('suspended_at')->whereNotNull('email_verified_at'),
                'suspended' => $query->whereNotNull('suspended_at'),
                'unverified' => $query->whereNull('email_verified_at'),
            })
            ->latest()
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('Admin.clients.index', [
            'clients' => $clients,
            'filters' => $filters,
            'counts' => [
                'all' => User::query()->clients()->count(),
                'active' => User::query()->clients()->whereNull('suspended_at')->whereNotNull('email_verified_at')->count(),
                'suspended' => User::query()->clients()->whereNotNull('suspended_at')->count(),
                'unverified' => User::query()->clients()->whereNull('email_verified_at')->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('Admin.clients.form', ['client' => new User]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique(User::class)],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\s()-]+$/'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $client = new User($validated);
        $client->forceFill(['is_admin' => false, 'email_verified_at' => now()])->save();

        return redirect()->route('admin.clients.show', $client)->with('status', "Client account for {$client->name} created. Share the temporary password with them so they can sign in.");
    }

    public function show(User $clientAccount): View
    {
        $clientAccount->loadCount('bookings')
            ->loadSum(['bookings as revenue' => fn (Builder $query) => $query->whereIn('status', BookingStatus::revenueValues())], 'amount');

        return view('Admin.clients.show', [
            'client' => $clientAccount,
            'bookings' => $clientAccount->bookings()->latest('travel_date')->get()->each->setRelation('client', $clientAccount),
            'enquiries' => $clientAccount->enquiries()->latest()->limit(10)->get(),
        ]);
    }

    public function update(Request $request, User $clientAccount): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique(User::class)->ignore($clientAccount)],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\s()-]+$/'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        $clientAccount->fill($validated);

        if ($clientAccount->isDirty('email')) {
            $clientAccount->email_verified_at = now();
        }

        $clientAccount->save();

        return redirect()->route('admin.clients.show', $clientAccount)->with('status', isset($validated['password']) ? 'Client details and password updated.' : 'Client details updated.');
    }

    public function destroy(User $clientAccount): RedirectResponse
    {
        if ($clientAccount->bookings()->exists()) {
            return back()->withErrors(['client' => "{$clientAccount->name} has bookings, so the account cannot be deleted. Suspend it instead."]);
        }

        $clientAccount->delete();

        return redirect()->route('admin.clients.index')->with('status', "Client account for {$clientAccount->name} deleted.");
    }
}
