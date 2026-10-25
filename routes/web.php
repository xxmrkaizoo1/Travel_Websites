<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ChinaMuslimPackageController;
use App\Http\Controllers\Admin\HomepageCampaignController;
use App\Http\Controllers\Admin\HomepageServiceController;
use App\Http\Controllers\Admin\IslandHoneymoonController;
use App\Http\Controllers\Admin\MiceController;
use App\Http\Controllers\Admin\SmartMuslimMenuController;
use App\Http\Controllers\Admin\TourPackagePageController;
use App\Http\Controllers\ClientAuthController;
use App\Http\Middleware\EnsureAdmin;
use App\Models\ChinaAboutPage;
use App\Models\ChinaMuslimPackage;
use App\Models\HomepageCampaign;
use App\Models\HomepageService;
use App\Models\HomepageSetting;
use App\Models\IslandDestination;
use App\Models\IslandHoneymoonSetting;
use App\Models\MiceClient;
use App\Models\MiceGalleryImage;
use App\Models\MicePage;
use App\Models\TourPackagePage;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get('/', function (): View {
    $seriesPage = TourPackagePage::query()
        ->where('slug', 'series-departures-2026')
        ->first(['slug', 'hero_image', 'package_data']);
    $popularTours = collect($seriesPage?->package_data['series'] ?? [])
        ->take(5)
        ->map(fn (array $destination): array => [
            'title' => $destination['name'],
            'country' => $destination['country'],
            'tagline' => $destination['tagline'] ?? '',
            'image' => $seriesPage->imageUrl($destination['gallery'][0]['image'] ?? $seriesPage->hero_image),
            'href' => route('tour-package.show', 'series-departures-2026').'#series-'.\Illuminate\Support\Str::slug($destination['name']),
        ])
        ->all();

    return view('SmartHolidays', compact('popularTours'));
})->name('home');

Route::get('/client/login', [ClientAuthController::class, 'create'])
    ->name('client.login');
Route::post('/client/login', [ClientAuthController::class, 'store'])
    ->middleware('throttle:client-login')
    ->name('client.login.store');
Route::get('/client/register', [ClientAuthController::class, 'register'])
    ->name('client.register');
Route::post('/client/register', [ClientAuthController::class, 'storeRegistration'])
    ->middleware('throttle:client-register')
    ->name('client.register.store');
Route::get('/client/verify-email', [ClientAuthController::class, 'verification'])
    ->name('client.verify');
Route::post('/client/verify-email', [ClientAuthController::class, 'verify'])
    ->middleware('throttle:client-otp-verify')
    ->name('client.verify.store');
Route::post('/client/verify-email/resend', [ClientAuthController::class, 'resendVerification'])
    ->middleware('throttle:client-otp-resend')
    ->name('client.verify.resend');
