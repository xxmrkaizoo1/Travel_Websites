<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\ChinaMuslimPackageController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\ClientSuspensionController;
use App\Http\Controllers\Admin\ContactPageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DestinationController;
use App\Http\Controllers\Admin\EnquiryController as AdminEnquiryController;
use App\Http\Controllers\Admin\GalleryPageController;
use App\Http\Controllers\Admin\GroupTourDestinationController;
use App\Http\Controllers\Admin\GroupTourDestinationStateController;
use App\Http\Controllers\Admin\GroupToursPageController;
use App\Http\Controllers\Admin\HomepageCampaignController;
use App\Http\Controllers\Admin\HomepageConsultantController;
use App\Http\Controllers\Admin\HomepageServiceController;
use App\Http\Controllers\Admin\IslandHoneymoonController;
use App\Http\Controllers\Admin\MiceController;
use App\Http\Controllers\Admin\OnlineBookingController;
use App\Http\Controllers\Admin\PackageLikeStatsController;
use App\Http\Controllers\Admin\PrivateToursPageController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SmartHolidaysPageController;
use App\Http\Controllers\Admin\SmartMuslimMenuController;
use App\Http\Controllers\Admin\TicketingPageController;
use App\Http\Controllers\Admin\TourPackagePageController;
use App\Http\Controllers\Client\BookingController as ClientBookingController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\EnquiryController as ClientEnquiryController;
use App\Http\Controllers\Client\PackageBookingController;
use App\Http\Controllers\Client\PasswordController as ClientPasswordController;
use App\Http\Controllers\Client\PortalTourController;
use App\Http\Controllers\Client\ProfileController as ClientProfileController;
use App\Http\Controllers\ClientAuthController;
use App\Http\Controllers\ClientPasswordResetController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\PackageLikeController;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureClient;
use App\Models\Booking;
use App\Models\ChinaAboutPage;
use App\Models\ChinaMuslimPackage;
use App\Models\ContactPage;
use App\Models\GalleryPage;
use App\Models\GroupTourDestination;
use App\Models\GroupToursPage;
use App\Models\HomepageCampaign;
use App\Models\HomepageConsultant;
use App\Models\HomepageService;
use App\Models\HomepageSetting;
use App\Models\IslandDestination;
use App\Models\IslandHoneymoonSetting;
use App\Models\MiceClient;
use App\Models\MiceGalleryImage;
use App\Models\MicePage;
use App\Models\PrivateToursPage;
use App\Models\SmartHolidaysPage;
use App\Models\TicketingPage;
use App\Models\TourPackagePage;
use App\Models\User;
use App\Support\BookablePackage;
use App\Support\PackageLikes;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

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
            'href' => route('tour-package.show', 'series-departures-2026').'#series-'.Str::slug($destination['name']),
            'likeKey' => PackageLikes::key($seriesPage, $destination['name']),
        ])
        ->all();

    return view('SmartHolidays', [
        'page' => SmartHolidaysPage::query()->firstOrFail(),
        'popularTours' => $popularTours,
    ]);
})->name('home');

Route::get('/group-tours', fn (): View => view('group-tours', [
    'page' => GroupToursPage::forKind(GroupToursPage::KIND_GROUP),
]))->name('group-tours');

Route::get('/ground-tours', fn (): View => view('group-tours', [
    'page' => GroupToursPage::forKind(GroupToursPage::KIND_GROUND),
]))->name('ground-tours');

Route::get('/domestic-tours', fn (): View => view('group-tours', [
    'page' => GroupToursPage::forKind(GroupToursPage::KIND_DOMESTIC),
]))->name('domestic-tours');

Route::get('/group-tours/booking-conditions', fn (): View => view('group-tours-booking-conditions', [
    'page' => GroupToursPage::forKind(GroupToursPage::KIND_GROUP),
]))->name('group-tours.booking-conditions');

Route::get('/group-tours/{destination}', fn (GroupTourDestination $destination): View => view('group-tours-destination', [
    'page' => GroupToursPage::forKind(GroupToursPage::KIND_GROUP),
    'destination' => $destination,
]))->name('group-tours.destination');

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
Route::get('/client/forgot-password', [ClientPasswordResetController::class, 'create'])
    ->name('client.password.request');
