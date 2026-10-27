<?php

namespace App\Support;

use App\Enums\BookingStatus;
use App\Enums\EnquiryStatus;
use App\Models\Booking;
use App\Models\Enquiry;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Figures for the admin "Travel overview": stat cards, the revenue chart and destination rankings.
 * Revenue counts confirmed and completed bookings on the day they were booked.
 */
class TravelOverview
{
    /** Chart ranges the revenue panel offers. */
    public const RANGES = ['7d' => 'Last 7 days', '30d' => 'Last 30 days', '12m' => 'Last 12 months'];

    private const SPARKLINE_MONTHS = 8;

    private CarbonImmutable $now;

    public function __construct(private DestinationCatalog $catalog)
    {
        $this->now = CarbonImmutable::now();
    }

    /**
     * @return array<int, array{label: string, value: string, icon: string, tone: string, trend: ?float, foot: string, footTone: string, sparkline: string}>
     */
    public function statCards(): array
    {
        $since = $this->now->startOfMonth()->subMonths(self::SPARKLINE_MONTHS - 1);
        $bookings = Booking::query()->where('created_at', '>=', $since)->get(['amount', 'status', 'created_at']);
        $bookingsByMonth = $this->monthlyTotals($bookings, fn (): float => 1);
        $revenueByMonth = $this->monthlyTotals($bookings->filter(fn (Booking $booking): bool => $booking->status->earnsRevenue()), fn (Booking $booking): float => (float) $booking->amount);

        $clientsBefore = User::query()->clients()->where('created_at', '<', $since)->count();
        $newClientsByMonth = $this->monthlyTotals(User::query()->clients()->where('created_at', '>=', $since)->get(['created_at']), fn (): float => 1);
        $clientTotals = [];
        foreach ($newClientsByMonth as $count) {
            $clientTotals[] = $clientsBefore += $count;
        }

        $enquiriesByMonth = $this->monthlyTotals(Enquiry::query()->where('created_at', '>=', $since)->get(['created_at']), fn (): float => 1);
        $pendingEnquiries = Enquiry::query()->pending()->count();
        $newToday = Enquiry::query()->where('status', EnquiryStatus::New->value)->where('created_at', '>=', $this->now->startOfDay())->count();
        $newClientsThisMonth = (int) end($newClientsByMonth);
        $bookingTrend = $this->change(...array_slice($bookingsByMonth, -2));
        $revenueTrend = $this->change(...array_slice($revenueByMonth, -2));

        return [
            [
                'label' => 'Total bookings',
                'value' => number_format(Booking::query()->count()),
                'icon' => '▤',
                'tone' => 'blue',
                'trend' => $bookingTrend,
                'foot' => $bookingTrend === null ? 'No bookings last month to compare' : 'new bookings vs last month',
                'footTone' => '',
                'sparkline' => $this->sparkline($bookingsByMonth),
            ],
            [
                'label' => 'Revenue',
                'value' => 'RM '.number_format((float) Booking::query()->earningRevenue()->sum('amount')),
                'icon' => '＄',
                'tone' => 'green',
                'trend' => $revenueTrend,
                'foot' => $revenueTrend === null ? 'No revenue last month to compare' : 'vs last month',
                'footTone' => '',
                'sparkline' => $this->sparkline($revenueByMonth),
            ],
            [
                'label' => 'Active clients',
                'value' => number_format(User::query()->clients()->whereNull('suspended_at')->count()),
                'icon' => '♧',
                'tone' => 'purple',
                'trend' => null,
                'foot' => $newClientsThisMonth > 0 ? '+'.number_format($newClientsThisMonth).' new this month' : 'No new sign-ups this month',
                'footTone' => $newClientsThisMonth > 0 ? 'up' : '',
                'sparkline' => $this->sparkline($clientTotals),
            ],
            [
                'label' => 'Pending enquiries',
                'value' => number_format($pendingEnquiries),
                'icon' => '✉',
                'tone' => 'orange',
                'trend' => null,
                'foot' => $pendingEnquiries === 0 ? 'All caught up' : ($newToday > 0 ? $newToday.' new today' : 'Needs attention'),
                'footTone' => $pendingEnquiries === 0 ? 'up' : 'attention',
                'sparkline' => $this->sparkline($enquiriesByMonth),
            ],
        ];
    }

