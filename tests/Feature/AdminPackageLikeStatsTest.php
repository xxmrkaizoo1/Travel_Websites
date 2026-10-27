<?php

namespace Tests\Feature;

use App\Models\ChinaMuslimPackage;
use App\Models\PackageLike;
use App\Models\User;
use App\Support\PackageLikes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AdminPackageLikeStatsTest extends TestCase
{
    use RefreshDatabase;

    private const DA_NANG = 'tour-package/series-departures-2026#da-nang';

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-10-27 12:00'));
    }

    public function test_admin_sees_totals_trend_and_every_package_ranked_by_likes(): void
    {
        $guilin = ChinaMuslimPackage::factory()->create(['title' => 'GUILIN YANGSHUO TEST TOUR']);
        $this->like(self::DA_NANG, 'guest:a', now()->subDays(20));
        $this->like(self::DA_NANG, 'guest:b', now()->subDays(2));
        $this->like(self::DA_NANG, 'user:7', now()->subHour());
        $this->like(PackageLikes::key($guilin), 'guest:c', now()->subDays(40));

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.package-likes'))
            ->assertOk()
            ->assertSee('Package likes')
            ->assertViewHas('summary', fn (array $summary): bool => $summary['total'] === 4 && $summary['week'] === 2 && $summary['loved'] === 2 && $summary['memberShare'] === 25)
            ->assertViewHas('rows', function ($rows) use ($guilin): bool {
                $daNang = $rows->firstWhere('key', self::DA_NANG);
                $china = $rows->firstWhere('key', PackageLikes::key($guilin));

                return $rows->first()['key'] === self::DA_NANG
                    && [$daNang['total'], $daNang['week'], $daNang['month'], $daNang['members']] === [3, 2, 3, 1]
                    && [$china['total'], $china['week'], $china['month']] === [1, 0, 0]
                    && $rows->where('total', 0)->isNotEmpty();
            })
            ->assertViewHas('daily', fn (array $daily): bool => count($daily) === 14
                && $daily[13]['likes'] === 1
                && $daily[11]['likes'] === 1
                && array_sum(array_column($daily, 'likes')) === 2);
    }

    public function test_packages_can_be_filtered_by_group_and_sorted_by_recent_likes(): void
    {
        $china = ChinaMuslimPackage::factory()->create();
        $this->like(self::DA_NANG, 'guest:a', now()->subDays(3));
        $this->like(PackageLikes::key($china), 'guest:b', now()->subMinute());
        $this->actingAs(User::factory()->admin()->create());

        $this->get(route('admin.package-likes', ['group' => 'china']))
            ->assertViewHas('rows', fn ($rows): bool => $rows->pluck('group')->unique()->all() === ['china']);

        $this->get(route('admin.package-likes', ['sort' => 'recent']))
            ->assertViewHas('rows', fn ($rows): bool => $rows->take(2)->pluck('key')->all() === [PackageLikes::key($china), self::DA_NANG]);

        $this->get(route('admin.package-likes', ['sort' => 'nonsense', 'group' => 'nonsense']))
            ->assertViewHas('sort', 'total')
            ->assertViewHas('group', 'all');
    }

    public function test_dashboard_lists_the_most_loved_packages(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertSee('Most loved packages')
            ->assertSee('No likes yet.');

        $this->like(self::DA_NANG, 'guest:a', now());

        $this->get(route('admin.dashboard'))
            ->assertViewHas('lovedPackages', fn ($loved): bool => $loved->count() === 1 && $loved->first()['name'] === 'Da Nang' && $loved->first()['total'] === 1);
    }

    public function test_only_admins_can_see_like_statistics(): void
    {
        $this->get(route('admin.package-likes'))->assertRedirect();

        $this->actingAs(User::factory()->create())
            ->get(route('admin.package-likes'))
            ->assertForbidden();
    }

    private function like(string $key, string $visitor, Carbon $at): void
    {
        PackageLike::query()->create(['package_key' => $key, 'visitor_key' => $visitor])
            ->forceFill(['created_at' => $at, 'updated_at' => $at])
            ->save();
    }
}
