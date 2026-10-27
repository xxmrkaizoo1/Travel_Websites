<?php

namespace App\Providers;

use App\Models\ChinaMuslimPackage;
use App\Models\Enquiry;
use App\Models\GroupTourDestination;
use App\Models\GroupToursPage;
use App\Models\HomepageCampaign;
use App\Models\HomepageService;
use App\Models\IslandDestination;
use App\Models\SmartMuslimMenu;
use App\Models\TourPackagePage;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Database\Eloquent\Relations\Relation;
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
        Relation::morphMap([
            'tour-package' => TourPackagePage::class,
            'china-muslim-package' => ChinaMuslimPackage::class,
        ]);

        View::composer(['partials.site-header', 'Admin.partials.sidebar'], function (ViewContract $view): void {
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
                ->with('groupTourDestinations', GroupTourDestination::query()->orderBy('position')->orderBy('name')->get(['slug', 'name', 'states', 'regions', 'place_term']))
                ->with('groundToursPage', GroupToursPage::query()->where('kind', GroupToursPage::KIND_GROUND)->first(['kind', 'tours']))
                ->with('smartMuslimMenus', $smartMuslimMenus)
                ->with('seriesDestinations', TourPackagePage::query()->where('slug', 'series-departures-2026')->first(['slug', 'package_data'])?->package_data['series'] ?? [])
                ->with('customTourPagesByParent', $pages->groupBy(fn (TourPackagePage $page): string => (string) $page->parent_slug));
        });

        View::composer('partials.site-header', function (ViewContract $view): void {
            $user = auth()->user();

            $view->with('clientBookings', $user !== null && ! $user->is_admin
                ? $user->bookings()->latest('travel_date')->limit(3)->get()
                : collect());
        });

        View::composer('client.portal.layout', function (ViewContract $view): void {
            $client = auth()->user();

            $view->with('portalCounts', [
                'bookings' => $client->bookings()->upcoming()->count(),
                'requests' => $client->enquiries()->pending()->count(),
            ]);
        });

        View::composer('Admin.layouts.workspace', function (ViewContract $view): void {
            $view->with('sidebarPendingEnquiries', Enquiry::query()->pending()->count());
        });

        View::composer('Admin.partials.sidebar', function (ViewContract $view): void {
            $view->with('sidebarHomepageCampaigns', HomepageCampaign::query()->orderBy('position')->get(['slug', 'headline']))
                ->with('sidebarHomepageServices', HomepageService::query()->orderBy('position')->get(['slug', 'title']))
                ->with('sidebarIslandDestinations', IslandDestination::query()->orderBy('position')->get(['slug', 'name']))
                ->with('sidebarChinaMuslimPackages', ChinaMuslimPackage::query()->orderBy('position')->orderBy('id')->get(['id', 'title']));
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
        RateLimiter::for('website-enquiries', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
        RateLimiter::for('client-otp-verify', function (Request $request) {
            $email = $request->session()->get('client.pending_email');

            return Limit::perMinute(10)->by((is_string($email) ? Str::lower($email) : 'guest').'|'.$request->ip());
        });
        RateLimiter::for('client-password-reset', fn (Request $request) => [
            Limit::perMinute(3)->by('minute|'.$request->ip()),
            Limit::perHour(10)->by('hour|'.$request->ip()),
        ]);
        RateLimiter::for('client-password-reset-verify', function (Request $request) {
            $email = $request->session()->get('client.reset_email');

            return Limit::perMinute(10)->by((is_string($email) ? $email : 'guest').'|'.$request->ip());
        });
        RateLimiter::for('client-otp-resend', function (Request $request) {
            $email = $request->session()->get('client.pending_email');

            return Limit::perMinute(3)->by((is_string($email) ? Str::lower($email) : 'guest').'|'.$request->ip());
        });
    }
}