    /**
     * Revenue per day (or month) for the chosen range, drawn for a 700 × 205 chart.
     *
     * @return array{range: string, rangeLabel: string, total: float, change: ?float, previousLabel: string, xLabels: array<int, string>, yLabels: array<int, string>, line: string, area: string, lastPoint: array{x: float, y: float}, points: array<int, array{label: string, value: float}>}
     */
    public function revenueChart(string $range): array
    {
        $range = array_key_exists($range, self::RANGES) ? $range : '7d';
        $monthly = $range === '12m';
        $count = ['7d' => 7, '30d' => 30, '12m' => 12][$range];
        $end = $monthly ? $this->now->startOfMonth() : $this->now->startOfDay();
        $start = $monthly ? $end->subMonths($count - 1) : $end->subDays($count - 1);
        $previousStart = $monthly ? $start->subMonths($count) : $start->subDays($count);

        $bookings = Booking::query()->earningRevenue()->where('created_at', '>=', $previousStart)->get(['amount', 'created_at']);
        $bucketKey = fn (CarbonImmutable $date): string => $date->format($monthly ? 'Y-m' : 'Y-m-d');
        $totals = $bookings->groupBy(fn (Booking $booking): string => $bucketKey($booking->created_at->toImmutable()))
            ->map(fn (Collection $group): float => (float) $group->sum('amount'));

        $points = [];
        for ($i = 0; $i < $count; $i++) {
            $date = $monthly ? $start->addMonths($i) : $start->addDays($i);
            $points[] = ['date' => $date, 'label' => $date->format($monthly ? 'M Y' : 'D, j M'), 'value' => $totals[$bucketKey($date)] ?? 0.0];
        }

        $total = array_sum(array_column($points, 'value'));
        $previous = (float) $bookings->filter(fn (Booking $booking): bool => $booking->created_at->lt($start))->sum('amount');
        $max = $this->niceCeiling(max(array_column($points, 'value')));
        $coordinates = [];
        foreach ($points as $i => $point) {
            $coordinates[] = ['x' => round($i * 700 / max($count - 1, 1), 1), 'y' => round(184 - ($point['value'] / $max) * 172, 1)];
        }

        $line = $this->smoothPath($coordinates);
        $labelCount = min($count, $monthly ? 6 : 7);
        $xLabels = [];
        for ($i = 0; $i < $labelCount; $i++) {
            $date = $points[(int) round($i * ($count - 1) / max($labelCount - 1, 1))]['date'];
            $xLabels[] = $date->format($monthly ? 'M' : ($range === '7d' ? 'D' : 'j M'));
        }

        return [
            'range' => $range,
            'rangeLabel' => self::RANGES[$range],
            'total' => $total,
            'change' => $this->change($previous, $total),
            'previousLabel' => ['7d' => 'previous 7 days', '30d' => 'previous 30 days', '12m' => 'previous 12 months'][$range],
            'xLabels' => $xLabels,
            'yLabels' => array_map(fn (float $step): string => $this->shortMoney($max * $step), [1, 0.75, 0.5, 0.25, 0]),
            'line' => $line,
            'area' => $line.' V184H0Z',
            'lastPoint' => end($coordinates),
            'points' => array_map(fn (array $point): array => ['label' => $point['label'], 'value' => $point['value']], $points),
        ];
    }

    /**
     * Destinations ranked by bookings (cancelled bookings excluded).
     *
     * @return Collection<int, array{name: string, bookings: int, travellers: int, revenue: float, image: ?string, initials: string}>
     */
    public function destinations(?int $limit = null): Collection
    {
        $query = Booking::query()
            ->where('status', '!=', BookingStatus::Cancelled->value)
            ->select('destination')
            ->selectRaw('COUNT(*) as bookings_count')
            ->selectRaw('SUM(adults + children) as travellers')
            ->selectRaw('SUM(CASE WHEN status IN (?, ?) THEN amount ELSE 0 END) as revenue', BookingStatus::revenueValues())
            ->groupBy('destination')
            ->orderByDesc('bookings_count')
            ->orderBy('destination');

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->toBase()->get()->map(fn (object $row): array => [
            'name' => $row->destination,
            'bookings' => (int) $row->bookings_count,
            'travellers' => (int) $row->travellers,
            'revenue' => (float) $row->revenue,
            'image' => $this->catalog->imageFor($row->destination),
            'initials' => mb_strtoupper(mb_substr($row->destination, 0, 2)),
        ]);
    }

