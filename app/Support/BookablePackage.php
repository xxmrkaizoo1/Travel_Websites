<?php

namespace App\Support;

use App\Models\ChinaMuslimPackage;
use App\Models\TourPackagePage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * A Smart Muslim package clients can book online, with the choices it offers:
 * series departures, private itineraries, or the China Muslim package itself.
 */
class BookablePackage
{
    public function __construct(public readonly TourPackagePage|ChinaMuslimPackage $package) {}

    /**
     * Finds a package from its booking route key, e.g. "tour-package/series-2027" or "china-muslim-package/4".
     * Packages that offer nothing to book are treated as missing.
     */
    public static function fromRouteKey(string $routeKey): ?self
    {
        [$type, $key] = array_pad(explode('/', $routeKey, 2), 2, '');

        $package = match ($type) {
            'tour-package' => TourPackagePage::query()->where('slug', $key)->first(),
            'china-muslim-package' => ctype_digit($key) ? ChinaMuslimPackage::query()->find($key) : null,
            default => null,
        };

        if ($package === null) {
            return null;
        }

        $bookable = new self($package);

        return $bookable->options() === [] ? null : $bookable;
    }

    public function routeKey(): string
    {
        $key = $this->package instanceof TourPackagePage ? $this->package->slug : $this->package->getKey();

        return $this->package->getMorphClass().'/'.$key;
    }

    /**
     * Link to the booking form, optionally starting on one option or series destination.
     *
     * @param  array{option?: string, destination?: string}  $query
     */
    public function bookingUrl(array $query = []): string
    {
        return route('client.package-bookings.create', ['bookable' => $this->routeKey(), ...$query]);
    }

    /**
     * Link for a "Book now" button, or null when booking is closed or there is nothing to book
     * for that series destination or option.
     */
    public function bookNowUrl(?string $destination = null, ?string $option = null): ?string
    {
        if (! $this->isOpen()) {
            return null;
        }

        if ($destination !== null) {
            $option = $this->firstOptionKeyFor($destination);

            if ($option === null) {
                return null;
            }
        } elseif ($option !== null ? $this->option($option) === null : ! $this->isBookable()) {
            return null;
        }

        return $this->bookingUrl(array_filter(['option' => $option]));
    }

    public function title(): string
    {
        return $this->package instanceof TourPackagePage ? $this->package->name : $this->package->title;
    }

    public function pageUrl(): string
    {
        return $this->package instanceof TourPackagePage ? $this->package->publicUrl() : route('china-muslim');
    }

    public function imageUrl(): string
    {
        return $this->package instanceof TourPackagePage
            ? $this->package->imageUrl($this->package->hero_image)
            : $this->package->imageUrl();
    }

    public function isOpen(): bool
    {
        return (bool) $this->package->booking_open;
    }

    /**
     * Whether the package has something clients can book, open or not.
     */
    public function isBookable(): bool
    {
        return $this->options() !== [];
    }

    /**
     * The choices offered on the booking form. A fixed travel date comes from a series departure;
     * otherwise the client picks their own date.
     *
     * @return list<array{key: string, group: ?string, label: string, destination: string, package_name: string, travel_date: ?string, price: ?int, days: ?int}>
     */
    public function options(): array
    {
        if ($this->package instanceof ChinaMuslimPackage) {
            return [[
                'key' => 'package',
                'group' => null,
                'label' => "{$this->package->title} · {$this->package->days}D{$this->package->nights}N · from {$this->package->formattedPrice()}",
                'destination' => 'China',
                'package_name' => $this->package->title,
                'travel_date' => null,
                'price' => $this->package->price > 0 ? $this->package->price : null,
                'days' => $this->package->days ?: null,
            ]];
        }

        if ($this->package->isSeries()) {
            return $this->seriesOptions();
        }

        if ($this->package->hasItineraries()) {
            return $this->itineraryOptions();
        }

        return [];
    }

    /**
     * @return array{key: string, group: ?string, label: string, destination: string, package_name: string, travel_date: ?string, price: ?int, days: ?int}|null
     */
    public function option(?string $key): ?array
    {
        return collect($this->options())->firstWhere('key', $key);
    }

    /**
     * Key of the first option for a series destination, used by the "Book" buttons on each destination.
     */
    public function firstOptionKeyFor(string $destination): ?string
    {
        return collect($this->options())->firstWhere('group', $destination)['key'] ?? null;
    }

