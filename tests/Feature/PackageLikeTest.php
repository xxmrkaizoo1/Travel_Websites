<?php

namespace Tests\Feature;

use App\Http\Controllers\PackageLikeController;
use App\Models\ChinaMuslimPackage;
use App\Models\PackageLike;
use App\Models\User;
use App\Support\PackageLikes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PackageLikeTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_likes_and_unlikes_a_package_and_is_remembered_by_cookie(): void
    {
        $key = PackageLikes::key(ChinaMuslimPackage::factory()->create());

        $response = $this->postJson(route('package-likes.toggle'), ['key' => $key])
            ->assertOk()
            ->assertExactJson(['count' => 1, 'liked' => true])
            ->assertCookie(PackageLikeController::GUEST_COOKIE);
        $guestToken = $response->getCookie(PackageLikeController::GUEST_COOKIE)->getValue();

        $this->withCredentials()->withCookie(PackageLikeController::GUEST_COOKIE, $guestToken)
            ->getJson(route('package-likes.index', ['keys' => [$key]]))
            ->assertExactJson([$key => ['count' => 1, 'liked' => true]]);

        $this->withCredentials()->withCookie(PackageLikeController::GUEST_COOKIE, $guestToken)
            ->postJson(route('package-likes.toggle'), ['key' => $key])
            ->assertExactJson(['count' => 0, 'liked' => false]);

        $this->assertDatabaseCount('package_likes', 0);
    }

    public function test_each_visitor_counts_once_and_signed_in_users_like_as_their_account(): void
    {
        $key = PackageLikes::key(ChinaMuslimPackage::factory()->create());
        PackageLike::query()->create(['package_key' => $key, 'visitor_key' => 'guest:'.Str::uuid()]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('package-likes.toggle'), ['key' => $key])
            ->assertExactJson(['count' => 2, 'liked' => true])
            ->assertCookieMissing(PackageLikeController::GUEST_COOKIE);

        $this->assertDatabaseHas('package_likes', ['package_key' => $key, 'visitor_key' => 'user:'.$user->id]);

        $this->actingAs(User::factory()->create())
            ->getJson(route('package-likes.index', ['keys' => [$key]]))
            ->assertExactJson([$key => ['count' => 2, 'liked' => false]]);
    }

    public function test_likes_are_only_accepted_for_real_packages_and_destinations(): void
    {
        $chinaPackage = ChinaMuslimPackage::factory()->create();

        foreach ([
            'china-muslim-package/'.($chinaPackage->id + 100),
            'china-muslim-package/'.$chinaPackage->id.'#extra',
            'tour-package/does-not-exist',
            'tour-package/series-departures-2026#atlantis',
            'user/1',
        ] as $key) {
            $this->postJson(route('package-likes.toggle'), ['key' => $key])->assertJsonValidationErrors('key');
        }

        $this->assertDatabaseCount('package_likes', 0);
    }

    public function test_like_buttons_appear_on_smart_muslim_packages(): void
    {
        $chinaPackage = ChinaMuslimPackage::factory()->create();

        $this->get(route('china-muslim'))
            ->assertSee('data-like-key="'.PackageLikes::key($chinaPackage).'"', false);

        $this->get(route('home'))
            ->assertSee('data-like-key="tour-package/series-departures-2026#', false);

        $this->get(route('tour-package.show', 'series-departures-2026'))
            ->assertSee('data-like-key="tour-package/series-departures-2026#', false);

        $this->postJson(route('package-likes.toggle'), ['key' => 'tour-package/series-departures-2026#da-nang'])
            ->assertOk()
            ->assertJson(['liked' => true]);
    }
}
