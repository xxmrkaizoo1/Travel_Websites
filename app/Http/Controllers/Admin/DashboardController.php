<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Support\PackageLikes;
use App\Support\TravelOverview;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, TravelOverview $overview): View
    {
        return view('Admin.dashboard', [
            'stats' => $overview->statCards(),
            'chart' => $overview->revenueChart((string) $request->query('range', '7d')),
            'ranges' => TravelOverview::RANGES,
            'popularDestinations' => $overview->destinations(4),
            'recentBookings' => Booking::query()->with('client:id,name,email')->latest()->latest('id')->limit(6)->get(),
            'bookingCount' => Booking::query()->count(),
            'lovedPackages' => $this->lovedPackages(),
        ]);
    }

    /**
     * The five packages with the most likes.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function lovedPackages(): Collection
    {
        $totals = PackageLikes::totals();

        if ($totals->isEmpty()) {
            return collect();
        }

        return PackageLikes::catalogue()
            ->filter(fn (array $package): bool => $totals->has($package['key']))
            ->map(fn (array $package): array => [...$package, ...$totals[$package['key']]])
            ->sortByDesc('total')
            ->take(5)
            ->values();
    }
}
