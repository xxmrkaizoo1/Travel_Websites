<?php

namespace App\Providers;

use App\Models\SmartMuslimMenu;
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
            $smartMuslimMenus = SmartMuslimMenu::query()
                ->whereNull('parent_id')
                ->with(['children', 'pages'])
                ->orderBy('position')
                ->orderBy('id')
                ->get();

            $view->with('customTourPages', $pages)
                ->with('smartMuslimMenus', $smartMuslimMenus)
                ->with('seriesDestinations', TourPackagePage::query()->where('slug', 'series-departures-2026')->first(['slug', 'package_data'])?->package_data['series'] ?? [])
                ->with('customTourPagesByParent', $pages->groupBy(fn (TourPackagePage $page): string => (string) $page->parent_slug));
        });

        RateLimiter::for('admin-login', function (Request $request) {
            $email = $request->input('email');
            $email = is_string($email) ? Str::lower($email) : '';

            return Limit::perMinute(5)
                ->by(Str::transliterate($email).'|'.$request->ip());
        });

        RateLimiter::for('client-login', function (Request $request) {
            $email = $request->input('email');
            $email = is_string($email) ? Str::lower($email) : '';

            return Limit::perMinute(5)
                ->by(Str::transliterate($email).'|'.$request->ip());
        });

        RateLimiter::for('client-register', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
        RateLimiter::for('client-otp-verify', function (Request $request) {
            $email = $request->session()->get('client.pending_email');

            return Limit::perMinute(10)->by((is_string($email) ? Str::lower($email) : 'guest').'|'.$request->ip());
        });
        RateLimiter::for('client-otp-resend', function (Request $request) {
            $email = $request->session()->get('client.pending_email');

            return Limit::perMinute(3)->by((is_string($email) ? Str::lower($email) : 'guest').'|'.$request->ip());
        });
    }
}