    /**
     * Departures that are not fully booked or already gone, one option each. A destination
     * without any listed departures can still be booked for a date the client chooses.
     *
     * @return list<array{key: string, group: ?string, label: string, destination: string, package_name: string, travel_date: ?string, price: ?int, days: ?int}>
     */
    private function seriesOptions(): array
    {
        $options = [];

        foreach ($this->package->package_data['series'] as $destination) {
            $name = (string) ($destination['name'] ?? '');
            $details = $destination['package_details'] ?? [];
            $departures = $details['departures'] ?? [];
            $slug = Str::slug($name);

            if ($name === '' || $slug === '') {
                continue;
            }

            $base = [
                'group' => $name,
                'destination' => $name,
                'package_name' => $this->package->name.' · '.(filled($details['headline'] ?? '') ? $details['headline'] : $name),
                'days' => self::days((string) ($details['duration'] ?? '')),
            ];

            if ($departures === []) {
                $options[] = [
                    'key' => $slug,
                    'label' => "{$name} · choose your own date",
                    'travel_date' => null,
                    'price' => self::price((string) ($details['price_from'] ?? '')),
                    ...$base,
                ];

                continue;
            }

            foreach ($departures as $index => $departure) {
                [$date, $lastDate] = self::departureDates((string) ($departure['date'] ?? ''));

                if (Str::contains((string) ($departure['availability'] ?? ''), 'fully booked', true) || ($lastDate !== null && $lastDate < today()->toDateString())) {
                    continue;
                }

                $options[] = [
                    'key' => "{$slug}-{$index}",
                    'label' => collect([$name, $departure['date'] ?? '', $departure['price'] ?? ''])->filter()->implode(' · '),
                    'travel_date' => $date,
                    'price' => self::price((string) ($departure['price'] ?? '')),
                    ...$base,
                ];
            }
        }

        return $options;
    }

    /**
     * @return list<array{key: string, group: ?string, label: string, destination: string, package_name: string, travel_date: ?string, price: ?int, days: ?int}>
     */
    private function itineraryOptions(): array
    {
        $options = [];

        foreach ($this->package->package_data['itineraries'] as $index => $itinerary) {
            $title = (string) ($itinerary['title'] ?? '');

            if ($title === '') {
                continue;
            }

            $options[] = [
                'key' => "itinerary-{$index}",
                'group' => null,
                'label' => collect([$title, Str::title(Str::lower((string) ($itinerary['duration'] ?? '')))])->filter()->implode(' · '),
                'destination' => $this->package->name,
                'package_name' => $title,
                'travel_date' => null,
                'price' => null,
                'days' => self::days((string) ($itinerary['duration'] ?? '')),
            ];
        }

        return $options;
    }

    /**
     * The fixed departure date and the last possible date (both Y-m-d) of a departure such as
     * "6 Mar 2027", "6 – 11 Sep 2026" or "November 2026: 7, 14, 21, 28". A list of several days
     * has no fixed date, so the client picks one; text that cannot be read leaves both open.
     *
     * @return array{0: ?string, 1: ?string}
     */
    private static function departureDates(string $text): array
    {
        $text = trim($text);

        if (preg_match('/^(\d{1,2})(?:\s*[–-]\s*\d{1,2})? ([A-Za-z]{3,9} \d{4})$/u', $text, $match)) {
            $date = self::date("{$match[1]} {$match[2]}");

            return [$date, $date];
        }

        if (preg_match('/^([A-Za-z]{3,9} \d{4}):\s*(\d{1,2}(?:\s*,\s*\d{1,2})*)$/u', $text, $match)) {
            $days = array_map('intval', preg_split('/\s*,\s*/', $match[2]));
            $last = self::date(max($days)." {$match[1]}");

            return [count($days) === 1 ? $last : null, $last];
        }

        return [null, null];
    }

    private static function date(string $text): ?string
    {
        try {
            return Carbon::parse($text)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * The first ringgit amount in a price such as "RM 7,790" or "Now only RM 7,790 · normal price RM 8,090".
     */
    private static function price(string $text): ?int
    {
        return preg_match('/RM\s*([\d,]+)/i', $text, $match) ? (int) str_replace(',', '', $match[1]) : null;
    }

    /**
     * Number of days in a duration such as "7D6N", "4 DAYS · 3 NIGHTS" or "9 days · 7 nights".
     */
    private static function days(string $text): ?int
    {
        return preg_match('/(\d+)\s*D(?:AYS?)?(?![A-Z])/i', $text, $match) && (int) $match[1] > 0 ? (int) $match[1] : null;
    }
}