Route::post('/client/forgot-password', [ClientPasswordResetController::class, 'store'])
    ->middleware('throttle:client-password-reset')
    ->name('client.password.email');
Route::get('/client/reset-password', [ClientPasswordResetController::class, 'edit'])
    ->name('client.password.reset');
Route::post('/client/reset-password', [ClientPasswordResetController::class, 'update'])
    ->middleware('throttle:client-password-reset-verify')
    ->name('client.password.store');
Route::post('/client/reset-password/resend', [ClientPasswordResetController::class, 'resend'])
    ->middleware('throttle:client-password-reset')
    ->name('client.password.resend');
Route::post('/client/logout', [ClientAuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('client.logout');

Route::get('/smart-muslim', static function (): View {
    return view('SmartMuslimpage', [
        'campaigns' => HomepageCampaign::query()->orderBy('position')->get(),
        'services' => HomepageService::query()->orderBy('position')->get(),
        'consultants' => HomepageConsultant::query()->orderBy('position')->orderBy('id')->get(),
        'servicesHeading' => HomepageSetting::query()
            ->where('key', 'services_heading')
            ->firstOrFail()
            ->value,
    ]);
})->name('smart-muslim');

Route::get('/package-likes', [PackageLikeController::class, 'index'])
    ->middleware('throttle:60,1')
    ->name('package-likes.index');
Route::post('/package-likes', [PackageLikeController::class, 'toggle'])
    ->middleware('throttle:30,1')
    ->name('package-likes.toggle');

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
    'page' => GalleryPage::query()->firstOrFail(),
]))->name('gallery');

Route::view('/privacy-policy', 'privacy-policy')->name('privacy-policy');

Route::get('/island-honeymoon', function () {
    return view('Smart-Muslim.Tour-Package(private).island-honeymoon.island-honeymoon', [
        'page' => IslandHoneymoonSetting::query()->firstOrFail(),
        'destinations' => IslandDestination::query()->orderBy('position')->get(),
        'islandPackages' => TourPackagePage::query()->where('is_custom', true)->where('parent_slug', 'island-honeymoon')->orderBy('name')->get(),
    ]);
})->name('island-honeymoon');

Route::get('/ticketing', fn (): View => view('ticketing', [
    'page' => TicketingPage::query()->firstOrFail(),
]))->name('ticketing');

Route::get('/contact-us', fn (): View => view('contact', [
    'page' => ContactPage::query()->firstOrFail(),
]))->name('contact');

Route::get('/tour-package-private', fn (): View => view('Smart-Muslim.Tour-Package(private).introduction', [
    'page' => PrivateToursPage::query()->firstOrFail(),
]))->name('tour-package-private');

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

Route::post('/enquiries', [EnquiryController::class, 'store'])
    ->middleware('throttle:website-enquiries')
    ->name('enquiries.store');

Route::bind('clientBooking', fn (string $reference): Booking => Booking::query()
    ->where('reference', $reference)
    ->where('user_id', request()->user()?->id)
    ->firstOrFail());

Route::bind('bookable', fn (string $value): BookablePackage => BookablePackage::fromRouteKey($value) ?? abort(404));

