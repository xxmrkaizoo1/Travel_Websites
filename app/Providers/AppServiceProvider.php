<?php

namespace App\Providers;

use App\Models\TourPackagePage;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    private const FIXED_DESTINATIONS_BY_COUNTRY = [
        'vietnam' => ['Hanoi', 'Da Nang', 'Ho Chi Minh', 'Hoi An'],
        'philippines' => ['Manila', 'Boracay', 'Palawan', 'Cebu'],
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['partials.site-header', 'Admin.dashboard'], function (ViewContract $view): void {
            $pages = TourPackagePage::query()
                ->where('is_custom', true)
                ->orderBy('name')
                ->get(['slug', 'name', 'kind', 'parent_slug']);

            $view->with('customTourPages', $pages)
                ->with('customTourPagesByParent', $pages->groupBy(fn (TourPackagePage $page): string => (string) $page->parent_slug))
                ->with('fixedDestinationsByCountry', self::FIXED_DESTINATIONS_BY_COUNTRY);
        });

        RateLimiter::for('admin-login', function (Request $request) {
            $email = $request->input('email');
            $email = is_string($email) ? Str::lower($email) : '';

            return Limit::perMinute(5)
                ->by(Str::transliterate($email).'|'.$request->ip());
        });
    }
}
