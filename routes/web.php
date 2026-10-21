<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\HomepageCampaignController;
use App\Http\Controllers\Admin\HomepageServiceController;
use App\Http\Controllers\Admin\IslandHoneymoonController;
use App\Http\Controllers\Admin\TourPackagePageController;
use App\Http\Middleware\EnsureAdmin;
use App\Models\HomepageCampaign;
use App\Models\HomepageService;
use App\Models\HomepageSetting;
use App\Models\IslandDestination;
use App\Models\IslandHoneymoonSetting;
use App\Models\TourPackagePage;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::view('/', 'SmartHolidays')->name('home');

Route::get('/smart-muslim', static function (): View {
    return view('SmartMuslimpage', [
        'campaigns' => HomepageCampaign::query()->orderBy('position')->get(),
        'services' => HomepageService::query()->orderBy('position')->get(),
        'servicesHeading' => HomepageSetting::query()
            ->where('key', 'services_heading')
            ->firstOrFail()
            ->value,
    ]);
})->name('smart-muslim');

Route::get('/island-honeymoon', function () {
    return view('Tour-Package(private).island-honeymoon.island-honeymoon', [
        'page' => IslandHoneymoonSetting::query()->firstOrFail(),
        'destinations' => IslandDestination::query()->orderBy('position')->get(),
    ]);
})->name('island-honeymoon');

Route::view('/tour-package-private', 'Tour-Package(private).introduction')
    ->name('tour-package-private');

Route::get('/indonesia', fn (): View => view('Tour-Package(private).indonesia.indonesia', [
    'page' => TourPackagePage::forSlug('indonesia'),
]))->name('indonesia');

Route::get('/indonesia/aceh', fn (): View => view('Tour-Package(private).custom.show', [
    'page' => TourPackagePage::query()->where('is_custom', true)->where('slug', 'aceh')->firstOrFail(),
]))->name('indonesia.aceh');

Route::get('/bali', fn (): View => view('Tour-Package(private).custom.show', [
    'page' => TourPackagePage::query()->where('is_custom', true)->where('slug', 'bali')->firstOrFail(),
]))->name('bali');

Route::get('/thailand', fn (): View => view('Tour-Package(private).thailand.thailand', [
    'page' => TourPackagePage::forSlug('thailand'),
]))->name('thailand');

Route::get('/vietnam', fn (): View => view('Tour-Package(private).vietnam.vietnam', [
    'page' => TourPackagePage::forSlug('vietnam'),
]))->name('vietnam');

Route::get('/philippines', fn (): View => view('Tour-Package(private).philippines.philippines', [
    'page' => TourPackagePage::forSlug('philippines'),
]))->name('philippines');

Route::get('/tour/{slug}', fn (string $slug): View => view('Tour-Package(private).custom.show', [
    'page' => TourPackagePage::query()->where('is_custom', true)->where('slug', $slug)->firstOrFail(),
]))->where('slug', '[a-z0-9-]+')->name('tour-package.show');

Route::get('/admin/login', [AuthController::class, 'create'])
    ->name('login');

Route::post('/admin/login', [AuthController::class, 'store'])
    ->middleware('throttle:admin-login')
    ->name('login.store');

Route::middleware(['auth', EnsureAdmin::class])->group(function () {
    Route::view('/admin', 'Admin.dashboard')
        ->name('admin.dashboard');

    Route::get('/admin/homepage', [HomepageCampaignController::class, 'index'])
        ->name('admin.homepage');
    Route::get('/admin/homepage/create', [HomepageCampaignController::class, 'create'])
        ->name('admin.homepage.create');
    Route::post('/admin/homepage', [HomepageCampaignController::class, 'store'])
        ->name('admin.homepage.store');
    Route::get('/admin/homepage/{campaign:slug}/edit', [HomepageCampaignController::class, 'edit'])
        ->name('admin.homepage.edit');
    Route::put('/admin/homepage/{campaign:slug}', [HomepageCampaignController::class, 'update'])
        ->name('admin.homepage.update');
    Route::delete('/admin/homepage/{campaign:slug}', [HomepageCampaignController::class, 'destroy'])
        ->name('admin.homepage.destroy');

    Route::get('/admin/homepage/services', [HomepageServiceController::class, 'index'])
        ->name('admin.homepage.services');
    Route::put('/admin/homepage/services/heading', [HomepageServiceController::class, 'updateHeading'])
        ->name('admin.homepage.services.heading');
    Route::get('/admin/homepage/services/{service:slug}/edit', [HomepageServiceController::class, 'edit'])
        ->name('admin.homepage.services.edit');
    Route::put('/admin/homepage/services/{service:slug}', [HomepageServiceController::class, 'update'])
        ->name('admin.homepage.services.update');

    Route::get('/admin/island-honeymoon', [IslandHoneymoonController::class, 'index'])
        ->name('admin.island-honeymoon');
    Route::put('/admin/island-honeymoon', [IslandHoneymoonController::class, 'updatePage'])
        ->name('admin.island-honeymoon.update');
    Route::get('/admin/island-honeymoon/destinations/create', [IslandHoneymoonController::class, 'createDestination'])
        ->name('admin.island-honeymoon.destinations.create');
    Route::post('/admin/island-honeymoon/destinations', [IslandHoneymoonController::class, 'storeDestination'])
        ->name('admin.island-honeymoon.destinations.store');
    Route::get('/admin/island-honeymoon/destinations/{destination:slug}/edit', [IslandHoneymoonController::class, 'editDestination'])
        ->name('admin.island-honeymoon.destinations.edit');
    Route::put('/admin/island-honeymoon/destinations/{destination:slug}', [IslandHoneymoonController::class, 'updateDestination'])
        ->name('admin.island-honeymoon.destinations.update');
    Route::delete('/admin/island-honeymoon/destinations/{destination:slug}', [IslandHoneymoonController::class, 'destroyDestination'])
        ->name('admin.island-honeymoon.destinations.destroy');

    Route::get('/admin/tour-packages/create', [TourPackagePageController::class, 'create'])
        ->name('admin.tour-packages.create');
    Route::post('/admin/tour-packages', [TourPackagePageController::class, 'store'])
        ->name('admin.tour-packages.store');
    Route::get('/admin/tour-packages/{page:slug}/edit', [TourPackagePageController::class, 'edit'])
        ->name('admin.tour-packages.edit');
    Route::put('/admin/tour-packages/{page:slug}', [TourPackagePageController::class, 'update'])
        ->name('admin.tour-packages.update');
    Route::delete('/admin/tour-packages/{page:slug}', [TourPackagePageController::class, 'destroy'])
        ->name('admin.tour-packages.destroy');

    Route::post('/admin/logout', [AuthController::class, 'destroy'])
        ->name('logout');
});
