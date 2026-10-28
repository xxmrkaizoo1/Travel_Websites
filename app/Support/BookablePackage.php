<?php

namespace App\Support;

use App\Enums\BookingStatus;
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
    /** @var array<string, int>|null Travellers on confirmed bookings, by departure key ("" for none). */
    private ?array $seatsTaken = null;

    /** @var array<string, int>|null */
    private ?array $departureSeats = null;

    /** @var list<array<string, mixed>>|null */
    private ?array $options = null;

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

        return $bookable->isBookable() ? $bookable : null;
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
     * Link for a "Book now" button, or null when booking is closed, every seat is taken, or there
     * is nothing to book for that series destination or option.
     */
    public function bookNowUrl(?string $destination = null, ?string $option = null): ?string
    {
        if (! $this->isOpen() || $this->isFull()) {
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
     * Series packages count seats per departure; every other package counts them for the whole package.
     */
    public function hasSeatsPerDeparture(): bool
    {
        return $this->package instanceof TourPackagePage && $this->package->isSeries();
    }

    /**
     * Stable key of a series departure, e.g. "melbourne--6-oct-2026". Bookings and seat settings use it.
     */
    public static function departureKey(string $destination, string $date): string
    {
        return Str::slug($destination).'--'.Str::slug($date);
    }

    /**
     * Seats the admin offers, or null for no limit: on one departure for series packages,
     * on the whole package otherwise.
     */
    public function seats(?string $departureKey = null): ?int
    {
        if (! $this->hasSeatsPerDeparture()) {
            return $this->package->seats;
        }

        if ($departureKey === null) {
            return null;
        }

        $this->departureSeats ??= $this->package->departureSeats()->pluck('seats', 'departure_key')->all();

        return isset($this->departureSeats[$departureKey]) ? (int) $this->departureSeats[$departureKey] : null;
    }

    /**
     * Travellers (adults and children) on confirmed or completed bookings, for one departure on series
     * packages. Requests still pending and cancelled bookings do not hold a seat.
     */
    public function seatsTaken(?string $departureKey = null): int
    {
        $this->seatsTaken ??= $this->package->bookings()
            ->whereIn('status', BookingStatus::revenueValues())
            ->groupBy('bookable_option')
            ->selectRaw('bookable_option, SUM(adults + children) as travellers')
            ->pluck('travellers', 'bookable_option')
            ->map(fn (mixed $travellers): int => (int) $travellers)
            ->all();

        if (! $this->hasSeatsPerDeparture()) {
            return array_sum($this->seatsTaken);
        }

        return $departureKey === null ? 0 : ($this->seatsTaken[$departureKey] ?? 0);
    }

    /**
     * Seats still free, or null when there is no limit.
     */
    public function seatsLeft(?string $departureKey = null): ?int
    {
        $seats = $this->seats($departureKey);

        return $seats === null ? null : max(0, $seats - $this->seatsTaken($departureKey));
    }

    /**
     * Whether every choice is fully booked: the whole package, or every departure on a series package.
     */
    public function isFull(): bool
    {
        return $this->options() === [] && $this->isBookable();
    }

    /**
     * Every departure of a series package, past and fully booked ones included, with its seats.
     *
     * @return list<array{key: string, destination: string, date: string, price: string, travel_date: ?string, seats: ?int, taken: int, left: ?int}>
     */
    public function departures(): array
    {
        if (! $this->hasSeatsPerDeparture()) {
            return [];
        }

        $departures = [];

        foreach ($this->package->package_data['series'] ?? [] as $destination) {
            $name = (string) ($destination['name'] ?? '');

            foreach ($destination['package_details']['departures'] ?? [] as $departure) {
                $date = (string) ($departure['date'] ?? '');

                if ($name === '' || $date === '') {
                    continue;
                }

                $key = self::departureKey($name, $date);
                $departures[] = [
                    'key' => $key,
                    'destination' => $name,
                    'date' => $date,
                    'price' => (string) ($departure['price'] ?? ''),
                    'travel_date' => self::departureDates($date)[0],
                    'seats' => $this->seats($key),
                    'taken' => $this->seatsTaken($key),
                    'left' => $this->seatsLeft($key),
                ];
            }
        }

        return $departures;
    }

    /**
     * Whether the package has something clients can book, open or not, even if every seat is taken.
     */
    public function isBookable(): bool
    {
        return $this->allOptions() !== [];
    }

    /**
     * The choices offered on the booking form, leaving out those with no seats left. A fixed travel
     * date comes from a series departure; otherwise the client picks their own date.
     *
     * @return list<array{key: string, group: ?string, label: string, destination: string, package_name: string, travel_date: ?string, price: ?int, days: ?int, seat_key: ?string, seats_left: ?int}>
     */
    public function options(): array
    {
        return $this->options ??= array_values(array_filter($this->allOptions(), fn (array $option): bool => $option['seats_left'] !== 0));
    }

    /**
     * @return list<array{key: string, group: ?string, label: string, destination: string, package_name: string, travel_date: ?string, price: ?int, days: ?int, seat_key: ?string, seats_left: ?int}>
     */
    private function allOptions(): array
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
                'seat_key' => null,
                'seats_left' => $this->seatsLeft(),
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
     * @return array{key: string, group: ?string, label: string, destination: string, package_name: string, travel_date: ?string, price: ?int, days: ?int, seat_key: ?string, seats_left: ?int}|null
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
     * Departures that are not marked fully booked or already gone, one option each, with the seats left
     * on that departure. A destination without any listed departures can still be booked for a date the
     * client chooses.
     *
     * @return list<array{key: string, group: ?string, label: string, destination: string, package_name: string, travel_date: ?string, price: ?int, days: ?int, seat_key: ?string, seats_left: ?int}>
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
                    'seat_key' => null,
                    'seats_left' => null,
                    ...$base,
                ];

                continue;
            }

            foreach ($departures as $index => $departure) {
                [$date, $lastDate] = self::departureDates((string) ($departure['date'] ?? ''));

                if (Str::contains((string) ($departure['availability'] ?? ''), 'fully booked', true) || ($lastDate !== null && $lastDate < today()->toDateString())) {
                    continue;
                }

                $seatKey = self::departureKey($name, (string) ($departure['date'] ?? ''));
                $seatsLeft = $this->seatsLeft($seatKey);

                $options[] = [
                    'key' => "{$slug}-{$index}",
                    'label' => collect([$name, $departure['date'] ?? '', $departure['price'] ?? '', $seatsLeft === null ? '' : $seatsLeft.' '.Str::plural('seat', $seatsLeft).' left'])->filter()->implode(' · '),
                    'travel_date' => $date,
                    'price' => self::price((string) ($departure['price'] ?? '')),
                    'seat_key' => $seatKey,
                    'seats_left' => $seatsLeft,
                    ...$base,
                ];
            }
        }

        return $options;
    }

    /**
     * @return list<array{key: string, group: ?string, label: string, destination: string, package_name: string, travel_date: ?string, price: ?int, days: ?int, seat_key: ?string, seats_left: ?int}>
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
                'seat_key' => null,
                'seats_left' => $this->seatsLeft(),
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