    /**
     * Sums a value per calendar month for the sparkline window, oldest month first.
     *
     * @param  Collection<int, Model>  $models
     * @param  callable(Model): float  $value
     * @return array<int, float>
     */
    private function monthlyTotals(Collection $models, callable $value): array
    {
        $totals = [];
        for ($i = self::SPARKLINE_MONTHS - 1; $i >= 0; $i--) {
            $totals[$this->now->startOfMonth()->subMonths($i)->format('Y-m')] = 0.0;
        }

        foreach ($models as $model) {
            $month = $model->created_at->format('Y-m');

            if (array_key_exists($month, $totals)) {
                $totals[$month] += $value($model);
            }
        }

        return array_values($totals);
    }

    /**
     * Percentage change, or null when there is nothing to compare against.
     */
    private function change(float $previous, float $current): ?float
    {
        if ($previous <= 0) {
            return null;
        }

        return round(($current - $previous) / $previous * 100, 1);
    }

    /**
     * A 100 × 34 sparkline path.
     *
     * @param  array<int, float>  $values
     */
    private function sparkline(array $values): string
    {
        $max = max(max($values), 1);
        $min = min($values);
        $span = max($max - $min, 1);
        $points = [];

        foreach (array_values($values) as $i => $value) {
            $points[] = ['x' => round($i * 100 / max(count($values) - 1, 1), 1), 'y' => round(30 - (($value - $min) / $span) * 26, 1)];
        }

        return $this->smoothPath($points);
    }

    /**
     * A smooth curve through the points (Catmull-Rom converted to cubic Béziers).
     *
     * @param  array<int, array{x: float, y: float}>  $points
     */
    private function smoothPath(array $points): string
    {
        $path = 'M'.$points[0]['x'].' '.$points[0]['y'];
        $last = count($points) - 1;
        $minY = min(array_column($points, 'y'));
        $maxY = max(array_column($points, 'y'));

        for ($i = 0; $i < $last; $i++) {
            $p0 = $points[max($i - 1, 0)];
            $p1 = $points[$i];
            $p2 = $points[$i + 1];
            $p3 = $points[min($i + 2, $last)];
            $c1y = min(max($p1['y'] + ($p2['y'] - $p0['y']) / 6, $minY), $maxY);
            $c2y = min(max($p2['y'] - ($p3['y'] - $p1['y']) / 6, $minY), $maxY);
            $path .= sprintf(
                'C%s %s %s %s %s %s',
                round($p1['x'] + ($p2['x'] - $p0['x']) / 6, 1), round($c1y, 1),
                round($p2['x'] - ($p3['x'] - $p1['x']) / 6, 1), round($c2y, 1),
                $p2['x'], $p2['y'],
            );
        }

        return $path;
    }

    /**
     * Rounds a chart maximum up to 1, 2, 2.5 or 5 times a power of ten (at least RM 1,000).
     */
    private function niceCeiling(float $value): float
    {
        if ($value <= 1000) {
            return 1000;
        }

        $magnitude = 10 ** floor(log10($value));

        foreach ([1, 2, 2.5, 5, 10] as $step) {
            if ($value <= $step * $magnitude) {
                return $step * $magnitude;
            }
        }

        return 10 * $magnitude;
    }

    private function shortMoney(float $amount): string
    {
        return match (true) {
            $amount >= 1_000_000 => 'RM '.rtrim(rtrim(number_format($amount / 1_000_000, 2), '0'), '.').'m',
            $amount >= 1000 => 'RM '.rtrim(rtrim(number_format($amount / 1000, 1), '0'), '.').'k',
            default => 'RM '.number_format($amount),
        };
    }
}