Route::middleware(['auth', EnsureClient::class])->prefix('my-account')->name('client.')->group(function () {
    Route::get('/', ClientDashboardController::class)->name('dashboard');
    Route::get('/bookings', [ClientBookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{clientBooking}', [ClientBookingController::class, 'show'])->name('bookings.show');
    Route::get('/requests', [ClientEnquiryController::class, 'index'])->name('enquiries.index');
    Route::post('/requests', [ClientEnquiryController::class, 'store'])
        ->middleware('throttle:website-enquiries')
        ->name('enquiries.store');
    Route::get('/profile', [ClientProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ClientProfileController::class, 'update'])->name('profile.update');
    Route::put('/password', [ClientPasswordController::class, 'update'])->name('password.update');
    Route::post('/tour/complete', PortalTourController::class)->name('tour.complete');
    Route::get('/book/{bookable}', [PackageBookingController::class, 'create'])
        ->where('bookable', '(tour-package|china-muslim-package)/[a-z0-9-]+')
        ->name('package-bookings.create');
    Route::post('/book/{bookable}', [PackageBookingController::class, 'store'])
        ->where('bookable', '(tour-package|china-muslim-package)/[a-z0-9-]+')
        ->middleware('throttle:website-enquiries')
        ->name('package-bookings.store');
});

Route::bind('clientAccount', fn (string $value): User => User::query()->clients()->findOrFail($value));

Route::get('/admin/login', [AuthController::class, 'create'])
    ->name('login');

Route::post('/admin/login', [AuthController::class, 'store'])
    ->middleware('throttle:admin-login')
    ->name('login.store');

Route::middleware(['auth', EnsureAdmin::class])->group(function () {
    Route::get('/admin', DashboardController::class)
        ->name('admin.dashboard');

    Route::resource('/admin/bookings', BookingController::class)
        ->except('show')
        ->names('admin.bookings');
    Route::patch('/admin/bookings/{booking}/status', [BookingController::class, 'updateStatus'])
        ->name('admin.bookings.status');

    Route::resource('/admin/clients', ClientController::class)
        ->except('edit')
        ->parameters(['clients' => 'clientAccount'])
        ->names('admin.clients');
    Route::post('/admin/clients/{clientAccount}/suspension', [ClientSuspensionController::class, 'store'])
        ->name('admin.clients.suspension.store');
    Route::delete('/admin/clients/{clientAccount}/suspension', [ClientSuspensionController::class, 'destroy'])
        ->name('admin.clients.suspension.destroy');

    Route::resource('/admin/enquiries', AdminEnquiryController::class)
        ->only(['index', 'create', 'store', 'update', 'destroy'])
        ->names('admin.enquiries');

    Route::get('/admin/package-likes', PackageLikeStatsController::class)
        ->name('admin.package-likes');

    Route::get('/admin/settings', [SettingsController::class, 'edit'])
        ->name('admin.settings');
    Route::put('/admin/settings/profile', [SettingsController::class, 'updateProfile'])
        ->name('admin.settings.profile');
    Route::put('/admin/settings/appearance', [SettingsController::class, 'updateAppearance'])
        ->name('admin.settings.appearance');
    Route::put('/admin/settings/password', [SettingsController::class, 'updatePassword'])
        ->middleware('throttle:6,1')
        ->name('admin.settings.password');
    Route::delete('/admin/settings/sessions', [SettingsController::class, 'destroyOtherSessions'])
        ->middleware('throttle:6,1')
        ->name('admin.settings.sessions');

    Route::get('/admin/online-booking', [OnlineBookingController::class, 'edit'])
        ->name('admin.online-booking.edit');
    Route::put('/admin/online-booking', [OnlineBookingController::class, 'update'])
        ->name('admin.online-booking.update');
    Route::put('/admin/online-booking/{bookable}', [OnlineBookingController::class, 'toggle'])
        ->where('bookable', '(tour-package|china-muslim-package)/[a-z0-9-]+')
        ->name('admin.online-booking.toggle');

    Route::get('/admin/destinations', [DestinationController::class, 'index'])
        ->name('admin.destinations.index');

    Route::get('/admin/smart-holidays', [SmartHolidaysPageController::class, 'edit'])
        ->name('admin.smart-holidays.edit');
    Route::put('/admin/smart-holidays', [SmartHolidaysPageController::class, 'update'])
        ->name('admin.smart-holidays.update');

    Route::get('/admin/group-tours', [GroupToursPageController::class, 'edit'])
        ->name('admin.group-tours.edit');
    Route::put('/admin/group-tours', [GroupToursPageController::class, 'update'])
        ->name('admin.group-tours.update');
    Route::get('/admin/ground-tours', [GroupToursPageController::class, 'edit'])
        ->defaults('kind', GroupToursPage::KIND_GROUND)
        ->name('admin.ground-tours.edit');
    Route::put('/admin/ground-tours', [GroupToursPageController::class, 'update'])
        ->defaults('kind', GroupToursPage::KIND_GROUND)
        ->name('admin.ground-tours.update');
    Route::get('/admin/domestic-tours', [GroupToursPageController::class, 'edit'])
        ->defaults('kind', GroupToursPage::KIND_DOMESTIC)
        ->name('admin.domestic-tours.edit');
    Route::put('/admin/domestic-tours', [GroupToursPageController::class, 'update'])
        ->defaults('kind', GroupToursPage::KIND_DOMESTIC)
        ->name('admin.domestic-tours.update');
    Route::get('/admin/group-tours/booking-conditions', [GroupToursPageController::class, 'editBookingConditions'])
        ->name('admin.group-tours.booking-conditions.edit');
    Route::put('/admin/group-tours/booking-conditions', [GroupToursPageController::class, 'updateBookingConditions'])
        ->name('admin.group-tours.booking-conditions.update');
    Route::get('/admin/group-tours/destinations/create', [GroupTourDestinationController::class, 'create'])
        ->name('admin.group-tour-destinations.create');
    Route::post('/admin/group-tours/destinations', [GroupTourDestinationController::class, 'store'])
        ->name('admin.group-tour-destinations.store');
    Route::get('/admin/group-tours/destinations/{destination}/edit', [GroupTourDestinationController::class, 'edit'])
        ->name('admin.group-tour-destinations.edit');
    Route::put('/admin/group-tours/destinations/{destination}', [GroupTourDestinationController::class, 'update'])
        ->name('admin.group-tour-destinations.update');
    Route::get('/admin/group-tours/destinations/{destination}/states/create', [GroupTourDestinationStateController::class, 'create'])
        ->name('admin.group-tour-destinations.states.create');
    Route::post('/admin/group-tours/destinations/{destination}/states', [GroupTourDestinationStateController::class, 'store'])
        ->name('admin.group-tour-destinations.states.store');
    Route::get('/admin/group-tours/destinations/{destination}/states/{state}/edit', [GroupTourDestinationStateController::class, 'edit'])
        ->name('admin.group-tour-destinations.states.edit');
    Route::put('/admin/group-tours/destinations/{destination}/states/{state}', [GroupTourDestinationStateController::class, 'update'])
        ->name('admin.group-tour-destinations.states.update');
    Route::delete('/admin/group-tours/destinations/{destination}/states/{state}', [GroupTourDestinationStateController::class, 'destroy'])
        ->name('admin.group-tour-destinations.states.destroy');

    Route::get('/admin/homepage', [HomepageCampaignController::class, 'index'])
        ->name('admin.homepage');
    // Registered before the campaign routes so "consultants" is not read as a campaign slug.
    Route::get('/admin/homepage/consultants', [HomepageConsultantController::class, 'index'])
        ->name('admin.homepage.consultants');
    Route::post('/admin/homepage/consultants', [HomepageConsultantController::class, 'store'])
        ->name('admin.homepage.consultants.store');
    Route::put('/admin/homepage/consultants', [HomepageConsultantController::class, 'update'])
        ->name('admin.homepage.consultants.update');
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
    Route::put('/admin/china-muslim-travellers', [ChinaMuslimPackageController::class, 'updateTravellerPhotos'])
        ->name('admin.china-muslim.travellers');
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

    Route::get('/admin/ticketing', [TicketingPageController::class, 'edit'])
        ->name('admin.ticketing.edit');
    Route::put('/admin/ticketing', [TicketingPageController::class, 'update'])
        ->name('admin.ticketing.update');

    Route::get('/admin/contact', [ContactPageController::class, 'edit'])
        ->name('admin.contact.edit');
    Route::put('/admin/contact', [ContactPageController::class, 'update'])
        ->name('admin.contact.update');

    Route::get('/admin/private-tours', [PrivateToursPageController::class, 'edit'])
        ->name('admin.private-tours.edit');
    Route::put('/admin/private-tours', [PrivateToursPageController::class, 'update'])
        ->name('admin.private-tours.update');

    Route::get('/admin/gallery', [GalleryPageController::class, 'edit'])
        ->name('admin.gallery.edit');
    Route::put('/admin/gallery', [GalleryPageController::class, 'update'])
        ->name('admin.gallery.update');

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