Route::post('/client/logout', [ClientAuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('client.logout');

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

Route::get('/china-muslim', fn (): View => view('Smart-Muslim.China-Muslim.china-muslim', [
    'about' => ChinaAboutPage::query()->first(),
    'packages' => ChinaMuslimPackage::query()->orderBy('position')->orderBy('id')->get(),
]))->name('china-muslim');

Route::get('/company-trip-mice', fn (): View => view('Smart-Muslim.company-trip-mice', [
    'page' => MicePage::query()->firstOrFail(),
    'clients' => MiceClient::query()->orderBy('position')->orderBy('id')->get(),
    'galleryImages' => MiceGalleryImage::query()->orderBy('position')->orderBy('id')->get(),
]))->name('company-trip-mice');

Route::get('/gallery', fn (): View => view('Smart-Muslim.gallery', [
    'destinations' => [
        [
            'country' => 'South Korea',
            'image' => 'images/series/flag-kr.png',
            'gallery_image' => 'images/series/seoul-1.jpg',
            'gallery_alt' => 'Historic palace in South Korea',
        ],
        [
            'country' => 'China',
            'image' => 'images/series/flag-cn.png',
            'gallery_image' => 'images/series/kunming-1.jpg',
            'gallery_alt' => 'Traditional Chinese architecture',
        ],
        [
            'country' => 'Vietnam',
            'image' => 'images/series/flag-vn.png',
            'gallery_image' => 'images/series/danang-1.jpg',
            'gallery_alt' => 'Illuminated bridge over a river in Vietnam',
        ],
        [
            'country' => 'Indonesia',
            'image' => 'images/series/flag-id.png',
            'gallery_image' => 'images/series/medan-1.jpg',
            'gallery_alt' => 'Waterfall surrounded by forest in Indonesia',
        ],
//    [
//             'country' => 'Indonesia',
//             'image' => 'images/series/flag-id.png',
//             'gallery_image' => 'images/series/medan-1.jpg',
//             'gallery_alt' => 'Waterfall surrounded by forest in Indonesia',
//         ],






    ],
]))->name('gallery');

Route::view('/privacy-policy', 'privacy-policy')->name('privacy-policy');

Route::get('/island-honeymoon', function () {
    return view('Smart-Muslim.Tour-Package(private).island-honeymoon.island-honeymoon', [
        'page' => IslandHoneymoonSetting::query()->firstOrFail(),
        'destinations' => IslandDestination::query()->orderBy('position')->get(),
    ]);
})->name('island-honeymoon');

Route::view('/tour-package-private', 'Smart-Muslim.Tour-Package(private).introduction')
    ->name('tour-package-private');

Route::get('/indonesia', fn (): View => view('Smart-Muslim.Tour-Package(private).indonesia.indonesia', [
    'page' => TourPackagePage::forSlug('indonesia'),
]))->name('indonesia');

Route::get('/indonesia/aceh', fn (): View => view('Smart-Muslim.Tour-Package(private).custom.show', [
    'page' => TourPackagePage::query()->where('is_custom', true)->where('slug', 'aceh')->firstOrFail(),
]))->name('indonesia.aceh');

Route::get('/bali', fn (): View => view('Smart-Muslim.Tour-Package(private).custom.show', [
    'page' => TourPackagePage::query()->where('is_custom', true)->where('slug', 'bali')->firstOrFail(),
]))->name('bali');

Route::get('/thailand', fn (): View => view('Smart-Muslim.Tour-Package(private).thailand.thailand', [
    'page' => TourPackagePage::forSlug('thailand'),
]))->name('thailand');

Route::get('/vietnam', fn (): View => view('Smart-Muslim.Tour-Package(private).vietnam.vietnam', [
    'page' => TourPackagePage::forSlug('vietnam'),
]))->name('vietnam');

Route::get('/philippines', fn (): View => view('Smart-Muslim.Tour-Package(private).philippines.philippines', [
    'page' => TourPackagePage::forSlug('philippines'),
]))->name('philippines');

Route::get('/tour/{slug}', function (string $slug): View {
    $page = TourPackagePage::query()->where('is_custom', true)->where('slug', $slug)->firstOrFail();
    $seriesPages = $page->slug === 'series-departures-2026'
        ? TourPackagePage::query()->where('is_custom', true)->where('parent_slug', $page->slug)->orderBy('name')->get()
        : collect();

    return view('Smart-Muslim.Tour-Package(private).custom.show', compact('page', 'seriesPages'));
})->where('slug', '[a-z0-9-]+')->name('tour-package.show');

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

    Route::put('/admin/china-muslim-about', [ChinaMuslimPackageController::class, 'updateAbout'])
        ->name('admin.china-muslim.about');
    Route::get('/admin/china-muslim', [ChinaMuslimPackageController::class, 'index'])
        ->name('admin.china-muslim');
    Route::get('/admin/china-muslim/create', [ChinaMuslimPackageController::class, 'create'])
        ->name('admin.china-muslim.create');
    Route::post('/admin/china-muslim', [ChinaMuslimPackageController::class, 'store'])
        ->name('admin.china-muslim.store');
    Route::get('/admin/china-muslim/{package}/edit', [ChinaMuslimPackageController::class, 'edit'])
        ->name('admin.china-muslim.edit');
    Route::put('/admin/china-muslim/{package}', [ChinaMuslimPackageController::class, 'update'])
        ->name('admin.china-muslim.update');
    Route::delete('/admin/china-muslim/{package}', [ChinaMuslimPackageController::class, 'destroy'])
        ->name('admin.china-muslim.destroy');

    Route::get('/admin/mice', [MiceController::class, 'index'])
        ->name('admin.mice');
    Route::put('/admin/mice', [MiceController::class, 'updatePage'])
        ->name('admin.mice.update');
    Route::post('/admin/mice/clients', [MiceController::class, 'storeClient'])
        ->name('admin.mice.clients.store');
    Route::delete('/admin/mice/clients/{client}', [MiceController::class, 'destroyClient'])
        ->name('admin.mice.clients.destroy');
    Route::post('/admin/mice/gallery', [MiceController::class, 'storeGalleryImage'])
        ->name('admin.mice.gallery.store');
    Route::delete('/admin/mice/gallery/{image}', [MiceController::class, 'destroyGalleryImage'])
        ->name('admin.mice.gallery.destroy');

    Route::get('/admin/tour-packages/create', [TourPackagePageController::class, 'create'])
        ->name('admin.tour-packages.create');
    Route::post('/admin/tour-packages', [TourPackagePageController::class, 'store'])
        ->name('admin.tour-packages.store');
    Route::get('/admin/smart-muslim/menus', [SmartMuslimMenuController::class, 'index'])
        ->name('admin.smart-muslim.menus.index');
    Route::get('/admin/smart-muslim/menus/{menu}/pages/create', [TourPackagePageController::class, 'createForSmartMuslimMenu'])
        ->name('admin.smart-muslim.menus.pages.create');
    Route::post('/admin/smart-muslim/menus/{menu}/pages', [TourPackagePageController::class, 'storeForSmartMuslimMenu'])
        ->name('admin.smart-muslim.menus.pages.store');
    Route::post('/admin/smart-muslim/menus', [SmartMuslimMenuController::class, 'store'])
        ->name('admin.smart-muslim.menus.store');
    Route::put('/admin/smart-muslim/menus/{menu}', [SmartMuslimMenuController::class, 'update'])
        ->name('admin.smart-muslim.menus.update');
    Route::post('/admin/smart-muslim/menus/{menu}/items', [SmartMuslimMenuController::class, 'storeChild'])
        ->name('admin.smart-muslim.menus.items.store');
    Route::delete('/admin/smart-muslim/menus/{menu}', [SmartMuslimMenuController::class, 'destroy'])
        ->name('admin.smart-muslim.menus.destroy');
    Route::put('/admin/tour-packages/{page:slug}/move', [TourPackagePageController::class, 'move'])
        ->name('admin.tour-packages.move');
    Route::get('/admin/tour-packages/{page:slug}/edit', [TourPackagePageController::class, 'edit'])
        ->name('admin.tour-packages.edit');
    Route::put('/admin/tour-packages/{page:slug}', [TourPackagePageController::class, 'update'])
        ->name('admin.tour-packages.update');
    Route::delete('/admin/tour-packages/{page:slug}', [TourPackagePageController::class, 'destroy'])
        ->name('admin.tour-packages.destroy');

    Route::post('/admin/logout', [AuthController::class, 'destroy'])
        ->name('logout');
});
