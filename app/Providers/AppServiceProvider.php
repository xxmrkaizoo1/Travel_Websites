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
                ->with('seriesDestinations', TourPackagePage::query()->where('slug', 'series-departures-2026')->first(['slug', 'package_data'])?->package_data['series'] ?? [])
                ->with('customTourPagesByParent', $pages->groupBy(fn (TourPackagePage $page): string => (string) $page->parent_slug));
        });

        RateLimiter::for('admin-login', function (Request $request) {
            $email = $request->input('email');
            $email = is_string($email) ? Str::lower($email) : '';

            return Limit::perMinute(5)
                ->by(Str::transliterate($email).'|'.$request->ip());
        });
    }
}
